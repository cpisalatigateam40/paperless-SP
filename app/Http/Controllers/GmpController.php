<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GmpController extends Controller
{
    public function index()
    {
        return view('gmp_employee.index');
    }
}