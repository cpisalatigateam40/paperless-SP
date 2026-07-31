<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SsoLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['ticket' => 'required|uuid']);

        $response = Http::withToken(config('services.employee_api.sso_secret'))
            ->timeout(10)
            ->post(config('services.employee_api.url') . '/sso/verify', [
                'ticket'       => $request->ticket,
                'project_uuid' => config('services.employee_api.this_project_uuid'),
            ]);

        if ($response->status() === 403) {
            return redirect('/login')->withErrors([
                'sso' => 'Anda tidak memiliki akses ke sistem Premix.',
            ]);
        }

        if ($response->failed()) {
            return redirect('/login')->withErrors([
                'sso' => 'Sesi login otomatis tidak valid atau sudah kedaluwarsa. Silakan login manual.',
            ]);
        }

        $remoteUser = $response->json('user');

        // dd( $remoteUser['uuid']);

        $user = User::where('uuid', $remoteUser['uuid'])->first();

        // dd($user);

        if (! $user) {
            return redirect('/login')->withErrors([
                'sso' => 'Akun tidak ditemukan di sistem Paperless SP.',
            ]);
        }

        

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}