<?php

namespace App\Http\Controllers;

use App\Models\ShiftSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function showShiftSelection()
    {
        $todayShift = ShiftSelection::where('user_id', Auth::id())
            ->whereDate('selected_at', today())
            ->first();

        return view('shift.select', [
            'todayShift' => $todayShift
        ]);
    }

    public function storeShiftSelection(Request $request)
    {
        $validated = $request->validate([
            'shift_number' => 'required|integer|in:1,2,3',
            'group' => 'required|string|in:A,B,C,D,E'
        ]);

        session([
            'shift_number' => $validated['shift_number'],
            'shift_group' => $validated['group'],
            'shift_label' => 'Shift ' . $validated['shift_number'] . ' - Group ' . $validated['group']
        ]);

        return redirect()->route('dashboard');
    }


    public function changeShift()
    {
        return view('shift.select', ['isChange' => true]);
    }
}