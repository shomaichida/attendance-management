<?php

namespace App\Http\Controllers;

use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $todayAttendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        return view('dashboard', compact('todayAttendance'));
    }
}
