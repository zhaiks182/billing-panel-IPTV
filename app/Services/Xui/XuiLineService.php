<?php

namespace App\Services\Xui;

use App\Models\Line;
use App\Models\Order;
use App\Models\XuiSetting;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Crea la línea M3U en XUI ONE cuando se aprueba un pedido.
 * Cada pedido genera su propia línea independiente (un cliente puede acumular
 * varias líneas activas, por ejemplo una por dispositivo).
 * Lanza XuiApiException si la llamada a XUI ONE falla; el llamador (Admin\OrderController)
 * decide qué hacer con el pedido en ese caso (queda en status=error para reintentar).
 */
class XuiLineService
{
    public function __construct(private readonly XuiOneClient $client)
    {
    }

    public function activate(Order $order): Line
    {
        $order->loadMissing(['user', 'package']);

        if (! $order->package->xui_package_id) {
            throw new XuiApiException("El paquete «{$order->package->name}» no tiene un ID de paquete XUI asignado (Admin > Paquetes).");
        }

        $data = $this->client->createLine($order->package->xui_package_id);

        return Line::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'xui_line_id' => $data['id'] ?? null,
            'xui_username' => $data['username'] ?? throw new RuntimeException('XUI ONE no devolvió un usuario para la línea creada.'),
            'xui_password' => $data['password'] ?? '',
            'm3u_url' => $this->buildM3uUrl($data['username'] ?? null, $data['password'] ?? null),
            'max_connections' => (int) ($data['max_connections'] ?? $order->package->max_connections),
            'expires_at' => $this->parseExpiry($data['exp_date'] ?? null) ?? now()->addDays($order->package->durationInDays()),
            'status' => 'active',
        ]);
    }

    private function parseExpiry(mixed $expDate): ?Carbon
    {
        if (! $expDate || ! is_numeric($expDate)) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $expDate);
    }

    private function buildM3uUrl(?string $username, ?string $password): ?string
    {
        $streamUrl = XuiSetting::current()->stream_url;

        if (! $streamUrl || ! $username || ! $password) {
            return null;
        }

        return rtrim($streamUrl, '/')."/playlist/{$username}/{$password}/m3u_plus";
    }
}
