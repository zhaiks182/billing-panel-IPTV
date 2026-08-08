<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderApproved;
use App\Notifications\OrderInvoice;
use App\Notifications\OrderRejected;
use App\Services\Xui\XuiApiException;
use App\Services\Xui\XuiLineService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'package', 'paymentMethod'])
            ->when($request->status, fn ($q, $status) => $q->whereIn('status', (array) $status))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function approve(Order $order, XuiLineService $xui)
    {
        abort_unless($order->status === 'pending', 404);

        $this->activate($order, $xui);

        return back()->with('status', "Pedido #{$order->id} aprobado.");
    }

    public function retry(Order $order, XuiLineService $xui)
    {
        abort_unless(in_array($order->status, ['approved', 'error']), 404);

        $this->activate($order, $xui);

        return back()->with('status', "Pedido #{$order->id} reintentado.");
    }

    public function reject(Request $request, Order $order)
    {
        abort_unless(in_array($order->status, ['pending', 'approved', 'error']), 404);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $order->user->notify(new OrderRejected($order));

        return back()->with('status', "Pedido #{$order->id} rechazado.");
    }

    /**
     * Dos pasos reales, no uno: primero se guarda "approved" (el pago ya está confirmado,
     * esto nunca falla porque es solo una escritura local) y recién después se intenta
     * crear la línea en XUI. Si XUI falla, el pedido queda en "error" pero con el registro
     * de que sí se aprobó — antes pasaba directo de "pending" a "error" sin dejar rastro de
     * que el pago se había confirmado. Esto también protege ante una caída del servidor
     * justo entre ambos pasos: el pedido queda en "approved" (recuperable con "Reintentar
     * activación"), no perdido a medio camino.
     */
    private function activate(Order $order, XuiLineService $xui): void
    {
        $order->update([
            'status' => 'approved',
            'admin_note' => null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        try {
            $line = $xui->activate($order);

            $order->update(['status' => 'activated']);

            $order->user->notify(new OrderApproved($order, $line));

            if (! $order->package->is_trial) {
                $order->user->notify(new OrderInvoice($order));
            }
        } catch (XuiApiException $e) {
            $order->update([
                'status' => 'error',
                'admin_note' => $e->getMessage(),
            ]);
        }
    }
}
