<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Treatment;
use App\Models\TreatmentMedicine;
use App\Models\InvoiceItems;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    /** READ */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = Medicine::where('status', 'active');
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('medicine_id', 'like', $like)
                  ->orWhere('medicine_name', 'like', $like)
                  ->orWhere('unit', 'like', $like);
            });
        }

        $medicines = $query->orderBy('medicine_id')->get()->map(function ($m) {
            $m->stock_status = $this->stockStatus($m);
            return $m;
        });

        $all = Medicine::where('status', 'active')->get();
        $totalMedicines = $all->count();
        $normalCount = $all->filter(fn ($m) => $this->stockStatus($m) === 'normal')->count();
        $lowCount    = $all->filter(fn ($m) => $this->stockStatus($m) === 'low')->count();
        $outCount    = $all->filter(fn ($m) => $this->stockStatus($m) === 'out')->count();

        $panel = $request->query('panel');
        $selectedMedicine = null;
        if (in_array($panel, ['edit', 'delete'], true)) {
            $selectedMedicine = Medicine::where('medicine_id', $request->query('id'))->first();
        }

        return view('vetcare.manager.medicines', compact(
            'medicines', 'search', 'panel', 'selectedMedicine',
            'totalMedicines', 'normalCount', 'lowCount', 'outCount'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            Medicine::insert([
                'medicine_id'    => $this->nextId(),
                'medicine_name'  => $data['medicine_name'],
                'unit'           => $data['unit'],
                'cost_price'     => $data['cost_price'],
                'selling_price'  => $data['selling_price'],
                'stock_quantity' => $data['stock_quantity'],
                'minimum_stock'  => $data['minimum_stock'],
                'status'         => 'active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        return redirect()->route('vetcare.manager.medicines')->with('success', 'เพิ่มยาใหม่เรียบร้อยแล้ว');
    }

    /** UPDATE */
    public function update(Request $request, string $id)
    {
        abort_unless(Medicine::where('medicine_id', $id)->exists(), 404);

        $data = $this->validated($request);

        Medicine::where('medicine_id', $id)->update($data + ['updated_at' => now()]);

        return redirect()->route('vetcare.manager.medicines')->with('success', 'บันทึกข้อมูลยาเรียบร้อยแล้ว');
    }

    /** DELETE (ถ้ายาเคยถูกใช้ในการรักษา/ใบเสร็จ จะเปลี่ยนเป็น inactive แทน) */
    public function destroy(string $id)
    {
        abort_unless(Medicine::where('medicine_id', $id)->exists(), 404);

        $referenced =
            TreatmentMedicine::where('medicine_id', $id)->exists() ||
            InvoiceItems::where('medicine_id', $id)->exists();

        if ($referenced) {
            Medicine::where('medicine_id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

            return redirect()->route('vetcare.manager.medicines')
                ->with('success', 'ยานี้มีประวัติการใช้งาน จึงปิดการใช้งานแทนการลบถาวร');
        }

        Medicine::where('medicine_id', $id)->delete();

        return redirect()->route('vetcare.manager.medicines')->with('success', 'ลบยาเรียบร้อยแล้ว');
    }

    /* ---------------------------------------------------------- */

    private function validated(Request $request): array
    {
        return $request->validate([
            'medicine_name'  => ['required', 'string', 'max:150'],
            'unit'           => ['required', 'string', 'max:30'],
            'cost_price'     => ['required', 'numeric', 'min:0', 'max:99999999'],
            'selling_price'  => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock'  => ['required', 'integer', 'min:0'],
        ], [
            'medicine_name.required'  => 'กรุณากรอกชื่อยา',
            'unit.required'           => 'กรุณากรอกหน่วย',
            'cost_price.required'     => 'กรุณากรอกราคาต้นทุน',
            'cost_price.numeric'      => 'ราคาต้นทุนต้องเป็นตัวเลข',
            'selling_price.required'  => 'กรุณากรอกราคาขาย',
            'selling_price.numeric'   => 'ราคาขายต้องเป็นตัวเลข',
            'stock_quantity.required' => 'กรุณากรอกจำนวนคงเหลือ',
            'stock_quantity.integer'  => 'จำนวนคงเหลือต้องเป็นจำนวนเต็ม',
            'minimum_stock.required'  => 'กรุณากรอกจุดเตือนสต็อกต่ำ',
            'minimum_stock.integer'   => 'จุดเตือนต้องเป็นจำนวนเต็ม',
        ]);
    }

    private function stockStatus(object $m): string
    {
        if ((int) $m->stock_quantity <= 0) {
            return 'out';
        }

        return (int) $m->stock_quantity <= (int) ($m->minimum_stock ?? 0) ? 'low' : 'normal';
    }

    private function nextId(): string
    {
        $max = Medicine::where('medicine_id', 'like', 'M%')->max('medicine_id');
        $n = $max ? (int) substr($max, 1) : 0;

        return 'M' . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT);
    }
}