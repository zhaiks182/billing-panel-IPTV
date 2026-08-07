# Instalación en un servidor LAMP

Guía para instalar Billing Panel (4LivePro Latino) en un servidor Linux + Apache + MySQL + PHP
nuevo, propio o de un cliente. El script [`install.sh`](install.sh) automatiza todo el proceso.

## Prerrequisitos del servidor LAMP

Probado sobre Debian/Ubuntu con Apache (no nginx). Necesitas:

| Componente | Versión mínima | Notas |
|---|---|---|
| PHP | 8.3 | + extensiones: `mbstring`, `xml`, `curl`, `pdo`, `pdo_mysql`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`, `openssl`, `session`, `dom` |
| Composer | 2.x | https://getcomposer.org/download/ |
| MySQL / MariaDB | 8.0 / 10.6+ | servidor y cliente (`mysql`) |
| Apache | 2.4 | con `mod_rewrite` habilitado |
| Node.js | 20+ | solo si vas a compilar los assets (`npm run build`) en el propio servidor |
| Git | cualquiera | opcional, recomendado para llevar el código al servidor |

Instalación rápida de todo lo anterior en Debian/Ubuntu:

```bash
sudo apt update && sudo apt install -y apache2 mysql-server php php-cli php-mbstring \
  php-xml php-curl php-mysql php-bcmath php-zip unzip git openssl
sudo a2enmod rewrite
# Composer: https://getcomposer.org/download/
# Node.js 20+: https://github.com/nodesource/distributions
```

El script `install.sh` verifica todo esto automáticamente al arrancar y te dice qué falta.
Para solo revisar sin instalar nada:

```bash
./install.sh --check
```

## Qué pide el script y dónde se recomienda cada cosa

- **Directorio del proyecto**: fuera de `/var/www/html`, uno por dominio/cliente, por ejemplo
  `/var/www/<dominio>` (así se hizo en `desarrollo.4livepro.com` → `/var/www/desarrollo.4livepro.com`,
  ver [CLAUDE.md](CLAUDE.md)). El `DocumentRoot` de Apache siempre debe apuntar a la subcarpeta
  `public/` dentro de ese directorio, nunca a la raíz del proyecto.
- **Base de datos**: MySQL, no SQLite (SQLite es solo para desarrollo local). El script crea la
  base de datos y un usuario dedicado (no usa `root` para la app) restringido a `127.0.0.1`.
- **Usuario y clave administradora del sistema**: es la cuenta con la que inicias sesión en
  `/adm_4livepro` dentro del panel (rol `admin` en la tabla `users`), **no** tiene relación con el
  usuario de MySQL ni con el usuario del sistema operativo. El seeder de datos de ejemplo
  (`database/seeders/DatabaseSeeder.php`) ya **no** crea ningún usuario — el admin se crea
  siempre con el comando dedicado `php artisan app:create-admin {email} {password}
  --name="..."`, que el script llama automáticamente con los datos que le des. Este comando
  es idempotente: si el correo ya existe, actualiza su contraseña y lo vuelve admin.

## Uso

Copia el proyecto a un directorio temporal del servidor (por ejemplo con `git clone` o subiendo
un `.zip`/`.tar`) y desde ahí corre el instalador como root:

```bash
sudo ./install.sh
```

Te va a preguntar, en este orden: directorio de instalación, dominio, nombre/usuario/clave de
la base de datos, y nombre/correo/clave del administrador. Deja la clave en blanco para que se
genere una segura automáticamente. Al final, todo queda resumido en
`<directorio-instalación>/install-credentials.txt` (permisos `600`) — cópialo a un lugar seguro
y bórralo del servidor.

### Modo no interactivo (para automatizar instalaciones repetidas, ej. multi-cliente)

```bash
sudo ./install.sh -y \
  --install-dir=/var/www/facturacion.clientex.com \
  --domain=facturacion.clientex.com \
  --db-name=billing_clientex --db-user=billing_clientex --db-pass='...' \
  --admin-name="Administrador" --admin-email=admin@clientex.com --admin-pass='...' \
  --mysql-root-pass='...'
```

Cualquier clave que no pases con `--db-pass`/`--admin-pass` se genera automáticamente. Otras
flags útiles: `--no-apache` (no tocar la configuración de Apache), `--no-build` (no correr
`npm install && npm run build`), `--seed` (cargar categoría/paquetes/métodos de pago de
ejemplo, además de la base de datos vacía).

## Qué hace el script, paso a paso

1. Verifica los prerrequisitos (aborta si falta algo, salvo que uses `--check`).
2. Copia el proyecto al directorio de instalación (excluyendo `.git`, `vendor`, `node_modules`,
   la base sqlite de desarrollo).
3. Crea la base de datos y el usuario en MySQL.
4. Genera `.env` (producción: `APP_DEBUG=false`, `DB_CONNECTION=mysql`, `APP_URL` con tu dominio).
5. `composer install --no-dev`, `php artisan key:generate`, `migrate --force`, `storage:link`.
6. Crea el usuario administrador (`app:create-admin`).
7. Compila los assets con `npm` (opcional).
8. Cachea config/rutas/vistas para producción.
9. Ajusta permisos (`www-data:www-data`, `storage/` y `bootstrap/cache/` en 775).
10. Configura el `VirtualHost` de Apache (opcional) — HTTPS **no** se automatiza; una vez que
    el DNS del dominio apunte al servidor, corre `certbot --apache -d tu-dominio`.
11. Agrega a `cron` de `www-data` la tarea `php artisan schedule:run` cada minuto (necesaria
    para los recordatorios de vencimiento de líneas, ver `SendLineExpirationReminders` en
    [CLAUDE.md](CLAUDE.md)).
12. Imprime y guarda un resumen con todas las credenciales generadas.

## Después de instalar

- Entra a `https://tu-dominio/adm_4livepro` con el correo/clave de administrador.
- Configura desde el panel (`Admin > Configuración`): XUI ONE, correo SMTP, Telegram, Turnstile
  — estos valores viven en la base de datos, no en `.env` (ver modelo de datos en
  [CLAUDE.md](CLAUDE.md)).
- Borra `install-credentials.txt` del servidor una vez guardadas las claves en otro lugar.
