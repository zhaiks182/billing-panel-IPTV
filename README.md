# Billing Panel — 4LivePro Latino

Panel de reventa de IPTV construido en Laravel. Los clientes compran paquetes M3U, suben su
comprobante de pago, un administrador aprueba el pedido y el sistema provisiona automáticamente
la línea en un panel **XUI ONE** vía su API de reseller.

## Funcionalidades

- Catálogo de paquetes por categoría, con paquetes de prueba (demo/trial) y de pago.
- Carrito de compra y flujo de pedido con comprobante de pago (transferencia, Zelle, etc.).
- Panel de administración: pedidos (aprobar/rechazar/reintentar), paquetes, categorías,
  métodos de pago, usuarios.
- Integración con **XUI ONE** para crear/consultar líneas M3U automáticamente al aprobar un pedido.
- Activación automática de líneas de prueba tras verificar el correo (evita abuso con correos falsos).
- Notificaciones por **Telegram** (pedidos nuevos, líneas activadas) y por **correo**
  (SMTP configurable desde el propio panel, sin tocar `.env`).
- Recordatorios de vencimiento de líneas por correo (`php artisan lines:send-expiration-reminders`).
- Protección con Cloudflare Turnstile configurable desde el admin.

## Stack

Laravel 13 (PHP 8.3) · Breeze (auth) · Blade + Tailwind CSS + Alpine.js · Vite · MySQL en
producción / SQLite en desarrollo local.

## Documentación

- [`CLAUDE.md`](CLAUDE.md) — arquitectura, flujo de negocio, modelo de datos, infraestructura
  (VPS, despliegue, repos) y bitácora de cambios. Punto de partida para entender el proyecto.
- [`INSTALL.md`](INSTALL.md) — cómo instalar este panel en un servidor LAMP nuevo (prerrequisitos,
  base de datos, usuario administrador) usando [`install.sh`](install.sh).

## Desarrollo local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed          # carga categoría/paquetes/métodos de pago de ejemplo
php artisan app:create-admin admin@ejemplo.com "tu-clave" --name="Administrador"
npm install && npm run dev
php artisan serve
```

## Licencia

Proyecto privado de 4LivePro Latino. No es software de código abierto para redistribuir.
