<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShiftSelection;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required'], // login = username atau email
            'password' => ['required'],
        ]);

        $login_type = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $login_type => $request->input('login'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Kalau QC Inspector → wajib pilih shift
            if ($user->hasRole('QC Inspector')) {
                return redirect()->route('shift.select');
            }

            // Role lain langsung dashboard
            return redirect()->route('dashboard');
        }


        return back()->withErrors([
            'login' => 'Username/email atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus session shift saat logout
        $request->session()->forget(['shift_number', 'shift_group', 'shift_label']);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}