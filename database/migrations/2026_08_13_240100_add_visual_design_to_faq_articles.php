<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rediseño visual de los 11 artículos de "Preguntas frecuentes" — a pedido del usuario tras
 * mostrar una captura de referencia (ícono grande junto al título, caja de "Resumen" al
 * inicio, íconos por sección). Es un patrón de diseño genérico (no protegido); el texto de
 * cada artículo sigue siendo 100% original, reescrito con esta estructura nueva — no es una
 * traducción de ninguna fuente externa (ver CLAUDE.md "Módulo de Ayuda/Tutoriales").
 */
return new class extends Migration
{
    public function up(): void
    {
        $summary = fn (string $text) => '<div class="help-summary"><span class="help-summary-icon">💡</span><div><strong>Resumen</strong><p>'.$text.'</p></div></div>';
        $ul = fn (array $items) => '<ul>'.collect($items)->map(fn ($i) => "<li>{$i}</li>")->implode('').'</ul>';

        $articles = [
            'que-es-iptv' => [
                'icon' => '📺',
                'content' =>
                    $summary('IPTV transmite televisión a través de internet, en vez de por cable, antena o satélite — funciona igual que cualquier video en streaming, pero con canales en vivo.')
                    .'<p>IPTV significa <strong>Internet Protocol Television</strong>. En la práctica, es lo mismo que ver un video en streaming, pero con canales de TV en vivo en vez de contenido grabado.</p>'
                    .'<h2>🔄 ¿Cómo funciona?</h2>'
                    .'<p>El contenido se transmite en paquetes de datos a través de tu conexión a internet. Tu dispositivo (TV, celular, TV Box, etc.) usa una app reproductora que recibe esa señal y la muestra como un canal normal.</p>'
                    .'<h2>✅ Ventajas frente al cable tradicional</h2>'
                    .$ul([
                        'Puedes ver tus canales en varios dispositivos, no solo en un televisor conectado a un cable físico.',
                        'No depende de infraestructura local (antena, cableado del edificio) — solo de tu conexión a internet.',
                        'Suele incluir más variedad de canales y contenido internacional.',
                    ])
                    .'<p>Lo único que necesitas es una conexión a internet estable y un dispositivo compatible (ver nuestras guías de instalación por dispositivo).</p>',
            ],
            'como-funciona-iptv' => [
                'icon' => '🔄',
                'content' =>
                    $summary('IPTV usa la misma tecnología que cualquier streaming: el video se empaqueta en datos, viaja por tu conexión a internet, y tu dispositivo lo decodifica en tiempo real.')
                    .'<p>IPTV entrega televisión usando la misma tecnología con la que funciona cualquier video en streaming — solo que en vez de un archivo grabado, es una señal continua de un canal en vivo.</p>'
                    .'<h2>📡 El recorrido de la señal</h2>'
                    .$ul([
                        'Un servidor recibe la señal original del canal y la convierte en un formato apto para transmitir por internet.',
                        'Esa señal se empaqueta en datos y se envía a través de la red hasta tu conexión a internet.',
                        'Tu dispositivo (TV, celular, TV Box) usa una app reproductora que recibe esos datos y los decodifica en video, igual que hace con cualquier video de YouTube o Netflix.',
                    ])
                    .'<h2>⏱️ En vivo, no descargado</h2>'
                    .'<p>La diferencia con ver una película en una plataforma de streaming es que un canal de IPTV se transmite <strong>en vivo, sin pausas</strong> — no descargas el contenido, lo recibes en tiempo real igual que la TV tradicional.</p>',
            ],
            'que-es-suscripcion-iptv' => [
                'icon' => '📅',
                'content' =>
                    $summary('Tu suscripción es el acceso por tiempo determinado a un catálogo de canales, ligado a tu línea personal — usuario, contraseña y URL M3U únicos.')
                    .'<p>Una suscripción IPTV es el <strong>acceso por tiempo determinado</strong> a un catálogo de canales (y a veces también películas/series bajo demanda), ligado a tu línea personal.</p>'
                    .'<h2>📦 ¿Qué incluye normalmente?</h2>'
                    .$ul([
                        'Acceso a los canales del bouquet/plan que elegiste.',
                        'Una cantidad determinada de <strong>conexiones simultáneas</strong>.',
                        'Una <strong>duración</strong> fija — al vencer, hay que renovar para seguir con acceso.',
                    ])
                    .'<h2>🔒 Es personal</h2>'
                    .'<p>A diferencia de un servicio de streaming genérico, tu suscripción es personal e intransferible — no debe compartirse con personas fuera de tu hogar, y superar el límite de conexiones simultáneas puede cortar la reproducción.</p>',
            ],
            'que-es-una-lista-m3u' => [
                'icon' => '📃',
                'content' =>
                    $summary('Una lista M3U es el archivo (o URL) que contiene todos tus canales — nombre, enlace, categoría y logo — y es lo que arma tu guía de canales dentro de la app.')
                    .'<p>Una lista M3U es un archivo (o una URL que genera ese archivo) que contiene la información de todos los canales disponibles en tu servicio: su nombre, el enlace de transmisión, la categoría a la que pertenecen y su logo.</p>'
                    .'<p>Cuando cargas tu lista M3U en una app reproductora, esa app lee el archivo y arma automáticamente tu guía de canales — es lo que hace posible ver todo organizado por categorías en vez de tener que buscar cada canal a mano.</p>'
                    .'<h2>🔑 Tu URL M3U personal</h2>'
                    .'<p>Cada línea tiene su propia URL M3U única, con tu usuario y contraseña incluidos en el enlace. Por eso es importante <strong>no compartirla</strong> — cualquiera con esa URL puede ver tus canales, y hacerlo desde varios lugares a la vez puede superar el límite de conexiones simultáneas de tu plan.</p>'
                    .'<p>Puedes encontrar tu URL M3U en el correo que recibiste al activarse tu línea, o pidiéndola por soporte si la perdiste.</p>',
            ],
            'que-es-vod' => [
                'icon' => '🎬',
                'content' =>
                    $summary('VOD es un catálogo de películas y series que eliges y reproduces cuando quieras — a diferencia de un canal en vivo, que sigue su propia programación.')
                    .'<p>VOD significa <strong>Video On Demand</strong> (video bajo demanda) — un catálogo de películas y series que puedes elegir y reproducir cuando quieras, a diferencia de un canal en vivo que sigue su propia programación.</p>'
                    .'<h2>🔀 ¿Cómo se diferencia de un canal en vivo?</h2>'
                    .$ul([
                        'Un canal en vivo transmite lo mismo a todos los espectadores al mismo tiempo — no puedes elegir qué se pasa ni cuándo.',
                        'El contenido VOD está disponible como una biblioteca — lo pausas, adelantas, retrocedes o retomas cuando quieras, como en cualquier plataforma de streaming.',
                    ])
                    .'<p>Muchos planes IPTV incluyen tanto canales en vivo como una sección VOD dentro de la misma app reproductora — revisa tu app para ver si tiene una sección de "Películas" o "Series" además de la guía de canales en vivo.</p>',
            ],
            'que-es-catchup-tv' => [
                'icon' => '⏪',
                'content' =>
                    $summary('El Catch-up TV te deja ver un programa que ya se transmitió, dentro de una ventana de tiempo reciente, sin haberlo grabado tú mismo.')
                    .'<p>Catch-up TV (también llamado "TV en diferido") te permite ver un programa que <strong>ya se transmitió</strong> dentro de una ventana de tiempo reciente (por ejemplo, las últimas 24-72 horas), sin necesidad de haberlo grabado.</p>'
                    .'<h2>⏱️ ¿En qué se diferencia de una grabadora (DVR)?</h2>'
                    .$ul([
                        'Un DVR graba localmente lo que tú programas de antemano — ocupa espacio de almacenamiento y depende de que hayas configurado la grabación.',
                        'El Catch-up TV ya está disponible del lado del servidor para ciertos canales — simplemente retrocedes en la guía de programación y reproduces lo que ya pasó, sin haber hecho nada antes.',
                    ])
                    .'<p>No todos los canales ni todos los planes incluyen catch-up — depende de si el canal lo ofrece y de si tu app reproductora lo soporta (suele verse como una flecha hacia atrás junto al programa en la guía).</p>',
            ],
            'iptv-vs-cable' => [
                'icon' => '⚖️',
                'content' =>
                    $summary('IPTV solo necesita internet y funciona en varios dispositivos; el cable necesita cableado físico y un decodificador propio del proveedor.')
                    .'<p>Ambos te dan canales de televisión, pero llegan a tu casa de forma completamente distinta.</p>'
                    .'<h2>📡 TV por cable</h2>'
                    .$ul([
                        'Necesita un cableado físico instalado por la operadora hasta tu casa.',
                        'Suele requerir un decodificador propio del proveedor.',
                        'La cobertura depende de la infraestructura del operador en tu zona.',
                    ])
                    .'<h2>🌐 IPTV</h2>'
                    .$ul([
                        'Solo necesita una conexión a internet — no depende de cableado especial del proveedor de TV.',
                        'Funciona en múltiples dispositivos (TV, celular, tablet, TV Box) con la misma suscripción.',
                        'Suele ofrecer más variedad de canales, incluyendo contenido internacional.',
                    ])
                    .'<p>La contrapartida de IPTV es que la calidad depende directamente de tu conexión a internet — una conexión lenta o inestable puede causar cortes, algo que no le pasa a un cable dedicado.</p>',
            ],
            'que-es-android-tv-box' => [
                'icon' => '📱',
                'content' =>
                    $summary('Un Android TV Box convierte cualquier televisor en un Smart TV, con acceso a Google Play y a cualquier app reproductora de IPTV.')
                    .'<p>Un Android TV Box es un pequeño dispositivo que se conecta a tu televisor por HDMI y corre el sistema operativo Android — básicamente convierte cualquier TV "normal" en un Smart TV, con acceso a Google Play Store, apps de streaming, y por supuesto, reproductores de IPTV.</p>'
                    .'<h2>⭐ ¿Por qué es popular para IPTV?</h2>'
                    .$ul([
                        'Es económico comparado con comprar un Smart TV nuevo.',
                        'Al correr Android, tiene acceso a prácticamente cualquier app reproductora de IPTV que exista.',
                        'Se actualiza y sigue recibiendo apps nuevas, a diferencia de algunos Smart TV con sistemas cerrados que dejan de recibir actualizaciones.',
                    ])
                    .'<p>Para usar tu servicio en un Android TV Box, sigue la misma guía que para Android TV/Fire TV Stick — instalar una app reproductora y cargar tu lista M3U.</p>',
            ],
            'que-es-dispositivo-mag' => [
                'icon' => '📟',
                'content' =>
                    $summary('Un dispositivo MAG se conecta a un portal y se identifica por su dirección MAC, en vez de usar usuario/contraseña como una app normal.')
                    .'<p>Un dispositivo MAG es un tipo de <strong>set-top-box</strong> (receptor) pensado específicamente para IPTV, distinto de un Android TV Box genérico. En vez de instalar una app y cargar una URL M3U, un MAG se conecta a un <strong>portal</strong> del proveedor y se identifica mediante su <strong>dirección MAC</strong>.</p>'
                    .'<h2>🆚 Diferencias frente a una app en Android</h2>'
                    .$ul([
                        'No usa usuario/contraseña como una lista M3U — tu línea queda asociada directamente a la MAC del dispositivo.',
                        'La interfaz y el menú de canales corren de forma nativa en el dispositivo, no dentro de una app instalada.',
                        'Suele ser más simple de usar para quienes no quieren lidiar con apps o configuraciones — enciendes el equipo y ya carga tu contenido.',
                    ])
                    .'<p>Si tienes un dispositivo MAG, contáctanos con su dirección MAC para activar tu línea — ver nuestra guía de instalación para MAG.</p>',
            ],
            'que-es-revendedor-iptv' => [
                'icon' => '💼',
                'content' =>
                    $summary('Un revendedor compra acceso a un mayorista y lo revende bajo su propia marca y soporte — es el modelo de negocio detrás de este mismo panel.')
                    .'<p>Un revendedor IPTV es una persona o empresa que compra acceso al contenido a un proveedor mayorista y lo revende a clientes finales bajo su propia marca, precios y soporte.</p>'
                    .'<h2>⚙️ ¿Cómo funciona?</h2>'
                    .$ul([
                        'El revendedor administra un panel (como XUI ONE) donde crea y gestiona las líneas de sus clientes.',
                        'Cada línea tiene su propio usuario, contraseña, límite de conexiones y fecha de vencimiento.',
                        'El revendedor se encarga del soporte directo al cliente — instalación, renovaciones, resolución de problemas.',
                    ])
                    .'<p>Como cliente, esto significa que tu servicio, soporte y facturación pasan directamente por nosotros — no necesitas lidiar con ningún proveedor mayorista de por medio.</p>',
            ],
            'cuantas-conexiones-simultaneas' => [
                'icon' => '🔢',
                'content' =>
                    $summary('El límite de conexiones simultáneas es cuántos dispositivos pueden reproducir tu lista al mismo tiempo, no en cuántos puedes instalarla.')
                    .'<p>Cada plan tiene un número máximo de <strong>conexiones simultáneas</strong> — es decir, cuántos dispositivos pueden estar reproduciendo tu lista M3U <em>al mismo tiempo</em>.</p>'
                    .'<p>Esto no limita en cuántos dispositivos puedes <em>instalar</em> la lista (puedes tenerla configurada en tu celular, tu TV y tu tablet a la vez), sino cuántos pueden estar viendo contenido en el mismo instante.</p>'
                    .'<h2>⚠️ ¿Qué pasa si supero el límite?</h2>'
                    .'<p>Si intentas abrir una conexión adicional por encima de tu límite, esa conexión nueva se rechaza o se corta la más antigua, dependiendo de la configuración — lo más común es que simplemente no puedas reproducir hasta que cierres una de las sesiones activas.</p>'
                    .'<p>Si necesitas ver contenido en más pantallas al mismo tiempo de forma regular, puedes solicitar un plan con más conexiones simultáneas.</p>',
            ],
        ];

        foreach ($articles as $slug => $data) {
            DB::table('help_articles')->where('slug', $slug)->update([
                'icon' => $data['icon'],
                'content' => $data['content'],
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
