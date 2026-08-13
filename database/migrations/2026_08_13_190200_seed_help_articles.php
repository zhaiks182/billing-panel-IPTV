<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Contenido inicial del módulo de Ayuda (App\Models\HelpCategory / HelpArticle) — 4
 * categorías, 21 artículos, escritos desde cero (conocimiento general de IPTV/XUI ONE,
 * no copiado ni traducido de ninguna fuente externa — ver CLAUDE.md "Sección de
 * Ayuda/Tutoriales"). 100% editable después desde Admin > Documentación; este set inicial
 * es un punto de partida, no pretende cubrir cada tema posible.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ol = fn (array $items) => '<ol>'.collect($items)->map(fn ($i) => "<li>{$i}</li>")->implode('').'</ol>';
        $ul = fn (array $items) => '<ul>'.collect($items)->map(fn ($i) => "<li>{$i}</li>")->implode('').'</ul>';

        $categories = [
            [
                'name' => 'Instalación',
                'slug' => 'instalacion',
                'description' => 'Cómo activar tu lista M3U en cada tipo de dispositivo.',
                'audience' => 'public',
                'sort_order' => 1,
            ],
            [
                'name' => 'Preguntas frecuentes',
                'slug' => 'preguntas-frecuentes',
                'description' => 'Conceptos básicos para entender tu servicio.',
                'audience' => 'public',
                'sort_order' => 2,
            ],
            [
                'name' => 'XUI ONE: Administración del panel',
                'slug' => 'xui-one-administracion',
                'description' => 'Guías internas para configurar servidores, bouquets, paquetes y contenido en el panel.',
                'audience' => 'internal',
                'sort_order' => 1,
            ],
            [
                'name' => 'XUI ONE: Líneas y revendedores',
                'slug' => 'xui-one-lineas-revendedores',
                'description' => 'Guías internas para crear y gestionar líneas de clientes y sub-revendedores.',
                'audience' => 'internal',
                'sort_order' => 2,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('help_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'audience' => $category['audience'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // updateOrInsert no devuelve el id — se relee acá, una sola vez.
        $categoryIds = DB::table('help_categories')->pluck('id', 'slug')->all();

        $articles = [
            // ── Instalación (pública) ──────────────────────────────────────────────
            [
                'category' => 'instalacion',
                'title' => 'Cómo activar tu lista M3U en Android y Fire TV Stick',
                'slug' => 'instalar-android-fire-tv-stick',
                'excerpt' => 'Instala una app de reproducción IPTV y activa tu lista M3U en Android TV o Fire TV Stick.',
                'sort_order' => 1,
                'content' =>
                    '<p>Android TV, los TV Box genéricos y el Fire TV Stick de Amazon usan el mismo proceso: instalar una app reproductora de IPTV y cargar tu lista M3U ahí.</p>'
                    .'<h2>Pasos</h2>'
                    .$ol([
                        'Abre la <strong>tienda de aplicaciones</strong> de tu dispositivo (Google Play Store en Android TV, o Amazon Appstore en Fire TV Stick).',
                        'Busca e instala un reproductor IPTV compatible con listas M3U (por ejemplo, uno que acepte "Xtream Codes" o "M3U URL" al configurar una nueva lista).',
                        'Abre la app y elige la opción para <strong>agregar una nueva lista/playlist</strong>.',
                        'Ingresa la URL M3U que te enviamos por correo al activar tu línea (empieza con <code>http://</code> y termina en <code>m3u_plus</code> o similar).',
                        'Guarda y espera a que la app descargue la lista de canales — puede tardar unos segundos la primera vez.',
                        'Listo: ya puedes navegar por categorías y reproducir tus canales.',
                    ])
                    .'<p>Si tu Fire TV Stick no tiene la app en la Appstore, puedes instalarla mediante un instalador de APKs de terceros (por ejemplo Downloader), pegando la URL de descarga directa del instalador de la app.</p>'
                    .'<blockquote>Si la lista tarda mucho en cargar o se congela, revisa tu conexión a internet — IPTV necesita una conexión estable, idealmente por cable de red.</blockquote>',
            ],
            [
                'category' => 'instalacion',
                'title' => 'Cómo activar tu lista M3U en Smart TV (Samsung/LG)',
                'slug' => 'instalar-smart-tv-samsung-lg',
                'excerpt' => 'Configura tu lista M3U directamente en televisores Samsung (Tizen) o LG (webOS).',
                'sort_order' => 2,
                'content' =>
                    '<p>Los Smart TV de Samsung y LG no siempre tienen tiendas de aplicaciones tan abiertas como Android, pero igual pueden reproducir IPTV instalando una app compatible desde su tienda propia.</p>'
                    .'<h2>Pasos</h2>'
                    .$ol([
                        'Abre la <strong>tienda de aplicaciones</strong> de tu TV (Samsung Smart Hub o LG Content Store).',
                        'Busca una app reproductora de IPTV disponible en esa tienda e instálala.',
                        'Abre la app y busca la opción para agregar una lista por <strong>URL M3U</strong>.',
                        'Pega la URL M3U de tu línea (te la enviamos por correo al activarla).',
                        'Confirma y espera a que cargue la lista de canales.',
                    ])
                    .'<p>Si tu modelo de TV es muy antiguo y no tiene una app compatible en su tienda, la alternativa es usar un dispositivo externo (un TV Box Android o un Fire TV Stick conectado por HDMI) — ver la guía de instalación para Android.</p>',
            ],
            [
                'category' => 'instalacion',
                'title' => 'Cómo activar tu lista M3U en iPhone y Apple TV',
                'slug' => 'instalar-iphone-apple-tv',
                'excerpt' => 'Instala una app de IPTV desde el App Store y configura tu lista M3U en iOS o Apple TV.',
                'sort_order' => 3,
                'content' =>
                    '<p>En dispositivos Apple (iPhone, iPad y Apple TV) el proceso es el mismo: instalar una app reproductora desde el App Store y cargar ahí tu lista M3U.</p>'
                    .'<h2>Pasos</h2>'
                    .$ol([
                        'Abre el <strong>App Store</strong> en tu iPhone, iPad o Apple TV.',
                        'Busca e instala una app reproductora de IPTV compatible con listas M3U.',
                        'Abre la app y elige <strong>agregar una lista nueva</strong> por URL.',
                        'Pega la URL M3U que te enviamos por correo al activar tu línea.',
                        'Guarda los cambios y espera a que la app cargue los canales.',
                    ])
                    .'<p>Algunas apps de IPTV para iOS son de pago (el cobro es de la app, no de tu servicio) — hay varias opciones gratuitas también, cualquiera que acepte listas M3U estándar funciona con tu línea.</p>',
            ],
            [
                'category' => 'instalacion',
                'title' => 'Cómo activar tu lista M3U en dispositivos MAG',
                'slug' => 'instalar-dispositivos-mag',
                'excerpt' => 'Configura el portal de tu dispositivo MAG con los datos de tu línea.',
                'sort_order' => 4,
                'content' =>
                    '<p>Los dispositivos MAG (STB tipo set-top-box) funcionan distinto a una app: en vez de una lista M3U, usan un <strong>portal</strong> y se identifican por su dirección MAC.</p>'
                    .'<h2>Pasos</h2>'
                    .$ol([
                        'Enciende tu dispositivo MAG y entra al <strong>menú de configuración</strong> (usualmente con el botón "Menu" o "Settings" del control remoto).',
                        'Busca la sección de <strong>configuración de red</strong> y confirma que el dispositivo tiene conexión a internet (por cable o WiFi, según el modelo).',
                        'Ve a la sección de <strong>Portal</strong> o "System Settings" → "Servers".',
                        'Ingresa la URL del portal que te dimos al activar tu línea MAG (formato típico: <code>http://tu-panel.com/portal.php</code> o similar).',
                        'Guarda y reinicia el dispositivo.',
                        'Al encender de nuevo, el portal debería cargar automáticamente tu lista de canales — la línea ya queda asociada a la MAC de tu dispositivo.',
                    ])
                    .'<p>Si necesitas cambiar de dispositivo MAG, contáctanos con la nueva dirección MAC para reasignar tu línea — cada línea MAG queda ligada a un dispositivo específico.</p>',
            ],

            // ── Preguntas frecuentes (pública) ─────────────────────────────────────
            [
                'category' => 'preguntas-frecuentes',
                'title' => '¿Qué es IPTV?',
                'slug' => 'que-es-iptv',
                'excerpt' => 'La diferencia entre IPTV y la televisión por cable o satélite tradicional.',
                'sort_order' => 1,
                'content' =>
                    '<p>IPTV significa <strong>Internet Protocol Television</strong> — televisión transmitida a través de internet, en vez de por cable coaxial, antena o satélite. En la práctica, es lo mismo que ver un video en streaming, pero con canales de TV en vivo.</p>'
                    .'<h2>¿Cómo funciona?</h2>'
                    .'<p>El contenido se transmite en paquetes de datos a través de tu conexión a internet. Tu dispositivo (TV, celular, TV Box, etc.) usa una app reproductora que recibe esa señal y la muestra como un canal normal.</p>'
                    .'<h2>Ventajas frente al cable tradicional</h2>'
                    .$ul([
                        'Puedes ver tus canales en varios dispositivos, no solo en un televisor conectado a un cable físico.',
                        'No depende de infraestructura local (antena, cableado del edificio) — solo de tu conexión a internet.',
                        'Suele incluir más variedad de canales y contenido internacional.',
                    ])
                    .'<p>Lo único que necesitas es una conexión a internet estable y un dispositivo compatible (ver nuestras guías de instalación por dispositivo).</p>',
            ],
            [
                'category' => 'preguntas-frecuentes',
                'title' => '¿Qué es una lista M3U?',
                'slug' => 'que-es-una-lista-m3u',
                'excerpt' => 'Qué contiene tu lista M3U y por qué es la clave de tu servicio IPTV.',
                'sort_order' => 2,
                'content' =>
                    '<p>Una lista M3U es un archivo (o una URL que genera ese archivo) que contiene la información de todos los canales disponibles en tu servicio: su nombre, el enlace de transmisión, la categoría a la que pertenecen y su logo.</p>'
                    .'<p>Cuando cargas tu lista M3U en una app reproductora, esa app lee el archivo y arma automáticamente tu guía de canales — es lo que hace posible ver todo organizado por categorías en vez de tener que buscar cada canal a mano.</p>'
                    .'<h2>Tu URL M3U personal</h2>'
                    .'<p>Cada línea tiene su propia URL M3U única, con tu usuario y contraseña incluidos en el enlace. Por eso es importante <strong>no compartirla</strong> — cualquiera con esa URL puede ver tus canales, y hacerlo desde varios lugares a la vez puede superar el límite de conexiones simultáneas de tu plan.</p>'
                    .'<p>Puedes encontrar tu URL M3U en el correo que recibiste al activarse tu línea, o pidiéndola por soporte si la perdiste.</p>',
            ],
            [
                'category' => 'preguntas-frecuentes',
                'title' => '¿Cuántas conexiones simultáneas puedo usar?',
                'slug' => 'cuantas-conexiones-simultaneas',
                'excerpt' => 'Qué significa el límite de conexiones de tu plan y qué pasa si lo superas.',
                'sort_order' => 3,
                'content' =>
                    '<p>Cada plan tiene un número máximo de <strong>conexiones simultáneas</strong> — es decir, cuántos dispositivos pueden estar reproduciendo tu lista M3U <em>al mismo tiempo</em>.</p>'
                    .'<p>Esto no limita en cuántos dispositivos puedes <em>instalar</em> la lista (puedes tenerla configurada en tu celular, tu TV y tu tablet a la vez), sino cuántos pueden estar viendo contenido en el mismo instante.</p>'
                    .'<h2>¿Qué pasa si supero el límite?</h2>'
                    .'<p>Si intentas abrir una conexión adicional por encima de tu límite, esa conexión nueva se rechaza o se corta la más antigua, dependiendo de la configuración — lo más común es que simplemente no puedas reproducir hasta que cierres una de las sesiones activas.</p>'
                    .'<p>Si necesitas ver contenido en más pantallas al mismo tiempo de forma regular (por ejemplo, varias personas en la misma casa viendo canales distintos), puedes solicitar un plan con más conexiones simultáneas.</p>',
            ],

            // ── XUI ONE: Administración del panel (interna) ────────────────────────
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo agregar un servidor',
                'slug' => 'xui-agregar-servidor',
                'excerpt' => 'Registra un nuevo servidor de streaming en el panel XUI ONE.',
                'sort_order' => 1,
                'content' =>
                    '<p>Antes de poder asignar canales o líneas, XUI ONE necesita saber a qué <strong>servidor de streaming</strong> apuntar — es la máquina real que entrega el video.</p>'
                    .$ol([
                        'Entra al panel XUI ONE con tu cuenta de administrador.',
                        'Ve a la sección de <strong>Servers</strong> (Servidores) en el menú lateral.',
                        'Haz clic en <strong>Add Server</strong> (Agregar servidor).',
                        'Completa el nombre del servidor, su IP/dominio, y las credenciales de acceso (usuario SSH y contraseña, o llave, según cómo esté configurado el servidor).',
                        'Selecciona el tipo de servidor (Load Balancer / Streaming) según corresponda a tu infraestructura.',
                        'Guarda — el panel intentará conectarse para verificar que los datos son correctos.',
                    ])
                    .'<p>Un servidor mal configurado (IP incorrecta o credenciales inválidas) hace que las líneas asociadas a él fallen al reproducir, aunque el resto del panel funcione con normalidad — si un cliente reporta que no carga video, revisar primero el estado del servidor asignado a su línea.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo crear bouquets',
                'slug' => 'xui-crear-bouquets',
                'excerpt' => 'Agrupa canales en bouquets para armar los paquetes que vendes.',
                'sort_order' => 2,
                'content' =>
                    '<p>Un <strong>bouquet</strong> es un grupo de canales/categorías que se asigna después a un paquete de venta — es la unidad con la que armas "qué incluye" cada plan.</p>'
                    .$ol([
                        'Ve a <strong>Bouquets</strong> en el menú lateral del panel.',
                        'Haz clic en <strong>Add Bouquet</strong>.',
                        'Ponle un nombre descriptivo (ej. "Deportes Latino" o "Paquete Básico").',
                        'Selecciona las categorías de canales que quieres incluir en este bouquet.',
                        'Guarda los cambios.',
                    ])
                    .'<p>Un mismo canal puede pertenecer a varios bouquets a la vez — por ejemplo, un canal deportivo puede estar tanto en el bouquet "Deportes" como en un bouquet "Todo Incluido" más grande. Los bouquets luego se asignan a los paquetes (ver "Cómo crear paquetes").</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo crear categorías de canales',
                'slug' => 'xui-crear-categorias',
                'excerpt' => 'Organiza tus canales en categorías para que los bouquets y la guía queden ordenados.',
                'sort_order' => 3,
                'content' =>
                    '<p>Las categorías organizan los canales dentro del panel (ej. "Deportes", "Noticias", "Películas", "Infantil") — son la base sobre la que después se arman los bouquets.</p>'
                    .$ol([
                        'Ve a <strong>Categories</strong> (Categorías) en el menú del panel.',
                        'Elige el tipo de categoría: Live TV (canales en vivo), Movies (VOD) o Series, según lo que vayas a organizar.',
                        'Haz clic en <strong>Add Category</strong>.',
                        'Ponle un nombre claro — es lo que el cliente final ve al navegar su guía de canales.',
                        'Guarda y repite para cada categoría que necesites.',
                    ])
                    .'<p>Recomendación: mantén nombres de categoría cortos y consistentes (ej. siempre "Deportes" y no a veces "Deporte", a veces "Sports") — un cliente que navega la guía se guía por estos nombres.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo crear paquetes',
                'slug' => 'xui-crear-paquetes',
                'excerpt' => 'Arma los planes que vendes asignando bouquets, duración y conexiones.',
                'sort_order' => 4,
                'content' =>
                    '<p>Un <strong>paquete</strong> en XUI ONE es la plantilla que después usas al crear una línea de cliente — define qué bouquets incluye, cuánto dura, y cuántas conexiones simultáneas permite.</p>'
                    .$ol([
                        'Ve a <strong>Packages</strong> (Paquetes) en el panel.',
                        'Haz clic en <strong>Add Package</strong>.',
                        'Ponle un nombre (ej. "1 Mes - 1 Pantalla").',
                        'Selecciona los <strong>bouquets</strong> que incluye este paquete.',
                        'Define la <strong>duración</strong> (en días u horas) y el número de <strong>conexiones simultáneas</strong> permitidas.',
                        'Guarda el paquete.',
                    ])
                    .'<p>Este panel de reventa (4LivePro Latino) toma el <code>ID de paquete de XUI</code> generado acá y lo vincula al paquete de venta correspondiente en Admin &gt; Paquetes — sin ese ID, el sistema no puede activar la línea automáticamente al aprobar un pedido.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo agregar un proveedor de streams',
                'slug' => 'xui-agregar-proveedor',
                'excerpt' => 'Conecta una fuente externa de canales (proveedor) al panel.',
                'sort_order' => 5,
                'content' =>
                    '<p>Si el contenido no se sube manualmente sino que viene de un <strong>proveedor externo</strong> (otro panel que revende su señal), hay que registrar ese proveedor antes de poder importar sus canales.</p>'
                    .$ol([
                        'Ve a <strong>Providers</strong> (Proveedores) en el menú lateral.',
                        'Haz clic en <strong>Add Provider</strong>.',
                        'Selecciona el tipo de conexión (ej. Xtream Codes API, M3U URL, etc.).',
                        'Ingresa las credenciales o la URL que te dio el proveedor.',
                        'Guarda y verifica que el panel logre conectarse (debería mostrar la cantidad de canales disponibles de ese proveedor).',
                    ])
                    .'<p>Una vez agregado el proveedor, sus canales quedan disponibles para importar (ver "Cómo importar canales") y organizarlos en tus propias categorías/bouquets.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo importar canales',
                'slug' => 'xui-importar-canales',
                'excerpt' => 'Trae los canales de un proveedor conectado hacia tus categorías.',
                'sort_order' => 6,
                'content' =>
                    '<p>Una vez que un proveedor está conectado (ver guía anterior), sus canales se pueden importar al panel para organizarlos en tus propias categorías.</p>'
                    .$ol([
                        'Ve a <strong>Import</strong> (Importar) en el menú del panel.',
                        'Selecciona el <strong>proveedor</strong> de origen.',
                        'Elige los canales que quieres importar (puedes filtrar por nombre o categoría del proveedor).',
                        'Asigna cada canal (o el grupo completo) a una <strong>categoría propia</strong> del panel.',
                        'Confirma la importación — según la cantidad de canales, puede tardar unos minutos.',
                    ])
                    .'<p>Después de importar, revisa que los canales reproduzcan correctamente antes de asignarlos a un bouquet activo — a veces un canal del proveedor puede estar caído temporalmente.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo agregar EPG al panel',
                'slug' => 'xui-agregar-epg',
                'excerpt' => 'Conecta una guía de programación (EPG) para que los clientes vean qué está pasando ahora.',
                'sort_order' => 7,
                'content' =>
                    '<p>El EPG (<strong>Electronic Program Guide</strong>, guía electrónica de programación) es lo que le muestra al cliente qué programa está pasando ahora y qué sigue en cada canal — mejora mucho la experiencia de uso.</p>'
                    .$ol([
                        'Ve a <strong>EPG</strong> en el menú lateral del panel.',
                        'Agrega una fuente de EPG (una URL XMLTV pública, o una que te dé tu proveedor de contenido).',
                        'Espera a que el panel sincronice esa fuente por primera vez.',
                        'Ve a cada canal (o en bloque, según lo permita tu versión del panel) y asígnale el <strong>EPG ID</strong> correcto que corresponda de esa fuente.',
                        'Verifica en la guía de un cliente de prueba que la información de "ahora" y "después" aparece correctamente.',
                    ])
                    .'<p>El EPG se sincroniza automáticamente cada cierto tiempo una vez configurado — no hace falta repetir el proceso salvo que cambies de fuente.</p>',
            ],
            [
                'category' => 'xui-one-administracion',
                'title' => 'Cómo crear access codes',
                'slug' => 'xui-crear-access-codes',
                'excerpt' => 'Genera y administra los códigos de acceso a la API del panel.',
                'sort_order' => 8,
                'content' =>
                    '<p>Un <strong>access code</strong> es la credencial que usa una aplicación externa (como este panel de reventa) para comunicarse con la API de XUI ONE — crear/consultar/renovar líneas, sin tener que iniciar sesión manualmente en el panel.</p>'
                    .$ol([
                        'Ve a <strong>Access Codes</strong> en el menú de administración del panel.',
                        'Haz clic en <strong>Add Access Code</strong> (o similar, según la versión).',
                        'Define permisos/alcance si el panel lo permite (por ejemplo, limitar a ciertas acciones).',
                        'Guarda y copia el <strong>código generado</strong> — junto con tu API key, es lo que se configura en Admin &gt; XUI ONE de este sistema.',
                    ])
                    .'<p>Trata el access code como una contraseña: cualquiera que lo tenga puede crear/eliminar líneas a través de la API. Si sospechas que se filtró, revócalo y genera uno nuevo, actualizando también la configuración en este panel.</p>',
            ],

            // ── XUI ONE: Líneas y revendedores (interna) ───────────────────────────
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo crear una línea',
                'slug' => 'xui-crear-linea',
                'excerpt' => 'Genera manualmente una línea M3U para un cliente desde el panel.',
                'sort_order' => 1,
                'content' =>
                    '<p>Aunque este sistema crea líneas automáticamente al aprobar un pedido, a veces hace falta crear una a mano directamente en XUI ONE (pruebas, casos especiales, clientes fuera del flujo normal).</p>'
                    .$ol([
                        'Ve a <strong>Lines</strong> (Líneas) en el menú del panel.',
                        'Haz clic en <strong>Add Line</strong>.',
                        'Elige el <strong>paquete</strong> a aplicar (define bouquets, duración y conexiones).',
                        'El panel genera automáticamente un usuario y contraseña (o puedes definirlos manualmente, según la versión).',
                        'Guarda — el panel te mostrará la URL M3U final para entregar al cliente.',
                    ])
                    .'<p>Si la línea la creaste a mano fuera del flujo normal de este sistema, recuerda que no va a aparecer asociada a ningún pedido acá — solo existirá dentro de XUI ONE.</p>',
            ],
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo crear una línea MAG',
                'slug' => 'xui-crear-linea-mag',
                'excerpt' => 'Registra una línea asociada a la dirección MAC de un dispositivo MAG.',
                'sort_order' => 2,
                'content' =>
                    '<p>Las líneas para dispositivos MAG funcionan distinto a las M3U: en vez de usuario/contraseña, se identifican por la <strong>dirección MAC</strong> del dispositivo físico del cliente.</p>'
                    .$ol([
                        'Ve a <strong>Lines</strong> → pestaña de líneas <strong>MAG</strong> (o "Add MAG Line", según la versión del panel).',
                        'Pide al cliente la dirección MAC de su dispositivo (suele estar en una etiqueta física o en el menú de sistema del equipo).',
                        'Ingresa esa MAC en el panel.',
                        'Selecciona el <strong>paquete</strong> a aplicar.',
                        'Guarda — la línea queda ligada a ese dispositivo específico.',
                    ])
                    .'<p>Si el cliente cambia de dispositivo MAG, hay que actualizar la MAC registrada en la línea — de lo contrario el nuevo dispositivo no podrá reproducir aunque la línea siga activa.</p>',
            ],
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo extender o renovar una línea',
                'slug' => 'xui-extender-linea',
                'excerpt' => 'Suma tiempo a una línea existente sin perder la configuración actual.',
                'sort_order' => 3,
                'content' =>
                    '<p>Extender una línea suma duración a partir de su fecha de vencimiento actual, sin necesidad de crear una línea nueva ni perder las credenciales que el cliente ya tiene guardadas en sus apps.</p>'
                    .$ol([
                        'Ve a <strong>Lines</strong> y busca la línea del cliente (por usuario XUI o por su correo, si el panel lo permite buscar así).',
                        'Abre la línea y busca la opción <strong>Extend</strong> / "Apply Package".',
                        'Selecciona el paquete a aplicar — si es el mismo plan que ya tenía, simplemente suma la duración de ese paquete a la fecha de vencimiento actual.',
                        'Confirma — el panel recalcula automáticamente la nueva fecha de vencimiento.',
                    ])
                    .'<p><strong>Cuidado con aplicarlo dos veces por error</strong> — cada aplicación suma duración completa, así que confirmar dos veces seguidas extendería el doble de lo esperado. Este panel de reventa ya bloquea el botón de "Renovar" tras el primer clic para evitar justamente ese error.</p>',
            ],
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo descargar una playlist',
                'slug' => 'xui-descargar-playlist',
                'excerpt' => 'Obtén el archivo o la URL M3U de una línea para entregarla o revisarla.',
                'sort_order' => 4,
                'content' =>
                    '<p>A veces hace falta obtener directamente el archivo/URL M3U de una línea — para reenviarlo a un cliente, o para probarlo tú mismo antes de entregarlo.</p>'
                    .$ol([
                        'Ve a <strong>Lines</strong> y abre la línea que necesitas.',
                        'Busca la opción <strong>Get M3U</strong> / "Download Playlist" (el nombre exacto varía según la versión del panel).',
                        'El panel te dará la URL M3U completa, lista para pegar en cualquier app reproductora compatible.',
                        'También suele ofrecer formatos alternativos (M3U Plus, o el enlace directo tipo Xtream Codes: servidor + usuario + contraseña) — útil según qué formato acepte la app del cliente.',
                    ])
                    .'<p>Recuerda que esta URL incluye la contraseña de la línea en texto plano — solo compártela por canales seguros (correo directo al cliente, no publicada en ningún lugar abierto).</p>',
            ],
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo obtener tu API key',
                'slug' => 'xui-obtener-api-key',
                'excerpt' => 'Encuentra y genera la API key que conecta este panel de reventa con XUI ONE.',
                'sort_order' => 5,
                'content' =>
                    '<p>La API key es la credencial que este sistema (el panel de reventa) usa para comunicarse con tu instalación de XUI ONE: crear líneas automáticamente al aprobar un pedido, consultar paquetes disponibles, renovar líneas, etc.</p>'
                    .$ol([
                        'Entra a tu panel XUI ONE como administrador.',
                        'Ve a la sección de <strong>API</strong> / "Reseller API" en la configuración del panel.',
                        'Genera (o copia, si ya existe) tu <strong>API key</strong>.',
                        'Copia también el <strong>access code</strong> asociado (ver "Cómo crear access codes").',
                        'Ve a este panel de reventa: <strong>Admin &gt; XUI ONE</strong>, y pega ahí la URL de tu panel, el access code y la API key.',
                        'Guarda — usa el botón "Probar conexión" (si está disponible) para confirmar que los datos son correctos antes de operar con clientes reales.',
                    ])
                    .'<p>Si cambias la API key en XUI ONE por seguridad, recuerda actualizarla también acá — si no, las activaciones automáticas de pedidos van a empezar a fallar.</p>',
            ],
            [
                'category' => 'xui-one-lineas-revendedores',
                'title' => 'Cómo crear un sub-revendedor',
                'slug' => 'xui-crear-sub-revendedor',
                'excerpt' => 'Da acceso limitado a otra persona para que revenda usando tus créditos.',
                'sort_order' => 6,
                'content' =>
                    '<p>Un sub-revendedor es una cuenta con acceso limitado al panel, pensada para alguien a quien le das permiso de vender líneas usando un crédito/cupo que tú le asignas — sin darle acceso total a tu administración.</p>'
                    .$ol([
                        'Ve a <strong>Resellers</strong> (Revendedores) en el menú del panel.',
                        'Haz clic en <strong>Add Reseller</strong>.',
                        'Completa los datos de la cuenta (usuario, contraseña, datos de contacto).',
                        'Asigna un <strong>crédito inicial</strong> — es lo que le permite crear líneas hasta agotarlo.',
                        'Define qué paquetes puede vender y, si el panel lo permite, un <strong>DNS personalizado</strong> para que sus clientes vean su propia marca en vez de la tuya.',
                        'Guarda — el sub-revendedor ya puede iniciar sesión con su propio acceso limitado.',
                    ])
                    .'<p>El crédito se descuenta automáticamente cada vez que el sub-revendedor crea o extiende una línea — vale la pena revisar periódicamente su saldo y actividad, sobre todo al principio de la relación comercial.</p>',
            ],
        ];

        foreach ($articles as $article) {
            DB::table('help_articles')->updateOrInsert(
                ['slug' => $article['slug']],
                [
                    'help_category_id' => $categoryIds[$article['category']],
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
            'instalar-android-fire-tv-stick', 'instalar-smart-tv-samsung-lg',
            'instalar-iphone-apple-tv', 'instalar-dispositivos-mag',
            'que-es-iptv', 'que-es-una-lista-m3u', 'cuantas-conexiones-simultaneas',
            'xui-agregar-servidor', 'xui-crear-bouquets', 'xui-crear-categorias',
            'xui-crear-paquetes', 'xui-agregar-proveedor', 'xui-importar-canales',
            'xui-agregar-epg', 'xui-crear-access-codes',
            'xui-crear-linea', 'xui-crear-linea-mag', 'xui-extender-linea',
            'xui-descargar-playlist', 'xui-obtener-api-key', 'xui-crear-sub-revendedor',
        ])->delete();

        DB::table('help_categories')->whereIn('slug', [
            'instalacion', 'preguntas-frecuentes',
            'xui-one-administracion', 'xui-one-lineas-revendedores',
        ])->delete();
    }
};
