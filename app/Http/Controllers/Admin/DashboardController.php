<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->startOfMonth();

        $dateTo = $request->date_to
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfMonth();

        $pendingCount = Order::where('status', 'pending')->count();
        $errorCount = Order::where('status', 'error')->count();

        $periodRevenue = Order::where('status', 'approved')
            ->whereBetween('approved_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $newClientsInPeriod = User::whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $activeLinesCount = Line::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $approvedOrdersInPeriod = Order::where('status', 'approved')
            ->whereBetween('approved_at', [$dateFrom, $dateTo])
            ->count();

        $expiringSoon = Line::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->with('user')
            ->orderBy('expires_at')
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount', 'errorCount', 'periodRevenue', 'expiringSoon',
            'newClientsInPeriod', 'activeLinesCount', 'approvedOrdersInPeriod',
            'dateFrom', 'dateTo',
        ));
    }
}
