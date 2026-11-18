<?php
/**
 * Click 2 Action — Configuración general del sitio.
 * PLANTILLA: cópiala como config/config.php y llena tus datos.
 * config.php no se sube a git (ver .gitignore).
 */

// ---------------------------------------------------------------
// Entorno
// ---------------------------------------------------------------
define('APP_ENV', 'production');          // 'development' muestra errores

// URL pública del sitio, sin barra final (se usa en canonical, sitemap y Open Graph)
define('SITE_URL', 'https://click2action.com.co');

// Si la web vive en una subcarpeta (ej. http://localhost/click2action) pon '/click2action'.
// En la raíz del dominio déjalo vacío.
define('BASE_PATH', '');

// ---------------------------------------------------------------
// Base de datos MySQL (blog + mensajes del formulario)
// ---------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_de_la_base');
define('DB_USER', 'usuario_de_la_base');
define('DB_PASS', 'contraseña_de_la_base');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------
// Datos de la agencia
// ---------------------------------------------------------------
define('SITE_NAME', 'Click 2 Action');
define('SITE_LEGAL_NAME', 'Click 2 Action Comunicaciones Estratégicas');
define('SITE_TAGLINE', 'Agencia de marketing digital y diseño web en Medellín');
define('SITE_FOUNDED', 2018);              // Año de fundación: calcula los "+8 años" automáticamente
define('TEAM_SIZE', 24);                  // Especialistas en el equipo
define('ACTIVE_BRANDS', 50);              // Marcas activas con las que trabajamos

// WhatsApp: solo dígitos, con indicativo de país (57 = Colombia)
define('WHATSAPP_NUMBER', '573127772476');
define('WHATSAPP_DEFAULT_MSG', 'Hola Click 2 Action, quiero hablar sobre un proyecto para mi marca.');

define('PHONE_DISPLAY', '+57 312 777 2476');
define('PHONE_LINK', '+573127772476');
define('CONTACT_EMAIL', 'correo@click2action.com.co');

// Correo que recibe los mensajes del formulario
define('FORM_TO_EMAIL', 'correo@click2action.com.co');
// Remitente del correo (debe pertenecer al dominio del hosting para no caer en spam)
define('FORM_FROM_EMAIL', 'no-reply@click2action.com.co');

define('ADDRESS_STREET', 'Carrera 43A # 25 B sur 108, Edificio Hojas Verdes');
define('ADDRESS_CITY', 'Envigado');
define('ADDRESS_REGION', 'Antioquia');
define('ADDRESS_COUNTRY', 'CO');
define('GEO_LAT', '6.1759');
define('GEO_LNG', '-75.5917');

define('SOCIAL', [
    'instagram' => 'https://www.instagram.com/click2action/',
    'facebook'  => 'https://www.facebook.com/Click2Action',
    'linkedin'  => 'https://www.linkedin.com/company/click2action/',
    'tiktok'    => 'https://www.tiktok.com/@click2action',
]);

// ---------------------------------------------------------------
// Blog
// ---------------------------------------------------------------
define('POSTS_PER_PAGE', 9);
define('UPLOAD_DIR', __DIR__ . '/../uploads/blog/');
define('UPLOAD_URL', '/uploads/blog/');
define('UPLOAD_MAX_BYTES', 3 * 1024 * 1024); // 3 MB

// Analítica (opcional): ID de Google Analytics 4, ej. 'G-XXXXXXX'. Vacío = desactivado.
define('GA4_ID', '');

// Hotjar (opcional): Site ID numérico, ej. '1234567'. Vacío = desactivado.
define('HOTJAR_ID', '');
