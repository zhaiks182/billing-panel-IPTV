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

        // 'approved' y 'activated' significan lo mismo para ingresos: el pago ya se
        // confirmó, sin importar si la línea logró crearse en XUI o no todavía.
        $periodRevenue = Order::whereIn('status', ['approved', 'activated'])
            ->whereBetween('approved_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $newClientsInPeriod = User::where('role', 'customer')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $activeLinesCount = Line::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $expiringSoon = Line::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(Line::EXPIRING_SOON_DAYS)])
            ->with(['user', 'order.package'])
            ->orderBy('expires_at')
            ->get();

        $expiringSoonCount = $expiringSoon->count();

        // "Atención requerida": un pedido con status=error en esta app YA significa "se
        // aprobó el pago pero XUI falló al crear la línea" (ver Admin\OrderController::activate),
        // así que es un único conteo real, no dos señales distintas.
        $linesExpiringTodayCount = Line::where('status', 'active')
            ->whereDate('expires_at', today())
            ->count();

        $recentOrders = Order::with(['user', 'package'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'pendingCount', 'errorCount', 'periodRevenue', 'expiringSoon', 'expiringSoonCount',
            'newClientsInPeriod', 'activeLinesCount', 'recentOrders', 'linesExpiringTodayCount',
            'dateFrom', 'dateTo',
        ));
    }
}
