# ICE — Iglesia Cristiana Evangélica

Sitio web público de ICE, publicado con **GitHub Pages**.

- URL: https://jahulin-dotcom.github.io/Iglesia/
- Tecnología: HTML + CSS estáticos (sin servidor, sin base de datos).
- No requiere instalación ni configuración: Pages sirve `index.html` directamente.

## Estructura

```
index.html          página pública
assets/site.css     estilos (responsive, modo móvil/tablet/escritorio)
.nojekyll           evita el procesado de Jekyll en GitHub Pages
```

## Cómo editar el contenido

Todo el texto está en `index.html`. Los fragmentos principales:

| Qué | Dónde |
| --- | --- |
| Título de la pestaña y descripción | `<title>` y `<meta name="description">` |
| Nombre y subtítulo de la cabecera | `<header class="site-header">` |
| Titular y bienvenida | `<section class="hero">` |
| Secciones ("Nuestra fe", "Vida en comunidad") | `<div class="section-list">` |
| Pie de página | `<footer>` |

Para añadir una sección, copia un bloque `<article>` completo y cambia el número, el `<h3>` y el `<p>`.

## Panel de administración (versión PHP)

La versión original con panel `/admin.php`, SQLite y autenticación **no se puede ejecutar en GitHub Pages** porque necesita PHP. Está conservada intacta en la rama `php`:

```bash
git checkout php
```

Para usarla hace falta un hosting con PHP 8.2+, `pdo_sqlite` y `mbstring` (por ejemplo Render, Railway, Fly.io o un hosting compartido). Las instrucciones completas están en `LEEME.md` dentro de esa rama.

## Publicar cambios

```bash
git add .
git commit -m "Actualiza contenido"
git push origin main
```

GitHub Pages reconstruye el sitio automáticamente en un par de minutos.
