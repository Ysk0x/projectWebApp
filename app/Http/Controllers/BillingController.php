<?php

namespace App\Http\Controllers;

use App\Support\VetHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /** ปุ่มใน UI => ค่าใน DB */
    private const METHODS = [
        'cash'     => ['db' => 'cash',         'label' => 'เงินสด'],
        'transfer' => ['db' => 'qr_promptpay', 'label' => 'โอนเงิน (QR)'],
        'card'     => ['db' => 'credit_card',  'label' => 'บัตรเครดิต'],
    ];

    /** READ: ไม่มี ?invoice= -> รายการรอชำระ / มี -> หน้าชำระเงิน + ใบเสร็จ */
    public function index(Request $request)
    {
        $invoiceId = $request->query('invoice');

        if (! $invoiceId) {
            return view('vetcare.staff.billing', [
                'thaiDate' => VetHelper::thaiDate(),
                'invoice'  => null,
                'unpaid'   => $this->listQuery()->where('i.status', 'unpaid')->orderBy('i.invoice_date', 'desc')->get(),
                'recent'   => $this->listQuery()->where('i.status', 'paid')->orderBy('i.invoice_date', 'desc')->limit(5)->get(),
            ]);
        }

        $invoice = DB::table('invoices as i')
            ->join('owners as o', 'o.owner_id', '=', 'i.owner_id')
            ->join('pets as p', 'p.pet_id', '=', 'i.pet_id')
            ->where('i.invoice_id', $invoiceId)
            ->select('i.*', 'o.first_name', 'o.last_name', 'o.phone', 'p.pet_name', 'p.species', 'p.breed')
            ->first();
        abort_unless($invoice, 404);

        $treatment = $invoice->treatment_id
            ? DB::table('treatments as t')->leftJoin('users as u', 'u.user_id', '=', 't.staff_id')
                ->where('t.treatment_id', $invoice->treatment_id)->select('t.*', 'u.full_name as staff_name')->first()
            : null;

        $items = DB::table('invoice_items')->where('invoice_id', $invoice->invoice_id)->orderBy('invoice_item_id')->get();

        $payment = DB::table('payments as py')->join('users as u', 'u.user_id', '=', 'py.received_by')
            ->where('py.invoice_id', $invoice->invoice_id)->orderBy('py.payment_date', 'desc')
            ->select('py.*', 'u.full_name as receiver')->first();

        $isPaid = $invoice->status === 'paid';

        // วิธีชำระ: ถ้าจ่ายแล้วใช้ตามจริง ไม่งั้นใช้ที่เลือกใน URL
        $method = array_key_exists($request->query('method'), self::METHODS) ? $request->query('method') : 'cash';
        if ($payment) {
            $method = collect(self::METHODS)->search(fn ($m) => $m['db'] === $payment->payment_method) ?: $method;
        }

        $serviceTotal  = (float) $items->where('item_type', 'service')->sum('total_price');
        $medicineTotal = (float) $items->where('item_type', 'medicine')->sum('total_price');

        return view('vetcare.staff.billing', [
            'thaiDate'      => VetHelper::thaiDate(),
            'invoice'       => $invoice,
            'treatment'     => $treatment,
            'items'         => $items,
            'payment'       => $payment,
            'owner'         => VetHelper::fullName($invoice->first_name, $invoice->last_name),
            'invoiceDate'   => VetHelper::thaiShort($invoice->invoice_date),
            'method'        => $method,
            'methodName'    => self::METHODS[$method]['label'],
            'isPaid'        => $isPaid,
            'isCancelled'   => $invoice->status === 'cancelled',
            'printMode'     => $request->query('print') === '1',
            'serviceTotal'  => $serviceTotal,
            'medicineTotal' => $medicineTotal,
            'discount'      => (float) ($invoice->discount ?? 0),
            'grandTotal'    => (float) $invoice->total_amount,
            'methods'       => self::METHODS,
        ]);
    }

    /** CREATE payment + อัปเดตใบเสร็จเป็น paid + audit + ปิดนัดหมาย */
    public function pay(Request $request, string $id)
    {
        $data = $request->validate([
            'method'       => ['required', 'in:cash,transfer,card'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ], ['method.required' => 'กรุณาเลือกวิธีชำระเงิน']);

        $invoice = DB::table('invoices')->where('invoice_id', $id)->first();
        abort_unless($invoice, 404);

        if ($invoice->status !== 'unpaid') {
            return redirect()->route('vetcare.staff.billing', ['invoice' => $id])
                ->with('error', $invoice->status === 'paid' ? 'ใบเสร็จนี้ชำระเงินแล้ว' : 'ใบเสร็จนี้ถูกยกเลิก ไม่สามารถรับชำระได้');
        }

        $userId = VetHelper::userId($request->user());
        abort_unless($userId, 403);

        DB::transaction(function () use ($invoice, $data, $userId) {
            $paymentId = VetHelper::nextId('payments', 'payment_id', 'PY', 4);
            $refNo = $data['reference_no'] ?: 'PAY-' . now()->format('Ymd') . '-' . substr($paymentId, 2);

            DB::table('payments')->insert([
                'payment_id'     => $paymentId,
                'invoice_id'     => $invoice->invoice_id,
                'received_by'    => $userId,
                'payment_date'   => now(),
                'amount'         => $invoice->total_amount,
                'payment_method' => self::METHODS[$data['method']]['db'],
                'payment_status' => 'completed',
                'reference_no'   => $refNo,
                'notes'          => $data['notes'] ?? null,
            ]);

            DB::table('invoices')->where('invoice_id', $invoice->invoice_id)
                ->update(['status' => 'paid', 'updated_at' => now()]);

            DB::table('invoice_audits')->insert([
                'audit_id'   => VetHelper::nextId('invoice_audits', 'audit_id', 'IA', 4),
                'invoice_id' => $invoice->invoice_id,
                'audited_by' => $userId,
                'action'     => 'status_changed',
                'old_status' => 'unpaid',
                'new_status' => 'paid',
                'remarks'    => 'รับชำระด้วย ' . self::METHODS[$data['method']]['label'],
                'audited_at' => now(),
            ]);

            // ปิดนัดหมายที่ผูกกับการรักษานี้
            $apptId = DB::table('treatments')->where('treatment_id', $invoice->treatment_id)->value('appointment_id');
            if ($apptId) {
                DB::table('appointments')->where('appointment_id', $apptId)
                    ->update(['status' => 'completed', 'updated_at' => now()]);
            }
        });

        return redirect()->route('vetcare.staff.billing', ['invoice' => $id])
            ->with('success', 'บันทึกการชำระเงินเรียบร้อยแล้ว');
    }

    private function listQuery()
    {
        return DB::table('invoices as i')
            ->join('owners as o', 'o.owner_id', '=', 'i.owner_id')
            ->join('pets as p', 'p.pet_id', '=', 'i.pet_id')
            ->select('i.*', 'o.first_name', 'o.last_name', 'p.pet_name');
    }
}