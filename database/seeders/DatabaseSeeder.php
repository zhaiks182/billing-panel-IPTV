<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\XuiSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@example.com',
            'email_verified_at' => now(),
        ]);

        $category = PackageCategory::firstOrCreate(['slug' => 'iptv-full'], [
            'name' => 'IPTV Full [+8,500 Canales]',
            'description' => 'Latinoamérica, USA, España y Eventos Diarios. Elige qué canales encender según tus necesidades.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $features = static fn (int $connections) => implode("\n", [
            '+8,500 Canales',
            'Latinoamérica, USA, España y Eventos Diarios',
            'El cliente elige qué canales encender según sus necesidades',
            "Máximo {$connections} conexión(es) simultánea(s)",
        ]);

        $packages = [
            ['name' => '1 mes - 1 pantalla', 'price' => 10, 'duration_days' => 30, 'max_connections' => 1],
            ['name' => '3 meses - 1 pantalla', 'price' => 25, 'duration_days' => 90, 'max_connections' => 1],
            ['name' => '1 mes - 2 pantallas', 'price' => 15, 'duration_days' => 30, 'max_connections' => 2],
        ];

        foreach ($packages as $data) {
            Package::firstOrCreate(['slug' => Str::slug($data['name'])], [
                'package_category_id' => $category->id,
                'name' => $data['name'],
                'description' => "Acceso completo por {$data['duration_days']} días, {$data['max_connections']} conexión(es) simultánea(s).",
                'features' => $features($data['max_connections']),
                'price' => $data['price'],
                'duration_days' => $data['duration_days'],
                'max_connections' => $data['max_connections'],
                'is_active' => true,
            ]);
        }

        Package::firstOrCreate(['slug' => 'demo'], [
            'package_category_id' => $category->id,
            'xui_package_id' => '1',
            'name' => 'Demo',
            'description' => 'Prueba gratuita por tiempo limitado para conocer el servicio antes de comprar.',
            'features' => $features(3),
            'price' => 0,
            'duration_days' => 2,
            'duration_unit' => 'hours',
            'max_connections' => 3,
            'is_active' => true,
            'is_trial' => true,
        ]);

        PaymentMethod::firstOrCreate(['name' => 'Transferencia bancaria'], [
            'instructions' => "Banco: Ejemplo\nCuenta: 000-000000-0\nTitular: Tu Nombre\n\nSube el comprobante luego de transferir.",
            'is_active' => true,
        ]);

        PaymentMethod::firstOrCreate(['name' => 'Zelle'], [
            'instructions' => "Zelle a: tu-correo@ejemplo.com\nNombre: Tu Nombre\n\nSube la captura de pantalla del envío.",
            'is_active' => true,
        ]);

        XuiSetting::firstOrCreate([], [
            'panel_url' => null,
            'api_token' => null,
            'bouquet_ids' => [],
        ]);
    }
}
