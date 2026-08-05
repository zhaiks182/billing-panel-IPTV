# Billing Panel — 4LivePro Latino

Panel de reventa de IPTV: los clientes compran paquetes M3U, suben comprobante de pago,
un admin aprueba el pedido y el sistema provisiona automáticamente la línea en un panel XUI ONE.

## Stack

- Laravel 13 (PHP 8.3), Breeze (auth con verificación de email obligatoria)
- Blade + Tailwind CSS 3 + Alpine.js, Vite
- Base de datos: SQLite en desarrollo (`database/database.sqlite`)
- Sin cola real en producción por ahora: `QUEUE_CONNECTION` a revisar (ver `.env`)

## ⚠️ Estado de infraestructura (importante)

- **No hay repositorio git** ni en local (`C:\Claude\Billing Panel`) ni en el VPS
  (`/var/www/desarrollo.4livepro.com`). No hay control de versiones, no hay forma
  de revertir cambios ni de diffear local vs. producción salvo comparando archivos a mano.
  **Recomendado:** inicializar git localmente y usarlo como fuente de verdad, luego
  desplegar al VPS (rsync/git pull) en lugar de editar ambos lados por separado.
- Existen **dos carpetas locales** con el mismo proyecto:
  - `C:\Claude\Billing Panel` ← **la real, activa** (tiene `vendor/`, `node_modules/`,
    `database.sqlite`, toda la app). Trabajar siempre aquí.
  - `C:\Users\Jbrito\OneDrive\Desktop\Claude Code\Billing Panel` ← esqueleto vacío de
    Laravel recién instalado (quedó de una creación inicial). **No usar.** Considerar
    borrarla para evitar confusión en el futuro.

## Flujo de trabajo (decidido 2026-08-05)

**Se trabaja directo sobre el VPS por SSH**, no en local. El usuario no quiere levantar
XAMPP/Laragon para cada sesión. Esto implica:

- Todos los cambios de código se hacen en `/var/www/desarrollo.4livepro.com` vía `ssh whmcs-vps`.
- Para editar un archivo: se descarga a un temporal local (scratchpad), se edita ahí con
  las herramientas normales, y se sube de vuelta (`scp`) — nunca se mantiene una copia
  local persistente sincronizada a mano.
- Tras escribir en `storage/` o `bootstrap/cache/`, devolver el ownership a `www-data:www-data`
  (Apache corre como `www-data`; si quedan archivos de `root` con permisos insuficientes
  puede romper cachés/logs).
- Comandos artisan/composer/npm se ejecutan por SSH: `ssh whmcs-vps "cd /var/www/desarrollo.4livepro.com && php artisan ..."`.
- Previsualización: directamente en https://desarrollo.4livepro.com (no hay entorno local activo).
- **Se inicializó git en el VPS hoy** (no existía antes) con un commit base
  (`Initial snapshot: estado del proyecto al 2026-08-05`) para poder revertir cambios.
  `git config --global --add safe.directory /var/www/desarrollo.4livepro.com` ya quedó
  configurado (necesario porque se conecta como `root` sobre archivos de `www-data`).
- Pendiente/opcional: limpiar `storage/framework/views/*.php` del historial de git (cachés
  de Blade compiladas que quedaron en el primer commit sin querer) y añadirlas a `.gitignore`.
- La carpeta local `C:\Claude\Billing Panel` queda solo como referencia/lectura de este
  `CLAUDE.md`; no se edita código ahí de forma rutinaria.

## Entornos

| Entorno | URL / Host | Notas |
|---|---|---|
| Local (Laragon) | http://localhost:8000 (`php artisan serve`) | PHP en `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` (ver `.claude/launch.json`) |
| Local (XAMPP) | mencionado por el usuario, no configurado aún en este repo | pendiente de confirmar vhost/puerto |
| Desarrollo/staging | https://desarrollo.4livepro.com | VPS, Apache, DocumentRoot `/var/www/desarrollo.4livepro.com/public` |
| Producción cliente (otro proyecto en el mismo VPS) | https://clientes.4livepro.com | no confundir con este proyecto |

## VPS / SSH

- Host: `167.148.33.82`, usuario `root`
- Alias SSH configurado en `~/.ssh/config` (Windows): **`whmcs-vps`** → usa la key
  `~/.ssh/4livepro_deploy`. Conectar con `ssh whmcs-vps`.
- ⚠️ Ojo: en el mismo `~/.ssh/config` existe otro alias, `iptv-vps`, mismo IP pero
  otra key (`iptv_lamp_deploy`) — es para **otro proyecto** (IPTV watch), no confundir.
- Ruta del proyecto en el servidor: `/var/www/desarrollo.4livepro.com`
- Servidor web: Apache (no nginx). Vhosts en `/etc/apache2/sites-available/`:
  `desarrollo.4livepro.com.conf` (+ `-le-ssl.conf` para HTTPS/Let's Encrypt).
- Owner de los archivos en el VPS: `www-data`.

## Flujo de negocio principal

1. Cliente ve paquetes por categoría (`PackageController@index/category`), agrega al
   carrito (`CartController`, basado en sesión) y compra (`OrderController@create/store`)
   subiendo comprobante de pago (`proof_path`) y eligiendo `PaymentMethod`.
2. El pedido nace en `status = pending`. `OrderObserver@created` manda notificación a
   Telegram (`TelegramNotifier`, usa `TelegramSetting` guardado en BD, no en `.env`).
3. Un admin revisa en `Admin\OrderController@index` y aprueba/rechaza/reintenta:
   - **Aprobar/Reintentar** → `XuiLineService@activate`: llama a `XuiOneClient::createLine`
     (API del panel XUI ONE, formato `GET {panel_url}/{access_code}/?api_key=...&action=...`),
     crea un registro `Line` (usuario/clave M3U, `expires_at`, `max_connections`) y notifica
     al cliente (`OrderApproved`). Si XUI falla, el pedido queda en `status = error` para
     reintentar después (no se pierde, no hay reintentos automáticos).
   - **Rechazar** → `status = rejected`, notifica `OrderRejected`, requiere `admin_note` opcional.
   - Cada `Line` creada dispara `LineObserver@created` → notificación a Telegram.
4. **Paquetes trial/demo** (`is_trial = true`, ej. paquete "Demo", 2 horas): el pedido se
   crea en `pending` pero la línea **no** se activa hasta que el usuario verifica su email
   (`VerifyEmailController` → `TrialActivator::activatePendingFor`). Esto evita crear
   líneas XUI reales para correos falsos.
5. Vencimientos: `php artisan lines:send-expiration-reminders {--days=3}` (comando Artisan,
   pensado para cron) busca líneas activas no-trial que vencen pronto y sin recordatorio
   enviado, notifica `LineExpiringSoon` y marca `reminder_sent_at`.

## Modelo de datos (tablas/relaciones clave)

- `users`: + `role` (`customer`|`admin`, enum), `phone`, `phone_country_code`, `company`,
  dirección completa (billing fields, migración `2026_08_04_115353`). `isAdmin()` en el modelo.
- `package_categories` → `packages` (1:N, ordenado por precio)
- `packages`: `price`, `duration_days`, `duration_unit` (`days`|`hours`), `max_connections`,
  `is_active`, `is_trial`, `xui_package_id` (FK lógica al ID de paquete en XUI ONE — **si
  está vacío, la activación falla** con mensaje explícito), `features` (texto multilínea).
- `payment_methods`: nombre + instrucciones de pago (texto libre), `is_active`.
- `orders`: `user_id`, `package_id`, `payment_method_id` (nullable — permite pedidos sin
  método, ej. trials), `amount`, `proof_path`, `status` (`pending`|`approved`|`rejected`|`error`),
  `admin_note`, `is_renewal`, `approved_by`, `approved_at`. `hasOne(Line)`.
- `lines`: `user_id`, `order_id`, `xui_line_id`, `xui_username`, `xui_password`, `m3u_url`,
  `max_connections`, `expires_at`, `status`, `reminder_sent_at`.
- `xui_settings` (singleton vía `XuiSetting::current()`): `panel_url`, `access_code`,
  `api_token` (**encriptado** con cast `encrypted`), `stream_url`, `server_url`.
- `mail_settings`, `telegram_settings`, `turnstile_settings`: configuración dinámica en BD
  editable desde el admin, en vez de solo `.env` (patrón repetido: modelo singleton +
  controller `edit/update` que aplica los valores a `Config::set(...)` en runtime cuando aplica).

## Panel de administración (`/admin`, middleware `admin` → `EnsureUserIsAdmin`)

- Dashboard, Pedidos (aprobar/rechazar/reintentar con filtros por estado y fecha)
- CRUD de Paquetes, Categorías, Métodos de pago (resource controllers, español en las rutas:
  `/admin/paquetes`, `/admin/categorias`, `/admin/metodos-pago`)
- Configuración: XUI ONE, Correo (SMTP con test de envío), Turnstile (captcha Cloudflare),
  Telegram (bot notificaciones)
- Usuarios: listar, verificar email manualmente, eliminar

## Puntos abiertos / riesgos conocidos

- **Renovación en XUI**: el comentario en `XuiOneClient` dice explícitamente que el
  mecanismo de renovación (qué campo de `edit_line` extiende `exp_date`) **no se pudo
  verificar** porque solo había un paquete demo (duración 0) disponible al integrar.
  Falta probar con un paquete real cuando exista uno con `xui_package_id` de pago.
  `Order.is_renewal` existe en el modelo pero no vi lógica que lo use todavía — revisar.
- Sin git → sin despliegue reproducible. Confirmar con el usuario el flujo real para
  pasar cambios de local a `desarrollo.4livepro.com` (¿rsync manual? ¿ambas carpetas se
  editan por separado y quedan casi sincronizadas por casualidad?).
- `config/countries.php` existe (custom) — revisar contenido si se toca el formulario de
  dirección/perfil.
- XAMPP local mencionado por el usuario pero no hay vhost/configuración de XAMPP en este
  repo todavía; el `launch.json` de Claude apunta a Laragon, no XAMPP.

## Comandos útiles

```bash
php artisan serve                              # local
php artisan migrate                            # aplicar migraciones
php artisan db:seed                            # datos demo (admin@example.com / password)
php artisan lines:send-expiration-reminders    # recordatorios de vencimiento (cron)
npm run dev                                    # vite dev
npm run build                                  # vite build
ssh whmcs-vps                                  # conectar al VPS de desarrollo/producción
```

## Bitácora de sesiones

### 2026-08-05
- Sesión anterior se perdió por un error de Claude. Se re-analizó el proyecto desde cero.
- Se detectó que la carpeta de esta sesión de Claude Code (OneDrive) estaba vacía/desactualizada;
  el proyecto real vive en `C:\Claude\Billing Panel`. El usuario cambió el directorio de trabajo
  de la sesión a `C:\Claude\Billing Panel` — confirmado correcto.
- Se verificó acceso SSH al VPS (`whmcs-vps`, `167.148.33.82`) — funcional.
- Se creó este archivo `CLAUDE.md` como documentación viva del proyecto.
- Pendiente: decidir próxima tarea de desarrollo con el usuario.
