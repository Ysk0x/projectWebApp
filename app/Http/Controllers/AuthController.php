<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->role === 'manager'
                ? 'vetcare.manager.dashboard'
                : 'vetcare.staff.dashboard');
        }

        return view('vetcare/login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'role'     => ['nullable', 'in:staff,manager'],
        ], [
            'email.required'    => 'กรุณากรอกอีเมล',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $selectedRole = $data['role'] ?? 'staff';
        $throttleKey  = Str::lower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => "พยายามเข้าสู่ระบบมากเกินไป กรุณาลองใหม่ในอีก {$seconds} วินาที"]);
        }

        // บัญชีที่ถูกระงับ (รหัสผ่านถูกแต่ status = inactive)
        $record = User::where('email', $data['email'])->first();
        if ($record && $record->status !== 'active' && Hash::check($data['password'], $record->password)) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้จัดการ']);
        }

        $attempt = [
            'email'    => $data['email'],
            'password' => $data['password'],
            'status'   => 'active',
        ];

        if (Auth::attempt($attempt, $request->boolean('remember'))) {
            $isManager = Auth::user()->role === 'manager';

            // ประเภทผู้ใช้งานที่เลือกต้องตรงกับบัญชีจริง (vet ถือเป็นฝั่งพนักงาน)
            if (($selectedRole === 'manager') !== $isManager) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                RateLimiter::hit($throttleKey);

                return redirect()->route('login')
                    ->withInput($request->only('email', 'role'))
                    ->withErrors(['role' => 'ประเภทผู้ใช้งานที่เลือกไม่ตรงกับบัญชีนี้']);
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return $isManager
                ? redirect()->intended(route('vetcare.manager.dashboard'))
                : redirect()->intended(route('vetcare.staff.dashboard'));
        }

        RateLimiter::hit($throttleKey);

        return back()->withInput($request->only('email', 'role'))
            ->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}