<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.auth.login'); 
    }

    // ตรวจสอบข้อมูล Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // เช็ค Role เพื่อเตะไปหน้า Dashboard ที่ถูกต้อง
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin-dashboard');
            }
            return redirect()->intended('/staff-dashboard');
        }

        return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง']);
    }

    // ล็อกเอาท์
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

