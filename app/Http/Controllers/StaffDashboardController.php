<?php

namespace App\Http\Controllers;

use App\Support\VetHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StaffDashboardController extends Controller
{
    /** READ: ภาพรวมคลินิกวันนี้ */
    public function index()
    {
        $today = Carbon::now('Asia/Bangkok');
        $todayYmd = $today->format('Y-m-d');

        // ตารางงานวันนี้
        $rows = DB::table('appointments as a')
            ->join('pets as p', 'p.pet_id', '=', 'a.pet_id')
            ->join('owners as o', 'o.owner_id', '=', 'a.owner_id')
            ->where('a.appointment_date', $todayYmd)
            ->where('a.status', '!=', 'cancelled')
            ->orderBy('a.appointment_time')
            ->select(
                'a.*', 'p.pet_name', 'o.first_name', 'o.last_name',
                DB::raw('EXISTS(select 1 from treatments t where t.appointment_id = a.appointment_id) as has_treatment')
            )->get();

        // invoice ที่ยังไม่ชำระ ของแต่ละนัดหมาย (ไว้ทำปุ่ม "ชำระเงิน")
        $unpaidByAppt = DB::table('invoices as i')
            ->join('treatments as t', 't.treatment_id', '=', 'i.treatment_id')
            ->where('i.status', 'unpaid')
            ->whereNotNull('t.appointment_id')
            ->pluck('i.invoice_id', 't.appointment_id');

        $schedule = $rows->map(fn ($r) => [
            'id'         => $r->appointment_id,
            'time'       => substr($r->appointment_time, 0, 5),
            'pet'        => $r->pet_name,
            'owner'      => VetHelper::fullName($r->first_name, $r->last_name),
            'service'    => $r->service_type,
            'status'     => VetHelper::appointmentStatus($r->status, (bool) $r->has_treatment, $r->appointment_date, $todayYmd),
            'invoice_id' => $unpaidByAppt[$r->appointment_id] ?? null,
        ])->all();

        $queueCount   = count($schedule);
        $waitingCount = collect($schedule)->where('status', 'waiting')->count();

        // เคสที่ต้องติดตาม = นัดหมายล่วงหน้า 7 วัน
        $followUps = DB::table('appointments as a')
            ->join('pets as p', 'p.pet_id', '=', 'a.pet_id')
            ->join('owners as o', 'o.owner_id', '=', 'a.owner_id')
            ->where('a.status', 'scheduled')
            ->whereBetween('a.appointment_date', [$todayYmd, $today->copy()->addDays(7)->format('Y-m-d')])
            ->where('a.appointment_date', '>', $todayYmd)
            ->orderBy('a.appointment_date')->orderBy('a.appointment_time')
            ->limit(5)
            ->select('a.*', 'p.pet_name', 'o.first_name', 'o.last_name')
            ->get()
            ->map(function ($r) use ($today) {
                $diff = $today->copy()->startOfDay()->diffInDays(Carbon::parse($r->appointment_date), false);
                return [
                    'pet'   => $r->pet_name,
                    'owner' => VetHelper::fullName($r->first_name, $r->last_name),
                    'note'  => $r->notes ?: $r->service_type,
                    'tag'   => $diff <= 1 ? 'tomorrow' : '3days',
                    'label' => $diff <= 1 ? 'พรุ่งนี้' : $diff . ' วัน',
                ];
            })->all();

        // รอชำระเงิน
        $unpaidInvoices = DB::table('invoices as i')
            ->join('pets as p', 'p.pet_id', '=', 'i.pet_id')
            ->join('owners as o', 'o.owner_id', '=', 'i.owner_id')
            ->where('i.status', 'unpaid')
            ->orderBy('i.invoice_date', 'desc')
            ->select('i.*', 'p.pet_name', 'o.first_name', 'o.last_name')
            ->get();
        $unpaidCount = $unpaidInvoices->count();
        $unpaidInvoices = $unpaidInvoices->take(5);

        return view('vetcare.staff.dashboard', [
            'thaiDate'       => VetHelper::thaiDate($today),
            'schedule'       => $schedule,
            'queueCount'     => $queueCount,
            'waitingCount'   => $waitingCount,
            'unpaidCount'    => $unpaidCount,
            'followUps'      => $followUps,
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }
}