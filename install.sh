#!/usr/bin/env bash
#
# Instalador de Billing Panel (4LivePro Latino) para un servidor LAMP (Linux + Apache + MySQL + PHP).
#
# Uso interactivo:
#   sudo ./install.sh
#
# Uso no interactivo (automatizado):
#   sudo ./install.sh -y \
#     --install-dir=/var/www/billing-panel \
#     --domain=facturacion.midominio.com \
#     --db-name=billing_panel --db-user=billing_panel --db-pass='...' \
#     --admin-name="Administrador" --admin-email=admin@midominio.com --admin-pass='...'
#
# Solo verificar prerrequisitos, sin instalar nada:
#   ./install.sh --check
#
set -euo pipefail

# ---------------------------------------------------------------------------
# Colores / helpers de salida
# ---------------------------------------------------------------------------
C_RESET='\033[0m'; C_GREEN='\033[0;32m'; C_RED='\033[0;31m'; C_YELLOW='\033[0;33m'; C_BLUE='\033[0;34m'

step()  { echo -e "\n${C_BLUE}==>${C_RESET} $*"; }
ok()    { echo -e "  ${C_GREEN}OK${C_RESET}    $*"; }
warn()  { echo -e "  ${C_YELLOW}AVISO${C_RESET} $*"; }
fail()  { echo -e "  ${C_RED}FALTA${C_RESET} $*"; }
die()   { echo -e "${C_RED}Error:${C_RESET} $*" >&2; exit 1; }

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# ---------------------------------------------------------------------------
# Valores por defecto (se pueden sobreescribir por flag o de forma interactiva)
# ---------------------------------------------------------------------------
INSTALL_DIR="/var/www/billing-panel"
DOMAIN=""
DB_NAME="billing_panel"
DB_USER="billing_panel"
DB_PASS=""
ADMIN_NAME="Administrador"
ADMIN_EMAIL=""
ADMIN_PASS=""
NON_INTERACTIVE=0
SETUP_APACHE=""
BUILD_ASSETS=""
RUN_SEED=""
CHECK_ONLY=0
MYSQL_ROOT_PASS=""

random_secret() { openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24; }

# ---------------------------------------------------------------------------
# Parseo de argumentos
# ---------------------------------------------------------------------------
for arg in "$@"; do
    case "$arg" in
        --check) CHECK_ONLY=1 ;;
        -y|--yes) NON_INTERACTIVE=1 ;;
        --install-dir=*) INSTALL_DIR="${arg#*=}" ;;
        --domain=*) DOMAIN="${arg#*=}" ;;
        --db-name=*) DB_NAME="${arg#*=}" ;;
        --db-user=*) DB_USER="${arg#*=}" ;;
        --db-pass=*) DB_PASS="${arg#*=}" ;;
        --admin-name=*) ADMIN_NAME="${arg#*=}" ;;
        --admin-email=*) ADMIN_EMAIL="${arg#*=}" ;;
        --admin-pass=*) ADMIN_PASS="${arg#*=}" ;;
        --mysql-root-pass=*) MYSQL_ROOT_PASS="${arg#*=}" ;;
        --with-apache) SETUP_APACHE=1 ;;
        --no-apache) SETUP_APACHE=0 ;;
        --with-build) BUILD_ASSETS=1 ;;
        --no-build) BUILD_ASSETS=0 ;;
        --seed) RUN_SEED=1 ;;
        --no-seed) RUN_SEED=0 ;;
        -h|--help)
            sed -n '2,16p' "$0"; exit 0 ;;
        *) die "Argumento desconocido: $arg (usa --help)" ;;
    esac
done

# ---------------------------------------------------------------------------
# 1. Prerrequisitos del servidor LAMP
# ---------------------------------------------------------------------------
MISSING=0

check_cmd() {
    if command -v "$1" >/dev/null 2>&1; then ok "$2"; else fail "$2 — $3"; MISSING=1; fi
}

check_php_ext() {
    if php -m 2>/dev/null | grep -qi "^$1\$"; then
        ok "Extensión PHP: $1"
    else
        fail "Extensión PHP: $1 — sudo apt install php-$1"
        MISSING=1
    fi
}

check_prerequisites() {
    step "Verificando prerrequisitos del servidor LAMP"

    check_cmd php    "PHP"              "sudo apt install php php-cli"
    if command -v php >/dev/null 2>&1; then
        PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
        if php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then
            ok "Versión de PHP: $PHP_VERSION (>= 8.3 requerido)"
        else
            fail "Versión de PHP: $PHP_VERSION — se requiere PHP >= 8.3"
            MISSING=1
        fi
        for ext in mbstring xml curl pdo pdo_mysql bcmath ctype fileinfo tokenizer openssl session dom; do
            check_php_ext "$ext"
        done
    fi

    check_cmd composer "Composer"        "https://getcomposer.org/download/"
    check_cmd mysql    "Cliente MySQL"   "sudo apt install mysql-client (o mariadb-client)"
    check_cmd apache2   "Apache"         "sudo apt install apache2"
    check_cmd node      "Node.js"        "https://github.com/nodesource/distributions (se usa para compilar assets con Vite)"
    check_cmd npm       "npm"            "viene con Node.js"
    check_cmd git        "Git"           "sudo apt install git (opcional, recomendado)"
    check_cmd openssl    "OpenSSL"       "sudo apt install openssl"

    if command -v apache2 >/dev/null 2>&1; then
        apache2ctl -M 2>/dev/null | grep -q rewrite_module \
            && ok "Módulo Apache: mod_rewrite habilitado" \
            || { fail "Módulo Apache: mod_rewrite — sudo a2enmod rewrite"; MISSING=1; }
    fi

    if [ "$MISSING" -eq 1 ]; then
        echo -e "\n${C_YELLOW}Faltan prerrequisitos. Instálalos y vuelve a correr el script.${C_RESET}"
        echo "En Debian/Ubuntu, algo como:"
        echo "  sudo apt update && sudo apt install -y apache2 mysql-server php php-cli php-mbstring \\"
        echo "    php-xml php-curl php-mysql php-bcmath php-zip unzip git openssl"
        echo "  # Composer: https://getcomposer.org/download/"
        echo "  # Node.js 20+: https://github.com/nodesource/distributions"
        return 1
    fi

    echo -e "\n${C_GREEN}Todos los prerrequisitos están presentes.${C_RESET}"
    return 0
}

check_prerequisites || { [ "$CHECK_ONLY" -eq 1 ] && exit 1 || die "Corrige los prerrequisitos antes de continuar."; }
[ "$CHECK_ONLY" -eq 1 ] && exit 0

[ "$EUID" -eq 0 ] || die "Este script debe correr como root (sudo ./install.sh) porque configura Apache, MySQL y permisos de archivos."

# ---------------------------------------------------------------------------
# 2. Configuración interactiva (directorio, dominio, base de datos, admin)
# ---------------------------------------------------------------------------
ask() {
    local prompt="$1" default="$2" var
    if [ "$NON_INTERACTIVE" -eq 1 ]; then echo "$default"; return; fi
    read -r -p "$prompt [$default]: " var
    echo "${var:-$default}"
}

ask_secret() {
    local prompt="$1" default="$2" var
    if [ -n "$default" ]; then echo "$default"; return; fi
    if [ "$NON_INTERACTIVE" -eq 1 ]; then echo "$(random_secret)"; return; fi
    read -r -s -p "$prompt (vacío = generar automáticamente): " var; echo >&2
    echo "${var:-$(random_secret)}"
}

step "Configuración de la instalación"
echo "Directorio recomendado: fuera de /var/www/html, uno por cliente/dominio, ej. /var/www/<dominio>."
INSTALL_DIR="$(ask "Directorio de instalación en el LAMP" "$INSTALL_DIR")"
DOMAIN="$(ask "Dominio (ej. facturacion.midominio.com)" "${DOMAIN:-facturacion.midominio.com}")"

echo -e "\nBase de datos MySQL (se crea si no existe). Si escribes la clave a mano, evita # ' \" \` \$ para no romper el script:"
DB_NAME="$(ask "  Nombre de la base de datos" "$DB_NAME")"
DB_USER="$(ask "  Usuario de la base de datos" "$DB_USER")"
DB_PASS="$(ask_secret "  Clave del usuario de la base de datos" "$DB_PASS")"

echo -e "\nUsuario administrador del sistema (para iniciar sesión en el panel). Mismo cuidado con la clave:"
ADMIN_NAME="$(ask "  Nombre" "$ADMIN_NAME")"
ADMIN_EMAIL="$(ask "  Correo" "${ADMIN_EMAIL:-admin@$DOMAIN}")"
ADMIN_PASS="$(ask_secret "  Clave" "$ADMIN_PASS")"

if [ -z "$SETUP_APACHE" ]; then
    [ "$NON_INTERACTIVE" -eq 1 ] && SETUP_APACHE=1 || {
        read -r -p $'\n¿Configurar el VirtualHost de Apache automáticamente? [S/n]: ' r
        [[ "${r:-s}" =~ ^[Ss]$ ]] && SETUP_APACHE=1 || SETUP_APACHE=0
    }
fi
if [ -z "$BUILD_ASSETS" ]; then
    [ "$NON_INTERACTIVE" -eq 1 ] && BUILD_ASSETS=1 || {
        read -r -p $'¿Compilar assets (npm install && npm run build)? [S/n]: ' r
        [[ "${r:-s}" =~ ^[Ss]$ ]] && BUILD_ASSETS=1 || BUILD_ASSETS=0
    }
fi
if [ -z "$RUN_SEED" ]; then
    [ "$NON_INTERACTIVE" -eq 1 ] && RUN_SEED=0 || {
        read -r -p $'¿Cargar datos de catálogo de ejemplo (categoría/paquetes/métodos de pago demo)? [s/N]: ' r
        [[ "${r:-n}" =~ ^[Ss]$ ]] && RUN_SEED=1 || RUN_SEED=0
    }
fi

echo -e "\n${C_BLUE}Resumen:${C_RESET}"
echo "  Directorio:  $INSTALL_DIR"
echo "  Dominio:     $DOMAIN"
echo "  Base datos:  $DB_NAME (usuario: $DB_USER)"
echo "  Admin:       $ADMIN_EMAIL"
echo "  Apache:      $([ "$SETUP_APACHE" = 1 ] && echo sí || echo no)"
echo "  Build npm:   $([ "$BUILD_ASSETS" = 1 ] && echo sí || echo no)"
echo "  Seed demo:   $([ "$RUN_SEED" = 1 ] && echo sí || echo no)"
if [ "$NON_INTERACTIVE" -eq 0 ]; then
    read -r -p $'\n¿Continuar con la instalación? [s/N]: ' r
    [[ "$r" =~ ^[Ss]$ ]] || die "Instalación cancelada."
fi

# ---------------------------------------------------------------------------
# 3. Copiar el proyecto al directorio de instalación
# ---------------------------------------------------------------------------
step "Copiando archivos a $INSTALL_DIR"
mkdir -p "$INSTALL_DIR"
if [ "$(cd "$INSTALL_DIR" && pwd)" != "$SOURCE_DIR" ]; then
    tar --exclude='.git' --exclude='node_modules' --exclude='vendor' \
        --exclude='database/database.sqlite' --exclude='storage/logs/*.log' \
        -C "$SOURCE_DIR" -cf - . | tar -C "$INSTALL_DIR" -xf -
    ok "Archivos copiados."
else
    ok "Ya se está instalando en el propio directorio del proyecto, no se copia nada."
fi
cd "$INSTALL_DIR"

# ---------------------------------------------------------------------------
# 4. Base de datos MySQL
# ---------------------------------------------------------------------------
systemctl enable --now apache2 >/dev/null 2>&1 || true
systemctl enable --now mysql >/dev/null 2>&1 || systemctl enable --now mariadb >/dev/null 2>&1 || true

step "Creando base de datos y usuario en MySQL"
run_mysql() {
    if mysql -u root -e "SELECT 1" >/dev/null 2>&1; then
        mysql -u root -e "$1"
    else
        if [ -z "$MYSQL_ROOT_PASS" ]; then
            [ "$NON_INTERACTIVE" -eq 1 ] && die "MySQL requiere clave de root; pásala con --mysql-root-pass=..."
            read -r -s -p "Clave de root de MySQL: " MYSQL_ROOT_PASS; echo
        fi
        MYSQL_PWD="$MYSQL_ROOT_PASS" mysql -u root -e "$1"
    fi
}
run_mysql "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
run_mysql "CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';"
run_mysql "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1'; FLUSH PRIVILEGES;"
ok "Base de datos '$DB_NAME' y usuario '$DB_USER' listos."

# ---------------------------------------------------------------------------
# 5. Archivo .env
# ---------------------------------------------------------------------------
step "Generando .env"
[ -f .env ] || cp .env.example .env
sed -i \
    -e "s#^APP_NAME=.*#APP_NAME=\"4LivePro Latino\"#" \
    -e "s#^APP_ENV=.*#APP_ENV=production#" \
    -e "s#^APP_DEBUG=.*#APP_DEBUG=false#" \
    -e "s#^APP_URL=.*#APP_URL=https://${DOMAIN}#" \
    -e "s#^DB_CONNECTION=.*#DB_CONNECTION=mysql#" \
    .env
# Descomentar (si estaban comentadas en el .env.example) y fijar los valores reales.
sed -i \
    -e "/^# *DB_HOST=/s/^# *//" -e "s#^DB_HOST=.*#DB_HOST=127.0.0.1#" \
    -e "/^# *DB_PORT=/s/^# *//" -e "s#^DB_PORT=.*#DB_PORT=3306#" \
    -e "/^# *DB_DATABASE=/s/^# *//" -e "s#^DB_DATABASE=.*#DB_DATABASE=${DB_NAME}#" \
    -e "/^# *DB_USERNAME=/s/^# *//" -e "s#^DB_USERNAME=.*#DB_USERNAME=${DB_USER}#" \
    -e "/^# *DB_PASSWORD=/s/^# *//" -e "s#^DB_PASSWORD=.*#DB_PASSWORD=${DB_PASS}#" \
    .env
chmod 640 .env
ok ".env generado (APP_ENV=production, DB_CONNECTION=mysql)."

# ---------------------------------------------------------------------------
# 6. Dependencias, key, migraciones, storage link
# ---------------------------------------------------------------------------
step "Instalando dependencias PHP (composer)"
composer install --no-dev --optimize-autoloader --no-interaction

step "Generando APP_KEY"
php artisan key:generate --force

step "Ejecutando migraciones"
php artisan migrate --force

step "Enlazando storage público"
php artisan storage:link || true

if [ "$RUN_SEED" = 1 ]; then
    step "Cargando datos de catálogo de ejemplo"
    php artisan db:seed --force
fi

step "Creando usuario administrador"
php artisan app:create-admin "$ADMIN_EMAIL" "$ADMIN_PASS" --name="$ADMIN_NAME"

if [ "$BUILD_ASSETS" = 1 ]; then
    step "Compilando assets (npm)"
    npm install
    npm run build
else
    warn "Assets no compilados (--no-build). Corre 'npm install && npm run build' antes de usar el panel."
fi

# ---------------------------------------------------------------------------
# 7. Permisos
# ---------------------------------------------------------------------------
step "Optimizando para producción"
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Config, rutas y vistas cacheadas."

step "Ajustando permisos"
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 775 storage bootstrap/cache
chmod +x artisan
ok "Propietario www-data:www-data, storage/ y bootstrap/cache/ en 775."

# ---------------------------------------------------------------------------
# 8. VirtualHost de Apache (opcional)
# ---------------------------------------------------------------------------
if [ "$SETUP_APACHE" = 1 ]; then
    step "Configurando VirtualHost de Apache para $DOMAIN"
    VHOST_FILE="/etc/apache2/sites-available/${DOMAIN}.conf"
    cat > "$VHOST_FILE" <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${INSTALL_DIR}/public

    <Directory ${INSTALL_DIR}/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>
EOF
    a2enmod rewrite >/dev/null 2>&1 || true
    a2ensite "${DOMAIN}.conf" >/dev/null
    apache2ctl configtest && systemctl reload apache2
    ok "VirtualHost creado en $VHOST_FILE y sitio habilitado."
    warn "HTTPS no se configuró automáticamente. Cuando el DNS de $DOMAIN apunte a este servidor, corre:"
    echo "    sudo apt install certbot python3-certbot-apache && sudo certbot --apache -d ${DOMAIN}"
else
    warn "No se configuró Apache automáticamente. DocumentRoot debe apuntar a: ${INSTALL_DIR}/public"
fi

# ---------------------------------------------------------------------------
# 9. Cron (recordatorios de vencimiento de líneas)
# ---------------------------------------------------------------------------
step "Configurando cron para tareas programadas (php artisan schedule:run)"
CRON_LINE="* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1"
( crontab -u www-data -l 2>/dev/null | grep -vF "$INSTALL_DIR"; echo "$CRON_LINE" ) | crontab -u www-data -
ok "Cron de www-data actualizado."

# ---------------------------------------------------------------------------
# 10. Resumen final
# ---------------------------------------------------------------------------
CREDS_FILE="${INSTALL_DIR}/install-credentials.txt"
cat > "$CREDS_FILE" <<EOF
Billing Panel — credenciales de instalación ($(date '+%Y-%m-%d %H:%M'))

URL:            https://${DOMAIN}
Directorio:     ${INSTALL_DIR}

Base de datos:
  Nombre:       ${DB_NAME}
  Usuario:      ${DB_USER}
  Clave:        ${DB_PASS}

Administrador del panel:
  Correo:       ${ADMIN_EMAIL}
  Clave:        ${ADMIN_PASS}

Guarda esta información en un lugar seguro (gestor de contraseñas) y luego
borra este archivo: rm ${CREDS_FILE}
EOF
chmod 600 "$CREDS_FILE"
chown www-data:www-data "$CREDS_FILE"

echo -e "\n${C_GREEN}Instalación completa.${C_RESET}"
echo "Credenciales guardadas en: $CREDS_FILE (perms 600 — bórralo después de copiarlas)."
cat "$CREDS_FILE"
