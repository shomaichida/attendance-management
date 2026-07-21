<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\View\View;

/**
 * 一般ユーザー向け勤怠ダッシュボードを表示するController。
 */
class DashboardController extends Controller
{
    /**
     * ログインユーザーの当日の勤怠と休憩状況を表示する。
     */
    public function index(): View
    {
        $todayAttendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        return view('dashboard', compact('todayAttendance'));
    }
}
