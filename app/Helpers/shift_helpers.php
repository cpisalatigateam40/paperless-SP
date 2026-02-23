<?php

if (!function_exists('get_current_shift')) {
    /**
     * Get current shift label from session
     *
     * @return string
     */
    function get_current_shift()
    {
        if (session()->has('shift_label')) {
            return session('shift_label');
        }

        if (session()->has('shift_number') && session()->has('shift_group')) {
            return 'Shift ' . session('shift_number') . ' - Group ' . session('shift_group');
        }

        return '';
    }
}

if (!function_exists('get_shift_number')) {
    /**
     * Get current shift number from session
     *
     * @return int|null
     */
    function get_shift_number()
    {
        return session('shift_number');
    }
}

if (!function_exists('get_shift_group')) {
    /**
     * Get current shift group from session
     *
     * @return string|null
     */
    function get_shift_group()
    {
        return session('shift_group');
    }
}