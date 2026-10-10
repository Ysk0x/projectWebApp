<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\InvoiceAudit;
use App\Models\Invoice;
use App\Models\Treatment;
use App\Models\Payment;
use App\Models\Appointments;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private const ROLES = ['vet', 'staff', 'manager'];
    private const STATUSES = ['active', 'inactive'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = User::query();
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('user_id', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('full_name', 'like', $like);
            });
        }
        $users = $query->orderBy('user_id')->get();

        $totalUsers    = User::count();
        $managerCount  = User::where('role', 'manager')->count();
        $staffCount    = User::where('role', '!=', 'manager')->count();
        $inactiveCount = User::where('status', 'inactive')->count();

        $panel = $request->query('panel');
        $selectedUser = null;
        if (in_array($panel, ['edit', 'reset', 'delete'], true)) {
            $selectedUser = User::where('user_id', $request->query('id'))->first();
        }

        return view('vetcare.manager.users', compact(
            'users', 'search', 'panel', 'selectedUser',
            'totalUsers', 'managerCount', 'staffCount', 'inactiveCount'
        ));
    }

    /** CREATE */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'     => ['required', 'email', 'max:50', 'unique:users,email'],
            'full_name' => ['required', 'string', 'max:100'],
            'password'  => ['required', 'string', 'min:8', 'max:255'],
            'role'      => ['required', Rule::in(self::ROLES)],
            'status'    => ['required', Rule::in(self::STATUSES)],
        ], $this->messages());

        DB::transaction(function () use ($data) {
            User::insert([
                'user_id'    => $this->nextId('users', 'user_id', 'U', 4),
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'full_name'  => $data['full_name'],
                'role'       => $data['role'],
                'status'     => $data['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('vetcare.manager.users')->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    /** UPDATE */
    public function update(Request $request, string $id)
    {
        $user = User::where('user_id', $id)->first();
        abort_unless($user, 404);

        $data = $request->validate([
            'email'     => ['required', 'email', 'max:50', Rule::unique('users', 'email')->ignore($id, 'user_id')],
            'full_name' => ['required', 'string', 'max:100'],
            'role'      => ['required', Rule::in(self::ROLES)],
            'status'    => ['required', Rule::in(self::STATUSES)],
        ], $this->messages());

        if ($this->isSelf($request, $user) && ($data['role'] !== 'manager' || $data['status'] !== 'active')) {
            return back()->withInput()->withErrors([
                'role' => 'ไม่สามารถลดสิทธิ์หรือระงับบัญชีของตัวเองได้',
            ]);
        }

        User::where('user_id', $id)->update([
            'email'      => $data['email'],
            'full_name'  => $data['full_name'],
            'role'       => $data['role'],
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);

        return redirect()->route('vetcare.manager.users')->with('success', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    /** รีเซ็ตรหัสผ่าน */
    public function resetPassword(Request $request, string $id)
    {
        abort_unless(User::where('user_id', $id)->exists(), 404);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], $this->messages());

        User::where('user_id', $id)->update([
            'password'   => Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        return redirect()->route('vetcare.manager.users')->with('success', 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::where('user_id', $id)->first();
        abort_unless($user, 404);

        if ($this->isSelf($request, $user)) {
            return redirect()->route('vetcare.manager.users')->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        $referenced =
            Appointments::where('staff_id', $id)->exists() ||
            Treatment::where('staff_id', $id)->exists() ||
            Invoice::where('created_by', $id)->exists() ||
            Payment::where('received_by', $id)->exists() ||
            InvoiceAudit::where('audited_by', $id)->exists();

        if ($referenced) {
            User::where('user_id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

            return redirect()->route('vetcare.manager.users')
                ->with('success', 'บัญชีนี้มีประวัติการใช้งานในระบบ จึงเปลี่ยนสถานะเป็น "ระงับ" แทนการลบ');
        }

        User::where('user_id', $id)->delete();

        return redirect()->route('vetcare.manager.users')->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }

    private function isSelf(Request $request, object $user): bool
    {
        return $request->user() && $request->user()->email === $user->email;
    }

    private function nextId(string $table, string $column, string $prefix, int $digits): string
{
    $max = (new class extends Model {
        protected $guarded = [];
        public $timestamps = false;
    })->setTable($table)
        ->newQuery()
        ->where($column, 'like', $prefix . '%')
        ->max($column);

    $n = $max ? (int) substr($max, strlen($prefix)) : 0;

    return $prefix . Str::padLeft((string) ($n + 1), $digits, '0');
}

    private function messages(): array
    {
        return [
            'email.required'     => 'กรุณากรอกอีเมล',
            'email.email'        => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique'       => 'อีเมลนี้ถูกใช้งานแล้ว',
            'email.max'          => 'อีเมลต้องไม่เกิน 50 ตัวอักษร',
            'full_name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'password.required'  => 'กรุณากรอกรหัสผ่าน',
            'password.min'       => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'role.in'            => 'บทบาทไม่ถูกต้อง',
            'status.in'          => 'สถานะไม่ถูกต้อง',
        ];
    }
}