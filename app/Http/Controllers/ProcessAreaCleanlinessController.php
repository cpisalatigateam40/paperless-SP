<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReportProcessAreaCleanliness;
use App\Models\DetailProcessAreaCleanliness;
use App\Models\ItemProcessAreaCleanliness;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessAreaCleanlinessController extends Controller
{
    public function index()
    {
        return view(view: 'cleanliness_PA.index');
    }
}