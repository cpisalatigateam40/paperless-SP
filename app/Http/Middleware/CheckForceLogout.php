<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class CheckForceLogout
{
 public function handle(Request $request, Closure $next)
 {
 if (Auth::check()) {
 $user = Auth::user();
 $loginAt = $request->session()->get('login_at');
 $stale = ! $loginAt || Carbon::parse($loginAt)
 ->lt($user->force_logout_after ?? now()->subCentury());
 if ($user->force_logout_after && $stale) {
 Auth::logout();
 $request->session()->invalidate();
 $request->session()->regenerateToken();
 return redirect('/login')->withErrors([
 'sso' => 'Anda telah logout dari sistem lain. Silakan login kembali.',
 ]);
 }
 }
 return $next($request);
 }
}
