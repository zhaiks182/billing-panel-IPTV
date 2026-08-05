<?php

namespace App\Http\Controllers;

use App\Models\XuiSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $lines = $user->lines()->with('order.package')->latest('expires_at')->get();
        $recentOrders = $user->orders()->with('package')->latest()->take(5)->get();
        $serverUrl = XuiSetting::current()->server_url;

        return view('dashboard', compact('lines', 'recentOrders', 'serverUrl'));
    }
}
