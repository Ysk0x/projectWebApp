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
        $today = today();
        $thaiDate = $today->locale('th')->isoFormat('D MMMM YYYY');

        $todaySchedule = DB::table('appointments')
            ->join('pets', 'appointments.pet_id', '=', 'pets.pet_id')
            ->join('owners', 'appointments.owner_id', '=', 'owners.owner_id')
            ->whereDate('appointment_date', $today)
            ->select(
                'appointments.*',
                'pets.pet_name as pet_name',
            )
            ->selectRaw("CONCAT(owners.first_name, ' ', owners.last_name) as owner_name")
            ->orderBy('appointments_time', 'asc')
            ->get();

        $followCase = DB::table('appointments')
            ->join('pets', 'appointments.pet_id', '=', 'pets.pet_id')
            ->join('owners', 'appointments.owner_id', '=', 'owners.owner_id')
            ->where('service_type', 'ติดตามอาการ')
            ->where('status', 'scheduled')
            ->whereBetween('appointment_date', [$today, $today->addDay(7)])
            ->select(
                'appointments.*',
                'pets.pet_name as pet_name',
            )
            ->selectRaw("CONCAT(owners.first_name, ' ', owners.last_name) as owner_name")
            ->orderBy('appointments_time', 'asc')
            ->get();

        $unpaidInvoice = DB::table('invoices')
            ->join('pets', 'invoices.pet_id', '=', 'pets.pet_id')
            ->join('owners', 'invoices.owner_id', '=', 'owners.owner_id')
            ->where('status', 'unpaid')
            ->select(
                'invoices.*',
                'pets.pet_name as pet_name',
            )
            ->selectRaw("CONCAT(owners.first_name, ' ', owners.last_name) as owner_name")
            ->orderBy('invoice_date', 'desc')
            ->get();

        $todayScheduleCount = $todaySchedule->count();
        $followCaseCount = $followCase->count();

        $waitingTreatment = DB::table('appointments')
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', $today)
            ->count();

        $waitingPayment = DB::table('invoices')
            ->where('status', 'unpaid')
            ->count();

        return view('vetcare.staff.dashboard', [
            'thaiDate' => $thaiDate,
            'followCase' => $followCase,
            'todaySchedule' => $todaySchedule,
            'unpaidInvoice' => $unpaidInvoice,
            'todayScheduleCount' => $todayScheduleCount,
            'followCaseCount' => $followCaseCount,
            'waitingTreatment' => $waitingTreatment,
            'waitingPayment' => $waitingPayment,
        ]);
    }
}