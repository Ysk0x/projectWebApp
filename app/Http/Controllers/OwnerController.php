<?php

namespace App\Http\Controllers;

use App\Support\VetHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    /** READ: รายชื่อเจ้าของ + รายละเอียด + สัตว์เลี้ยง */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('owners as o')
            ->select('o.*', DB::raw('(select count(*) from pets p where p.owner_id = o.owner_id) as pet_count'));

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('o.owner_id', 'like', $like)
                  ->orWhere('o.first_name', 'like', $like)
                  ->orWhere('o.last_name', 'like', $like)
                  ->orWhere('o.phone', 'like', $like)
                  ->orWhereExists(function ($sub) use ($like) {
                      $sub->select(DB::raw(1))->from('pets as p')
                          ->whereColumn('p.owner_id', 'o.owner_id')
                          ->where('p.pet_name', 'like', $like);
                  });
            });
        }

        $owners = $query->orderBy('o.first_name')->orderBy('o.last_name')->get();

        $selectedOwner = null;
        if ($request->query('owner')) {
            $selectedOwner = DB::table('owners')->where('owner_id', $request->query('owner'))->first();
        }
        $selectedOwner = $selectedOwner ?: $owners->first();

        $pets = $selectedOwner
            ? DB::table('pets')->where('owner_id', $selectedOwner->owner_id)->orderBy('pet_id')->get()
            : collect();

        return view('vetcare.staff.owners', [
            'thaiDate'      => VetHelper::thaiDate(),
            'search'        => $search,
            'owners'        => $owners,
            'selectedOwner' => $selectedOwner,
            'pets'          => $pets,
        ]);
    }

    /* ===================== OWNERS ===================== */

    public function storeOwner(Request $request)
    {
        $data = $request->validate($this->ownerRules(), $this->messages());

        $id = VetHelper::nextId('owners', 'owner_id', 'O', 4);
        DB::table('owners')->insert($this->ownerRow($data) + [
            'owner_id' => $id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('vetcare.staff.owners', ['owner' => $id])->with('success', 'เพิ่มเจ้าของสัตว์เรียบร้อยแล้ว');
    }

    public function updateOwner(Request $request, string $id)
    {
        abort_unless(DB::table('owners')->where('owner_id', $id)->exists(), 404);
        $data = $request->validate($this->ownerRules(), $this->messages());

        DB::table('owners')->where('owner_id', $id)->update($this->ownerRow($data) + ['updated_at' => now()]);

        return redirect()->route('vetcare.staff.owners', ['owner' => $id])->with('success', 'บันทึกข้อมูลเจ้าของเรียบร้อยแล้ว');
    }

    public function destroyOwner(string $id)
    {
        abort_unless(DB::table('owners')->where('owner_id', $id)->exists(), 404);

        if (DB::table('pets')->where('owner_id', $id)->exists()
            || DB::table('appointments')->where('owner_id', $id)->exists()
            || DB::table('invoices')->where('owner_id', $id)->exists()) {
            return redirect()->route('vetcare.staff.owners', ['owner' => $id])
                ->with('error', 'ลบไม่ได้ เพราะเจ้าของรายนี้ยังมีสัตว์เลี้ยงหรือประวัติการใช้บริการอยู่ (ลบสัตว์เลี้ยงก่อน หรือเก็บประวัติไว้)');
        }

        DB::table('owners')->where('owner_id', $id)->delete();

        return redirect()->route('vetcare.staff.owners')->with('success', 'ลบเจ้าของเรียบร้อยแล้ว');
    }

    /* ===================== PETS ===================== */

    public function storePet(Request $request)
    {
        $data = $request->validate(['owner_id' => ['required', 'exists:owners,owner_id']] + $this->petRules(), $this->messages());

        DB::table('pets')->insert($this->petRow($data) + [
            'pet_id'     => VetHelper::nextId('pets', 'pet_id', 'P', 4),
            'owner_id'   => $data['owner_id'],
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('vetcare.staff.owners', ['owner' => $data['owner_id']])->with('success', 'เพิ่มสัตว์เลี้ยงเรียบร้อยแล้ว');
    }

    public function updatePet(Request $request, string $id)
    {
        $pet = DB::table('pets')->where('pet_id', $id)->first();
        abort_unless($pet, 404);
        $data = $request->validate($this->petRules(), $this->messages());

        DB::table('pets')->where('pet_id', $id)->update($this->petRow($data) + ['updated_at' => now()]);

        return redirect()->route('vetcare.staff.owners', ['owner' => $pet->owner_id])->with('success', 'บันทึกข้อมูลสัตว์เลี้ยงเรียบร้อยแล้ว');
    }

    public function destroyPet(string $id)
    {
        $pet = DB::table('pets')->where('pet_id', $id)->first();
        abort_unless($pet, 404);

        if (DB::table('appointments')->where('pet_id', $id)->exists()
            || DB::table('treatments')->where('pet_id', $id)->exists()
            || DB::table('invoices')->where('pet_id', $id)->exists()) {
            return redirect()->route('vetcare.staff.owners', ['owner' => $pet->owner_id])
                ->with('error', 'ลบไม่ได้ เพราะสัตว์เลี้ยงตัวนี้มีประวัตินัดหมาย/การรักษา/ใบเสร็จแล้ว');
        }

        DB::table('pets')->where('pet_id', $id)->delete();

        return redirect()->route('vetcare.staff.owners', ['owner' => $pet->owner_id])->with('success', 'ลบสัตว์เลี้ยงเรียบร้อยแล้ว');
    }

    /* ===================== helpers ===================== */

    private function ownerRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'phone'      => ['nullable', 'regex:/^[0-9\-\+\s]{6,20}$/'],
            'email'      => ['nullable', 'email', 'max:100'],
            'address'    => ['nullable', 'string', 'max:255'],
        ];
    }

    private function ownerRow(array $d): array
    {
        return [
            'first_name' => $d['first_name'],
            'last_name'  => $d['last_name'],
            'phone'      => $d['phone'] ?? null,
            'email'      => $d['email'] ?? null,
            'address'    => $d['address'] ?? null,
        ];
    }

    private function petRules(): array
    {
        return [
            'pet_name'   => ['required', 'string', 'max:100'],
            'species'    => ['required', 'string', 'max:50'],
            'breed'      => ['nullable', 'string', 'max:100'],
            'gender'     => ['nullable', 'in:M,F'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'color'      => ['nullable', 'string', 'max:50'],
            'weight'     => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
        ];
    }

    private function petRow(array $d): array
    {
        return [
            'pet_name'   => $d['pet_name'],
            'species'    => $d['species'],
            'breed'      => $d['breed'] ?? null,
            'gender'     => $d['gender'] ?? null,
            'birth_date' => $d['birth_date'] ?? null,
            'color'      => $d['color'] ?? null,
            'weight'     => $d['weight'] ?? null,
        ];
    }

    private function messages(): array
    {
        return [
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required'  => 'กรุณากรอกนามสกุล',
            'phone.regex'         => 'เบอร์โทรไม่ถูกต้อง (ตัวเลข/ขีด 6-20 ตัว)',
            'email.email'         => 'รูปแบบอีเมลไม่ถูกต้อง',
            'pet_name.required'   => 'กรุณากรอกชื่อสัตว์เลี้ยง',
            'species.required'    => 'กรุณาระบุชนิดสัตว์',
            'birth_date.before_or_equal' => 'วันเกิดต้องไม่เกินวันนี้',
            'weight.numeric'      => 'น้ำหนักต้องเป็นตัวเลข',
        ];
    }
}