#!/usr/bin/env bash
#
# Despliega el commit actual (HEAD) de este repo local al VPS de desarrollo
# (desarrollo.4livepro.com), vía `git archive | ssh tar` (ver CLAUDE.md, sección
# "Flujo de trabajo"). Reemplaza la secuencia de comandos manual que se venía
# repitiendo a mano en cada deploy — un olvido del `chown` fue la causa de un
# incidente real (ver "Incidente 2026-08-05" en CLAUDE.md).
#
# Uso:
#   ./deploy.sh                # deploy normal
#   ./deploy.sh --migrate      # además corre `php artisan migrate --force`
#   ./deploy.sh --no-build     # no corre npm install/build (más rápido si no tocaste CSS/JS)
#   ./deploy.sh --composer     # corre `composer install` (usar si composer.json/lock cambió)
#
set -euo pipefail

SSH_HOST="whmcs-vps"
REMOTE_PATH="/var/www/desarrollo.4livepro.com"
DOMAIN="https://desarrollo.4livepro.com"

RUN_MIGRATE=0
RUN_BUILD=1
RUN_COMPOSER=0

for arg in "$@"; do
    case "$arg" in
        --migrate) RUN_MIGRATE=1 ;;
        --no-build) RUN_BUILD=0 ;;
        --composer) RUN_COMPOSER=1 ;;
        -h|--help) sed -n '2,14p' "$0"; exit 0 ;;
        *) echo "Argumento desconocido: $arg (usa --help)" >&2; exit 1 ;;
    esac
done

step() { echo -e "\n==> $*"; }

if [ -n "$(git status --porcelain)" ]; then
    echo "Hay cambios sin commitear. Haz commit antes de desplegar (o el deploy no los incluirá)." >&2
    git status --short
    exit 1
fi

COMMIT_SHA=$(git rev-parse --short HEAD)
COMMIT_MSG=$(git log -1 --pretty=%s)

step "Desplegando commit ${COMMIT_SHA} (\"${COMMIT_MSG}\") a ${REMOTE_PATH}"
git archive HEAD | ssh "$SSH_HOST" "tar -x -C '$REMOTE_PATH'"

step "Permisos (antes de tocar cachés)"
ssh "$SSH_HOST" "chown -R www-data:www-data '$REMOTE_PATH/storage' '$REMOTE_PATH/bootstrap/cache' && chmod +x '$REMOTE_PATH/artisan' '$REMOTE_PATH/install.sh' 2>/dev/null || true"

if [ "$RUN_COMPOSER" -eq 1 ]; then
    step "Dependencias PHP (composer install)"
    ssh "$SSH_HOST" "cd '$REMOTE_PATH' && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader"
fi

if [ "$RUN_MIGRATE" -eq 1 ]; then
    step "Migraciones"
    ssh "$SSH_HOST" "cd '$REMOTE_PATH' && php artisan migrate --force"
fi

if [ "$RUN_BUILD" -eq 1 ]; then
    step "Assets (npm install && npm run build)"
    ssh "$SSH_HOST" "cd '$REMOTE_PATH' && npm install && npm run build"
    ssh "$SSH_HOST" "chown -R www-data:www-data '$REMOTE_PATH/public/build'"
fi

step "Limpiando cachés de Laravel"
ssh "$SSH_HOST" "cd '$REMOTE_PATH' && php artisan optimize:clear"

step "Permisos (después de limpiar cachés — por si algo se recreó como root)"
ssh "$SSH_HOST" "chown -R www-data:www-data '$REMOTE_PATH/storage' '$REMOTE_PATH/bootstrap/cache'"

step "Reiniciando el worker de colas (para que tome el código nuevo)"
ssh "$SSH_HOST" "cd '$REMOTE_PATH' && php artisan queue:restart" || true

step "Respaldo en el git local del VPS"
ssh "$SSH_HOST" "cd '$REMOTE_PATH' && git add -A && git commit -q -m 'Deploy: ${COMMIT_SHA} ${COMMIT_MSG}' --allow-empty"

step "Verificando que el sitio responde"
HTTP_CODE=$(curl -sk -o /dev/null -w '%{http_code}' "$DOMAIN/")
echo "HTTP $HTTP_CODE — $DOMAIN"

if [ "$HTTP_CODE" != "200" ]; then
    echo "⚠️  El sitio no respondió 200 después del deploy. Revisar antes de dar por terminado." >&2
    exit 1
fi

echo -e "\nDeploy completo."
