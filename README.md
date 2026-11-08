# ICE — Iglesia Cristiana Evangélica

Sitio web público de ICE, publicado con **GitHub Pages**.

- URL: https://jahulin-dotcom.github.io/Iglesia/
- Tecnología: HTML + CSS + un pequeño JS (sin servidor ni base de datos).
- No requiere instalación ni configuración.

## Estructura

```
index.html          página pública (todo el contenido)
assets/site.css     sistema de diseño y estilos responsive
assets/main.js      menú móvil, sombra del encabezado y animaciones de entrada
assets/favicon.svg  icono del sitio
assets/og.jpg       imagen 1200×630 para compartir en redes y WhatsApp
.nojekyll           evita el procesado de Jekyll en GitHub Pages
```

## Diseño

- Tipografía editorial con **Fraunces** (títulos) e **Inter** (texto), cargadas desde Google Fonts con alternativas locales.
- Paleta cálida: azul tinta, crema y dorado. Todos los colores son variables en `:root` dentro de `assets/site.css`.
- Adaptable a móvil (360 px), tablet y escritorio, con menú desplegable accesible.
- Respeta `prefers-reduced-motion` y funciona sin JavaScript (el contenido siempre es visible).

## Cómo editar el contenido

Todo el texto está en `index.html`:

| Qué | Dónde |
| --- | --- |
| Título de la pestaña, descripción y datos para compartir | `<head>` |
| Nombre y subtítulo de la cabecera | `<header class="site-header">` |
| Titular, bienvenida y botones | `<section class="hero">` |
| Los tres pilares (Fe, Esperanza, Comunidad) | `<section class="values">` |
| Nuestra fe y lista de características | `<section id="fe">` |
| Comunidad (tres tarjetas) | `<section id="comunidad">` |
| Vida en comunidad | `<section id="vida">` |
| Aviso de dirección y horarios | `<section id="visitanos">` |
| Pie de página | `<footer class="site-footer">` |

Para añadir una tarjeta, copia un `<article class="card">` completo y cambia el icono, el `<h3>` y el `<p>`.

## Publicar cambios

```bash
git add .
git commit -m "Actualiza contenido"
git push origin main
```

GitHub Pages reconstruye el sitio automáticamente en uno o dos minutos.

## Panel de administración (versión PHP)

La versión original con panel `/admin.php`, SQLite y autenticación **no se puede ejecutar en GitHub Pages** porque necesita PHP. Está conservada intacta en la rama `php`:

```bash
git checkout php
```

Para usarla hace falta un hosting con PHP 8.2+, `pdo_sqlite` y `mbstring` (Render, Railway, Fly.io o un hosting compartido). Las instrucciones completas están en `LEEME.md` dentro de esa rama.
