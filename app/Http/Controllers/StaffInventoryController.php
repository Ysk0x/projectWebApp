<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search     = trim((string) $request->query('search', ''));
        $status     = $request->query('status', 'all');
        $selectedId = $request->query('selected');

        $now      = Carbon::now('Asia/Bangkok')->locale('th');
        $thaiDate = $now->translatedFormat('j F ') . ($now->year + 543);

        $all = Medicine::orderBy('medicine_name')->get()->each(function ($m) {
            $m->stock_status = match (true) {
                $m->stock_quantity <= 0                        => 'out',
                $m->stock_quantity <= ($m->minimum_stock ?? 0) => 'low',
                default                                        => 'normal',
            };
        });

        $totalCount  = $all->count();
        $normalCount = $all->where('stock_status', 'normal')->count();
        $lowCount    = $all->where('stock_status', 'low')->count();
        $outCount    = $all->where('stock_status', 'out')->count();

        $medicines = $all
            ->when($search !== '', fn ($c) => $c->filter(fn ($m) =>
                str_contains(mb_strtolower($m->medicine_name), mb_strtolower($search)) ||
                str_contains(mb_strtolower((string) $m->medicine_id), mb_strtolower($search))
            ))
            ->when($status !== 'all', fn ($c) => $c->where('stock_status', $status))
            ->values();

        $selectedMedicine = $selectedId ? $all->firstWhere('medicine_id', $selectedId) : null;

        return view('vetcare.staff.inventory', compact(
            'thaiDate', 'totalCount', 'normalCount', 'lowCount', 'outCount',
            'search', 'status', 'medicines', 'selectedMedicine'
        ));
    }
}