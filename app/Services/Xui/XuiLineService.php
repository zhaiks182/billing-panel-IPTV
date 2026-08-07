<?php

namespace App\Services\Xui;

use App\Models\Line;
use App\Models\Order;
use App\Models\Package;
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
     * Extiende la línea aplicando la duración fija de $package sobre su vencimiento actual
     * (o desde ahora si ya venció). Usado tanto por "Renovar" (con el paquete original del
     * pedido) como por "Aplicar paquete" (con cualquier paquete elegido por el admin) — es
     * la ÚNICA forma de extender el vencimiento que la API de XUI ONE soporta de verdad
     * (verificado 2026-08-07 contra un panel real): no existe forma de sumar una cantidad
     * arbitraria de días, solo de reaplicar la duración fija de un paquete completo.
     * Reactiva la línea si estaba suspendida — aplicar un paquete siempre reactiva.
     */
    public function applyPackage(Line $line, Package $package): Line
    {
        if (! $package->xui_package_id) {
            throw new RuntimeException("El paquete «{$package->name}» no tiene un ID de paquete XUI asignado (Admin > Paquetes).");
        }

        if ($line->xui_line_id) {
            $this->client->editLine($line->xui_line_id, ['package' => $package->xui_package_id]);

            $fresh = $this->client->getLineInfo($line->xui_line_id);
            $newExpiry = $this->parseExpiry($fresh['exp_date'] ?? null) ?? $line->expires_at;
        } else {
            // Sin xui_line_id (línea de prueba o importada) — no hay nada real que consultar,
            // se calcula localmente sumando la duración del paquete al vencimiento actual.
            $base = ($line->expires_at && $line->expires_at->isFuture()) ? $line->expires_at->copy() : now();
            $newExpiry = $base->addSeconds((int) round($package->durationInDays() * 86400));
        }

        $line->update(['expires_at' => $newExpiry, 'status' => 'active']);

        return $line->fresh();
    }

    public function setSuspended(Line $line, bool $suspended): Line
    {
        if ($line->xui_line_id) {
            $suspended
                ? $this->client->disableLine($line->xui_line_id)
                : $this->client->enableLine($line->xui_line_id);
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

    /**
     * Bug real encontrado y corregido 2026-08-07: Carbon::createFromTimestamp() crea el
     * instante en UTC por defecto. Eloquent NO normaliza a config('app.timezone') antes de
     * guardar en la columna DATETIME — escribe la hora tal cual el objeto la tiene (UTC), pero
     * al releerla la interpreta como si estuviera en config('app.timezone') (America/Guayaquil,
     * UTC-5). Sin el ->setTimezone() de abajo, cada ida y vuelta por la BD sumaba 5 horas de más
     * al vencimiento real — afectaba tanto a esta función (ya existía desde activate()) como a
     * cualquier lectura posterior de esa misma línea.
     */
    private function parseExpiry(mixed $expDate): ?Carbon
    {
        if (! $expDate || ! is_numeric($expDate)) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $expDate)->setTimezone(config('app.timezone'));
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
