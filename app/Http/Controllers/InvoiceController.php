<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    private const PAYMENT_LABELS = [
        'cash'         => 'เงินสด',
        'qr_promptpay' => 'โอนเงิน (QR)',
        'credit_card'  => 'บัตรเครดิต',
    ];

    /** DB status => key ที่ blade/CSS ใช้อยู่เดิม */
    private const STATUS_MAP = [
        'paid'      => 'paid',
        'unpaid'    => 'pending',
        'cancelled' => 'void',
    ];

    /** READ: รายการ + รายละเอียด + modal void */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = $this->baseQuery();
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('i.invoice_number', 'like', $like)
                  ->orWhere('o.first_name', 'like', $like)
                  ->orWhere('o.last_name', 'like', $like)
                  ->orWhere('p.pet_name', 'like', $like)
                  ->orWhere('u.full_name', 'like', $like);
            });
        }

        $rows = $query->orderBy('i.invoice_date', 'desc')->orderBy('i.invoice_id', 'desc')->get();
        $invoices = $this->present($rows);

        // Summary (ทั้งระบบ ไม่ขึ้นกับคำค้นหา)
        $totalInvoices     = DB::table('invoices')->count();
        $totalRevenue      = (float) DB::table('invoices')->where('status', 'paid')->sum('total_amount');
        $paidCount         = DB::table('invoices')->where('status', 'paid')->count();
        $voidCount         = DB::table('invoices')->where('status', 'cancelled')->count();
        $auditWaitingCount = DB::table('invoices as i')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('invoice_audits as a')->whereColumn('a.invoice_id', 'i.invoice_id');
            })->count();

        // Panel
        $panel = $request->query('panel');
        $selectedInvoice = null;
        $items = collect();
        $audits = collect();

        if (in_array($panel, ['detail', 'void'], true)) {
            $row = $this->baseQuery()->where('i.invoice_id', $request->query('id'))->first();
            if ($row) {
                $selectedInvoice = $this->present(collect([$row]))[0];
                $selectedInvoice['subtotal'] = (float) $row->subtotal;
                $selectedInvoice['discount'] = (float) ($row->discount ?? 0);

                $items = DB::table('invoice_items')->where('invoice_id', $row->invoice_id)->orderBy('invoice_item_id')->get();
                $audits = DB::table('invoice_audits as a')
                    ->join('users as u', 'u.user_id', '=', 'a.audited_by')
                    ->where('a.invoice_id', $row->invoice_id)
                    ->orderBy('a.audited_at', 'desc')
                    ->select('a.*', 'u.full_name as auditor')
                    ->get();
            }
        }

        return view('vetcare.manager.invoices', compact(
            'invoices', 'search', 'panel', 'selectedInvoice', 'items', 'audits',
            'totalInvoices', 'totalRevenue', 'paidCount', 'auditWaitingCount', 'voidCount'
        ));
    }

    /** UPDATE (สถานะ -> cancelled) + บันทึก invoice_audits */
    public function void(Request $request, string $id)
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:500'],
        ], ['remarks.max' => 'หมายเหตุต้องไม่เกิน 500 ตัวอักษร']);

        $invoice = DB::table('invoices')->where('invoice_id', $id)->first();
        abort_unless($invoice, 404);

        if ($invoice->status === 'cancelled') {
            return redirect()->route('vetcare.manager.invoices')->with('error', 'ใบเสร็จนี้ถูกยกเลิกไปแล้ว');
        }

        $auditorId = DB::table('users')->where('email', $request->user()->email)->value('user_id');
        abort_unless($auditorId, 403);

        DB::transaction(function () use ($invoice, $auditorId, $request) {
            DB::table('invoices')->where('invoice_id', $invoice->invoice_id)->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

            $max = DB::table('invoice_audits')->where('audit_id', 'like', 'IA%')->max('audit_id');
            $n = $max ? (int) substr($max, 2) : 0;

            DB::table('invoice_audits')->insert([
                'audit_id'   => 'IA' . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->invoice_id,
                'audited_by' => $auditorId,
                'action'     => 'status_changed',
                'old_status' => $invoice->status,
                'new_status' => 'cancelled',
                'remarks'    => $request->input('remarks') ?: 'ผู้จัดการยกเลิกบิล',
                'audited_at' => now(),
            ]);
        });

        return redirect()->route('vetcare.manager.invoices')
            ->with('success', 'ยกเลิกใบเสร็จ ' . $invoice->invoice_number . ' เรียบร้อยแล้ว');
    }

    /* ---------------------------------------------------------- */

    private function baseQuery()
    {
        return DB::table('invoices as i')
            ->join('owners as o', 'o.owner_id', '=', 'i.owner_id')
            ->join('pets as p', 'p.pet_id', '=', 'i.pet_id')
            ->join('users as u', 'u.user_id', '=', 'i.created_by')
            ->select(
                'i.*',
                DB::raw("o.first_name || ' ' || o.last_name as owner_name"),
                'p.pet_name',
                'u.full_name as creator_name'
            );
    }

    /** แปลง row จาก DB -> array ที่ blade ใช้ */
    private function present($rows)
    {
        $ids = $rows->pluck('invoice_id')->all();

        $payments = DB::table('payments as py')
            ->join('users as u', 'u.user_id', '=', 'py.received_by')
            ->whereIn('py.invoice_id', $ids)
            ->orderBy('py.payment_date', 'desc')
            ->select('py.invoice_id', 'py.payment_method', 'u.full_name as receiver')
            ->get()
            ->unique('invoice_id')
            ->keyBy('invoice_id');

        $audited = DB::table('invoice_audits')->whereIn('invoice_id', $ids)->distinct()->pluck('invoice_id')->flip();

        return $rows->map(function ($r) use ($payments, $audited) {
            $pay = $payments->get($r->invoice_id);

            return [
                'id'      => $r->invoice_id,
                'number'  => $r->invoice_number,
                'date'    => $this->thaiDate($r->invoice_date),
                'staff'   => $pay->receiver ?? $r->creator_name,
                'owner'   => $r->owner_name,
                'pet'     => $r->pet_name,
                'amount'  => (float) $r->total_amount,
                'payment' => $pay ? (self::PAYMENT_LABELS[$pay->payment_method] ?? $pay->payment_method) : '-',
                'status'  => self::STATUS_MAP[$r->status] ?? $r->status,
                'raw_status' => $r->status,
                'audit'   => $audited->has($r->invoice_id) ? 'done' : 'waiting',
            ];
        })->values()->all();
    }

    private function thaiDate($value): string
    {
        $d = Carbon::parse($value);

        return $d->format('d/m/') . ($d->year + 543);
    }
}