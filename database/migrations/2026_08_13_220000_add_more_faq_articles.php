<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Amplía "Preguntas frecuentes" con 8 artículos más, a pedido del usuario tras comparar
 * con el sitio de referencia (le faltaban varios temas de "¿Qué es...?"). Contenido escrito
 * desde cero (conocimiento general de IPTV, no copiado/traducido de ninguna fuente — ver
 * CLAUDE.md "Módulo de Ayuda/Tutoriales" para la razón de derechos de autor).
 */
return new class extends Migration
{
    public function up(): void
    {
        $ul = fn (array $items) => '<ul>'.collect($items)->map(fn ($i) => "<li>{$i}</li>")->implode('').'</ul>';

        $categoryId = DB::table('help_categories')->where('slug', 'preguntas-frecuentes')->value('id');

        $articles = [
            [
                'title' => '¿Cómo funciona IPTV?',
                'slug' => 'como-funciona-iptv',
                'excerpt' => 'El recorrido de la señal, desde el servidor hasta tu pantalla.',
                'sort_order' => 4,
                'content' =>
                    '<p>IPTV entrega televisión usando la misma tecnología con la que funciona cualquier video en streaming — solo que en vez de un archivo grabado, es una señal continua de un canal en vivo.</p>'
                    .'<h2>El recorrido de la señal</h2>'
                    .$ul([
                        'Un servidor recibe la señal original del canal y la convierte en un formato apto para transmitir por internet.',
                        'Esa señal se empaqueta en datos y se envía a través de la red hasta tu conexión a internet.',
                        'Tu dispositivo (TV, celular, TV Box) usa una app reproductora que recibe esos datos y los decodifica en video, igual que hace con cualquier video de YouTube o Netflix.',
                    ])
                    .'<p>La diferencia con ver una película en una plataforma de streaming es que un canal de IPTV se transmite <strong>en vivo, sin pausas</strong> — no descargas el contenido, lo recibes en tiempo real igual que la TV tradicional.</p>',
            ],
            [
                'title' => '¿Qué es una suscripción IPTV?',
                'slug' => 'que-es-suscripcion-iptv',
                'excerpt' => 'Qué incluye tu plan y por cuánto tiempo dura el acceso.',
                'sort_order' => 5,
                'content' =>
                    '<p>Una suscripción IPTV es el <strong>acceso por tiempo determinado</strong> a un catálogo de canales (y a veces también películas/series bajo demanda), ligado a tu línea personal — tu usuario, contraseña y URL M3U.</p>'
                    .'<h2>¿Qué incluye normalmente?</h2>'
                    .$ul([
                        'Acceso a los canales del bouquet/plan que elegiste.',
                        'Una cantidad determinada de <strong>conexiones simultáneas</strong> (ver nuestra guía sobre eso).',
                        'Una <strong>duración</strong> fija — al vencer, hay que renovar para seguir con acceso.',
                    ])
                    .'<p>A diferencia de un servicio de streaming genérico, tu suscripción es personal e intransferible — no debe compartirse con personas fuera de tu hogar, y superar el límite de conexiones simultáneas puede cortar la reproducción.</p>',
            ],
            [
                'title' => '¿Qué es el Video On Demand (VOD)?',
                'slug' => 'que-es-vod',
                'excerpt' => 'La diferencia entre ver canales en vivo y elegir contenido bajo demanda.',
                'sort_order' => 6,
                'content' =>
                    '<p>VOD significa <strong>Video On Demand</strong> (video bajo demanda) — un catálogo de películas y series que puedes elegir y reproducir cuando quieras, a diferencia de un canal en vivo que sigue su propia programación.</p>'
                    .'<h2>¿Cómo se diferencia de un canal en vivo?</h2>'
                    .$ul([
                        'Un canal en vivo transmite lo mismo a todos los espectadores al mismo tiempo — no puedes elegir qué se pasa ni cuándo.',
                        'El contenido VOD está disponible como una biblioteca — lo pausas, adelantas, retrocedes o retomas cuando quieras, como en cualquier plataforma de streaming.',
                    ])
                    .'<p>Muchos planes IPTV incluyen tanto canales en vivo como una sección VOD dentro de la misma app reproductora — revisa tu app para ver si tiene una sección de "Películas" o "Series" además de la guía de canales en vivo.</p>',
            ],
            [
                'title' => '¿Qué es el Catch-up TV en IPTV?',
                'slug' => 'que-es-catchup-tv',
                'excerpt' => 'Cómo volver a ver un programa que ya pasó, sin grabarlo tú mismo.',
                'sort_order' => 7,
                'content' =>
                    '<p>Catch-up TV (también llamado "TV en diferido") te permite ver un programa que <strong>ya se transmitió</strong> dentro de una ventana de tiempo reciente (por ejemplo, las últimas 24-72 horas), sin necesidad de haberlo grabado.</p>'
                    .'<h2>¿En qué se diferencia de una grabadora (DVR)?</h2>'
                    .$ul([
                        'Un DVR graba localmente lo que tú programas de antemano — ocupa espacio de almacenamiento y depende de que hayas configurado la grabación.',
                        'El Catch-up TV ya está disponible del lado del servidor para ciertos canales — simplemente retrocedes en la guía de programación y reproduces lo que ya pasó, sin haber hecho nada antes.',
                    ])
                    .'<p>No todos los canales ni todos los planes incluyen catch-up — depende de si el canal lo ofrece y de si tu app reproductora lo soporta (suele verse como una flecha hacia atrás junto al programa en la guía).</p>',
            ],
            [
                'title' => '¿Cuál es la diferencia entre IPTV y la TV por cable?',
                'slug' => 'iptv-vs-cable',
                'excerpt' => 'Tecnología, costo y flexibilidad: en qué se parecen y en qué no.',
                'sort_order' => 8,
                'content' =>
                    '<p>Ambos te dan canales de televisión, pero llegan a tu casa de forma completamente distinta.</p>'
                    .'<h2>TV por cable</h2>'
                    .$ul([
                        'Necesita un cableado físico instalado por la operadora hasta tu casa.',
                        'Suele requerir un decodificador propio del proveedor.',
                        'La cobertura depende de la infraestructura del operador en tu zona.',
                    ])
                    .'<h2>IPTV</h2>'
                    .$ul([
                        'Solo necesita una conexión a internet — no depende de cableado especial del proveedor de TV.',
                        'Funciona en múltiples dispositivos (TV, celular, tablet, TV Box) con la misma suscripción.',
                        'Suele ofrecer más variedad de canales, incluyendo contenido internacional.',
                    ])
                    .'<p>La contrapartida de IPTV es que la calidad depende directamente de tu conexión a internet — una conexión lenta o inestable puede causar cortes, algo que no le pasa a un cable dedicado.</p>',
            ],
            [
                'title' => '¿Qué es un Android TV Box?',
                'slug' => 'que-es-android-tv-box',
                'excerpt' => 'El dispositivo más común para convertir cualquier TV en un Smart TV con IPTV.',
                'sort_order' => 9,
                'content' =>
                    '<p>Un Android TV Box es un pequeño dispositivo que se conecta a tu televisor por HDMI y corre el sistema operativo Android — básicamente convierte cualquier TV "normal" en un Smart TV, con acceso a Google Play Store, apps de streaming, y por supuesto, reproductores de IPTV.</p>'
                    .'<h2>¿Por qué es popular para IPTV?</h2>'
                    .$ul([
                        'Es económico comparado con comprar un Smart TV nuevo.',
                        'Al correr Android, tiene acceso a prácticamente cualquier app reproductora de IPTV que exista.',
                        'Se actualiza y sigue recibiendo apps nuevas, a diferencia de algunos Smart TV con sistemas cerrados que dejan de recibir actualizaciones.',
                    ])
                    .'<p>Para usar tu servicio en un Android TV Box, sigue la misma guía que para Android TV/Fire TV Stick — instalar una app reproductora y cargar tu lista M3U.</p>',
            ],
            [
                'title' => '¿Qué es un dispositivo MAG?',
                'slug' => 'que-es-dispositivo-mag',
                'excerpt' => 'Un tipo de receptor IPTV distinto, que se identifica por su dirección MAC.',
                'sort_order' => 10,
                'content' =>
                    '<p>Un dispositivo MAG es un tipo de <strong>set-top-box</strong> (receptor) pensado específicamente para IPTV, distinto de un Android TV Box genérico. En vez de instalar una app y cargar una URL M3U, un MAG se conecta a un <strong>portal</strong> del proveedor y se identifica mediante su <strong>dirección MAC</strong> (un identificador único de fábrica del propio dispositivo).</p>'
                    .'<h2>Diferencias frente a una app en Android</h2>'
                    .$ul([
                        'No usa usuario/contraseña como una lista M3U — tu línea queda asociada directamente a la MAC del dispositivo.',
                        'La interfaz y el menú de canales corren de forma nativa en el dispositivo, no dentro de una app instalada.',
                        'Suele ser más simple de usar para quienes no quieren lidiar con apps o configuraciones — enciendes el equipo y ya carga tu contenido.',
                    ])
                    .'<p>Si tienes un dispositivo MAG, contáctanos con su dirección MAC para activar tu línea — ver nuestra guía de instalación para MAG.</p>',
            ],
            [
                'title' => '¿Qué es un revendedor IPTV?',
                'slug' => 'que-es-revendedor-iptv',
                'excerpt' => 'Cómo funciona el modelo de negocio detrás de un servicio como el nuestro.',
                'sort_order' => 11,
                'content' =>
                    '<p>Un revendedor IPTV es una persona o empresa que compra acceso al contenido a un proveedor mayorista y lo revende a clientes finales bajo su propia marca, precios y soporte — es, en pocas palabras, el modelo de negocio detrás de este mismo panel.</p>'
                    .'<h2>¿Cómo funciona?</h2>'
                    .$ul([
                        'El revendedor administra un panel (como XUI ONE) donde crea y gestiona las líneas de sus clientes.',
                        'Cada línea tiene su propio usuario, contraseña, límite de conexiones y fecha de vencimiento.',
                        'El revendedor se encarga del soporte directo al cliente — instalación, renovaciones, resolución de problemas.',
                    ])
                    .'<p>Como cliente, esto significa que tu servicio, soporte y facturación pasan directamente por nosotros — no necesitas lidiar con ningún proveedor mayorista de por medio.</p>',
            ],
        ];

        foreach ($articles as $article) {
            DB::table('help_articles')->updateOrInsert(
                ['slug' => $article['slug']],
                [
                    'help_category_id' => $categoryId,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'sort_order' => $article['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('help_articles')->whereIn('slug', [
            'como-funciona-iptv', 'que-es-suscripcion-iptv', 'que-es-vod',
            'que-es-catchup-tv', 'iptv-vs-cable', 'que-es-android-tv-box',
            'que-es-dispositivo-mag', 'que-es-revendedor-iptv',
        ])->delete();
    }
};
