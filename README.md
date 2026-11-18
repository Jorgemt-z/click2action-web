# Click 2 Action — Sitio web corporativo

Sitio web multipágina con panel de administración para **Click 2 Action**, agencia de marketing digital y diseño web de Medellín (Colombia). Reemplazó la web anterior en WordPress y está en producción.

🔗 **En vivo:** [click2action.com.co](https://click2action.com.co)

Hecho con **PHP 8, MySQL, HTML, CSS y JavaScript**, sin frameworks ni CMS.

---

## Qué incluye

**Sitio público**
- Seis secciones: Inicio, Nosotros, Servicios, Clientes, Blog y Contacto, más la política de tratamiento de datos (Ley 1581).
- Un simulador de chat de WhatsApp en el inicio: el visitante elige un tema y se abre WhatsApp con el mensaje ya escrito.
- Un carrusel de logos de clientes y contadores dinámicos (años de experiencia, marcas activas, especialistas).
- Un blog con categorías, búsqueda, paginación y URLs limpias (`/blog/mi-articulo`).
- Un formulario de contacto que guarda cada mensaje en la base de datos y además lo envía por correo.
- Diseño responsive, carga diferida de imágenes y animaciones que respetan `prefers-reduced-motion`.

**Panel de administración (`/admin`)**
- Acceso con contraseña protegida con `password_hash`, protección CSRF y bloqueo temporal tras varios intentos fallidos.
- Entradas del blog: un editor con imagen de portada, título y descripción SEO, URL editable y vista previa de cómo se verá en Google.
- Clientes: crear, editar, ocultar, ordenar y eliminar marcas con su logo, y cambiar las cifras que se muestran en el sitio.
- Equipo: fotos e integrantes que aparecen en *Nosotros*.
- Bandeja con los mensajes que llegan por el formulario.
- Las subidas de archivos se validan por tipo MIME real (`finfo`) y por tamaño, y las carpetas de subida no ejecutan PHP.

**SEO y analítica**
- Cada página tiene su título, descripción, canonical, Open Graph y Twitter Card.
- Datos estructurados JSON-LD: Organization, ProfessionalService, WebSite, BreadcrumbList, Service, Blog y BlogPosting.
- Sitemap dinámico (`/sitemap.xml`), `robots.txt` y redirecciones 301 desde las URLs de la web anterior.
- Google Analytics 4 con eventos personalizados (`contact_whatsapp`, `generate_lead`) y Hotjar opcional.

## Tecnologías

| Capa | Herramientas |
|---|---|
| Backend | PHP 8 (PDO, sesiones, `mail()`), sin frameworks |
| Base de datos | MySQL / MariaDB (`utf8mb4`) |
| Frontend | HTML semántico, CSS propio (variables, grid, `clamp()`), JavaScript vanilla |
| Servidor | Apache con `mod_rewrite` (`.htaccess`) en Hostinger |

## Estructura

```
/
├── index.php, nosotros.php, servicios.php, clientes.php,
│   blog.php, articulo.php, contacto.php     Páginas públicas
├── enviar.php                Procesa el formulario de contacto
├── sitemap.php               Genera /sitemap.xml
├── .htaccess                 URLs limpias, caché, cabeceras de seguridad y redirecciones 301
├── router.php                Router para el servidor embebido de PHP (solo desarrollo)
├── config/
│   └── config.example.php    Plantilla de configuración (cópiala como config.php)
├── includes/                 Arranque, funciones, SEO (head.php), cabecera, pie y contenido (data.php)
├── admin/                    Panel: login, blog, clientes, equipo, mensajes y perfil
├── assets/                   CSS, JS e imágenes (logos de clientes)
├── uploads/                  Archivos subidos desde el panel (no se versionan)
└── database/schema.sql       Estructura de la base de datos
```

## Cómo correrlo en local

Requisitos: PHP 8.0 o superior y MySQL/MariaDB (por ejemplo, con XAMPP).

1. Clona el repositorio.
2. Crea una base de datos e importa `database/schema.sql`.
3. Copia la plantilla de configuración y llena los datos de tu base:
   ```bash
   cp config/config.example.php config/config.php
   ```
   Para ver los errores mientras desarrollas, pon `APP_ENV` en `'development'`.
4. Inicia el servidor:
   ```bash
   php -S localhost:8000 router.php
   ```
5. Abre http://localhost:8000/admin/setup.php, crea el usuario administrador y luego borra `admin/setup.php`.

## Publicar en un hosting

1. Sube los archivos a `public_html/`, excepto `router.php`.
2. Crea la base de datos e importa `database/schema.sql`.
3. Crea `config/config.php` con los datos del hosting.
4. Crea el administrador con `/admin/setup.php` y luego borra ese archivo.
5. Cuando el SSL esté activo, descomenta las líneas de HTTPS en `.htaccess`.
6. Da permisos de escritura (755) a las carpetas de `uploads/`.

---

Desarrollado por **Jorge Martinez** para Click 2 Action.
