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
        $today = Carbon::now('Asia/Bangkok');
        $thaiMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];
        $thaiDays = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
        $thaiDate = 'วัน' . $thaiDays[$today->dayOfWeek] . 'ที่ ' . $today->day . ' ' . $thaiMonths[$today->month] . ' ' . ($today->year + 543);


        $todayRevenue = Invoice::where('status', 'paid')
            ->whereDate('invoice_date', $today->toDateString())
            ->sum('total_amount');

        $monthlyRevenueTotal = Invoice::where('status', 'paid')
            ->whereMonth('invoice_date', $today->month)
            ->whereYear('invoice_date', $today->year)
            ->sum('total_amount');

        $petsServedToday = Treatment::whereDate('treatment_date', $today->toDateString())->count();
        
        $pendingPaymentsCount = Invoice::where('status', 'unpaid')->count();
        
        $stockAlertsCount = Medicine::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

        //มาไงหี request
        $revenuePeriod = $request->query('period', 'daily');
      
        $revenues = [];

        if ($revenuePeriod === 'daily') {
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

        $stockAlerts = Medicine::where('status', 'active')
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'code'    => $m->medicine_id,
                'name'    => $m->medicine_name,
                'unit'    => $m->unit,
                'stock'   => (int) $m->stock_quantity,
                'minimum' => (int) $m->minimum_stock,
                'status'  => (int) $m->stock_quantity <= 0 ? 'out' : 'low',
            ])->all();

        $methodLabels = ['cash' => 'เงินสด', 'qr_promptpay' => 'โอนเงิน (QR)', 'credit_card' => 'บัตรเครดิต'];

        $transactions = Invoice::with(['owner', 'pet', 'payments'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($inv) use ($methodLabels) {
                $pay = $inv->payments->sortByDesc('payment_date')->first();

                return [
                    'invoice_number'  => $inv->invoice_number,
                    'owner'   => trim(($inv->owner->first_name ?? '') . ' ' . ($inv->owner->last_name ?? '')),
                    'pet'     => $inv->pet->pet_name ?? '-',
                    'petType' => $inv->pet->species ?? '-',
                    'amount'  => (float) $inv->total_amount,
                    'method' => $pay ? ($methodLabels[$pay->payment_method] ?? $pay->payment_method) : '-',
                    'status'  => $inv->status,  
                ];
            })->all();
        
        $activities = InvoiceAudit::with(['auditor', 'invoice'])
            ->orderBy('audited_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($a) {
                $action = match (true) {
                    $a->action === 'discount_applied' => 'ให้ส่วนลดใบเสร็จ',
                    $a->new_status === 'paid'         => 'รับชำระเงินใบเสร็จ',
                    $a->new_status === 'cancelled'    => 'ยกเลิกใบเสร็จ',
                    default                           => 'เปลี่ยนสถานะใบเสร็จ',
                };

                return [
                    'time' => Carbon::parse($a->audited_at)->format('d/m H:i'),
                    'text' => ($a->auditor->full_name ?? 'ไม่ทราบผู้ใช้') . ' ' . $action . ' ' . ($a->invoice->invoice_number ?? ''),
                ];
            })->all();

        return view('vetcare.manager.dashboard', compact(
            'revenues',
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
