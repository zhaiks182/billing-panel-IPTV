<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TelegramSetting;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Recibe los mensajes que le escriben al bot de Telegram (Telegram los reenvía acá vía
 * webhook, configurado por Admin\TelegramSettingController@update llamando a
 * TelegramNotifier::setWebhook). Sin autenticación de Laravel (Telegram no manda cookie de
 * sesión ni token CSRF) — la seguridad es el header `X-Telegram-Bot-Api-Secret-Token`
 * (comparado contra TelegramSetting::webhook_secret) más el chat_id del mensaje (solo se
 * responde si coincide con el chat configurado en el panel, para que un desconocido que le
 * escriba al bot no pueda pedir las ventas del día).
 */
class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramNotifier $telegram): Response
    {
        $settings = TelegramSetting::current();

        if (! $settings->isActive() || ! $settings->webhook_secret) {
            return response('', 200);
        }

        if (! hash_equals($settings->webhook_secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response('', 403);
        }

        $chatId = $request->input('message.chat.id');
        $text = trim((string) $request->input('message.text', ''));

        if ((string) $chatId !== (string) $settings->chat_id || $text === '') {
            return response('', 200);
        }

        $command = strtolower(strtok($text, ' @'));

        match ($command) {
            '/ventashoy', '/ventas' => $telegram->send($this->salesTodayMessage()),
            '/start', '/help', '/ayuda' => $telegram->send(
                "👋 Hola, soy el bot de 4LivePro Latino.\n\n".
                'Comandos disponibles:'."\n".
                '/ventashoy — ventas y pedidos aprobados de hoy'
            ),
            default => null,
        };

        return response('', 200);
    }

    private function salesTodayMessage(): string
    {
        $today = now()->startOfDay();

        $approvedToday = Order::where('status', 'approved')
            ->whereDate('approved_at', $today)
            ->with('package')
            ->get();

        $paidOrders = $approvedToday->filter(fn (Order $order) => ! $order->package->is_trial);
        $trialOrders = $approvedToday->filter(fn (Order $order) => $order->package->is_trial);
        $revenue = $paidOrders->sum('amount');

        return "📊 <b>Ventas de hoy</b> ({$today->format('d/m/Y')})\n\n".
            "Pedidos pagados aprobados: {$paidOrders->count()}\n".
            'Ingresos: $'.number_format((float) $revenue, 2)." USD\n".
            "Demos activadas: {$trialOrders->count()}\n\n".
            "Total pedidos aprobados: {$approvedToday->count()}";
    }
}
