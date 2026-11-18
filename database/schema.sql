-- ==========================================================================
-- Click 2 Action — Base de datos del blog y del formulario de contacto
-- Importar en phpMyAdmin (pestaña "Importar") o con:
--   mysql -u USUARIO -p NOMBRE_BD < database/schema.sql
-- Luego abre /admin/setup.php para crear el primer usuario administrador.
-- ==========================================================================

SET NAMES utf8mb4;
SET time_zone = '-05:00';

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(90) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(200) NOT NULL,
    slug             VARCHAR(220) NOT NULL UNIQUE,
    excerpt          VARCHAR(300) NULL,
    content          MEDIUMTEXT NOT NULL,
    cover_image      VARCHAR(255) NULL,
    cover_alt        VARCHAR(200) NULL,
    category_id      INT UNSIGNED NULL,
    author_id        INT UNSIGNED NULL,
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    meta_title       VARCHAR(70) NULL,
    meta_description VARCHAR(170) NULL,
    published_at     DATETIME NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_date (status, published_at),
    CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_posts_author   FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    company    VARCHAR(120) NULL,
    email      VARCHAR(160) NOT NULL,
    phone      VARCHAR(20)  NOT NULL,
    reason     VARCHAR(120) NOT NULL,
    message    TEXT NOT NULL,
    ip         VARCHAR(45)  NULL,
    user_agent VARCHAR(255) NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip           VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clientes y cifras del sitio (se administran en Panel > Clientes).
-- La primera vez que abras esa sección se copian los clientes iniciales.
CREATE TABLE IF NOT EXISTS clients (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    logo       VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fotos del equipo en Nosotros (Panel > Equipo)
CREATE TABLE IF NOT EXISTS team (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    role       VARCHAR(80) NULL,
    photo      VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    name  VARCHAR(60) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------------
-- Categorías iniciales
-- --------------------------------------------------------------------------
INSERT IGNORE INTO categories (name, slug) VALUES
    ('Desarrollo web', 'desarrollo-web'),
    ('Marketing digital', 'marketing-digital'),
    ('SEO', 'seo'),
    ('Branding', 'branding'),
    ('Redes sociales', 'redes-sociales');

-- --------------------------------------------------------------------------
-- Artículos de ejemplo (puedes editarlos o borrarlos desde /admin)
-- --------------------------------------------------------------------------
INSERT IGNORE INTO posts (title, slug, excerpt, content, category_id, status, meta_title, meta_description, published_at) VALUES
(
    'Los 5 infaltables de una página web profesional',
    'infaltables-pagina-web-profesional',
    'Antes de invertir en pauta, revisa que tu sitio cumpla estos cinco puntos. Si falla uno, estás pagando por visitas que no se convierten.',
    '<p>Una página web no es un folleto digital: es el lugar donde una visita decide si te escribe o se va con la competencia. Después de más de ocho años diseñando sitios para empresas en Colombia, estos son los cinco puntos que revisamos antes de publicar cualquier proyecto.</p>
<h2>1. Contenido relevante</h2>
<p>Tu cliente llega con una pregunta. El texto de tu página debe responderla en los primeros segundos: qué haces, para quién y por qué elegirte. Evita los párrafos genéricos como «somos líderes en el mercado» y habla de lo que el cliente gana.</p>
<h2>2. Navegación intuitiva</h2>
<p>Cada página debe llevar a una acción clara: escribir por WhatsApp, llamar, pedir una cotización o comprar. Si el visitante tiene que pensar dónde hacer clic, ya perdiste parte de la venta.</p>
<h2>3. SEO desde el primer día</h2>
<p>El posicionamiento no se agrega al final. La estructura de títulos, las URL limpias, la velocidad de carga y las etiquetas de cada página se definen mientras se diseña. Así Google entiende tu sitio desde que se publica.</p>
<blockquote>Una web lenta o confusa encarece cada peso que inviertes en publicidad.</blockquote>
<h2>4. Diseño responsive</h2>
<p>En Colombia la mayoría de las visitas llegan desde el celular. El diseño debe pensarse primero para pantallas pequeñas: botones fáciles de tocar, textos legibles y formularios cortos.</p>
<h2>5. Seguridad</h2>
<p>Certificado SSL, formularios protegidos contra spam, copias de seguridad y actualizaciones al día. Un sitio inseguro no solo pone en riesgo tus datos: Google y los navegadores lo marcan y alejan a tus clientes.</p>
<h2>¿Tu sitio cumple los cinco?</h2>
<p>Si tienes dudas, escríbenos por WhatsApp y te ayudamos a revisarlo. Muchas veces pequeños ajustes generan más contactos que una campaña nueva.</p>',
    (SELECT id FROM categories WHERE slug = 'desarrollo-web'),
    'published',
    'Los 5 infaltables de una página web profesional',
    'Contenido, navegación, SEO, diseño responsive y seguridad: los cinco puntos que toda página web profesional debe cumplir antes de invertir en pauta.',
    NOW() - INTERVAL 20 DAY
),
(
    '¿Tu pauta es un gasto o una inversión?',
    'pauta-gasto-o-inversion',
    'Cómo saber si tus campañas en Google Ads y Meta están generando clientes o solo clics. Tres métricas que debes revisar cada mes.',
    '<p>Muchas empresas sienten que la publicidad digital es una cuenta que se paga cada mes sin saber qué devuelve. La diferencia entre gasto e inversión está en lo que mides.</p>
<h2>Mide conversaciones, no solo clics</h2>
<p>Un clic barato no sirve si nadie escribe. Configura la medición de conversiones: mensajes de WhatsApp, formularios enviados y llamadas. Con Google Analytics y las herramientas de Meta puedes saber qué anuncio trajo cada contacto.</p>
<h2>Las tres métricas que revisamos cada mes</h2>
<ul>
<li><strong>Costo por conversación:</strong> cuánto pagas por cada persona que te escribe.</li>
<li><strong>Tasa de cierre:</strong> cuántas de esas conversaciones se convierten en venta.</li>
<li><strong>Retorno de la inversión:</strong> cuánto vendiste por cada peso invertido en pauta.</li>
</ul>
<h2>El anuncio es la mitad del trabajo</h2>
<p>Si la página a la que llega el anuncio es lenta o confusa, la campaña fracasa aunque el anuncio sea excelente. Por eso trabajamos la pauta y la página web como un solo sistema.</p>
<h2>Ajusta cada semana</h2>
<p>Las campañas no se configuran una vez y se olvidan. Planeamos, ejecutamos, analizamos y seguimos: pausamos lo que no funciona y le damos más presupuesto a lo que sí trae clientes.</p>',
    (SELECT id FROM categories WHERE slug = 'marketing-digital'),
    'published',
    '¿Tu pauta en Google y Meta es gasto o inversión?',
    'Aprende a medir tus campañas de Google Ads y Meta con tres métricas clave para saber si tu publicidad digital genera clientes o solo clics.',
    NOW() - INTERVAL 12 DAY
),
(
    'SEO local: cómo aparecer en Google cuando buscan tu negocio en Medellín',
    'seo-local-medellin',
    'Perfil de empresa en Google, reseñas y contenido local: lo básico para que tus clientes cercanos te encuentren primero.',
    '<p>Cuando alguien busca «agencia de marketing en Medellín» o «odontólogo en Envigado», Google muestra primero los negocios que mejor entiende y en los que más confía. Eso es el SEO local.</p>
<h2>1. Completa tu Perfil de Empresa en Google</h2>
<p>Dirección, horario, teléfono, categoría correcta, fotos reales y un enlace a tu página web. Es gratuito y es la base de tu presencia en Google Maps.</p>
<h2>2. Pide reseñas a tus clientes</h2>
<p>Las reseñas influyen en tu posición y en la decisión del cliente. Pídelas después de cada servicio y respóndelas todas, también las negativas.</p>
<h2>3. Habla de tu ciudad en tu sitio</h2>
<p>Menciona las zonas donde trabajas en tus páginas de servicio y crea contenido útil para tu región. Google necesita señales claras de dónde atiendes.</p>
<h2>4. Datos iguales en todas partes</h2>
<p>Tu nombre, dirección y teléfono deben aparecer exactamente igual en tu web, redes sociales y directorios. Las diferencias confunden a los buscadores.</p>
<h2>5. Una web rápida y adaptada al celular</h2>
<p>La mayoría de las búsquedas locales se hacen desde el celular, muchas veces en movimiento. Si tu sitio tarda en cargar, el cliente llama al siguiente resultado.</p>',
    (SELECT id FROM categories WHERE slug = 'seo'),
    'published',
    'SEO local en Medellín: cómo aparecer primero en Google',
    'Guía práctica de SEO local para negocios en Medellín y Envigado: perfil de empresa en Google, reseñas, contenido local y velocidad web.',
    NOW() - INTERVAL 6 DAY
),
(
    'Si tapamos tu logo, ¿igual se reconoce tu marca?',
    'marca-reconocible-sin-logo',
    'Una marca fuerte se reconoce por sus colores, su tono y su forma de comunicar, no solo por el logotipo. Haz esta prueba.',
    '<p>Haz la prueba: toma tres publicaciones de tus redes y tapa el logo. ¿Alguien podría saber que son tuyas? Si la respuesta es no, tu marca todavía depende demasiado del logotipo.</p>
<h2>La identidad es un sistema</h2>
<p>Colores, tipografías, estilo de fotografía, forma de escribir y hasta el tipo de humor. Cuando todos esos elementos se repiten con coherencia, tu marca se vuelve reconocible en cualquier lugar.</p>
<h2>Señales de que necesitas un rebranding</h2>
<ul>
<li>Tus piezas se ven diferentes según quién las diseñe.</li>
<li>Tu logo no se lee bien en tamaño pequeño, como el perfil de Instagram.</li>
<li>Tu imagen ya no representa lo que tu empresa es hoy.</li>
</ul>
<h2>Tu marca tiene una esencia</h2>
<p>Nuestro trabajo es encontrarla y convertirla en una identidad que funcione en tu web, tus redes, tu publicidad y tus puntos de venta. Si sientes que tu marca necesita un nuevo look, hablemos.</p>',
    (SELECT id FROM categories WHERE slug = 'branding'),
    'published',
    'Si tapamos tu logo, ¿se reconoce tu marca?',
    'Una marca fuerte se reconoce sin su logo. Descubre si tu identidad es coherente y cuándo es momento de hacer un rebranding.',
    NOW() - INTERVAL 2 DAY
);
