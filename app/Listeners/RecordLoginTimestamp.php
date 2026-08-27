<?php
namespace App\Listeners;
use Illuminate\Auth\Events\Login;
class RecordLoginTimestamp
{
 public function handle(Login $event): void
 {
 session(['login_at' => now()->toDateTimeString()]);
 }
}
