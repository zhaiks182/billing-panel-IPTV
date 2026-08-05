# Billing Panel — 4LivePro Latino

Panel de reventa de IPTV: los clientes compran paquetes M3U, suben comprobante de pago,
un admin aprueba el pedido y el sistema provisiona automáticamente la línea en un panel XUI ONE.

## Stack

- Laravel 13 (PHP 8.3), Breeze (auth con verificación de email obligatoria)
- Blade + Tailwind CSS 3 + Alpine.js, Vite
- Base de datos: SQLite en desarrollo (`database/database.sqlite`), MySQL en instalaciones
  nuevas vía `install.sh` (ver [INSTALL.md](INSTALL.md))
- Sin cola real en producción por ahora: no hay ningún `ShouldQueue`, las notificaciones se
  envían sync. `QUEUE_CONNECTION=database` está seteado por si se necesita a futuro.

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
4. Desplegar al VPS (empaqueta el commit actual y lo extrae en el servidor):
   ```bash
   git archive HEAD | ssh whmcs-vps "tar -x -C /var/www/desarrollo.4livepro.com"
   ssh whmcs-vps "chown -R www-data:www-data /var/www/desarrollo.4livepro.com/storage /var/www/desarrollo.4livepro.com/bootstrap/cache && chmod +x /var/www/desarrollo.4livepro.com/artisan"
   ```
   Si el commit agrega migraciones nuevas, correrlas también en el VPS:
   ```bash
   ssh whmcs-vps "cd /var/www/desarrollo.4livepro.com && php artisan migrate --force"
   ```
5. Si el deploy tocó `resources/` (Blade/CSS/JS) y depende de assets compilados, correr
   `npm run build` en el VPS y verificar `public/build`.
6. Verificar en https://desarrollo.4livepro.com.

**Nota sobre `git archive | tar`:** solo agrega/sobrescribe archivos, no borra los que ya
no estén en el commit (si se elimina un archivo en local, hay que borrarlo a mano en el VPS
también). Tras el primer deploy grande de hoy, el `tar` dejó algunos archivos con dueño
`root` en `storage/` y quitó el bit ejecutable de `artisan` — por eso el `chown`/`chmod`
del paso 4 son parte fija del proceso, no algo puntual.

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
php artisan db:seed                            # catálogo demo (categoría/paquetes/métodos de pago, sin usuarios)
php artisan app:create-admin correo clave      # crear/actualizar el usuario admin
php artisan lines:send-expiration-reminders    # recordatorios de vencimiento (cron)
npm run dev                                    # vite dev
npm run build                                  # vite build
ssh whmcs-vps                                  # conectar al VPS de desarrollo/producción
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
