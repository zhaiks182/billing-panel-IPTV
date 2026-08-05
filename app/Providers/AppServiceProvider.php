<?php

namespace App\Providers;

use App\Models\EmailTemplate;
use App\Models\Line;
use App\Models\MailSetting;
use App\Models\Order;
use App\Observers\LineObserver;
use App\Observers\OrderObserver;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyMailSettings();

        Order::observe(OrderObserver::class);
        Line::observe(LineObserver::class);

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return EmailTemplate::mail('verify_email', [
                'user_name' => $notifiable->name,
                'verification_url' => $url,
            ]);
        });
    }

    /**
     * El correo (verificación de cuenta y confirmación de pedidos) se configura
     * desde Admin > Configuración de correo en vez de editar el .env. Si el admin
     * no ha configurado SMTP todavía, se deja el mailer por defecto (log) intacto.
     */
    private function applyMailSettings(): void
    {
        if (! Schema::hasTable('mail_settings')) {
            return;
        }

        $settings = MailSetting::query()->first();

        if (! $settings || $settings->mailer !== 'smtp' || ! $settings->host) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->host);
        Config::set('mail.mailers.smtp.port', $settings->port);
        Config::set('mail.mailers.smtp.username', $settings->username);
        Config::set('mail.mailers.smtp.password', $settings->password);
        Config::set('mail.mailers.smtp.encryption', $settings->encryption ?: null);

        if ($settings->from_address) {
            Config::set('mail.from.address', $settings->from_address);
            Config::set('mail.from.name', $settings->from_name ?: config('app.name'));
        }
    }
}
