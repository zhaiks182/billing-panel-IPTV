# Billing Panel — 4LivePro Latino

Panel de reventa de IPTV: los clientes compran paquetes M3U, suben comprobante de pago,
un admin aprueba el pedido y el sistema provisiona automáticamente la línea en un panel XUI ONE.

## Stack

- Laravel 13 (PHP 8.3), Breeze (auth con verificación de email obligatoria)
- Blade + Tailwind CSS 3 + Alpine.js, Vite
- Base de datos: **MySQL en local** (Laragon, `billing_panel` en `127.0.0.1:3306`, usuario
  `root` sin clave — el proyecto arrancó con SQLite pero el `.env` local cambió a MySQL en
  algún punto de una sesión anterior, ver bitácora del 05-08; el driver `sqlite` sigue
  soportado por Laravel si hiciera falta volver). MySQL también en instalaciones nuevas vía
  `install.sh` (ver [INSTALL.md](INSTALL.md)) y en el VPS de desarrollo.
- Sin cola real en producción por ahora: no hay ningún `ShouldQueue`, las notificaciones se
  envían sync. `QUEUE_CONNECTION=database` está seteado por si se necesita a futuro.

## Mapa de archivos clave

Referencia rápida de qué hace cada archivo — para el detalle de negocio/decisiones de cada
uno, ver la sección correspondiente más abajo o la bitácora. Todo lo que no tiene una nota
especial es scaffolding estándar de Laravel Breeze, sin personalizar.

**Controladores públicos** (`app/Http/Controllers`)
- `PackageController` — home y listado de paquetes por categoría.
- `CartController` — carrito basado en sesión (un solo paquete a la vez, no multi-ítem).
- `OrderController` — checkout (`create`/`store`), registro de invitado (`registerGuest`,
  duplicado con `RegisteredUserController`, ver "Registro de usuarios"), flujo trial
  (`storeTrial`) y polling de estado (`status`, usado por `trialGateForm` en `app.js`).
- `DashboardController` (raíz, no confundir con `Admin\DashboardController`) — "Mis Enlaces
  M3U" + pedidos recientes del cliente logueado.
- `ProfileController` — editar perfil / eliminar cuenta, 100% Breeze de fábrica, sin tocar.
- `TelegramWebhookController` — único controlador público sin auth/CSRF, ver sección
  "Bot de Telegram" más abajo.
- `TicketController` — módulo de soporte (clientes con sesión **e** invitados sin cuenta),
  ver sección "Módulo de Tickets de Soporte".

**Auth** (`app/Http/Controllers/Auth`) — Breeze de fábrica excepto donde se indica:
- `RegisteredUserController` — **personalizado**, ver "Registro de usuarios".
- `PasswordResetLinkController`, `NewPasswordController` — **personalizados** (mensajes en
  español vía `STATUS_MESSAGES`, Turnstile, medidor de contraseña), ver bitácora 05-08.
- `VerifyEmailController` — **personalizado**, dispara `TrialActivator::activatePendingFor()`
  tras verificar (ver "Flujo de negocio principal", paso 4).
- `AuthenticatedSessionController`, `ConfirmablePasswordController`,
  `EmailVerificationNotificationController`, `EmailVerificationPromptController`,
  `PasswordController` — sin personalizar.

**Admin** (`app/Http/Controllers/Admin`)
- `DashboardController` — estadísticas con filtro de fecha, ver "Panel de administración".
- `OrderController` — aprobar/rechazar/reintentar pedidos, ver "Flujo de negocio principal".
- `PackageController`, `PackageCategoryController`, `PaymentMethodController` — CRUD estándar
  (`Route::resource`, español en las rutas).
- `UserController` — listar/verificar/eliminar usuarios, modal de dirección (ver "Panel de
  administración").
- `XuiSettingController`, `MailSettingController`, `TurnstileSettingController`,
  `TelegramSettingController` — edición de cada singleton de configuración (`XuiSetting`,
  `MailSetting`, `TurnstileSetting`, `TelegramSetting`), cada uno con su propio botón de
  "probar" que no requiere guardar antes.
- `EmailTemplateController` — editor de las 9 plantillas de correo, ver "Plantillas de correo".
- `TicketController` — listado/detalle/respuesta/gestión de tickets, ver "Módulo de Tickets
  de Soporte".

**Modelos** (`app/Models`)
- `User` — `role` (customer/admin), dirección completa, `isAdmin()`, `hasVerifiedEmail()`.
- `PackageCategory`, `Package`, `PaymentMethod`, `Order`, `Line` — ver "Modelo de datos".
- `EmailTemplate` — ver "Plantillas de correo".
- `Ticket`, `TicketMessage`, `TicketAttachment` — ver "Módulo de Tickets de Soporte".
- `XuiSetting`, `MailSetting`, `TurnstileSetting`, `TelegramSetting` — singletons de config
  (patrón `::current()`, un solo registro en la tabla, se crea vacío si no existe).

**Servicios** (`app/Services`)
- `InvoicePdfService` — genera el PDF de factura con dompdf, ver "PDF de facturas".
- `Xui\XuiOneClient` — cliente HTTP crudo de la API de XUI ONE (`GET {panel}/{access_code}
  /?api_key=...&action=...`; acciones: `packages`, `create_line`, `get_line`, `delete_line`).
  Lanza `XuiApiException` si la respuesta no es `STATUS_SUCCESS`.
- `Xui\XuiLineService` — capa de negocio sobre `XuiOneClient`: crea la línea al aprobar un
  pedido, arma la URL M3U, calcula `expires_at`. Cada pedido genera su propia `Line`
  independiente (un cliente puede acumular varias líneas activas).
- `Xui\TrialActivator` — activa la línea trial tras verificar el correo, ver "Flujo de
  negocio principal".
- `Telegram\TelegramNotifier` — envía mensajes (`send`/`sendTo`) y gestiona el webhook
  (`setWebhook`/`deleteWebhook`), ver "Bot de Telegram".
- `Telegram\SalesReportBuilder` — arma el texto del resumen de ventas del día, compartido
  entre `/ventashoy` y el resumen automático de las 10pm.

**Notificaciones** (`app/Notifications`) — todas pasan por `EmailTemplate::mail()`, ver
"Plantillas de correo": `OrderInvoice`, `OrderApproved`, `OrderRejected`, `LineExpiringSoon`,
`TicketCreated`, `TicketReplied`, `TicketClosed`.
(`VerifyEmail` y `ResetPassword` no son clases propias — se personalizan vía
`Notification::toMailUsing()` en `AppServiceProvider`, no archivos en `app/Notifications`.)

**Observers** (`app/Observers`) — ambos solo notifican a Telegram, no tocan la lógica de
negocio: `OrderObserver@created` (pedido nuevo), `LineObserver@created` (línea activada).

**Middleware** (`app/Http/Middleware`)
- `EnsureUserIsAdmin` — alias `admin`, usado en el grupo de rutas `/admin`.
- `EnsureEmailIsVerified` — alias `verified` (reemplaza el de Laravel): hace lo mismo pero
  además guarda `url.intended` para regresar al usuario a donde quería ir (ej. comprar un
  paquete) después de verificar, en vez de mandarlo siempre a `/dashboard`.

**Reglas de validación** (`app/Rules`): `ValidTurnstile` — no-op si Turnstile no está activo.

**Comandos Artisan** (`app/Console/Commands`, programados en `routes/console.php`):
`CreateAdminUser` (manual, ver "Comandos útiles"), `SendLineExpirationReminders`
(`lines:send-expiration-reminders`, diario 9am), `SendTelegramDailySalesSummary`
(`telegram:daily-summary`, diario 10pm) — el cron del VPS ya corre `schedule:run` cada
minuto, cualquier tarea nueva en `routes/console.php` empieza a correr sola sin tocar el
servidor (ver "Bot de Telegram").

## Instalación en un servidor nuevo

Ver [INSTALL.md](INSTALL.md) y [install.sh](install.sh) — instalador para un LAMP (Debian/Ubuntu
+ Apache) nuevo: crea la base de datos MySQL, el `.env` de producción, corre migraciones,
crea el admin, configura el VirtualHost y el cron. Pensado para instalar este panel en el
servidor de un cliente/reseller nuevo (no solo para `desarrollo.4livepro.com`).

- El admin del sistema **ya no se crea por seeder** (antes `DatabaseSeeder` dejaba
  `admin@example.com` / `password` fijo — riesgo de seguridad en producción). Ahora se crea
  con `php artisan app:create-admin {email} {password} --name="..."`
  ([app/Console/Commands/CreateAdminUser.php](app/Console/Commands/CreateAdminUser.php)),
  idempotente (si el correo ya existe, actualiza clave y lo vuelve admin). `install.sh` lo
  llama automáticamente con lo que el usuario indique.
- `database/seeders/DatabaseSeeder.php` solo carga catálogo de ejemplo (categoría, paquetes,
  métodos de pago) — ya no crea usuarios.

## ⚠️ Estado de infraestructura (importante)

- Existen **dos carpetas locales** con el mismo proyecto:
  - `C:\Claude\Billing Panel` ← **la real, activa** (tiene `vendor/`, `node_modules/`,
    `database.sqlite`, toda la app, y ahora el repo git). Trabajar siempre aquí.
  - `C:\Users\Jbrito\OneDrive\Desktop\Claude Code\Billing Panel` ← esqueleto vacío de
    Laravel recién instalado (quedó de una creación inicial). **No usar.** Considerar
    borrarla para evitar confusión en el futuro.
- Hay **dos repos git independientes** (no comparten historial, es intencional):
  uno en local (`C:\Claude\Billing Panel`) que es el que se usa para desarrollar, y otro
  en el VPS (`/var/www/desarrollo.4livepro.com`) que solo sirve de red de seguridad por si
  se edita algo ahí directamente. El flujo normal es local → VPS, nunca al revés.

## Flujo de trabajo (decidido 2026-08-05)

**Se edita en local (`C:\Claude\Billing Panel`) y se despliega al VPS después.** El usuario
no quiere depender de un servidor local (XAMPP/Laragon) para cada sesión, así que la
previsualización real se hace en https://desarrollo.4livepro.com, pero el código fuente
vive y se versiona en local.

1. Editar archivos normalmente en `C:\Claude\Billing Panel` (Read/Edit/Write).
2. Probar localmente si hace falta ejecutar algo (`php artisan ...`, tests) usando el PHP
   de Laragon (`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`) — no es obligatorio
   levantar el servidor completo solo para editar código.
3. Commit local:
   ```bash
   git add -A && git commit -m "mensaje descriptivo"
   ```
4. Desplegar al VPS con **[`deploy.sh`](deploy.sh)** (agregado 2026-08-05, ver más abajo):
   ```bash
   ./deploy.sh                # deploy normal
   ./deploy.sh --migrate      # si el commit agrega migraciones nuevas
   ./deploy.sh --no-build     # si no tocaste resources/css o resources/js (más rápido)
   ```
5. Verificar en https://desarrollo.4livepro.com (el script ya hace un `curl` de verificación
   al final, pero conviene mirarlo en el navegador también).

### `deploy.sh`

Reemplaza la secuencia manual de comandos que se venía repitiendo a mano en cada deploy
(`git archive | tar`, `chown`, `npm run build`, `optimize:clear`...). Se creó después de un
incidente real: un `chown` que solo corría al principio del deploy dejó una ventana de unos
segundos donde `storage/framework/views/` no era escribible por `www-data`, y un cliente real
que probó registrarse justo en ese momento se topó con un error 500 (ver "Incidente 2026-08-05"
más abajo). El script:
1. Exige que no haya cambios sin commitear (si los hay, aborta).
2. `git archive HEAD | ssh tar -x` al VPS.
3. `chown`/`chmod` de permisos — **antes** de tocar cachés.
4. Migraciones (`--migrate`, opcional).
5. `npm install && npm run build` + `chown` de `public/build` (salvo `--no-build`).
6. `php artisan optimize:clear`.
7. `chown` de permisos **otra vez** — por si `optimize:clear` recreó algo como root.
8. Commit de respaldo en el git local del VPS.
9. `curl` de verificación — si el sitio no responde 200, el script termina con error.

**Nota sobre `git archive | tar`:** solo agrega/sobrescribe archivos, no borra los que ya
no estén en el commit — si se elimina un archivo en local (como pasó hoy con las 3 vistas
huérfanas), `deploy.sh` no lo borra del VPS automáticamente, hay que hacerlo a mano con
`ssh whmcs-vps "rm /var/www/desarrollo.4livepro.com/ruta/al/archivo"`.

⚠️ **Incidente 2026-08-05**: un usuario real intentó registrarse (`jorgeevil182@gmail.com`,
compra de la demo) justo durante/después de un deploy y le dio error 500 silencioso (el botón
se quedó en "Enviando...", sin popup, sin correo). En el log:
`tempnam(): file created in the system's temporary directory` al compilar una vista Blade —
`storage/framework/views/` no era escribible por `www-data` en ese momento. Causa probable:
el `chown` del paso 4 corre **antes** de `php artisan optimize:clear` (que borra la caché de
vistas compilada); si un visitante real pega justo en esa ventana de unos segundos mientras
Laravel recompila las vistas, puede toparse con permisos inconsistentes. Se verificó que ya
no persiste (chown posterior lo corrigió solo), pero **agregar un `chown -R www-data:www-data
storage bootstrap/cache` también al final de cada deploy** (después de `optimize:clear`), no
solo al principio, para cerrar esa ventana. El usuario no llegó a crearse en la BD (la request
falló antes de guardar nada), así que no hay que limpiar datos a medias.

**Nota sobre línea de comandos en Windows:** `rsync` no está disponible en el Git Bash de
esta máquina; por eso se usa `git archive | ssh ... tar -x` en vez de `rsync -avz`. `scp` y
`md5sum`/`sha1sum` sí están disponibles y sirven para comparar archivos sueltos si hace falta.

- **git en el VPS**: inicializado hoy (no existía antes), con
  `git config --global --add safe.directory /var/www/desarrollo.4livepro.com` (necesario
  porque se conecta como `root` sobre archivos de `www-data`). Sirve como respaldo/rollback
  local del servidor, no como el repo de desarrollo.
- **git en local**: inicializado hoy también. Historial empieza en
  `Baseline: sincronizado con VPS (desarrollo.4livepro.com) al 2026-08-05`, que se verificó
  archivo por archivo (hash md5) contra el VPS antes de crear el commit — estaban casi
  idénticos, con 4 diferencias que ya se resolvieron (ver bitácora).
- Pendiente/opcional: limpiar `storage/framework/views/*.php` cacheado que quedó en el
  primer commit del VPS sin querer, y las 3 vistas huérfanas (`admin/index.blade.php`,
  `login.blade.php`, `navigation.blade.php` en la raíz de `resources/views/`) que no las
  referencia ningún controller — parecen restos de una reorganización anterior.

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
- **Node.js 20 instalado el 2026-08-05** (vía NodeSource, `apt install nodejs`) — no venía en el
  VPS originalmente. Necesario porque `public/build` (Vite) está en `.gitignore`, así que el
  deploy normal (`git archive | ssh tar`) nunca lo toca: **cualquier cambio en `resources/css` o
  `resources/js` requiere correr `npm install && npm run build` a mano en el VPS después de
  desplegar**, si no los estilos/JS quedan desactualizados en el servidor aunque el código fuente
  ya esté ahí. Antes de esa fecha no había Node en el VPS y `public/build` llevaba desde el
  04-08 sin actualizarse (nadie lo había necesitado hasta el cambio de scrollbar).

## Repositorio GitHub

- **https://github.com/zhaiks182/billing-panel-IPTV** (rama `main`) — **repo público**, creado
  por el usuario y subido el 2026-08-05. Contiene todo el historial de git local (empieza en
  el commit "Baseline: sincronizado con VPS..."). No tiene GitHub Actions/CI configurado.
- Alias SSH en `~/.ssh/config` (Windows): **`github.com`** → usa la key dedicada
  `~/.ssh/github_billing_panel` (generada el 2026-08-05 solo para este repo, sin passphrase).
  La pública ya está agregada a la cuenta de GitHub del usuario (`zhaiks182`).
- El remoto local se llama `origin`. Push normal: `git push` (ya trackea `origin/main`).
- Es independiente del deploy a `desarrollo.4livepro.com` (que sigue siendo por
  `git archive | ssh tar`, ver "Flujo de trabajo" arriba) — GitHub es solo respaldo/historial
  por ahora, no dispara ningún deploy automático.
- Se confirmó antes de subir que no hay secretos trackeados (`.env`, llaves, etc. — todos
  excluidos por `.gitignore` desde el primer commit).

## Registro de usuarios (formulario de creación de cuenta)

`RegisteredUserController@store` ([app/Http/Controllers/Auth/RegisteredUserController.php](app/Http/Controllers/Auth/RegisteredUserController.php))
valida y guarda **todos** los campos de dirección como atributos del usuario (antes eran
`nullable`, ahora `required` — a pedido del usuario, 2026-08-05). La tabla `users` sigue con
esas columnas `nullable` a nivel de esquema (no se tocó la migración original) para no romper
usuarios ya registrados antes de este cambio; la obligatoriedad se aplica solo en la validación
del formulario de registro.

- `address_line_1`: requerido, texto libre (sí acepta números — una dirección real los necesita,
  ej. "Av. Amazonas 123"). Solo `city` y `state` están restringidos a **solo letras** (regex
  `/^[\pL\s\.\'-]+$/u`, acepta acentos/ñ, espacios, puntos, apóstrofes y guiones para nombres
  compuestos) — así lo pidió el usuario explícitamente, a diferencia de la dirección.
- `postal_code`: requerido, **solo dígitos** (regex `/^[0-9]+$/`, no se usa la regla `integer`
  de Laravel para no arriesgar que recorte ceros a la izquierda tipo "00926"). En el formulario,
  el campo tiene `x-data` + `@input` que descarta cualquier carácter no numérico mientras se
  escribe (⚠️ el `x-data` en ese `<x-text-input>` es necesario aunque parezca redundante — sin
  un scope Alpine propio o heredado, `@input` no hace nada; así se descubrió el bug la primera vez).
- `country`: requerido, debe ser uno de `config('countries.php')` (`Rule::in(...)`). La lista
  se amplió a **todos los países de América Latina y Norteamérica + España** (única europea) —
  se agregaron Belice y Haití, que faltaban. Ese mismo archivo se reutiliza para el selector de
  código de país telefónico, así que agregar un país ahí lo agrega a ambos selectores.
- `password`: `Password::min(8)->mixedCase()->numbers()->symbols()` — mínimo 8, mayúscula +
  minúscula, número y carácter especial. El usuario solo pidió explícitamente mayúscula/minúscula/
  especial; se agregó también el requisito de número por ser estándar en "contraseña fuerte" —
  si no lo quieren, es un solo `->numbers()` menos en la regla.
- **Medidor de fuerza** (bajo/medio/alto) en el propio formulario: Alpine.js calcula un puntaje
  0-6 (longitud ≥8, longitud ≥12, minúscula, mayúscula, número, símbolo) y lo muestra como barra +
  texto mientras el usuario escribe, sin llamada al servidor. Es solo indicativo — quien decide
  si la contraseña es válida sigue siendo la regla de Laravel en el backend. **Siempre visible**
  (a pedido del usuario, 2026-08-05) — con el campo vacío muestra la barra en gris y "Seguridad: —",
  no se oculta hasta que se empieza a escribir.
- Los desplegables de código de país telefónico y de país de la dirección usan `bg-ink` (el mismo
  fondo que el resto de la página) en vez de `bg-panel-alt`, que se veía como un tono distinto
  flotando sobre el formulario — a pedido del usuario, 2026-08-05.
- ✅ **Duplicación resuelta (2026-08-05)**: el bloque de "Información Personal" +
  "Dirección de Facturación" + "Seguridad de la Cuenta" que antes estaba copiado y pegado
  en `auth/register.blade.php` y `orders/create.blade.php` ahora vive en un solo componente,
  [`<x-guest-registration-fields />`](resources/views/components/guest-registration-fields.blade.php),
  usado por los dos. Un cambio a esos campos ahora se hace una sola vez. `orders/create.blade.php`
  sigue usándolo dentro de su propio `@guest` (solo aplica cuando el que compra no tiene cuenta
  todavía) — `OrderController@registerGuest` y `RegisteredUserController@store` siguen siendo
  dos validaciones separadas (reglas iguales, pero cada una con su propio flujo de creación de
  usuario), eso no se unificó.
- **Cloudflare Turnstile conectado (2026-08-05)**: existía todo el módulo de configuración
  (`TurnstileSetting`, `ValidTurnstile`, Admin > Cloudflare Turnstile) pero nunca se usaba en
  ningún formulario. Ahora [`<x-turnstile-widget :site-key="..." />`](resources/views/components/turnstile-widget.blade.php)
  se incluye en el registro y en el checkout de invitado, y `cf-turnstile-response` se valida
  con la regla `ValidTurnstile` en ambos controllers. Si Turnstile no está activado/configurado
  en Admin, el componente no renderiza nada (`$siteKey` null) y la regla de validación no
  falla (`ValidTurnstile` ya hacía ese chequeo de `isActive()` internamente) — cero impacto si
  el admin no lo configura. Centrado con `flex flex-col items-center` en el propio componente
  (a pedido del usuario, 2026-08-05) — como es un solo componente compartido, centrarlo ahí
  ya aplica a los dos formularios sin tocarlos por separado. Verificado visualmente activando
  temporalmente las llaves de prueba públicas de Cloudflare (`1x00000000000000000000AA` /
  `1x0000000000000000000000000000000AA`, siempre pasan, no necesitan dominio real) en el
  `TurnstileSetting` local, confirmando por coordenadas que el widget queda centrado respecto
  al formulario — y revirtiendo la configuración de prueba después.
- **Rate limiting agregado**: `throttle:10,1` (10 intentos por minuto por IP) en `POST /register`
  y en `POST /paquetes/{package}/comprar` — antes no tenían ningún límite, a diferencia de
  login/verificación de correo que sí lo traen por defecto de Breeze.
- Probado end-to-end en local, con servidor limpio (pestaña nueva, sesión cerrada) para evitar
  falsos positivos de sesiones/timers de pruebas anteriores: registro completo y checkout de
  demo, ambos con datos guardados correctamente después del refactor a componente compartido.
  Validación server-side probada antes directo con `Validator::make` para 5 casos (ciudad con
  números, código postal con letras, país no permitido, contraseña débil, todo válido).

## Flujo de negocio principal

1. Cliente ve paquetes por categoría (`PackageController@index/category`), agrega al
   carrito (`CartController`, basado en sesión) y compra (`OrderController@create/store`)
   subiendo comprobante de pago (`proof_path`) y eligiendo `PaymentMethod`.
2. El pedido nace en `status = pending`. `OrderObserver@created` manda notificación a
   Telegram (`TelegramNotifier`, usa `TelegramSetting` guardado en BD, no en `.env`) — el
   mensaje incluye el estado en español (`Pendiente`/`Aprobado`/`Rechazado`/`Error`, no el
   valor crudo en inglés de la columna) y termina con un enlace directo a
   `admin.orders.index` filtrado por pendientes, para aprobar el pedido sin buscarlo
   (2026-08-06, a pedido del usuario).
   Además (solo pedidos de pago, no trial), `OrderController@store` dispara `OrderInvoice`
   — un correo de "factura pendiente de pago" con los datos del pedido (monto, método de
   pago, dirección de facturación) a modo de confirmación de recepción del comprobante;
   no es un aviso de aprobación, esa es `OrderApproved` más adelante.
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
- `email_templates`: **una fila fija por cada correo transaccional** (`verify_email`,
  `order_invoice`, `order_approved`, `order_rejected`, `line_expiring_soon`, `password_reset`,
  `ticket_created`, `ticket_reply`, `ticket_closed` — 9 en total, clave en `key`, único). Cada
  fila tiene `subject`, `html_body`, `text_body`. Editable desde Admin > Plantillas de correo.
  Ver sección "Plantillas de correo" más abajo — estas filas son requeridas para que el
  sistema pueda enviar cualquier correo, por eso se insertan directo en migraciones de datos,
  no en el seeder opcional.
- `tickets`, `ticket_messages`, `ticket_attachments` — ver "Módulo de Tickets de Soporte".

## Panel de administración (`/admin`, middleware `admin` → `EnsureUserIsAdmin`)

- Dashboard, Pedidos (aprobar/rechazar/reintentar con filtros por estado y fecha)
  - El dashboard (`Admin\DashboardController`) tiene su propio filtro `date_from`/`date_to`
    (2026-08-06, a pedido del usuario) — sin fechas en el query string, por defecto usa el
    mes actual (`now()->startOfMonth()`/`endOfMonth()`). Solo afecta las 3 tarjetas que tienen
    sentido acotar a un período (ingresos, clientes nuevos, pedidos aprobados) — "pedidos
    pendientes/con error" y "líneas activas" siguen siendo conteos del estado actual, no del
    período, y "líneas por vencer" sigue fija a los próximos 3 días. Mismo patrón de formulario
    GET que ya usaba `admin/orders/index.blade.php`, reutilizado tal cual.
- CRUD de Paquetes, Categorías, Métodos de pago (resource controllers, español en las rutas:
  `/admin/paquetes`, `/admin/categorias`, `/admin/metodos-pago`)
- Configuración: XUI ONE, Correo (SMTP con test de envío), Turnstile (captcha Cloudflare),
  Telegram (bot notificaciones)
- Usuarios: listar, verificar email manualmente, eliminar. La tabla no tenía cómo mostrar
  dirección/ciudad/país (sí se guardan en `users`, solo no se mostraban) — agregado
  (2026-08-05): clic en el nombre abre un modal con esos datos. Implementado sin ruta ni
  petición nueva: `admin/users/index.blade.php` serializa `usersData` (un array con los
  campos de dirección de cada usuario de la página actual) vía `@js()` en el `x-data` de la
  tabla, y el modal es un `x-show` que busca por id — mismo patrón que el modal de espera del
  demo (`orders/create.blade.php`). No escala a miles de usuarios por página (manda todos los
  datos igual estén abiertos o no), pero con paginación de 20 no es un problema.
- Plantillas de correo (`/admin/plantillas-correo`): ver sección dedicada abajo.

## Bot de Telegram: comando `/ventashoy` y resumen automático (2026-08-06)

Hasta ahora Telegram solo servía para **recibir** avisos (`TelegramNotifier::send()`, cuando
se crea un pedido — ver "Flujo de negocio principal"). A pedido del usuario ("quiero usar el
bot de Telegram para enviarle un comando y me indique cuántas ventas se han realizado hoy"),
el bot ahora también **responde** cuando le escriben, vía webhook de Telegram.

- [`App\Http\Controllers\TelegramWebhookController@handle`](app/Http/Controllers/TelegramWebhookController.php)
  — ruta pública `POST /telegram/webhook` (`routes/web.php`), **sin** autenticación de Laravel
  ni CSRF (Telegram no manda cookie de sesión ni token — se agregó la excepción en
  `bootstrap/app.php` con `$middleware->validateCsrfTokens(except: ['telegram/webhook'])`).
  La seguridad es doble:
  1. Header `X-Telegram-Bot-Api-Secret-Token` (comparado con `hash_equals()` contra
     `TelegramSetting::webhook_secret`, un string aleatorio de 48 caracteres generado la
     primera vez que se activa Telegram) — Telegram lo manda automáticamente en cada llamada
     al webhook porque se registró así en `setWebhook`.
  2. El `chat.id` del mensaje entrante debe coincidir con el `chat_id` configurado en el panel
     — así, si alguien más le escribe al bot (cualquiera puede encontrarlo por su @usuario en
     Telegram), no recibe ninguna respuesta ni puede pedir las ventas del día.
- Comando soportado: **`/ventashoy`** (o `/ventas`) — responde con pedidos pagados aprobados
  hoy, ingresos del día, y demos activadas hoy (`Order::where('status','approved')
  ->whereDate('approved_at', today())`, separado en pagados vs. trial por `package->is_trial`).
  `/start`, `/help` y `/ayuda` responden con un mensaje corto listando el comando disponible.
  Cualquier otro texto se ignora en silencio (siempre devuelve `200` a Telegram, que si no
  recibe `200` reintenta la entrega).
- El webhook se registra/borra **automáticamente** al guardar Admin > Telegram
  (`Admin\TelegramSettingController@update`): si queda activo, llama a
  `TelegramNotifier::setWebhook($botToken, route('telegram.webhook'), $secret)`; si se
  desactiva, llama a `deleteWebhook()` con el token que tenía antes del cambio (capturado
  **antes** de sobreescribir `$settings->bot_token`, porque `getOriginal()` en un atributo con
  cast `encrypted` devuelve el valor todavía cifrado, no serviría para llamar a la API de
  Telegram). Telegram **exige HTTPS público** para webhooks — en local (`http://127.0.0.1`)
  el registro se salta solo y se avisa en el mensaje de "Configuración guardada" que el
  comando no va a funcionar ahí; en `desarrollo.4livepro.com` sí funciona.
- Migración `2026_08_06_105216_add_webhook_secret_to_telegram_settings_table.php` agrega la
  columna `webhook_secret` (no cifrada — es un secreto compartido de bajo riesgo comparado con
  el `bot_token`, que si se filtrara permitiría enviar mensajes con el bot; el `webhook_secret`
  filtrado como mucho permite simular una llamada de webhook, ya acotada al `chat_id`
  configurado).
- Probado en local: reflexión directa sobre `salesTodayMessage()` (mensaje calculado
  correctamente con un pedido pagado + un demo aprobados hoy) y peticiones `curl` reales contra
  `php artisan serve` simulando el payload de Telegram — secreto incorrecto → `403`, secreto
  correcto pero `chat_id` distinto → `200` sin intentar enviar nada, secreto y `chat_id`
  correctos con `/ventashoy` → `200` e intento real de `sendMessage` (se ve en
  `storage/logs/laravel.log`, falla con `404` porque el bot token de prueba no es real — el
  fallo esperado confirma que sí se intentó el envío). Sin token de bot real no se pudo probar
  la entrega real a Telegram ni el registro real de `setWebhook` (requiere HTTPS público) —
  verificar en `desarrollo.4livepro.com` con un bot real después de desplegar.
- **Resumen automático diario a las 10:00 p.m.** (2026-08-06, a pedido del usuario tras
  confirmar que `/ventashoy` sí funcionaba en producción). Reutiliza el mismo texto que
  `/ventashoy` — se extrajo esa lógica a [`App\Services\Telegram\SalesReportBuilder`](app/Services/Telegram/SalesReportBuilder.php)
  (antes vivía como método privado en `TelegramWebhookController`) para no duplicarla entre el
  comando del chat y el envío programado.
  - Checkbox nuevo en Admin > Telegram: **"Enviar resumen automático de ventas todos los días a
    las 10:00 p.m."** — columna `daily_summary_enabled` en `telegram_settings` (migración
    `2026_08_06_110404_...`).
  - [`App\Console\Commands\SendTelegramDailySalesSummary`](app/Console/Commands/SendTelegramDailySalesSummary.php)
    (`php artisan telegram:daily-summary`) — no hace nada si Telegram no está activo o si el
    checkbox está desmarcado (mismo patrón defensivo que el resto de comandos/notificaciones).
    Programado en [`routes/console.php`](routes/console.php):
    `Schedule::command('telegram:daily-summary')->dailyAt('22:00')`, mismo patrón que
    `lines:send-expiration-reminders`.
  - **No hizo falta tocar el crontab del VPS** — ya existe `* * * * * cd
    /var/www/desarrollo.4livepro.com && php artisan schedule:run` corriendo cada minuto
    (confirmado con `crontab -l` por SSH), así que cualquier tarea agregada a
    `routes/console.php` con `Schedule::` empieza a correr sola en cuanto se despliega, sin
    pasos adicionales en el servidor.
  - Probado en local: `php artisan schedule:list` muestra `0 22 * * *
    php artisan telegram:daily-summary`; con el checkbox desactivado el comando termina sin
    intentar nada ("Resumen diario desactivado..."); activándolo a mano (con un bot_token falso)
    sí llega hasta el intento de `sendMessage` (falla por token falso, mismo patrón de
    verificación que el resto de la sesión) — configuración de prueba revertida después.

## Módulo de Tickets de Soporte (2026-08-06)

No existía ningún sistema de soporte — los clientes solo podían escribir por WhatsApp. El
usuario pidió un módulo de tickets "igual a WHMCS", compartió una captura del formulario
público de WHMCS como referencia visual, y después una lista específica de campos para el
negocio (IPTV) que es la que definió el diseño final: Cliente, Línea relacionada, Pedido
relacionado, Categoría, Prioridad, Estado, Administrador asignado, Mensajes, Archivos, Fecha
de apertura, Tiempo de respuesta, Solución aplicada. Categorías: Instalación, Credenciales,
Pago, Renovación, Límite de conexiones, Servicio intermitente, Canales o contenido, Otro.
Se planificó con `EnterPlanMode`/`AskUserQuestion` antes de escribir código — decisiones
confirmadas con el usuario:
- **Mensaje**: `<textarea>` simple, no editor con formato (sin librerías JS nuevas).
- **Quién abre tickets**: **ambos** — clientes con sesión iniciada y también invitados sin
  cuenta (como el formulario público real de WHMCS).
- **Estados**: 4 — Abierto → Respondido → (En progreso, manual) → Cerrado, con reapertura
  automática si el cliente responde un ticket cerrado.

### Base de datos

- `tickets`: `user_id` **nullable** (null = ticket de invitado), `guest_name`/`guest_email`
  (solo si `user_id` es null), `access_token` (string único, `Str::random(48)`, generado solo
  para invitados — es como acceden a su ticket sin cuenta), `line_id`/`order_id` (nullable, FK
  a **sus propias** líneas/pedidos si tiene sesión — los invitados no tienen de dónde elegir,
  así que esos campos no aparecen en su formulario), `category`, `priority`, `status`
  (mismo patrón que `order.status`: valor en inglés en BD, `match()` a español solo para
  mostrar — ver `Ticket::categoryLabel()`/`priorityLabel()`/`statusLabel()`),
  `assigned_admin_id` (FK `users`, nullable), `first_response_at` (se llena solo con la
  primera respuesta de un admin — "Tiempo de respuesta" se calcula y muestra como
  `created_at` → `first_response_at`, no se guarda como duración fija), `resolution` (texto,
  "Solución aplicada"), `closed_at`.
- `ticket_messages`: el hilo — `ticket_id`, `user_id` **nullable** (null = mensaje del
  invitado dueño del ticket), `message`. Autor para mostrar:
  `$message->user?->name ?? $ticket->guest_name`. Es respuesta de admin si
  `$message->user?->isAdmin()`.
- `ticket_attachments`: `ticket_message_id`, `path`, `original_name` — mismo patrón que
  `Order::proof_path` (`$file->store('ticket-attachments', 'public')`), extensiones
  `jpg,gif,jpeg,png,txt,pdf`, máx. 5MB. Un solo `<input type="file" name="attachments[]"
  multiple>` (no el patrón "Añadir más" de WHMCS — más simple, mismo resultado). Requiere el
  symlink `public/storage` (ya existía en el VPS desde el 04-08, verificado por SSH antes de
  desplegar).
- Migración de datos: 3 filas nuevas en `email_templates` (`ticket_created`, `ticket_reply`,
  `ticket_closed`), mismo patrón `$wrap()`/heredoc que `order_invoice`.

### Acceso de invitados (sin cuenta)

Un ticket de invitado se identifica por `access_token`. El acceso a
`GET /soporte/{ticket}` se autoriza si: el usuario autenticado es el dueño
(`$ticket->user_id === auth()->id()`), **o** el ticket es de invitado y
`?token=` en la URL coincide con `access_token` (comparación con `hash_equals()`,
ver `TicketController::authorizeAccess()`) — si no, `403`. El link con el token va
embebido en el correo de confirmación (`Ticket::publicUrl()`), así el invitado entra desde
ahí sin necesidad de cuenta. Los invitados no tienen listado (`tickets.index` requiere
`auth`) — solo acceden por ese link directo.

### Notificaciones (Telegram + correo)

Reutiliza `TelegramNotifier::send()` y `EmailTemplate::mail()` ya existentes, sin
infraestructura nueva:
- **Ticket nuevo**: Telegram al admin + correo de confirmación al cliente/invitado
  (`TicketCreated`, plantilla `ticket_created`). Para invitados, como no hay `User`, se usa
  notificación "on-demand" de Laravel: `Notification::route('mail', $ticket->guest_email)
  ->notify(...)` en vez de `$user->notify(...)`.
- **Respuesta del admin**: correo al cliente/invitado (`TicketReplied`, plantilla
  `ticket_reply`) — marca `first_response_at` (solo la primera vez) y `status = answered`.
- **Respuesta del cliente/invitado**: Telegram al admin, sin correo (no hace falta
  confirmarle a quien acaba de escribir). Si el ticket estaba `closed`, lo reabre a `open`.
- **Ticket cerrado**: correo con la `resolution` (`TicketClosed`, plantilla `ticket_closed`),
  solo si el estado *cambia* a `closed` (no se reenvía si ya estaba cerrado y se guarda de
  nuevo sin cambiar el estado).

### Rutas y controladores

Público/cliente en [`TicketController`](app/Http/Controllers/TicketController.php)
(sin namespace `Admin`): `tickets.create`/`tickets.store` (`/soporte/nuevo`, `/soporte`,
`throttle:10,1`), `tickets.show`/`tickets.reply` (`/soporte/{ticket}`, autorización mixta
auth-o-token descrita arriba, `throttle:20,1` en la respuesta), `tickets.index`
(`/soporte`, dentro del grupo `auth` ya existente — "Mis Tickets"). Admin en
[`Admin\TicketController`](app/Http/Controllers/Admin/TicketController.php) dentro del
grupo `/admin` ya existente: `index` (filtros por estado/categoría/prioridad/admin
asignado, mismo patrón GET que `admin/orders/index.blade.php`), `show`, `reply`, `update`
(categoría/prioridad/estado/admin asignado/solución — exige `resolution` si `status =
closed` vía `required_if`).

### Vistas y navegación

`resources/views/tickets/{create,index,show}.blade.php` (cliente/invitado) y
`resources/views/admin/tickets/{index,show}.blade.php`, más
[`<x-ticket-status-badge>`](resources/views/components/ticket-status-badge.blade.php)
(mismo patrón que `<x-order-status-badge>`). Todas las tablas usan `overflow-x-auto`
directamente (no `overflow-hidden`, el bug corregido hoy mismo en esta sesión). Enlace
"Soporte" agregado a la navegación para clientes con sesión, invitados (apunta a
`tickets.create`), y "Tickets" al dropdown de Admin — los 3 casos en desktop y mobile en
[`layouts/navigation.blade.php`](resources/views/layouts/navigation.blade.php).

### Pruebas

Probado end-to-end en local con `curl` (cookie jar persistente) simulando los 3 roles:
invitado (creación con token generado, acceso denegado sin token/con token incorrecto,
acceso concedido con token correcto, respuesta), cliente con sesión (ticket con línea y
pedido relacionados de verdad, aparece en "Mis Tickets"), admin (responde → confirma
`first_response_at`/`status=answered`, reasigna y cierra con solución → confirma correo
`ticket_closed` sin placeholders sueltos, cliente responde el ticket cerrado → confirma
reapertura automática, filtros del listado admin por estado/categoría/admin asignado).
Adjunto probado con un archivo real (`.txt`), confirmado el registro en
`ticket_attachments` y el archivo físico en `storage/app/public/ticket-attachments/`.
**Bug menor encontrado y corregido durante la prueba**: `Ticket::messages()` ordenaba solo
por `created_at`, que puede empatar entre mensajes creados en el mismo segundo (pasó en las
pruebas con peticiones muy seguidas) — se agregó un segundo `orderBy('id')` como desempate
determinista. Todos los tickets/usuarios/línea/pedido de prueba eliminados después
(cascada verificada: borrar un `Ticket` borra sus `ticket_messages` y
`ticket_attachments` solos, gracias a `cascadeOnDelete()` en las migraciones).

### Ajustes tras la primera prueba real (2026-08-06)

- **No es un bug: Turnstile no aparece si el ticket lo crea un usuario con sesión iniciada**
  — el usuario probó con una cuenta ya logueada (`jorgeevil182@gmail.com`) y no vio el
  widget de Cloudflare; es el mismo comportamiento que registro/checkout (Turnstile solo se
  pide a invitados sin cuenta, nunca a usuarios autenticados). Se le explicó, sin cambios de
  código.
- **Correo a la bandeja de soporte cuando se abre un ticket**, con el mismo contenido que el
  aviso de Telegram (a pedido del usuario: "exactamente igual como en Telegram"). Se agregó
  `TicketController::notifyAdminEmail()`, que manda un `Mail::raw()` (no pasa por
  `EmailTemplate::mail()` — es un aviso interno de operación, no un correo de marca para el
  cliente) al `username` ya configurado en Admin > Configuración de correo (la cuenta SMTP
  real, ej. `soporte@4livepro.com`) — **no se agregó un campo nuevo configurable**, se
  reutilizó el que ya existía porque coincide exactamente con la dirección que pidió el
  usuario; no hace nada si no hay correo configurado. Probado en local seteando
  `MailSetting::username` a mano y confirmando en `storage/logs/laravel.log` que llega con
  el mismo asunto/cuerpo que el mensaje de Telegram, incluyendo el link directo al ticket en
  el admin — configuración de prueba revertida después.
- **Se quitó el campo "Línea relacionada" del formulario de creación** (a pedido del
  usuario, viendo una captura del formulario) — solo queda "Pedido relacionado". También se
  quitó la palabra "(opcional)" de las etiquetas "Pedido relacionado" y "Adjuntos". La
  columna `line_id` y la relación `Ticket::line()` **no se tocaron** (se dejaron en el
  esquema/modelo por si hace falta más adelante, simplemente ya no se llenan desde el
  formulario de creación) — `TicketController::create()`/`store()` ya no calculan ni
  validan `line_id`.
- **Espaciado del detalle del ticket** — el usuario mandó una captura señalando que se veía
  "muy pegado" (el formulario de responder, especialmente el input de adjuntos justo encima
  del botón). Se aumentó el espaciado de `space-y-4` a `space-y-6` en las columnas
  principales y a `space-y-5` en los formularios, en `tickets/show.blade.php` y
  `admin/tickets/show.blade.php`.
- **Enlace del menú renombrado de "Soporte" a "Contacto"** (a pedido del usuario) — en los 4
  lugares donde aparecía en `layouts/navigation.blade.php` (desktop/mobile, con y sin
  sesión). Solo cambió el texto visible del enlace; las rutas siguen llamándose `tickets.*`.

## Plantillas de correo

Los 9 correos transaccionales del sistema (verificación de cuenta, **factura pendiente de
pago**, pedido aprobado/línea activada, pedido rechazado, recordatorio de vencimiento,
restablecer contraseña, ticket creado, respuesta de ticket, ticket cerrado) **ya no tienen
el diseño por defecto de Laravel** — cada uno se
edita desde Admin > Plantillas de correo (asunto, diseño HTML con vista previa en vivo, y
versión en texto plano con un botón para regenerarla desde el HTML).

- Modelo [`EmailTemplate`](app/Models/EmailTemplate.php): una fila por `key`
  (`verify_email`, `order_invoice`, `order_approved`, `order_rejected`, `line_expiring_soon`,
  `password_reset`),
  con `subject`, `html_body`, `text_body`. `EmailTemplate::mail($key, $variables)` sustituye `{{variable}}`
  por su valor (regex, tolera espacios: `{{ variable }}` también funciona) y devuelve un
  `MailMessage` con `->view(['html' => 'emails.template-html', 'text' => 'emails.template-text'], ...)`
  — esas dos vistas son solo un wrapper mínimo (`{!! $html !!}` / `{{ $text }}`), sin ningún
  layout de Laravel de por medio.
- Cada notificación (`OrderApproved`, `OrderRejected`, `LineExpiringSoon`) y los closures
  `VerifyEmail::toMailUsing` / `ResetPassword::toMailUsing` en
  [`AppServiceProvider`](app/Providers/AppServiceProvider.php) construyen el array de
  variables desde sus datos y llaman a `EmailTemplate::mail(...)` en vez de armar el mensaje
  a mano con `->line()`/`->action()` (que es lo que generaba el diseño genérico azul/inglés
  de Laravel — `ResetPassword` es el mismo caso que `VerifyEmail` tenía antes: el reset de
  contraseña de Breeze usa la notificación estándar de Laravel si no se sobreescribe, agregado
  2026-08-05 a pedido del usuario, junto con la plantilla `password_reset` en una migración de
  datos nueva (`2026_08_05_153041_add_password_reset_email_template.php`) — el enlace se arma
  igual que el `ResetPassword` original de Laravel (`route('password.reset', ['token'=>...,
  'email'=>...], false)`), y `{{expire_minutes}}` sale de `config('auth.passwords.users.expire')`
  (60 por defecto) en vez de estar hardcodeado en el texto.
- Variables disponibles por plantilla: hardcodeadas en `EmailTemplate::variableCatalog()` — si
  agregas una variable nueva a una notificación, agrégala ahí también para que aparezca en el
  editor del admin.
- Las 4 filas se insertan **directo en la migración** (no en `DatabaseSeeder`, que es opcional)
  porque sin ellas ningún correo se puede enviar — `EmailTemplate::render()` usa `firstOrFail()`.
  El editor del admin no tiene botón de eliminar, a propósito.
- **Diseño por defecto (2026-08-05)**: header y footer oscuros (`#0f1720`) con el logo
  (`public/images/logo.png`, referenciado con `asset('images/logo.png')` — **la URL absoluta
  queda fija en la BD al momento de correr la migración**, tomada del `APP_URL` de cada entorno;
  si `APP_URL` cambia después, hay que reeditar la plantilla o volver a correr esta migración
  a mano para actualizar el `src`), tarjeta de credenciales con fondo gris y fuente monoespaciada
  para usuario/contraseña en `order_approved`, botones grandes color `#2aa890` (brand). Esto vive
  en la migración de datos `2026_08_05_110740_update_email_templates_professional_design.php`
  (actualiza las filas que crea la migración anterior, no crea una tabla nueva). Si el admin ya
  editó una plantilla a mano, esta migración de todos modos la sobreescribe — solo se pensó para
  correr una vez, al desplegar esta mejora; **no volver a correrla** si ya se personalizaron las
  plantillas después (usar `php artisan migrate:status` para confirmar si ya corrió).
- **Se quitó la palabra "IPTV" de las 4 plantillas** (2026-08-05, a pedido del usuario) vía
  otra migración de datos (`2026_08_05_151005_remove_iptv_word_from_email_templates.php`),
  con `strtr()` sobre frases completas conocidas (no un `str_replace('IPTV', '')` genérico,
  para no dejar espacios dobles o gramática rota) — afectaba el footer común
  ("4LivePro Latino — IPTV Premium" → "4LivePro Latino") y el asunto/cuerpo de
  `order_approved` y `line_expiring_soon` ("tu línea IPTV" → "tu línea"). Mismo aviso que la
  migración anterior: si se vuelve a correr después de que el admin edite las plantillas a
  mano, las sobreescribe.
- **"Probar esta plantilla"**: cada editor tiene un envío de prueba real (`EmailTemplateController@test`,
  ruta `POST /admin/plantillas-correo/{template}/probar`). Envía el asunto/HTML/texto que hay
  **ahora mismo en el formulario** (aunque no se haya guardado, igual que "Probar conexión" de
  Telegram), sustituyendo variables con datos de ejemplo (`EmailTemplate::sampleVariables()`,
  hardcodeados por plantilla, ajustar si cambian las variables reales). El remitente **no es
  configurable aquí a propósito** (a pedido del usuario, 2026-08-05) — siempre usa el mailer
  por defecto de Admin > Configuración de correo, vía `Mail::send(...)` sin `->from(...)`. Se
  probó con remitente custom en una versión anterior y se quitó; si hace falta reintroducirlo,
  usarlo solo para casos de prueba explícitos, no como default. No pasa por `EmailTemplate::mail()`
  (esa función lee la plantilla ya guardada en BD; el test usa lo que está escrito en pantalla).
- El editor (`resources/views/admin/email-templates/edit.blade.php`) usa Alpine.js: textarea de
  HTML con `x-model` + `<iframe :srcdoc="html">` para vista previa en vivo, botón "Generar desde
  el HTML" que usa `div.innerText` del navegador para derivar el texto plano, y botones de
  variables que insertan `{{nombre}}` en el campo (HTML o texto) que tenía el foco.
  ⚠️ **Cuidado al tocar ese archivo**: escribir literalmente `{{` seguido de `}}` en el código
  JS/PHP de una vista Blade (fuera de `@php`/`@js`) rompe la compilación, porque Blade lo
  interpreta como su propia sintaxis de echo. Por eso el JS construye el placeholder como
  `'{' + '{' + name + '}' + '}'` en vez de `'{{' + name + '}}'` — ya nos pasó una vez al
  escribirlo directo, se verificó compilando la vista a mano con
  `app('blade.compiler')->compileString(...)` + `php -l` antes de confiar en que funcionaba.

## PDF de facturas

El correo `order_invoice` lleva el PDF de la factura **adjunto**, generado 100% en el
servidor con [`barryvdh/laravel-dompdf`](https://github.com/barryvdh/laravel-dompdf)
(dompdf puro PHP, sin Node/Chromium/binarios externos — corre bien en cualquier LAMP,
a pedido explícito del usuario: "para generar los PDF debes hacerlo en el servidor LAMP").

- [`App\Services\InvoicePdfService`](app/Services/InvoicePdfService.php): `generate(Order $order): string`
  devuelve los bytes crudos del PDF (`Pdf::loadView('pdf.invoice', [...])->output()`).
  `App\Notifications\OrderInvoice` lo adjunta con `->attachData(...)`.
- Vista dedicada [`resources/views/pdf/invoice.blade.php`](resources/views/pdf/invoice.blade.php)
  — **no** reutiliza el HTML de `EmailTemplate` (ese vive como texto en la BD con
  `{{variable}}` para sustitución simple; el PDF usa Blade normal con `$order`, `$order->user`,
  etc. directo). Tampoco tiene por qué mantenerse igual al diseño del correo — es un documento
  aparte, inspirado en el mismo formato de referencia que el usuario compartió (número de
  factura, insignia de estado, cajas "Emitida por"/"Facturada a", tabla de ítems).
- El logo se embebe como **base64 (`data:image/png;base64,...`)**, no como URL remota —
  dompdf puede cargar imágenes remotas pero requiere `isRemoteEnabled` en su config (riesgo de
  SSRF si alguna vez esa URL dependiera de datos de usuario) y depende de que el servidor
  pueda alcanzarse a sí mismo por HTTP; con base64 no hace falta ninguna petición de red.
  CSS limitado a lo que dompdf soporta bien: tablas para layouts de columnas (nada de flexbox).
- No hay ruta para descargar la factura después por la web (solo llega por correo). Si se pide
  eso a futuro, es agregar una ruta que llame a `InvoicePdfService::generate()` y la devuelva
  con `response($bytes)->header('Content-Type', 'application/pdf')` — el servicio ya está
  listo para reutilizarse así.
- **`deploy.sh` no corría `composer install`** — no hacía falta hasta ahora porque nunca se
  había agregado una dependencia PHP nueva desde que existe el script. Se agregó la flag
  `--composer` (corre `COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader`
  en el VPS, ya que se conecta como `root`) — **usarla cada vez que `composer.json`/`composer.lock`
  cambien**, si no el VPS nunca instala el paquete nuevo y la app tira "Class not found".
- **Los paquetes demo/trial también reciben esta factura por correo** (2026-08-05, a pedido
  del usuario: "puedes agregar para que a los paquetes demo llegue el pdf y el correo").
  `OrderController::storeTrial()` llama `$user->notify(new OrderInvoice($order))` justo
  después de crear el pedido (antes solo lo hacía el flujo de pedido pagado en `store()`) —
  se envía siempre al crear el pedido, sin importar si la línea se activa de inmediato o
  queda esperando verificación de correo. Como un trial no tiene comprobante ni método de
  pago, tanto el correo como el PDF necesitaban texto distinto al de "pendiente de pago":
  - `EmailTemplate::mail('order_invoice', [...])` ahora recibe `status_label` (`"Prueba
    gratuita"` vs `"Pendiente de pago"`) e `intro_text` (párrafo de saludo distinto para
    cada caso) — variables nuevas en `variableCatalog()`/`sampleVariables()` de
    [`EmailTemplate`](app/Models/EmailTemplate.php). La fila `order_invoice` en BD ya no
    tiene "Pendiente de pago" fijo en el asunto/insignia/texto: se reemplazó por
    `{{status_label}}`/`{{intro_text}}` vía la migración de datos
    `2026_08_05_161000_update_order_invoice_template_for_trials.php` (mismo patrón `strtr()`
    que la migración que quitó "IPTV").
  - `InvoicePdfService::generate()` calcula `statusLabel` con `match(true)`, chequeando
    `$order->package->is_trial` **antes** que `$order->status` (un trial siempre está en
    `pending` mientras espera verificación, así que sin este chequeo se habría mostrado
    "Pendiente de pago" igual). La vista `pdf/invoice.blade.php` también muestra "Prueba
    gratuita" en la fila de "Método de pago" en vez de `—` (los trials no tienen
    `payment_method_id`).
  - Probado end-to-end en local con un pedido trial real (`is_trial=true`, `amount=0`,
    `payment_method_id=null`): correo revisado en `storage/logs/laravel.log` (asunto
    "Factura #29 - Prueba gratuita", insignia y método de pago correctos, intro distinta a
    la de un pedido pagado) y el PDF adjunto generado e inspeccionado directamente
    (insignia ámbar "PRUEBA GRATUITA", $0.00 USD, dirección completa, logo) — usuario y
    pedido de prueba eliminados después.

## Puntos abiertos / riesgos conocidos

- **Renovación en XUI**: el comentario en `XuiOneClient` dice explícitamente que el
  mecanismo de renovación (qué campo de `edit_line` extiende `exp_date`) **no se pudo
  verificar** porque solo había un paquete demo (duración 0) disponible al integrar.
  Falta probar con un paquete real cuando exista uno con `xui_package_id` de pago.
  `Order.is_renewal` existe en el modelo pero no vi lógica que lo use todavía — revisar.
  (Explícitamente fuera de alcance para el usuario por ahora, ver instrucción del 05-08.)
- XAMPP local mencionado por el usuario en la sesión del 05-08 pero nunca se llegó a usar ni
  configurar en este repo (`launch.json` apunta a Laragon) — probablemente ya no aplica, dado
  que el flujo de trabajo actual es local (Laragon) → VPS vía `deploy.sh`, sin depender de un
  servidor local para previsualizar.
- **`hasUsedTrial()` cuenta pedidos demo `pending` como "ya usado"** (revisado 2026-08-05):
  si un cliente pide el trial pero nunca hace clic en el enlace de verificación del correo,
  ese pedido se queda `pending` para siempre y el cliente **no puede volver a pedir el demo**
  — `hasUsedTrial()` no distingue entre "ya lo usó" y "lo pidió pero nunca confirmó". Se le
  explicó esto al usuario junto con dos posibles soluciones (contar solo trials `approved`, o
  un cron que cancele pedidos demo pendientes sin verificar tras X horas). **El usuario decidió
  explícitamente no tocarlo** ("No hagas nada, al parecer ya está bien") — no es un bug
  corregido, es una decisión de negocio tomada a propósito. No volver a "arreglarlo" sin que
  el usuario lo pida.

## Comandos útiles

```bash
php artisan serve                              # local
php artisan migrate                            # aplicar migraciones
php artisan db:seed                            # catálogo demo (categoría/paquetes/métodos de pago, sin usuarios)
php artisan app:create-admin correo clave      # crear/actualizar el usuario admin
php artisan lines:send-expiration-reminders    # recordatorios de vencimiento (cron, diario 9am)
php artisan telegram:daily-summary             # resumen de ventas por Telegram (cron, diario 10pm)
php artisan schedule:list                      # ver qué comandos están programados y cuándo corren
npm run dev                                    # vite dev
npm run build                                  # vite build
ssh whmcs-vps                                  # conectar al VPS de desarrollo/producción
./deploy.sh                                    # desplegar el commit actual a desarrollo.4livepro.com
git push                                       # subir a github.com/zhaiks182/billing-panel-IPTV
```

## Si mueves el proyecto a otra computadora

Este archivo (`CLAUDE.md`) viaja con el proyecto porque está dentro del repo git. Pero hay dos
cosas que **viven fuera del repo, en la carpeta de usuario de Windows**, y no se mueven solas:

1. **Llaves SSH** (`C:\Users\<usuario>\.ssh\`):
   - `4livepro_deploy` (+ `.pub`) — acceso al VPS (`whmcs-vps`).
   - `github_billing_panel` (+ `.pub`) — push a GitHub. Si generas una nueva en la otra
     máquina en vez de copiar esta, hay que agregar la nueva pública en
     https://github.com/settings/keys (GitHub permite varias llaves por cuenta).
   - El archivo `~/.ssh/config` con los `Host whmcs-vps` y `Host github.com` que apuntan a
     esas llaves (ver secciones "VPS / SSH" y "Repositorio GitHub" arriba). Sin esto, los
     comandos `ssh whmcs-vps` y `git push`/`git pull` de este documento no van a funcionar
     hasta reconfigurarlo.
2. **El repo remoto** ya tiene todo el código e historial —
   `git clone git@github.com:zhaiks182/billing-panel-IPTV.git` en la computadora nueva trae
   el proyecto completo (una vez configurada la llave SSH de GitHub ahí). Después:
   `composer install`, copiar/crear `.env` (nunca viaja por git, hay que rearmarlo o copiarlo
   a mano — ver `.env.example`), `npm install`.
3. **Para retomar el trabajo**, lo primero que debe leer cualquier sesión nueva (humana o de
   IA) es este `CLAUDE.md` completo: explica qué es el proyecto, dónde está desplegado, cómo
   se despliega, y el historial de decisiones en la bitácora de abajo.

## Bitácora de sesiones

### 2026-08-05
- Sesión anterior se perdió por un error de Claude. Se re-analizó el proyecto desde cero.
- Se detectó que la carpeta de esta sesión de Claude Code (OneDrive) estaba vacía/desactualizada;
  el proyecto real vive en `C:\Claude\Billing Panel`. El usuario cambió el directorio de trabajo
  de la sesión a `C:\Claude\Billing Panel` — confirmado correcto.
- Se verificó acceso SSH al VPS (`whmcs-vps`, `167.148.33.82`) — funcional.
- Se creó este archivo `CLAUDE.md` como documentación viva del proyecto.
- Se definió el flujo de trabajo: **local (`C:\Claude\Billing Panel`) es la fuente de
  verdad, se despliega al VPS después de cada commit** (ver sección "Flujo de trabajo").
  Primero se probó "editar directo en el VPS" pero el usuario pidió cambiarlo a local-first.
- Se inicializó git en el VPS (`Initial snapshot...`) y luego en local
  (`Baseline: sincronizado con VPS...`), tras verificar por hash que ambas copias
  coincidían casi al 100% (4 diferencias, resueltas: `resources/js/app.js` y 3 vistas
  huérfanas traídas de VPS a local).
- Se hizo el primer deploy real local → VPS (`git archive | ssh tar`), subiendo `CLAUDE.md`.
  Se detectó y corrigió: pérdida del bit ejecutable de `artisan` y archivos de `storage/`
  quedando con dueño `root` tras el `tar` — ambos ahora son pasos fijos del proceso de deploy.
  Se verificó que el sitio sigue respondiendo (HTTP 200) después del deploy.
- Se reescribió el texto de ayuda de `admin/telegram-settings/edit.blade.php` (pasos para
  crear el bot con @BotFather y obtener Chat ID) a pedido del usuario.
- Se agregó un botón real de **"Probar conexión"** en notificaciones de Telegram (antes solo
  existía "Guardar y enviar mensaje de prueba", que requería guardar primero):
  - `TelegramNotifier::sendTo($botToken, $chatId, $message)` — nuevo método que envía sin
    depender de `TelegramSetting` guardado en BD.
  - `TelegramSettingController@test` — nueva ruta `POST /admin/configuracion-telegram/probar`.
  - En la vista, el botón usa Alpine.js (`fetch`) para probar con los valores actuales del
    formulario sin necesidad de guardar antes.
  - **Bug encontrado y corregido**: usar `:disabled="testing"` sobre un componente Blade
    (`<x-secondary-button>`) causó `Error 500: Undefined constant "testing"`. Blade interpreta
    `:atributo="expr"` en tags `<x-...>` como "evalúa `expr` como PHP", chocando con la
    sintaxis de binding de Alpine.js que usa la misma notación. **Regla para el futuro:**
    en componentes Blade (`<x-...>`), cualquier atributo de Alpine que empiece con `:`
    (`:disabled`, `:class`, etc.) debe escribirse con doble dos-puntos `::disabled="..."`
    para que Blade lo pase como texto literal en vez de evaluarlo. En tags HTML normales
    (no componentes) no hace falta escapar, ahí no hay conflicto.
  - Verificado renderizando la vista completa por `tinker` en el servidor (sin credenciales
    de admin a mano para probar por navegador) — compila y el botón aparece correctamente.
- Pendiente: decidir próxima tarea de desarrollo con el usuario. Pendiente también que el
  usuario confirme visualmente en el navegador que "Probar conexión" funciona con un bot real.
- Se creó [install.sh](install.sh) + [INSTALL.md](INSTALL.md): instalador para desplegar este
  panel en un servidor LAMP nuevo (prerrequisitos, directorio recomendado, base de datos MySQL,
  usuario/clave admin del sistema). Se refactorizó `DatabaseSeeder` (ya no crea el admin por
  defecto) y se agregó el comando `app:create-admin` para crear/actualizar el admin de forma
  segura e idempotente. **No se corrió `install.sh` en `desarrollo.4livepro.com`** (ya está
  instalado con SQLite) — solo se desplegaron los archivos nuevos/editados con el flujo normal
  (`git archive | ssh tar`). El script no se probó end-to-end en un servidor real todavía
  (sintaxis verificada con `bash -n`); probarlo en un LAMP limpio antes de confiar en él para
  un cliente real.
- Se subió el proyecto a GitHub a pedido del usuario: **https://github.com/zhaiks182/billing-panel-IPTV**
  (repo público — el usuario decidió mantenerlo así, no cambiarlo a privado). Se generó una
  llave SSH dedicada (`~/.ssh/github_billing_panel`), se agregó a la cuenta de GitHub del
  usuario, y se hizo `git push -u origin main` con todo el historial local. Se verificó antes
  de subir que no hay `.env`/secretos trackeados en git.
- Se reescribió `README.md` (tenía el contenido genérico de Laravel sin relación con el
  proyecto) y luego se tradujo al inglés a pedido del usuario, quitando la frase "built with
  Laravel" — el proyecto se presenta como un panel IPTV **desplegado en un VPS LAMP**, con
  Laravel mencionado solo como parte del stack técnico.
- Se agregó la sección "Si mueves el proyecto a otra computadora" en este documento: aclara
  que las llaves SSH (`4livepro_deploy`, `github_billing_panel`) y `~/.ssh/config` viven fuera
  del repo (en el perfil de Windows) y no se mueven solas al cambiar de computadora — hay que
  copiarlas a mano o generar unas nuevas. El pedido del usuario fue justamente "documentar todo
  para poder entender el proyecto si toca mover los archivos a otro ordenador".
- Se construyó el módulo **Plantillas de correo** (a pedido del usuario, quitando además todo
  rastro del diseño por defecto de Laravel en los correos). Ver sección dedicada arriba.
  Se probó todo en local antes de desplegar: migración corrida contra el sqlite de
  `C:\Claude\Billing Panel`, `EmailTemplate::render()`/`mail()` probados por tinker con datos
  de ejemplo (sustitución de variables, sin `{{...}}` sin reemplazar), las dos vistas del admin
  renderizadas por tinker, y la vista `edit.blade.php` compilada a mano para atrapar el bug de
  `{{`/`}}` literales antes de subir (ver advertencia en la sección de arriba).
- Se agregó "Probar esta plantilla" (envío de correo de prueba con remitente configurable) a
  cada editor, a pedido del usuario. Probado localmente por tinker: sustitución con datos de
  ejemplo, `Mail::send` con remitente custom, contenido verificado en `storage/logs/laravel.log`
  (mailer `log` en desarrollo) — se ve el HTML final ya con las variables reemplazadas.
- El usuario pidió quitar los campos de remitente/nombre del remitente del "Probar esta
  plantilla" — debe usarse siempre lo configurado en Admin > Configuración de correo, no un
  override por prueba. Se simplificó el formulario (solo queda "Enviar a") y el controller
  (`Mail::send` ya no llama a `->from(...)`).
- Se rediseñaron las 4 plantillas por defecto (más profesional, con el logo de
  `public/images/logo.png`) a pedido del usuario — ver detalle en la sección de arriba.
  Se generaron previews HTML con datos de ejemplo (`EmailTemplate::sampleVariables()`) y se
  enviaron como archivos al usuario para que las revisara antes de desplegar (no se pudo tomar
  captura de pantalla del navegador en esta sesión — el panel de Chrome no estaba visible del
  lado del usuario). El usuario aprobó el diseño ("Procede") antes de subir a GitHub/VPS.
- Se endureció la validación del formulario de registro (`RegisteredUserController`, `register.blade.php`,
  `config/countries.php`) — ver sección dedicada "Registro de usuarios" arriba. Se encontró y
  corrigió un bug real durante las pruebas: el filtro de solo-números del código postal no
  funcionaba porque el `@input` de Alpine estaba fuera de cualquier `x-data`. Probado en
  navegador real (Laragon local): registro completo exitoso con datos guardados correctamente,
  medidor de contraseña mostrando Baja/Alta según lo escrito, y Belice/Haití visibles en el
  selector de país. Validación de casos inválidos confirmada con `Validator::make` directo.
- Dos ajustes rápidos al mismo formulario, a pedido del usuario tras ver capturas: la barra de
  fuerza de contraseña ahora es siempre visible (antes solo aparecía al escribir), y los
  desplegables de país/código telefónico pasaron de `bg-panel-alt` a `bg-ink` para que coincidan
  con el fondo del resto del sitio. Verificado con JS en el navegador (color computado idéntico
  al del body, `rgb(15, 23, 32)`).
- El usuario notó que "también debe aplicarse para los paquetes el registro" — se encontró que
  `orders/create.blade.php` (checkout) duplica el formulario de registro completo. Se replicó
  ahí todo lo hecho hoy en `register.blade.php`/`RegisteredUserController`: fondo `bg-ink` en
  los desplegables, campos de dirección obligatorios + regex de ciudad/estado/código postal,
  contraseña fuerte con medidor siempre visible. Se documentó la duplicación arriba para no
  volver a olvidarla. Probado en navegador local (invitado, sin sesión): filtro de código
  postal y medidor de contraseña funcionando igual que en el registro normal.
- Scroll delgado y oscuro (clase `.scrollbar-dark` en `resources/css/app.css`, vía
  `scrollbar-width`/`scrollbar-color` + pseudo-elementos `::-webkit-scrollbar`) para los
  desplegables de país/teléfono en registro y checkout, a pedido del usuario (mostró una captura
  de otro proyecto como referencia de estilo). **Este cambio expuso que el VPS no tenía
  Node/npm instalado** — se instaló Node 20 (ver sección "VPS / SSH"). Verificado localmente con
  `npm run build` + navegador (computed `scrollbar-width: thin`, `scrollbar-color` con el gris
  "steel" del tema) antes de desplegar y compilar también en el VPS.
- Se agregó un indicador en vivo de "las contraseñas coinciden/no coinciden" debajo de
  "Confirmar contraseña", en registro y checkout (mismo patrón Alpine que el medidor de fuerza:
  getter `match` que compara `password` vs `passwordConfirmation`, `null` cuando el segundo
  campo está vacío). Probado en navegador: casos distinto y coincide, ambos correctos.
- **Bug preexistente encontrado y corregido**: el botón "Activar prueba gratis" (checkout de un
  paquete demo cuando el cliente aún no verificó su correo) no mostraba texto — solo la flecha.
  Causa: `orders/create.blade.php` llama `x-data="trialGateForm()"` pero esa función **nunca
  estaba definida** en `resources/js/app.js` (confirmado con `git log` que ya faltaba desde el
  primer commit — no es algo que se rompiera en esta sesión). Como Alpine no podía inicializar
  el componente, `submitting` quedaba `undefined` y ambos `x-show` del botón se ocultaban. Se
  implementó `window.trialGateForm()` completo: envía el pedido por `fetch`, abre el modal de
  espera, sondea `GET /pedidos/{id}/estado` cada 3s hasta que el pedido pasa de `pending` a
  `approved`/`error`, y actualiza el modal (`waiting` → `ready`/`error`). Probado end-to-end en
  local: pedido creado, modal de espera con el correo correcto, verificación de email simulada
  por tinker + `TrialActivator`, y el polling detectó el cambio a `approved` y mostró el mensaje
  de éxito automáticamente, sin recargar la página.
  ⚠️ Al probar esto se activó una línea trial real en el panel XUI ONE configurado (con un
  usuario de prueba ficticio, `carlos.trial.test@example.com`) — es una demo de 2 horas, expira
  sola, pero avisar si esto no es aceptable para pruebas futuras.
- Nota aparte: en algún punto de la sesión el `.env` local pasó de `DB_CONNECTION=sqlite` a
  `mysql` (`billing_panel` en `127.0.0.1:3306`, usuario `root` sin clave) — no fue un cambio que
  yo hiciera; parece que Laragon/el usuario cambió el motor de BD local. No afecta nada en git
  ni en el VPS (`.env` nunca se versiona), solo dejarlo anotado por si algo local se comporta
  distinto a lo esperado en sesiones futuras.
- El usuario probó registrar `jorgeevil182@gmail.com` (paquete demo) y reportó: botón atascado
  en "Enviando...", sin correo, sin popup. Se investigó por SSH: el usuario/pedido **nunca se
  creó** en la BD del VPS; el log mostró un error 500 de permisos en `storage/framework/views/`
  (ver "Incidente 2026-08-05" en la sección de despliegue arriba) — coincidió con la ventana de
  un deploy reciente. Se verificó con una petición real simulada (`curl` con token CSRF real)
  que el endpoint ya responde `200 {"status":"pending_verification",...}` correctamente; se
  limpiaron los datos de prueba. SMTP confirmado configurado (Gmail) — el correo no llegó
  porque la petición nunca llegó a completarse, no por un problema de envío de correo en sí.
  Se le pidió al usuario reintentar el registro.
- Se quitó el placeholder `987654321` del campo de teléfono (registro y checkout) — a pedido
  del usuario, se veía como si ya hubiera un número escrito.
- El usuario reintentó el registro de `jorgeevil182@gmail.com` y **volvió a quedarse en
  "Enviando..."** — pero esta vez sin ninguna entrada nueva en el log del VPS (el log llevaba
  desde las 12:36 sin tocarse, hora del servidor 13:50), o sea el error ya no era de permisos.
  Se reprodujo en local con un click real (`btn.click()`, no una llamada manual a la función)
  y se encontró el bug real: **`this.$el` en Alpine no es fijo al elemento raíz del `x-data`**
  — depende de qué directiva lo evalúa. Como `@submit.prevent="submit"` está en el `<form>`
  (no en el div con `x-data` que lo envuelve), dentro de `submit()`, `this.$el` apuntaba al
  propio `<form>`, así que `this.$el.querySelector('form')` buscaba un `<form>` DENTRO del
  form (no existe) → `null` → `form.action` lanzaba `Cannot read properties of null` de forma
  síncrona, ANTES de llegar al `fetch()` — por eso `submitting` quedaba en `true` para siempre
  sin ninguna petición de red, sin popup, sin log en el servidor (el error nunca llegó a viajar).
  Esto explica también por qué mi primera prueba "funcionó": llamé `Alpine.$data(root).submit()`
  directo (sin pasar por la directiva `@submit`), y en ese contexto `$el` sí resolvía al div raíz.
  **Fix**: se agregó `init() { this.rootEl = this.$el }` (el hook `init()` de Alpine sí corre
  con `$el` = raíz del componente, de forma confiable) y se guarda esa referencia; `submit()`
  ahora recibe el evento (`@submit.prevent="submit($event)"`) y usa `event.target` para obtener
  el `<form>` directamente, sin depender de `$el`. De paso se agregó manejo de errores visible
  (antes solo había `alert()`, que puede pasar desapercibido): mensaje de error en pantalla,
  timeout de 20s con `AbortController` para no quedar colgado indefinidamente si la red falla,
  y `console.error` en cada rama de fallo para poder diagnosticar más rápido la próxima vez.
  Verificado con un click real end-to-end: modal se abre, correo correcto, sin errores en consola.
- El usuario preguntó si el modal "Verifica tu correo" (`trialGateForm`) tiene algún límite de
  tiempo si nunca confirma el correo — no lo tenía, el sondeo (`pollStatus`) corría cada 3s para
  siempre mientras la pestaña siguiera abierta. Se agregó un límite de **10 minutos**: pasado
  ese tiempo se detiene el `setInterval` y el modal cambia a un estado `timeout` con botón
  "Cerrar" (`closeModal()`, que también limpia el intervalo). Importante: **es solo cosmético,
  del lado del navegador** — el pedido sigue `pending` en la BD sin límite, y si el cliente
  verifica el correo después (aunque haya cerrado la ventana o pasado el timeout), la línea se
  activa igual vía `TrialActivator`, sin depender de que esta ventana siga abierta. Se le explicó
  al usuario que esto expone un vacío real ya documentado arriba (`hasUsedTrial()`), pero pidió
  no tocarlo. Probado en navegador forzando el estado `timeout` manualmente (sin esperar los
  10 minutos reales) y confirmando que el botón "Cerrar" cierra el modal correctamente.
- El usuario pidió aplicar todas las mejoras sugeridas **excepto** probar `install.sh` en un
  servidor real y la renovación de líneas en XUI. Se hicieron las 5 restantes en un solo lote:
  1. Turnstile conectado al registro y checkout (antes solo existía la configuración, sin usarse).
  2. Rate limiting (`throttle:10,1`) en registro y compra de pedidos.
  3. Borradas las 3 vistas huérfanas (`admin/index.blade.php`, `login.blade.php`,
     `navigation.blade.php` en la raíz de `resources/views/`).
  4. Extraído el bloque duplicado de registro/checkout a `<x-guest-registration-fields />`
     (ver sección "Registro de usuarios" arriba).
  5. Creado [`deploy.sh`](deploy.sh) para no repetir a mano la secuencia de deploy (ver sección
     "Flujo de trabajo" arriba) — este mismo commit se desplegó usándolo por primera vez.
  Todo probado en local antes de subir (lint de PHP, compilación de Blade a mano, y pruebas
  end-to-end en navegador de registro + checkout después del refactor a componente compartido).
- El usuario notó que Admin > Usuarios no mostraba dirección/país aunque sí se guardan — se
  agregó el modal de detalle al hacer clic en el nombre (ver sección "Panel de administración"
  arriba). También pidió quitar la palabra "IPTV" de las plantillas de correo — se hizo con
  una migración de datos dedicada (ver sección "Plantillas de correo" arriba). Verificado con
  tinker: 0 ocurrencias de "IPTV" en las 4 plantillas después de correr la migración, variables
  `{{...}}` intactas, y las 4 plantillas siguen renderizando sin placeholders sin reemplazar.
- El usuario notó que faltaba conectar la plantilla de "olvidé mi contraseña" (seguía en inglés,
  sin el diseño de las demás — usaba el `ResetPassword` genérico de Laravel, el mismo problema
  que tenía `VerifyEmail` antes de conectarlo). Se agregó la 5ª plantilla `password_reset` (ver
  sección "Plantillas de correo" arriba). Probado end-to-end en local: `Password::sendResetLink()`
  real para el admin, correo verificado en `storage/logs/laravel.log` (mailer `log`) — asunto en
  español, URL de reset con token real, "60 minutos" sustituido correctamente desde `config()`,
  y el editor del admin muestra la plantilla y sus variables sin cambios adicionales (la lista
  del admin no tiene claves hardcodeadas, lee todo de la tabla).
- Texto mejorado en `packages/index.blade.php` (sección de demo gratis) para clientes que ya
  usaron su prueba: "Ya usaste tu prueba gratuita. ¡Elige un plan abajo!" → "¿Te gustó la demo?
  Elige tu plan y sigue disfrutando sin interrupciones." — a pedido del usuario, solo el texto
  (no se agregó botón/scroll-anchor a los planes, se ofreció como alternativa pero no se pidió).
- El usuario vio "We have emailed your password reset link." en inglés al pedir recuperar
  contraseña, y preguntó si no debía llevar Turnstile también. Causa: `APP_LOCALE=es` está en
  `.env`, pero **el proyecto no tiene archivos `lang/es`** — los mensajes que arma uno mismo
  (`__('texto literal en español')`) siempre se ven bien porque Laravel devuelve la clave tal
  cual si no encuentra traducción, pero los códigos cortos que usa el password broker de Laravel
  (`passwords.sent`, `passwords.reset`, `passwords.token`, `passwords.user`) sí son claves reales
  que Laravel traduce con sus propios archivos internos, y sin `lang/es` caen al inglés. En vez
  de crear toda una carpeta `lang/es` (esta app no tiene i18n real en ningún otro lado, todo el
  español ya está hardcodeado), se mapearon esos códigos a texto en español directo en
  `PasswordResetLinkController` y `NewPasswordController` (`STATUS_MESSAGES`, mismo patrón que
  el resto de la app). Se agregó Turnstile también en `auth/forgot-password.blade.php` (mismo
  patrón que registro/checkout: `TurnstileSetting`, `ValidTurnstile`, invisible si no está
  configurado).
- El usuario pidió también el medidor de fuerza + coincidencia de contraseña en el formulario de
  "nueva contraseña" (`auth/reset-password.blade.php`, el segundo paso del reset, después de
  hacer clic en el enlace del correo) — antes solo tenía inputs simples sin ninguno de los dos.
  Se extrajo ese bloque (antes vivía solo dentro de `<x-guest-registration-fields />`) a un
  componente propio, [`<x-password-strength-fields />`](resources/views/components/password-strength-fields.blade.php),
  usado ahora en los tres formularios (registro, checkout, reset de contraseña) — evita duplicarlo
  una tercera vez, ya nos había mordido dos veces antes. De paso se endureció la regla de
  contraseña de `NewPasswordController` (usaba `Password::defaults()` genérico, solo mínimo 8
  caracteres) a la misma regla fuerte (`min(8)->mixedCase()->numbers()->symbols()`) que el resto,
  ya que el texto de ayuda del componente compartido promete ese requisito para los tres.
  Probado end-to-end en local: mensaje "Te enviamos por correo..." en español al pedir el
  enlace, medidor mostrando "Alta" al escribir una contraseña fuerte en el segundo paso, y
  "Tu contraseña fue restablecida correctamente." en español tras enviar.
- Se agregó espacio (`mt-4`) entre el campo de correo y el widget de Turnstile en
  `auth/forgot-password.blade.php` — se veían pegados. El componente compartido
  `<x-turnstile-widget>` no tiene margen propio a propósito (en registro/checkout el `space-y-6`
  del formulario ya lo separaba); acá se envolvió con un `div.mt-4` local en vez de tocar el
  componente, para no alterar el espaciado donde ya se veía bien.
- **Nueva 6ª plantilla: "Factura de pedido (pendiente de pago)"** (`order_invoice`), a pedido
  del usuario, que compartió como referencia una captura y dos PDF de facturas de otro panel
  (Teramont Host — solo como referencia de formato/diseño, no se copió su marca ni sus datos).
  Antes, al crear un pedido de pago (con comprobante subido) no se enviaba ningún correo hasta
  que un admin lo aprobaba o rechazaba — el cliente quedaba sin confirmación de que su pedido
  se recibió. Ahora `OrderController@store` dispara `App\Notifications\OrderInvoice` justo
  después de crear el pedido (solo pedidos de pago, no trial — un trial es $0, no tiene sentido
  facturarlo). Contenido: número de pedido, insignia "Pendiente de pago" (ámbar), caja
  "Facturado a" con el nombre y la dirección del cliente (de los mismos campos que ya se
  guardan en `users`), tabla con el paquete/método de pago/importe, fecha de emisión y total,
  botón "Ver mi pedido". A diferencia del ejemplo de referencia, no incluye botón para pagar en
  línea ni fecha de vencimiento — no aplica a nuestro modelo (el pago ya se hizo por fuera,
  transferencia/Zelle, y se sube el comprobante; lo "pendiente" es la revisión del admin, no el
  pago del cliente). Migración de datos nueva (`2026_08_05_155412_add_order_invoice_email_template.php`,
  mismo patrón de las anteriores). Variables nuevas en `EmailTemplate::variableCatalog()`:
  `billing_address` (HTML, con `<br>`) y `billing_address_text` (texto plano, con `\n`) — dos
  variables separadas para la misma dirección porque el texto plano no debe llevar HTML.
  Probado end-to-end en local con un pedido de pago real (checkout completo con comprobante
  subido vía `curl -F`): correo verificado en `storage/logs/laravel.log` con los datos reales
  del pedido (paquete, monto, método de pago, dirección) correctamente sustituidos, sin
  placeholders sueltos, tanto en HTML como en texto plano.
- El usuario preguntó si el correo de factura lleva el PDF adjunto (no lo llevaba) y pidió
  que la generación fuera **en el servidor LAMP** (no en el navegador/cliente). Se instaló
  `barryvdh/laravel-dompdf` y se armó todo el sistema de generación — ver sección dedicada
  "PDF de facturas" arriba. Probado en local: PDF real generado con datos de un pedido
  (`InvoicePdfService::generate()`), verificado visualmente con el propio `Read` de Claude
  (confirma cabecera con logo, insignia "Pendiente de pago", cajas de emitida-por/facturada-a,
  tabla de ítems — mismo formato que la referencia del usuario, con nuestra marca), y probado
  el flujo end-to-end completo con un pedido real por `curl`: el correo en el log muestra
  `Content-Type: application/pdf; name=factura-N.pdf` con `Content-Disposition: attachment`,
  confirmando que el adjunto llega correctamente. Se agregó la flag `--composer` a `deploy.sh`
  (ver sección dedicada) para este y futuros cambios de dependencias PHP.
- **La factura (correo + PDF) ahora también se envía en pedidos de paquetes demo/trial**, a
  pedido del usuario ("puedes agregar para que a los paquetes demo llegue el pdf y el correo")
  — antes `OrderInvoice` solo se disparaba en `OrderController@store` (pedidos de pago). Se
  agregó la misma llamada en `storeTrial()`, justo después de crear el pedido. Como un trial
  no tiene comprobante ni método de pago, se agregaron las variables `status_label` e
  `intro_text` (ver sección "PDF de facturas" arriba para el detalle completo) para que el
  correo y el PDF digan "Prueba gratuita" en vez de "Pendiente de pago", con un párrafo de
  saludo distinto. Migración de datos nueva
  (`2026_08_05_161000_update_order_invoice_template_for_trials.php`) que reemplaza el texto
  fijo de la plantilla `order_invoice` ya existente por los placeholders nuevos, mismo patrón
  `strtr()` que las migraciones de datos anteriores. Probado end-to-end en local con un pedido
  trial real: correo con asunto "Factura #N - Prueba gratuita" e intro correcta en
  `storage/logs/laravel.log`, y PDF adjunto inspeccionado directamente (insignia ámbar "PRUEBA
  GRATUITA", $0.00 USD) — usuario y pedido de prueba eliminados después.
- **Segunda factura "Pagada" al aprobar un pedido de pago** (2026-08-05). El usuario probó el
  flujo real en `desarrollo.4livepro.com` (registro → demo → pedido de pago) y notó que al
  aprobar el pedido no llegaba ninguna factura actualizada a "Pagada" — solo el correo de
  "línea activada" (`OrderApproved`). Se confirmó que no era un bug sino una función que nunca
  se construyó: `OrderInvoice` solo se disparaba una vez, al crear el pedido. Se le preguntó al
  usuario si prefería un correo nuevo separado o adjuntar el PDF pagado al correo de línea
  activada — eligió **correo nuevo separado**. Implementación:
  - `App\Notifications\OrderInvoice::toMail()` ahora calcula `status_label`/`intro_text` con un
    `match(true)` de 3 ramas: `is_trial` → "Prueba gratuita", `status === 'approved'` → "Pagada"
    (con intro "confirmamos tu pago..."), default → "Pendiente de pago" (sin cambios). No hizo
    falta tocar la plantilla en BD ni `InvoicePdfService` — ambos ya usaban `{{status_label}}`/
    `match(true)` genérico desde la extensión a trials de más arriba, así que "Pagada" ya
    funcionaba en cuanto se le pasara ese estado.
  - `Admin\OrderController::activate()` (privado, usado por `approve()` y `retry()`) ahora
    llama `$order->user->notify(new OrderInvoice($order))` justo después de `OrderApproved`,
    **solo si `! $order->package->is_trial`** — un trial ya recibió su única factura ("Prueba
    gratuita") al crearse, no tiene sentido reenviarla al activarse (no hay "pago" que
    confirmar). Esto corre tanto al aprobar un pedido normal como al reintentar uno que había
    quedado en `error`.
  - Probado end-to-end en local simulando el ciclo completo de un pedido de pago (creación →
    `status=pending`, correo "Factura #N - Pendiente de pago" → luego `status=approved`, correo
    "Factura #N - Pagada"): ambos verificados en `storage/logs/laravel.log` (asunto, insignia,
    intro, método de pago real) y el PDF de la versión "Pagada" inspeccionado directamente
    (insignia ámbar "PAGADA", método de pago correcto) — usuario y pedido de prueba eliminados
    después.
- ⚠️ **Bug real encontrado y corregido: el formulario de prueba gratuita (`trialGateForm`)
  mostraba "Ocurrió un error (código 200). Intenta de nuevo." en vez del error real** cada vez
  que la validación fallaba (Turnstile expirado/inválido, ya usó su prueba, etc.). El usuario lo
  reportó probando el registro manual en `desarrollo.4livepro.com`. Se confirmó viendo el log de
  Apache (`/var/log/apache2/desarrollo-access.log`): cada intento de `POST
  /paquetes/.../comprar` devolvía **302** (no 200/422 JSON), y `fetch()` seguía el redirect
  transparentemente, aterrizando en la página HTML de vuelta con status 200 — de ahí el mensaje
  engañoso, ya que `trialGateForm.submit()` solo sabe mostrar el `response.status` cuando el
  body no es JSON parseable (ver `resources/js/app.js`).
  - **Causa raíz real**: [`bootstrap/app.php`](bootstrap/app.php) tenía
    `$exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*'))` — esto es
    scaffolding por defecto de Laravel 13 que **nunca se tocó** desde el commit baseline. Esa
    línea **reemplaza por completo** la detección normal de Laravel (`$request->expectsJson()`,
    que mira el header `Accept`) por una que solo mira la URL. Como este proyecto no tiene
    ninguna ruta bajo `/api/*`, **ninguna excepción de validación en toda la app se renderizaba
    como JSON**, sin importar qué `Accept` mandara el cliente — afectaba a cualquier fetch/AJAX
    del sitio, no solo al trial. Confirmado reproduciendo el POST real con `curl -H "Accept:
    application/json"` contra producción: siempre 302, nunca JSON, incluso con Turnstile
    inválido a propósito.
  - **Fix**: `fn ($request) => $request->is('api/*') || $request->expectsJson()` — mantiene el
    comportamiento para `/api/*` y además restaura la detección estándar por header `Accept`
    para el resto de la app.
  - De paso se corrigió un segundo bug relacionado en `OrderController::store()`: la rama
    "ya usaste tu prueba gratuita" (`hasUsedTrial()`) hacía un `redirect()` a secas sin mirar
    `$request->wantsJson()` — ahora responde `response()->json(['status' =>
    'trial_already_used', 'message' => ...], 422)` cuando el cliente pide JSON, igual que el
    resto de `storeTrial()`.
  - Probado en local simulando los 3 casos con `curl` contra `php artisan serve` (con Turnstile
    desactivado localmente, se usó un campo requerido faltante para forzar la validación):
    validación fallida → antes 302 HTML, ahora `422 {"message":"...","errors":{...}}`; registro
    válido → sigue devolviendo `200 {"status":"pending_verification",...}` sin cambios; mismo
    usuario reintentando la prueba → `422 {"status":"trial_already_used","message":"..."}`.
    Desplegado a `desarrollo.4livepro.com` sin migraciones nuevas.
- **La factura de prueba gratuita ya no se envía al crear el pedido, sino al activarse la
  línea** (2026-08-05), a pedido del usuario: "no debe enviarse antes, sino existe la
  confirmacion del correo. Primero se debe confirmar el correo y hay debera llegar la factura
  de prueba gratuita seguido del correo con la linea". Antes `OrderInvoice` se disparaba justo
  al crear el pedido trial (`OrderController::storeTrial()`), sin importar si el correo ya
  estaba verificado — quedaba raro facturar algo que todavía podía no llegar a activarse nunca
  (correo falso sin verificar). Se movió el envío al momento real de activación, en los dos
  lugares donde eso ocurre:
  - `App\Services\Xui\TrialActivator::activatePendingFor()` — el flujo normal (usuario nuevo,
    sin verificar al pedir la demo): ahora, justo después de `$order->update(['status' =>
    'approved', ...])` y **antes** de `OrderApproved`, llama `$user->notify(new
    OrderInvoice($order))`. Este método también lo usa `Admin\UserController::verify()`
    (verificación manual desde el admin), así que ambos caminos quedan cubiertos con un solo
    cambio.
  - `OrderController::storeTrial()` — el caso menos común de un usuario que YA tenía el correo
    verificado al pedir la demo (se activa la línea al toque, sin esperar nada): se movió la
    misma línea `$user->notify(new OrderInvoice($order))` de justo-después-de-crear-el-pedido a
    justo-antes-de-`OrderApproved`, dentro del bloque `try` que llama `$xui->activate($order)`.
  - Orden final de correos para una demo: verificación de cuenta (al registrarse) → [usuario
    hace clic en el enlace] → factura "Prueba gratuita" → línea activada. Si la activación en
    XUI falla (`XuiApiException`), el pedido queda en `error` y **no** se envía ninguna
    factura — coherente con no facturar algo que no se activó.
  - Probado end-to-end en local simulando el flujo completo: pedido trial creado con usuario
    sin verificar (`email_verified_at = null`) → confirmado que no hay ningún correo de
    factura en `storage/logs/laravel.log` en ese punto → se marcó el correo como verificado y
    se llamó `TrialActivator::activatePendingFor()` directamente (mismo método que usa
    `VerifyEmailController`) → confirmado en el log que la "Factura #32 - Prueba gratuita"
    aparece **antes** que "Tu línea está activa - Pedido #32" — usuario, pedido y línea de
    prueba eliminados después (⚠️ esto activó una línea trial real en el XUI configurado, igual
    que advertencias anteriores de pruebas similares en este documento).
- **El widget de Cloudflare Turnstile no se reiniciaba tras un intento fallido en el formulario
  de prueba gratuita** (2026-08-05). Tras corregir el bug de JSON de más arriba, el usuario
  probó de nuevo el registro y vio "La verificación de seguridad falló. Inténtalo de nuevo."
  aunque el widget seguía mostrando "¡Operación exitosa!". Causa: un token de Turnstile es de
  **un solo uso y expira** (~5 min) — en registro/checkout normal esto nunca se nota porque
  cualquier error recarga la página completa (nuevo token automático), pero `trialGateForm`
  muestra el error **sin recargar** (es todo por `fetch`), así que el widget se queda
  mostrando el check verde de un token ya consumido/expirado, y cualquier reintento reenvía
  ese mismo token inservible — falla siempre hasta que el usuario recarga la página a mano.
  Esto también explica los reintentos fallidos repetidos que se vieron en el log de Apache
  antes del fix de JSON (cada uno reenviaba el mismo token ya gastado por el primero).
  - Fix en [`resources/js/app.js`](resources/js/app.js): nuevo método `resetTurnstile()` en
    `trialGateForm` que llama `window.turnstile.reset(container)` sobre el `div.cf-turnstile`
    del formulario — se invoca en la rama de error de validación de `submit()`, así que
    después de cualquier fallo (Turnstile inválido, correo duplicado, etc.) el widget queda
    listo para un nuevo intento sin recargar la página. No hace nada si Turnstile no está
    activo/configurado (`window.turnstile` no existe) — mismo patrón defensivo que el resto
    del componente.
  - Probado en el navegador local (`npm run build` + preview): con Turnstile desactivado
    localmente se confirmó que `resetTurnstile()` no lanza error (guard `container` es `null`,
    no hace nada) al forzar un 422 real (correo duplicado, `admin@example.com`) — el mensaje de
    error se mostró correctamente en pantalla y la consola no tuvo errores nuevos. No se pudo
    probar el reset real del widget en sí porque Turnstile solo está activo en
    `desarrollo.4livepro.com`, no en local — verificar ahí con el sitio real si el problema
    persiste tras desplegar.
- **Bug real de responsive en mobile encontrado y corregido (2026-08-06)**: el usuario reportó
  "el responsive para smartphone no está bien". Sin poder tomar capturas de pantalla en esta
  sesión (el panel de Chrome no estaba visible del lado del usuario), se auditó midiendo
  `scrollWidth` vs `innerWidth` por JS en viewport 375px (preset "mobile") en todas las páginas
  principales. La mayoría no tenía problema (home, categorías, checkout, dashboards vacíos) —
  el bug real apareció al probar tablas con datos reales: **8 vistas envolvían su `<table>` en
  un `<div class="... overflow-hidden">` en vez de `overflow-x-auto`**, así que en vez de
  poder hacer scroll horizontal para ver el contenido que no cabía en 375px, el contenido
  simplemente se **cortaba/ocultaba sin ningún indicio visual** de que faltaba algo. Confirmado
  midiendo una tabla real (`/pedidos` del cliente): la tabla mide 437px pero el contenedor solo
  374px — antes de este fix esos 63px de contenido (la columna de fecha, parte del badge de
  estado) quedaban invisibles.
  - Vistas corregidas (mismo cambio en las 8: `overflow-hidden` → `overflow-x-auto` en el div
    que envuelve la tabla): [`admin/dashboard.blade.php`](resources/views/admin/dashboard.blade.php)
    (línea por vencer), [`admin/email-templates/index.blade.php`](resources/views/admin/email-templates/index.blade.php),
    [`admin/package-categories/index.blade.php`](resources/views/admin/package-categories/index.blade.php),
    [`admin/packages/index.blade.php`](resources/views/admin/packages/index.blade.php),
    [`admin/payment-methods/index.blade.php`](resources/views/admin/payment-methods/index.blade.php),
    [`cart/index.blade.php`](resources/views/cart/index.blade.php) (tabla de ítems del carrito,
    **no** la tarjeta de resumen de al lado, esa sigue con `overflow-hidden` a propósito por
    las esquinas redondeadas), [`dashboard.blade.php`](resources/views/dashboard.blade.php)
    ("Mis Enlaces M3U"), [`orders/index.blade.php`](resources/views/orders/index.blade.php)
    ("Mis Pedidos"). Los otros 3 usos restantes de `overflow-hidden` en el proyecto
    (`cart/index.blade.php` línea 67, `components/modal.blade.php`, `layouts/guest.blade.php`)
    son tarjetas/paneles normales, no tablas — se dejaron intactos.
  - También se revisaron y **descartaron** como problema real: un grid de 2 columnas sin
    prefijo responsive en el modal de detalle de usuario (`admin/users/index.blade.php`, campos
    cortos tipo "Ciudad"/"Estado", cabe bien incluso en 375px) y los desplegables de país/código
    de teléfono en el formulario de registro (`w-72`/`w-full`, medidos con
    `getBoundingClientRect()` tras abrirlos por JS — ninguno se sale del viewport en 375px).
  - Ya que este era un bug de solo Blade/Tailwind (ninguna clase nueva, solo la que ya existía
    en el resto del proyecto), no requirió `npm run build` — se desplegó con `deploy.sh --no-build`.
  - Login/pruebas en el navegador local hechas sin poder usar clicks reales del `computer` tool
    (el panel de Chrome no compositaba frames en esta sesión) — en su lugar, login/logout y
    apertura de los desplegables Alpine se hicieron disparando eventos reales vía
    `element.click()`/`fetch()` con `javascript_tool`, que sí dispara los listeners de Alpine
    correctamente al ser un evento DOM genuino, no una llamada directa a la función.
- **Segundo bug de responsive, mucho más grave: el contenedor principal de página no tenía
  padding lateral en mobile** (2026-08-06). El usuario mandó una captura real de iPhone del
  checkout: la tarjeta "Resumen del pedido" tocaba los dos bordes de la pantalla, sin ningún
  margen — se veía "sin marcos". Causa: el contenedor estándar que usa **prácticamente cada
  vista del sitio** (`<div class="max-w-* mx-auto sm:px-6 lg:px-8">`, heredado tal cual del
  scaffolding de Laravel Breeze) solo define padding horizontal desde el breakpoint `sm:`
  (640px) hacia arriba — **por debajo de eso (cualquier smartphone en vertical) no hay padding
  en absoluto**, así que el contenido queda pegado a los bordes de la pantalla. A diferencia
  del bug de las tablas (que solo se notaba con contenido ancho), este afectaba a **toda página
  del sitio sin excepción**, tanto pública como de admin.
  - Fix: se agregó `px-4` como base a los **31 archivos** que usaban este patrón (`grep -rl
    'max-w-.*mx-auto sm:px-' resources/views` → reemplazo `sed` de `sm:px-6 lg:px-8` por
    `px-4 sm:px-6 lg:px-8` en todos). Cubre absolutamente todas las páginas: home/categorías
    (esas ya tenían su propio padding porque usan otro layout, no se vieron afectadas),
    checkout, carrito, mis pedidos, dashboard de cliente, todo el panel de admin (dashboard,
    pedidos, paquetes, categorías, métodos de pago, plantillas de correo, usuarios,
    configuración de XUI/correo/Turnstile/Telegram, formularios create/edit) y las vistas de
    auth (login, registro, forgot-password, reset-password, verify-email, confirm-password).
  - Las 31 vistas se compilaron una por una con `app('blade.compiler')->compileString(...)` +
    `php -l` antes de desplegar (mismo patrón de verificación que el resto del proyecto).
    Verificado también en el navegador local, midiendo la tarjeta "Resumen del pedido" del
    checkout con `getBoundingClientRect()` en viewport 375px: antes del fix estaba pegada a
    ambos bordes (0px de margen), después queda a 16px simétricos de cada lado.
  - Solo Blade (ninguna clase de Tailwind nueva, `px-4` ya existe en el CSS compilado) —
    desplegado con `deploy.sh --no-build`, sin necesidad de `npm run build`.
- **Ahora sí existe `lang/es`, con un `validation.php` completo (2026-08-06)** — reemplaza la
  decisión anterior (ver nota de "We have emailed your password reset link." más arriba, del
  05-08) de evitar crear la carpeta `lang/es` a propósito. El usuario reportó en
  `desarrollo.4livepro.com` que al pedir un demo con un correo ya registrado aparecía
  **"The email has already been taken."** en inglés (sin popup ni correo de verificación —
  eso último es correcto, no es un bug: al fallar la validación no se crea ningún pedido/usuario
  nuevo, así que no hay nada que confirmar por correo). Causa: igual que el caso de
  `passwords.*` del 05-08, pero esta vez para **cualquier regla de validación de todo el sitio**
  (`required`, `email`, `unique`, `confirmed`, reglas de `Password::min()`, etc.) — sin
  `lang/es/validation.php`, Laravel usa su archivo interno en inglés para cualquier mensaje no
  sobreescrito a mano. La vez anterior el problema eran solo 5 claves puntuales (`passwords.*`)
  y bastaba un array `STATUS_MESSAGES`; esta vez el problema es genérico y afecta a **cualquier**
  formulario del sitio (registro, checkout, y también los ~10 formularios del admin que nunca se
  habían probado con datos inválidos), así que esta vez sí se justificó crear
  [`lang/es/validation.php`](lang/es/validation.php) completo — mensajes estándar de Laravel
  traducidos + un array `attributes` con el nombre en español de **todos** los campos usados en
  algún `validate()` de la app (recolectados con `grep` sobre `app/Http/Controllers`), para que
  nunca se filtre un nombre de campo en snake_case en inglés (ej. "first_name" en vez de
  "nombre"). Los 4 mensajes ya personalizados por campo (`city.regex`, `state.regex`,
  `postal_code.regex`, `country.in`, en `RegisteredUserController`/`OrderController`) se
  dejaron intactos — siguen funcionando igual porque un mensaje inline en el `validate()` de un
  controller siempre gana sobre el archivo de idioma, no hubo conflicto ni duplicación real.
  Probado en local con `Validator::make()` directo por tinker y con peticiones `fetch()` reales
  contra `php artisan serve` (correo duplicado, campos vacíos, contraseña débil, país inválido,
  confirmación de contraseña distinta, y un envío 100% válido de control) — todos los mensajes
  salieron en español con el nombre de campo correcto, y el flujo válido no se vio afectado.
