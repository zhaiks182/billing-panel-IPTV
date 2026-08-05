<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingCount = Order::where('status', 'pending')->count();
        $errorCount = Order::where('status', 'error')->count();

        $monthlyRevenue = Order::where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('amount');

        $newClientsThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $activeLinesCount = Line::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $approvedOrdersThisMonth = Order::where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->count();

        $expiringSoon = Line::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->with('user')
            ->orderBy('expires_at')
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount', 'errorCount', 'monthlyRevenue', 'expiringSoon',
            'newClientsThisMonth', 'activeLinesCount', 'approvedOrdersThisMonth',
        ));
    }
}
