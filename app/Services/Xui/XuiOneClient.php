<?php

namespace App\Services\Xui;

use App\Models\XuiSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP para la API reseller de XUI ONE.
 *
 * Formato confirmado contra un panel real: GET {panel_url}/{access_code}/?api_key=...&action=...
 * Acciones confirmadas: packages, get_line, get_lines, create_line (param: package),
 * edit_line, enable_line, disable_line, delete_line.
 *
 * Renovación de vencimiento (verificado 2026-08-07 contra un panel real, con una línea demo
 * real creada/borrada para la prueba): `edit_line` con `exp_date` (probado como timestamp Unix,
 * como fecha "YYYY-MM-DD", y con 6 nombres de parámetro alternativos) **no tiene ningún efecto**
 * — la API responde éxito pero el vencimiento real no cambia. La única forma confirmada de
 * extender el vencimiento es reenviar `package` (el mismo xui_package_id que usa create_line):
 * el panel reaplica la duración fija de ese paquete sobre el vencimiento actual. No existe
 * ningún mecanismo para sumar una cantidad arbitraria de días.
 *
 * Suspender/reactivar: `edit_line` con un parámetro `enabled` tampoco tiene efecto — son
 * acciones separadas, `enable_line`/`disable_line`, cada una con un único parámetro `id`.
 *
 * `edit_line` sí funciona para `password` (verificado) y presumiblemente otros campos simples
 * de texto/número (username, max_connections, bouquets_selected) — solo `exp_date`/`enabled`
 * están confirmados como no-funcionales en esta instalación.
 */
class XuiOneClient
{
    private XuiSetting $settings;

    public function __construct(?XuiSetting $settings = null)
    {
        $this->settings = $settings ?? XuiSetting::current();
    }

    public function getPackages(): array
    {
        return $this->call('packages');
    }

    public function createLine(string $xuiPackageId): array
    {
        return $this->call('create_line', ['package' => $xuiPackageId]);
    }

    public function getLineInfo(string $xuiLineId): array
    {
        return $this->call('get_line', ['id' => $xuiLineId]);
    }

    public function deleteLine(string $xuiLineId): array
    {
        return $this->call('delete_line', ['id' => $xuiLineId]);
    }

    /**
     * Uso confirmado: `password` (cambiar contraseña) y `package` (renovar aplicando la
     * duración fija de ese paquete). `exp_date`/`enabled` no tienen efecto — ver docblock
     * de la clase.
     */
    public function editLine(string $xuiLineId, array $params): array
    {
        return $this->call('edit_line', array_merge(['id' => $xuiLineId], $params));
    }

    public function enableLine(string $xuiLineId): array
    {
        return $this->call('enable_line', ['id' => $xuiLineId]);
    }

    public function disableLine(string $xuiLineId): array
    {
        return $this->call('disable_line', ['id' => $xuiLineId]);
    }

    private function call(string $action, array $params = []): array
    {
        if (! $this->settings->panel_url || ! $this->settings->access_code || ! $this->settings->api_token) {
            throw new XuiApiException('El panel XUI ONE no está configurado (Admin > Configuración XUI).');
        }

        $baseUrl = rtrim($this->settings->panel_url, '/').'/'.$this->settings->access_code.'/';

        $response = Http::timeout(15)->get($baseUrl, array_merge([
            'api_key' => $this->settings->api_token,
            'action' => $action,
        ], $params));

        return $this->handle($response);
    }

    private function handle(Response $response): array
    {
        if ($response->failed()) {
            throw new XuiApiException("Error de la API XUI ONE ({$response->status()}): {$response->body()}");
        }

        $body = $response->json();

        if (($body['status'] ?? null) !== 'STATUS_SUCCESS') {
            throw new XuiApiException('Error de la API XUI ONE: '.($body['error'] ?? $body['status'] ?? 'respuesta inválida'));
        }

        return $body['data'] ?? [];
    }
}
