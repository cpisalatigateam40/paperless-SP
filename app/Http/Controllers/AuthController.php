<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShiftSelection;
use Illuminate\Support\Facades\Http;

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
        $user = $request->user();

        // Hapus session shift saat logout
        $request->session()->forget(['shift_number', 'shift_group', 'shift_label']);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            try {
            Http::withToken(config('services.employee_api.sso_secret'))
            ->timeout(5)
            ->post(config('services.employee_api.url') . '/sso/report-logout', [
            'user_uuid' => $user->uuid,
            'project_uuid' => config('services.employee_api.this_project_uuid'),
            ]);
            } catch (\Throwable $e) {
            // jangan blokir proses logout lokal walau central tidak terjangkau
            }
            }
            return redirect(config('services.employee_api.portal_url'));
    }
}