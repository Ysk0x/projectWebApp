<?php

namespace App\Http\Controllers;

use App\Support\VetHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public const SERVICES = ['ตรวจทั่วไป', 'ตรวจสุขภาพ', 'ฉีดวัคซีน', 'ตรวจรักษาโรค', 'รักษาโรคผิวหนัง', 'ตรวจฟัน', 'ทำหมัน', 'ทำแผล/ตัดไหม', 'ติดตามอาการ'];

    /** READ: รายการ / ปฏิทิน / panel (create, edit, cancel) */
    public function index(Request $request)
    {
        $today    = Carbon::now('Asia/Bangkok');
        $todayYmd = $today->format('Y-m-d');

        $view   = $request->query('view') === 'calendar' ? 'calendar' : 'list';
        $search = trim((string) $request->query('search', ''));

        $statusKeys = ['all', 'confirmed', 'waiting', 'serving', 'done', 'cancelled'];
        $status = in_array($request->query('status'), $statusKeys, true) ? $request->query('status') : 'all';

        $selectedDate = $request->query('date');
        if ($selectedDate) {
            $d = \DateTime::createFromFormat('!Y-m-d', $selectedDate);
            if (! $d || $d->format('Y-m-d') !== $selectedDate) {
                $selectedDate = null;
            }
        }

        $query = $this->baseQuery();
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('a.appointment_id', 'like', $like)
                  ->orWhere('p.pet_name', 'like', $like)
                  ->orWhere('o.first_name', 'like', $like)
                  ->orWhere('o.last_name', 'like', $like)
                  ->orWhere('a.service_type', 'like', $like);
            });
        }
        if ($selectedDate) {
            $query->where('a.appointment_date', $selectedDate);
        }

        $filtered = $this->present($query->orderBy('a.appointment_date', 'desc')->orderBy('a.appointment_time')->get(), $todayYmd);
        if ($status !== 'all') {
            $filtered = array_values(array_filter($filtered, fn ($a) => $a['status'] === $status));
        }

        // panel
        $panel = $request->query('panel');
        $editing = null;
        if (in_array($panel, ['edit', 'cancel'], true)) {
            $row = $this->baseQuery()->where('a.appointment_id', $request->query('id'))->first();
            $editing = $row ? $this->present(collect([$row]), $todayYmd)[0] : null;
        }

        $pets = $staffList = [];
        if ($panel === 'create' || ($panel === 'edit' && $editing)) {
            $pets = DB::table('pets as p')->join('owners as o', 'o.owner_id', '=', 'p.owner_id')
                ->orderBy('p.pet_name')->select('p.pet_id', 'p.pet_name', 'o.first_name', 'o.last_name')->get();
            $staffList = $this->staffList();
        }

        $appointmentDates = DB::table('appointments')->where('status', '!=', 'cancelled')
            ->pluck('appointment_date')->map(fn ($d) => substr($d, 0, 10))->unique()->values()->all();

        return view('vetcare.staff.appointments', [
            'thaiDate'         => VetHelper::thaiDate($today),
            'todayYmd'         => $todayYmd,
            'view'             => $view,
            'search'           => $search,
            'status'           => $status,
            'selectedDate'     => $selectedDate,
            'filtered'         => $filtered,
            'panel'            => $panel,
            'editing'          => $editing,
            'pets'             => $pets,
            'staffList'        => $staffList,
            'services'         => self::SERVICES,
            'appointmentDates' => $appointmentDates,
            'monthInput'       => $request->query('month'),
        ]);
    }

    /** CREATE */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());
        $this->assertNoConflict($data);

        $pet = DB::table('pets')->where('pet_id', $data['pet_id'])->first();

        DB::table('appointments')->insert([
            'appointment_id'   => VetHelper::nextId('appointments', 'appointment_id', 'A', 4),
            'pet_id'           => $pet->pet_id,
            'owner_id'         => $pet->owner_id,
            'staff_id'         => $data['staff_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'] . ':00',
            'service_type'     => $data['service_type'],
            'notes'            => $data['notes'] ?? null,
            'status'           => 'scheduled',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('vetcare.staff.appointments')->with('success', 'สร้างนัดหมายเรียบร้อยแล้ว');
    }

    /** UPDATE */
    public function update(Request $request, string $id)
    {
        abort_unless(DB::table('appointments')->where('appointment_id', $id)->exists(), 404);

        $data = $request->validate($this->rules() + [
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
        ], $this->messages());
        if ($data['status'] === 'scheduled') {
            $this->assertNoConflict($data, $id);
        }

        $pet = DB::table('pets')->where('pet_id', $data['pet_id'])->first();

        DB::table('appointments')->where('appointment_id', $id)->update([
            'pet_id'           => $pet->pet_id,
            'owner_id'         => $pet->owner_id,
            'staff_id'         => $data['staff_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'] . ':00',
            'service_type'     => $data['service_type'],
            'notes'            => $data['notes'] ?? null,
            'status'           => $data['status'],
            'updated_at'       => now(),
        ]);

        return redirect()->route('vetcare.staff.appointments')->with('success', 'บันทึกการแก้ไขนัดหมายเรียบร้อยแล้ว');
    }

    /** DELETE (ยกเลิกนัด = เปลี่ยนสถานะเป็น cancelled เพื่อเก็บประวัติ) */
    public function cancel(string $id)
    {
        $appt = DB::table('appointments')->where('appointment_id', $id)->first();
        abort_unless($appt, 404);

        if ($appt->status !== 'scheduled') {
            return redirect()->route('vetcare.staff.appointments')->with('error', 'ยกเลิกได้เฉพาะนัดหมายที่ยังไม่เสร็จสิ้น');
        }

        DB::table('appointments')->where('appointment_id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);

        return redirect()->route('vetcare.staff.appointments')->with('success', 'ยกเลิกนัดหมาย ' . $id . ' เรียบร้อยแล้ว');
    }

    /* ---------------------------------------------------------- */

    private function baseQuery()
    {
        return DB::table('appointments as a')
            ->join('pets as p', 'p.pet_id', '=', 'a.pet_id')
            ->join('owners as o', 'o.owner_id', '=', 'a.owner_id')
            ->join('users as u', 'u.user_id', '=', 'a.staff_id')
            ->select(
                'a.*', 'p.pet_name', 'o.first_name', 'o.last_name', 'u.full_name as staff_name',
                DB::raw('EXISTS(select 1 from treatments t where t.appointment_id = a.appointment_id) as has_treatment')
            );
    }

    private function present($rows, string $todayYmd): array
    {
        return $rows->map(fn ($r) => [
            'id'         => $r->appointment_id,
            'date'       => substr($r->appointment_date, 0, 10),
            'time'       => substr($r->appointment_time, 0, 5),
            'owner'      => VetHelper::fullName($r->first_name, $r->last_name),
            'pet'        => $r->pet_name,
            'pet_id'     => $r->pet_id,
            'service'    => $r->service_type,
            'staff'      => $r->staff_name,
            'staff_id'   => $r->staff_id,
            'notes'      => $r->notes,
            'raw_status' => $r->status,
            'status'     => VetHelper::appointmentStatus($r->status, (bool) $r->has_treatment, $r->appointment_date, $todayYmd),
        ])->values()->all();
    }

    private function staffList()
    {
        return DB::table('users')->where('status', 'active')->whereIn('role', ['staff', 'vet'])
            ->orderBy('full_name')->select('user_id', 'full_name')->get();
    }

    private function rules(): array
    {
        return [
            'pet_id'           => ['required', 'exists:pets,pet_id'],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'service_type'     => ['required', 'string', 'max:100'],
            'staff_id'         => ['required', 'exists:users,user_id'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    /** พนักงานคนเดียวกันห้ามมีนัด (ที่ยังไม่เสร็จ) วันและเวลาเดียวกัน */
    private function assertNoConflict(array $data, ?string $ignoreId = null): void
    {
        $q = DB::table('appointments')
            ->where('staff_id', $data['staff_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'] . ':00')
            ->where('status', 'scheduled');
        if ($ignoreId) {
            $q->where('appointment_id', '!=', $ignoreId);
        }

        if ($q->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'appointment_time' => 'พนักงานคนนี้มีนัดหมายในวันและเวลาดังกล่าวแล้ว',
            ]);
        }
    }

    private function messages(): array
    {
        return [
            'pet_id.required'           => 'กรุณาเลือกสัตว์เลี้ยง',
            'appointment_date.required' => 'กรุณาเลือกวันที่',
            'appointment_time.required' => 'กรุณาเลือกเวลา',
            'service_type.required'     => 'กรุณาเลือกประเภทบริการ',
            'staff_id.required'         => 'กรุณาเลือกพนักงาน',
        ];
    }
}