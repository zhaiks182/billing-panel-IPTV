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

        // No es "conexiones en vivo" real de XUI ONE — la API de reseller que usa este panel
        // no expone eso (get_lines/live_connections devuelven vacío con esta clave, verificado
        // 2026-08-07; ver conversación). Es la capacidad vendida (suma de max_connections de
        // líneas activas), el dato más cercano y real que sí podemos calcular nosotros mismos.
        $totalConnectionsCapacity = Line::where('status', 'active')
            ->where('expires_at', '>', now())
            ->sum('max_connections');

        $expiringSoon = Line::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(Line::EXPIRING_SOON_DAYS)])
            ->with(['user', 'order.package'])
            ->orderBy('expires_at')
            ->get();

        $expiringSoonCount = $expiringSoon->count();

        $recentOrders = Order::with(['user', 'package'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'pendingCount', 'errorCount', 'periodRevenue', 'expiringSoon', 'expiringSoonCount',
            'newClientsInPeriod', 'activeLinesCount', 'totalConnectionsCapacity', 'recentOrders',
            'dateFrom', 'dateTo',
        ));
    }
}
