<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureShiftSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kalau belum login, biarkan middleware auth yang handle
        if (!Auth::check()) {
            return $next($request);
        }

        // Skip untuk route shift & logout
        if (
            $request->routeIs('shift.select') ||
            $request->routeIs('shift.store') ||
            $request->routeIs('shift.change') ||
            $request->routeIs('logout')
        ) {
            return $next($request);
        }

        $user = Auth::user();

        // 🔹 Kalau bukan QC Inspector → tidak perlu shift
        if (!$user->hasRole('QC Inspector')) {
            return $next($request);
        }

        // 🔹 Kalau QC Inspector tapi belum pilih shift
        if (!session()->has('shift_number') || !session()->has('shift_group')) {
            return redirect()->route('shift.select');
        }

        return $next($request);
    }
}