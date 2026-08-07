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

    /**
     * Extiende la línea $days días (acepta fracciones, para paquetes con duración en
     * horas) desde su vencimiento actual si sigue vigente, o desde ahora si ya venció.
     * Reactiva la línea si estaba suspendida — renovar siempre implica volver a activarla.
     */
    public function renew(Line $line, float $days): Line
    {
        $base = ($line->expires_at && $line->expires_at->isFuture()) ? $line->expires_at->copy() : now();
        $newExpiry = $base->addSeconds((int) round($days * 86400));

        if ($line->xui_line_id) {
            $this->client->editLine($line->xui_line_id, ['exp_date' => $newExpiry->timestamp]);
        }

        $line->update(['expires_at' => $newExpiry, 'status' => 'active']);

        return $line->fresh();
    }

    public function setSuspended(Line $line, bool $suspended): Line
    {
        if ($line->xui_line_id) {
            $this->client->editLine($line->xui_line_id, ['enabled' => $suspended ? 0 : 1]);
        }

        $line->update(['status' => $suspended ? 'suspended' : 'active']);

        return $line->fresh();
    }

    public function changePassword(Line $line, string $newPassword): Line
    {
        if ($line->xui_line_id) {
            $this->client->editLine($line->xui_line_id, ['password' => $newPassword]);
        }

        $line->update([
            'xui_password' => $newPassword,
            'm3u_url' => $this->buildM3uUrl($line->xui_username, $newPassword),
        ]);

        return $line->fresh();
    }

    /**
     * Trae el estado real desde XUI ONE y lo refleja en la BD local — útil si algo se
     * editó directo en el panel XUI y quedó desincronizado de este lado.
     */
    public function syncFromXui(Line $line): Line
    {
        if (! $line->xui_line_id) {
            throw new RuntimeException('Esta línea no tiene un ID de XUI ONE asociado, no se puede sincronizar.');
        }

        $data = $this->client->getLineInfo($line->xui_line_id);

        $line->update(array_filter([
            'xui_username' => $data['username'] ?? null,
            'max_connections' => isset($data['max_connections']) ? (int) $data['max_connections'] : null,
            'expires_at' => $this->parseExpiry($data['exp_date'] ?? null),
            'status' => $this->mapXuiEnabledFlag($data),
        ], fn ($value) => $value !== null));

        return $line->fresh();
    }

    private function mapXuiEnabledFlag(array $data): ?string
    {
        if (! array_key_exists('enabled', $data)) {
            return null;
        }

        return ((int) $data['enabled']) === 1 ? 'active' : 'suspended';
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
