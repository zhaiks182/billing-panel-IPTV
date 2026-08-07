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
 * edit_line, delete_line. El mecanismo exacto de renovación (qué campo extiende exp_date
 * en edit_line) no pudo verificarse porque el único paquete disponible al integrar tenía
 * duración 0 (paquete "Demo"/trial) — revisar con un paquete real cuando exista.
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
     * `edit_line` está confirmado como acción real de la API (ver docblock de la clase),
     * pero el significado exacto de sus parámetros para extender `exp_date` no se pudo
     * verificar contra un paquete real (ver "Puntos abiertos" en CLAUDE.md) — se usa la
     * convención más común de XUI ONE (`exp_date` en timestamp Unix, `enabled` 0/1,
     * `password`). Verificar contra el panel real antes de confiar en esto a ciegas con
     * un cliente de pago.
     */
    public function editLine(string $xuiLineId, array $params): array
    {
        return $this->call('edit_line', array_merge(['id' => $xuiLineId], $params));
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
