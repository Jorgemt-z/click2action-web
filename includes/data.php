<?php
/**
 * Contenido estructurado del sitio (servicios, clientes, casos, equipo).
 * Editar aquí actualiza todas las páginas que lo usan.
 */

$SERVICES = [
    [
        'slug'  => 'desarrollo-web',
        'name'  => 'Desarrollo web',
        'short' => 'Sitios rápidos, claros y listos para vender, diseñados para posicionar en Google desde el primer día.',
        'text'  => 'Diseñamos y programamos páginas web a la medida de tu negocio: desde una landing page para una campaña hasta una tienda en línea con pasarela de pagos. Cada sitio sale optimizado para SEO, adaptado a celulares y con la seguridad al día.',
        'items' => ['Sitios web corporativos', 'Landing pages', 'Tiendas en línea (ecommerce)', 'Pasarelas de pago', 'Integraciones (CRM, WhatsApp, analítica)', 'Actualización y mantenimiento'],
        'wa'    => 'Hola Click 2 Action, quiero cotizar una página web para mi empresa.',
    ],
    [
        'slug'  => 'marketing-digital',
        'name'  => 'Redes sociales',
        'short' => 'Pauta, SEO y redes sociales conectadas a una sola meta: más conversaciones con clientes reales.',
        'text'  => 'Gestionamos tu marketing digital de forma integral: campañas en Google Ads, Meta (Facebook e Instagram), posicionamiento SEO, email marketing, SMS masivos y administración de CRM. Medimos cada peso invertido con Analytics para que la pauta sea inversión, no gasto.',
        'items' => ['Google Ads', 'Meta Ads (Facebook e Instagram)', 'Posicionamiento SEO y contenido', 'Gestión de redes sociales', 'Email marketing y SMS', 'Configuración y gestión de CRM'],
        'wa'    => 'Hola Click 2 Action, quiero mejorar los resultados de mi marketing digital.',
    ],
    [
        'slug'  => 'branding',
        'name'  => 'Branding',
        'short' => 'Una identidad que se reconoce incluso si tapas el logo.',
        'text'  => 'Creamos y renovamos marcas: logotipo, identidad corporativa y todo el material gráfico que tu negocio necesita para verse coherente en cada punto de contacto. Si tu marca necesita un nuevo aire, te acompañamos en el rebranding.',
        'items' => ['Diseño de logotipo', 'Identidad corporativa', 'Rebranding', 'Material gráfico y publicitario'],
        'wa'    => 'Hola Click 2 Action, quiero crear o renovar la identidad de mi marca.',
    ],
    [
        'slug'  => 'audiovisual',
        'name'  => 'Comunicación audiovisual',
        'short' => 'Foto, video y voz que cuentan tu historia con la calidad que tu marca merece.',
        'text'  => 'Producimos sesiones fotográficas, videos corporativos y piezas para redes, con edición, montaje y servicios de voz en off y voz comercial. Contenido pensado para el formato y la plataforma donde se va a ver.',
        'items' => ['Sesiones fotográficas', 'Video corporativo', 'Edición y montaje', 'Voz en off y voz comercial'],
        'wa'    => 'Hola Click 2 Action, necesito producción de foto o video para mi marca.',
    ],
    [
        'slug'  => 'bases-de-datos',
        'name'  => 'Gestión de bases de datos',
        'short' => 'Convertimos los contactos que ya tienes en ventas, campañas y clientes que vuelven a comprar.',
        'text'  => 'Tu base de datos vale más de lo que crees. La depuramos, organizamos y segmentamos para que sepas quién te compra, qué le interesa y cuándo volver a contactarlo. Con esa información activamos campañas alineadas con tu estrategia, para que cada contacto se convierta en una oportunidad de venta y tu marca siga creciendo.',
        'items' => ['Depuración y actualización de datos', 'Organización y segmentación de clientes', 'Análisis y reportes para tomar decisiones', 'Campañas de email, SMS y WhatsApp', 'Integración con tu CRM'],
        'wa'    => 'Hola Click 2 Action, quisiera que me ayuden a gestionar mis bases de datos.',
    ],
];

// Clientes iniciales. Desde que se abre Panel > Clientes por primera vez, la lista
// se administra allí (base de datos) y esta solo queda como respaldo sin conexión.
// Si falta el logo, se muestra el nombre del cliente como texto.
$CLIENTS_DEFAULT = [
    ['name' => 'Surtipulpas',                 'slug' => 'surtipulpas'],
    ['name' => 'Serenn Medicina Integrativa', 'slug' => 'serenn'],
    ['name' => 'Autoplus',                    'slug' => 'autoplus'],
    ['name' => 'Ditransa',                    'slug' => 'ditransa'],
    ['name' => 'DeliverD by Ditransa',        'slug' => 'delivery-d'],
    ['name' => 'Home D by Ditransa',          'slug' => 'homed'],
    ['name' => 'Refugio Natural',             'slug' => 'refugio-natural'],
    ['name' => 'Cipa',                        'slug' => 'cipa'],
    ['name' => 'Orallaser',                   'slug' => 'orallaser'],
    ['name' => 'Consciente Security',         'slug' => 'appconsciente'],
    ['name' => 'Jeu',                         'slug' => 'jeu'],
    ['name' => 'O&I Inspecciones',            'slug' => 'oyi'],
    ['name' => 'S&E y Cía',                   'slug' => 'sye'],
    ['name' => 'Ingeniería y Vivienda',       'slug' => 'ingenieria-y-vivienda'],
    ['name' => 'Quirófanos 25 Sur',           'slug' => 'quirofanos'],
    ['name' => 'Trasegar S.A.S.',             'slug' => 'trasegar'],
    ['name' => 'Kuinn Grupo Inmobiliario',    'slug' => 'kuinn'],
    ['name' => 'Produce Experience',          'slug' => 'produce-experience'],
    ['name' => 'Matiz',                       'slug' => 'matiz'],
    ['name' => 'Movo',                        'slug' => 'movo'],
    ['name' => '360 Legal',                   'slug' => '360-legal'],
    ['name' => 'Cerro Verde',                 'slug' => 'cerro-verde'],
    ['name' => 'Salamanca',                   'slug' => 'salamanca'],
    ['name' => 'Conexión Legal',              'slug' => 'conexion-legal'],
    ['name' => 'Cargacontrol',                'slug' => 'carga-control'],
    ['name' => 'Ecologística',                'slug' => 'ecologistica'],
    ['name' => 'Importecnical',               'slug' => 'importecnical'],
    ['name' => 'Arte Fino',                   'slug' => 'arte-fino'],
    ['name' => 'Patarroyo',                   'slug' => 'patarroyo'],
    ['name' => 'Pizza Americana',             'slug' => 'pizza-americana'],
];

$CASES = [
    [
        'client'  => 'Surtipulpas',
        'title'   => 'Estrategia de marketing integral para Surtipulpas',
        'summary' => 'Mejoramos el posicionamiento de la marca con una estrategia que unió comunicación, contenido y marketing digital bajo un mismo plan.',
        'tags'    => ['Estrategia integral', 'Redes sociales', 'Contenido'],
    ],
    [
        'client'  => 'Serenn Medicina Integrativa',
        'title'   => 'Presencia digital para Serenn',
        'summary' => 'Establecimos la presencia de Serenn en el mundo digital: identidad en redes, sitio web y comunicación coherente con su enfoque de medicina integrativa.',
        'tags'    => ['Desarrollo web', 'Branding', 'Redes sociales'],
    ],
    [
        'client'  => 'Autoplus',
        'title'   => 'Pauta digital efectiva para Autoplus',
        'summary' => 'Diseñamos y gestionamos campañas pagas orientadas a generar contactos de clientes interesados, con seguimiento constante de resultados.',
        'tags'    => ['Meta Ads', 'Google Ads', 'Analítica'],
    ],
    [
        'client'  => 'Pizza Americana',
        'title'   => 'Redes sociales con más vida para Pizza Americana',
        'summary' => 'Dinamizamos sus redes con variedad de contenidos y formatos que aumentaron la interacción con su comunidad.',
        'tags'    => ['Gestión de redes', 'Contenido audiovisual'],
    ],
];

$PROCESS = [
    ['name' => 'Planeamos',  'text' => 'Entendemos tu negocio, tu cliente y tu competencia antes de proponer una sola pieza.'],
    ['name' => 'Diseñamos',  'text' => 'Convertimos la estrategia en marca, sitio web, contenidos y campañas.'],
    ['name' => 'Ejecutamos', 'text' => 'Publicamos, pautamos y lanzamos con un equipo dedicado a tu cuenta.'],
    ['name' => 'Analizamos', 'text' => 'Medimos visitas, conversaciones y ventas para saber qué funciona.'],
    ['name' => 'Seguimos',   'text' => 'Ajustamos y optimizamos mes a mes. No te dejamos solo después del lanzamiento.'],
];

/** Archivo de logo incluido con el sitio (assets/img/clientes/<slug>.*), relativo a la raíz, o null. */
function client_logo_file(string $slug): ?string
{
    foreach (['svg', 'webp', 'png', 'jpg'] as $ext) {
        $rel = 'assets/img/clientes/' . $slug . '.' . $ext;
        if (is_file(__DIR__ . '/../' . $rel)) {
            return $rel;
        }
    }
    return null;
}

/** URL pública (con versión anticaché) de un archivo relativo a la raíz, o null si no existe. */
function public_file_url(?string $rel): ?string
{
    if (!$rel || !is_file(__DIR__ . '/../' . $rel)) {
        return null;
    }
    return url($rel) . '?v=' . filemtime(__DIR__ . '/../' . $rel);
}

/** Ajuste del sitio guardado desde el panel (tabla settings), o $default. */
function site_setting(string $key, ?string $default = null): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        if ($pdo = db()) {
            try {
                foreach ($pdo->query('SELECT name, value FROM settings') as $row) {
                    $cache[$row['name']] = $row['value'];
                }
            } catch (PDOException) {
                // La tabla aún no existe: se usan los valores de config.php
            }
        }
    }
    return $cache[$key] ?? $default;
}

function team_size(): int
{
    return (int) site_setting('team_size', (string) TEAM_SIZE);
}

function active_brands(): int
{
    return (int) site_setting('active_brands', (string) ACTIVE_BRANDS);
}

/** Clientes visibles en el sitio: los del panel si ya se configuraron, si no la lista inicial. */
function site_clients(array $fallback): array
{
    if (site_setting('clients_seeded') && ($pdo = db())) {
        try {
            $rows = $pdo->query('SELECT name, logo FROM clients WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
            return array_map(fn($r) => ['name' => $r['name'], 'logo' => public_file_url($r['logo'])], $rows);
        } catch (PDOException) {
            // Sigue con la lista inicial
        }
    }
    return array_map(fn($c) => ['name' => $c['name'], 'logo' => public_file_url(client_logo_file($c['slug']))], $fallback);
}

$CLIENTS = site_clients($CLIENTS_DEFAULT);

/** Integrantes visibles en Nosotros (Panel > Equipo). Solo los que tienen foto. */
function site_team(): array
{
    if (!($pdo = db())) {
        return [];
    }
    try {
        $rows = $pdo->query('SELECT * FROM team WHERE is_active = 1 AND photo IS NOT NULL ORDER BY sort_order, id')->fetchAll();
    } catch (PDOException) {
        return []; // La tabla se crea al abrir Panel > Equipo
    }
    $team = [];
    foreach ($rows as $r) {
        if ($photo = public_file_url($r['photo'])) {
            $team[] = ['name' => $r['name'], 'role' => $r['role'] ?? '', 'photo' => $photo];
        }
    }
    return $team;
}
