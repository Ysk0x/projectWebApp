<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Treatment;
use App\Models\Medicine;
use App\Models\InvoiceAudit;

class ManagerController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. วันที่ปัจจุบันภาษาไทย
        $today = Carbon::now('Asia/Bangkok');
        $thaiMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        $thaiDays = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
        $thaiDate = 'วัน' . $thaiDays[$today->dayOfWeek] . 'ที่ ' . $today->day . ' ' . $thaiMonths[$today->month] . ' ' . ($today->year + 543);

        // 2. ดึงข้อมูลสรุปด้านบน (Summary Cards) จาก Database จริง
        // รายได้ทั้งหมดที่ชำระแล้ว (paid)
        $todayRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $monthlyRevenueTotal = Invoice::where('status', 'paid')->sum('total_amount');
        
        // สัตว์รับบริการ (นับจากตาราง treatments)
        $petsServedToday = Treatment::count();
        
        // รอชำระเงิน (status = unpaid)
        $pendingPaymentsCount = Invoice::where('status', 'unpaid')->count();
        
        // ยาที่จำนวนสต็อกน้อยกว่าหรือเท่ากับจุดเตือน (stock_quantity <= minimum_stock)
        $stockAlertsCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

        // 3. กราฟรายได้ (ดึงและรวมยอดจริงจากตาราง invoices ตามวันที่)
        $revenuePeriod = $request->query('period', 'daily');
        $revenues = [];

        if ($revenuePeriod === 'daily') {
            // ดึงยอดรายรับตามวันที่มีการบันทึกจริงในฐานข้อมูล
            $dailyList = Invoice::where('status', 'paid')
                ->selectRaw("DATE(invoice_date) as inv_date, SUM(total_amount) as total")
                ->groupBy('inv_date')
                ->orderBy('inv_date', 'asc')
                ->take(7)
                ->get();

            $maxAmount = $dailyList->max('total') ?: 1;

            foreach ($dailyList as $item) {
                $cDate = Carbon::parse($item->inv_date);
                $revenues[] = [
                    'label' => $cDate->format('d/m'),
                    'amount' => $item->total >= 1000 ? number_format($item->total / 1000, 1) . 'K' : $item->total,
                    'height' => round(($item->total / $maxAmount) * 100),
                ];
            }
        } else {
            // ดึงยอดรายรับตามเดือนที่มีการบันทึกจริงในฐานข้อมูล
            $monthlyList = Invoice::where('status', 'paid')
                ->selectRaw("strftime('%Y-%m', invoice_date) as inv_month, SUM(total_amount) as total")
                ->groupBy('inv_month')
                ->orderBy('inv_month', 'asc')
                ->take(5)
                ->get();

            $maxAmount = $monthlyList->max('total') ?: 1;

            foreach ($monthlyList as $item) {
                $cDate = Carbon::parse($item->inv_month . '-01');
                $revenues[] = [
                    'label' => $thaiMonths[$cDate->month] ?? $item->inv_month,
                    'amount' => $item->total >= 1000 ? number_format($item->total / 1000, 1) . 'K' : $item->total,
                    'height' => round(($item->total / $maxAmount) * 100),
                ];
            }
        }

        // 4. รายการยาใกล้หมด/หมดสต็อกจริง (ดึงจากตาราง medicines)
        $stockAlerts = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        // 5. รายการใบเสร็จชำระเงินจริง 5 รายการล่าสุด (เชื่อม owner, pet, payments)
        $transactions = Invoice::with(['owner', 'pet', 'payments'])
            ->orderBy('invoice_date', 'desc')
            ->take(5)
            ->get();
        // 6. กิจกรรมล่าสุดจริง (ดึงจากตาราง invoice_audits)
        $activities = InvoiceAudit::orderBy('audited_at', 'desc')
            ->take(5)
            ->get();

        return view('vetcare.manager.dashboard', compact(
            'thaiDate',
            'revenuePeriod',
            'todayRevenue',
            'monthlyRevenueTotal',
            'petsServedToday',
            'pendingPaymentsCount',
            'stockAlertsCount',
            'revenues',
            'stockAlerts',
            'transactions',
            'activities'
        ));
    }
}
