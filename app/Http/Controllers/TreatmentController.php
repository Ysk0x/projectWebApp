<?php

namespace App\Http\Controllers;

use App\Support\VetHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TreatmentController extends Controller
{
    /** READ: หน้าบันทึกการรักษา (สร้างใหม่ ?appointment= / ?pet= หรือแก้ไข ?edit=) */
    public function index(Request $request)
    {
        $editing = null;
        $appointment = null;

        if ($request->query('edit')) {
            $editing = DB::table('treatments')->where('treatment_id', $request->query('edit'))->first();
            abort_unless($editing, 404);
            $petId = $editing->pet_id;
        } elseif ($request->query('appointment')) {
            $appointment = DB::table('appointments')->where('appointment_id', $request->query('appointment'))->first();
            abort_unless($appointment, 404);

            $existing = DB::table('treatments')->where('appointment_id', $appointment->appointment_id)->first();
            if ($existing) {
                return redirect()->route('vetcare.staff.treatments', ['edit' => $existing->treatment_id])
                    ->with('error', 'นัดหมายนี้มีบันทึกการรักษาแล้ว จึงเปิดโหมดแก้ไขให้แทน');
            }
            $petId = $appointment->pet_id;
        } else {
            $petId = $request->query('pet');
        }

        $pet = $petId
            ? DB::table('pets as p')->join('owners as o', 'o.owner_id', '=', 'p.owner_id')
                ->where('p.pet_id', $petId)
                ->select('p.*', 'o.first_name', 'o.last_name', 'o.phone')->first()
            : null;

        $petList = $pet ? collect() : DB::table('pets as p')->join('owners as o', 'o.owner_id', '=', 'p.owner_id')
            ->orderBy('p.pet_name')->select('p.pet_id', 'p.pet_name', 'o.first_name', 'o.last_name')->get();

        $histories = $pet ? DB::table('treatments as t')
            ->leftJoin('invoices as i', 'i.treatment_id', '=', 't.treatment_id')
            ->where('t.pet_id', $pet->pet_id)
            ->orderBy('t.treatment_date', 'desc')->limit(5)
            ->select('t.*', 'i.status as invoice_status')->get() : collect();

        return view('vetcare.staff.treatments', [
            'thaiDate'    => VetHelper::thaiDate(),
            'pet'         => $pet,
            'petList'     => $petList,
            'appointment' => $appointment,
            'editing'     => $editing,
            'histories'   => $histories,
            'medicines'   => DB::table('medicines')->where('status', 'active')->where('stock_quantity', '>', 0)->orderBy('medicine_name')->get(),
            'staffList'   => DB::table('users')->where('status', 'active')->whereIn('role', ['staff', 'vet'])->orderBy('full_name')->get(),
            'currentUserId' => VetHelper::userId($request->user()),
        ]);
    }

    /** CREATE: treatment + treatment_medicines + ตัดสต็อก + สร้างใบเสร็จ (unpaid) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'pet_id'          => ['required', 'exists:pets,pet_id'],
            'appointment_id'  => ['nullable', 'exists:appointments,appointment_id'],
            'treatment_date'  => ['required', 'date'],
            'staff_id'        => ['required', 'exists:users,user_id'],
            'symptoms'        => ['required', 'string', 'max:1000'],
            'diagnosis'       => ['nullable', 'string', 'max:1000'],
            'treatment_notes' => ['nullable', 'string', 'max:2000'],
            'service_desc'    => ['nullable', 'string', 'max:255'],
            'service_fee'     => ['required', 'numeric', 'min:0', 'max:9999999'],
            'meds'            => ['nullable', 'array'],
            'meds.*.medicine_id' => ['nullable', 'exists:medicines,medicine_id'],
            'meds.*.quantity'    => ['nullable', 'integer', 'min:1', 'max:100000'],
            'meds.*.directions'  => ['nullable', 'string', 'max:500'],
        ], [
            'symptoms.required'    => 'กรุณาระบุอาการ',
            'service_fee.required' => 'กรุณากรอกค่ารักษา (ใส่ 0 ได้)',
            'meds.*.quantity.min'  => 'จำนวนยาต้องมากกว่า 0',
        ]);

        // รวมแถวยาที่เลือกไว้ (ยาซ้ำรวมจำนวนให้)
        $lines = [];
        foreach ($data['meds'] ?? [] as $row) {
            if (empty($row['medicine_id'])) {
                continue;
            }
            if (empty($row['quantity'])) {
                throw ValidationException::withMessages(['meds' => 'กรุณาระบุจำนวนยาให้ครบทุกรายการที่เลือก']);
            }
            $id = $row['medicine_id'];
            $lines[$id]['qty'] = ($lines[$id]['qty'] ?? 0) + (int) $row['quantity'];
            $lines[$id]['directions'] = $lines[$id]['directions'] ?? ($row['directions'] ?? null);
        }

        $pet = DB::table('pets')->where('pet_id', $data['pet_id'])->first();

        if (! empty($data['appointment_id'])) {
            $appt = DB::table('appointments')->where('appointment_id', $data['appointment_id'])->first();
            if ($appt->pet_id !== $pet->pet_id) {
                throw ValidationException::withMessages(['pet_id' => 'นัดหมายไม่ตรงกับสัตว์เลี้ยงที่เลือก']);
            }
            if (DB::table('treatments')->where('appointment_id', $appt->appointment_id)->exists()) {
                throw ValidationException::withMessages(['appointment_id' => 'นัดหมายนี้มีบันทึกการรักษาแล้ว']);
            }
        }

        $creatorId = VetHelper::userId($request->user());
        $invoiceId = null;

        DB::transaction(function () use ($data, $lines, $pet, $creatorId, &$invoiceId) {
            $treatmentId = VetHelper::nextId('treatments', 'treatment_id', 'T', 4);

            DB::table('treatments')->insert([
                'treatment_id'    => $treatmentId,
                'pet_id'          => $pet->pet_id,
                'staff_id'        => $data['staff_id'],
                'appointment_id'  => $data['appointment_id'] ?? null,
                'treatment_date'  => \Carbon\Carbon::parse($data['treatment_date'] . ' ' . now()->format('H:i:s')),
                'symptoms'        => $data['symptoms'],
                'diagnosis'       => $data['diagnosis'] ?? null,
                'treatment_notes' => $data['treatment_notes'] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $fee      = round((float) $data['service_fee'], 2);
            $subtotal = $fee;
            $tmRows   = [];
            $itemRows = [];

            foreach ($lines as $medicineId => $line) {
                $med = DB::table('medicines')->where('medicine_id', $medicineId)->first();

                if ($med->status !== 'active') {
                    throw ValidationException::withMessages(['meds' => "ยา {$med->medicine_name} ปิดการใช้งานแล้ว"]);
                }
                if ($med->stock_quantity < $line['qty']) {
                    throw ValidationException::withMessages([
                        'meds' => "ยา {$med->medicine_name} คงเหลือไม่พอ (เหลือ {$med->stock_quantity} {$med->unit})",
                    ]);
                }

                $price = (float) ($med->selling_price ?? 0);
                $total = round($price * $line['qty'], 2);
                $subtotal += $total;

                $tmRows[] = [$medicineId, $line, $price, $total, $med];

                DB::table('medicines')->where('medicine_id', $medicineId)->update([
                    'stock_quantity' => DB::raw('stock_quantity - ' . (int) $line['qty']),
                    'updated_at'     => now(),
                ]);
            }

            // treatment_medicines (รหัส TM + 5 หลัก)
            $tmNum = (int) substr((string) (DB::table('treatment_medicines')->where('treatment_medicine_id', 'like', 'TM%')->max('treatment_medicine_id') ?? 'TM00000'), 2);
            foreach ($tmRows as [$medicineId, $line, $price, $total, $med]) {
                DB::table('treatment_medicines')->insert([
                    'treatment_medicine_id' => 'TM' . str_pad((string) ++$tmNum, 5, '0', STR_PAD_LEFT),
                    'treatment_id'          => $treatmentId,
                    'medicine_id'           => $medicineId,
                    'quantity'              => $line['qty'],
                    'dosage_instruction'    => $line['directions'],
                    'unit_price'            => $price,
                    'total_price'           => $total,
                ]);
            }

            // ไม่มีค่าใช้จ่ายเลย -> ไม่ต้องออกใบเสร็จ ปิดนัดหมายเลย
            if ($subtotal <= 0) {
                if (! empty($data['appointment_id'])) {
                    DB::table('appointments')->where('appointment_id', $data['appointment_id'])
                        ->update(['status' => 'completed', 'updated_at' => now()]);
                }
                return;
            }

            $invoiceId = VetHelper::nextId('invoices', 'invoice_id', 'I', 4);
            DB::table('invoices')->insert([
                'invoice_id'     => $invoiceId,
                'invoice_number' => VetHelper::nextInvoiceNumber(),
                'owner_id'       => $pet->owner_id,
                'pet_id'         => $pet->pet_id,
                'treatment_id'   => $treatmentId,
                'created_by'     => $creatorId,
                'invoice_date'   => now(),
                'subtotal'       => $subtotal,
                'discount'       => 0,
                'total_amount'   => $subtotal,
                'status'         => 'unpaid',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $iiNum = (int) substr((string) (DB::table('invoice_items')->where('invoice_item_id', 'like', 'II%')->max('invoice_item_id') ?? 'II00000'), 2);
            $nextItemId = fn () => 'II' . str_pad((string) ++$iiNum, 5, '0', STR_PAD_LEFT);

            if ($fee > 0) {
                DB::table('invoice_items')->insert([
                    'invoice_item_id' => $nextItemId(),
                    'invoice_id'      => $invoiceId,
                    'item_type'       => 'service',
                    'medicine_id'     => null,
                    'description'     => $data['service_desc'] ?: 'ค่ารักษา',
                    'quantity'        => 1,
                    'unit_price'      => $fee,
                    'total_price'     => $fee,
                ]);
            }
            foreach ($tmRows as [$medicineId, $line, $price, $total, $med]) {
                DB::table('invoice_items')->insert([
                    'invoice_item_id' => $nextItemId(),
                    'invoice_id'      => $invoiceId,
                    'item_type'       => 'medicine',
                    'medicine_id'     => $medicineId,
                    'description'     => $med->medicine_name,
                    'quantity'        => $line['qty'],
                    'unit_price'      => $price,
                    'total_price'     => $total,
                ]);
            }
        });

        if ($invoiceId) {
            return redirect()->route('vetcare.staff.billing', ['invoice' => $invoiceId])
                ->with('success', 'บันทึกการรักษาและตัดสต็อกยาเรียบร้อยแล้ว กรุณารับชำระเงิน');
        }

        return redirect()->route('vetcare.staff.appointments')->with('success', 'บันทึกการรักษาเรียบร้อยแล้ว');
    }

    /** UPDATE: แก้เฉพาะข้อความบันทึก (ไม่แตะยา/สต็อก/ใบเสร็จ เพื่อไม่ให้ยอดเงินเพี้ยน) */
    public function update(Request $request, string $id)
    {
        abort_unless(DB::table('treatments')->where('treatment_id', $id)->exists(), 404);

        $data = $request->validate([
            'staff_id'        => ['required', 'exists:users,user_id'],
            'symptoms'        => ['required', 'string', 'max:1000'],
            'diagnosis'       => ['nullable', 'string', 'max:1000'],
            'treatment_notes' => ['nullable', 'string', 'max:2000'],
        ], ['symptoms.required' => 'กรุณาระบุอาการ']);

        DB::table('treatments')->where('treatment_id', $id)->update($data + ['updated_at' => now()]);

        $pet = DB::table('treatments')->where('treatment_id', $id)->value('pet_id');

        return redirect()->route('vetcare.staff.treatments', ['pet' => $pet])->with('success', 'แก้ไขบันทึกการรักษาเรียบร้อยแล้ว');
    }
}