<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Present;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // ✅ Acara Aktif: yang TANGGALNYA hari ini DAN status = 'open'
        $activeEvents = Present::whereDate('tanggal', $today)
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ Acara Akan Datang: tanggal > hari ini (terlepas dari status)
        $upcomingEvents = Present::whereDate('tanggal', '>', $today)
            ->orderBy('tanggal', 'asc')
            ->get();

        // ✅ Acara Selesai: tanggal < hari ini (terlepas dari status)
        $completedEvents = Present::whereDate('tanggal', '<', $today)->count();

        return view('dashboard.index', compact('activeEvents', 'upcomingEvents', 'completedEvents'));
    }
}