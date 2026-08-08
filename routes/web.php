<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\LineController as AdminLineController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PackageCategoryController as AdminPackageCategoryController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\Admin\TelegramSettingController;
use App\Http\Controllers\Admin\TurnstileSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\XuiSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

Route::get('/', [PackageController::class, 'index'])->name('home');
Route::get('/categoria/{category:slug}', [PackageController::class, 'category'])->name('packages.category');

// Rutas de cliente/invitado que no exigen sesión iniciada, pero si quien las visita resulta
// ser un admin autenticado, se le manda de vuelta al panel en vez de dejarlo "comprar" o
// abrir tickets como si fuera cliente (ver App\Http\Middleware\RedirectAuthenticatedAdmin).
Route::middleware('no-admin')->group(function () {
    Route::get('/tickets/nuevo', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store')->middleware('throttle:10,1');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/responder', [TicketController::class, 'reply'])->name('tickets.reply')->middleware('throttle:20,1');

    Route::get('/carro', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carro/vaciar', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/carro/{package:slug}', [CartController::class, 'store'])->name('cart.store');

    Route::get('/paquetes/{package:slug}/comprar', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/paquetes/{package:slug}/comprar', [OrderController::class, 'store'])->name('orders.store')->middleware('throttle:10,1');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'no-admin', 'not-blocked'])
    ->name('dashboard');

Route::middleware(['auth', 'no-admin', 'not-blocked'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}/estado', [OrderController::class, 'status'])->name('orders.status');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
});

// Panel admin — módulo aparte, no anidado en el grupo `auth` de clientes: la entrada
// (login) debe ser accesible sin sesión, así que tiene su propio sub-grupo protegido adentro.
Route::prefix('adm_4livepro')->name('admin.')->group(function () {
    Route::get('/', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/', [AdminAuthController::class, 'store'])->name('login.store')->middleware('throttle:10,1');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::middleware(['auth', 'admin', 'admin.timeout'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/pedidos', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::post('/pedidos/{order}/aprobar', [AdminOrderController::class, 'approve'])->name('orders.approve');
        Route::post('/pedidos/{order}/rechazar', [AdminOrderController::class, 'reject'])->name('orders.reject');
        Route::post('/pedidos/{order}/reintentar', [AdminOrderController::class, 'retry'])->name('orders.retry');

        Route::get('/lineas', [AdminLineController::class, 'index'])->name('lines.index');
        Route::get('/lineas/{line}', [AdminLineController::class, 'show'])->name('lines.show');
        Route::post('/lineas/{line}/renovar', [AdminLineController::class, 'renew'])->name('lines.renew');
        Route::post('/lineas/{line}/aplicar-paquete', [AdminLineController::class, 'applyPackage'])->name('lines.apply-package');
        Route::post('/lineas/{line}/suspender', [AdminLineController::class, 'toggleSuspend'])->name('lines.toggle-suspend');
        Route::post('/lineas/{line}/password', [AdminLineController::class, 'changePassword'])->name('lines.change-password');
        Route::post('/lineas/{line}/reenviar', [AdminLineController::class, 'resend'])->name('lines.resend');
        Route::post('/lineas/{line}/sincronizar', [AdminLineController::class, 'sync'])->name('lines.sync');
        Route::delete('/lineas/{line}', [AdminLineController::class, 'destroy'])->name('lines.destroy');

        Route::resource('paquetes', AdminPackageController::class)->except('show')->parameters(['paquetes' => 'package']);
        Route::resource('categorias', AdminPackageCategoryController::class)->except('show')->parameters(['categorias' => 'category']);
        Route::resource('metodos-pago', AdminPaymentMethodController::class)->except('show')->parameters(['metodos-pago' => 'paymentMethod']);

        Route::get('/configuracion-xui', [XuiSettingController::class, 'edit'])->name('xui.edit');
        Route::put('/configuracion-xui', [XuiSettingController::class, 'update'])->name('xui.update');

        Route::get('/configuracion-correo', [MailSettingController::class, 'edit'])->name('mail.edit');
        Route::put('/configuracion-correo', [MailSettingController::class, 'update'])->name('mail.update');

        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/administradores', [AdminUserController::class, 'admins'])->name('users.admins');
        Route::get('/usuarios/nuevo', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/usuarios/{user}/verificar', [AdminUserController::class, 'verify'])->name('users.verify');
        Route::post('/usuarios/{user}/bloquear', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');
        Route::delete('/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/configuracion-turnstile', [TurnstileSettingController::class, 'edit'])->name('turnstile.edit');
        Route::put('/configuracion-turnstile', [TurnstileSettingController::class, 'update'])->name('turnstile.update');

        Route::get('/configuracion-telegram', [TelegramSettingController::class, 'edit'])->name('telegram.edit');
        Route::put('/configuracion-telegram', [TelegramSettingController::class, 'update'])->name('telegram.update');
        Route::post('/configuracion-telegram/probar', [TelegramSettingController::class, 'test'])->name('telegram.test');

        Route::get('/plantillas-correo', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('/plantillas-correo/{emailTemplate}', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::put('/plantillas-correo/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::post('/plantillas-correo/{emailTemplate}/probar', [EmailTemplateController::class, 'test'])->name('email-templates.test');

        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/responder', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::put('/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('tickets.update');
    });
});

require __DIR__.'/auth.php';
