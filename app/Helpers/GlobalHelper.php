<?php

use Carbon\Carbon;

if (!function_exists('getShift')) {
    function getShift($datetime = null)
    {
        $now = $datetime ? Carbon::parse($datetime) : now();
        $hour = $now->hour;

        if ($hour >= 7 && $hour < 15) {
            return '1';
        } elseif ($hour >= 15 && $hour < 23) {
            return '2';
        } else {
            return '3';
        }
    }
}