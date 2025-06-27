<?php

use Carbon\Carbon;

if (!function_exists('getShift')) {
    function getShift($datetime = null)
    {
        $now = $datetime ? Carbon::parse($datetime) : now();
        $hour = $now->hour;

        if ($hour >= 6 && $hour < 14) {
            return '1';
        } elseif ($hour >= 14 && $hour < 22) {
            return '2';
        } else {
            return '3';
        }
    }
}