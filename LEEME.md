# ICE — página web con administración

Paquete listo para instalar. No es una web ya publicada ni tiene un dominio asignado.

## Incluye
- Página pública adaptable a móvil, tablet y ordenador.
- `/admin.php` con usuario y contraseña, autenticación en el servidor y cierre de sesión.
- Edición de portada, bienvenida, dirección, horarios, teléfono y correo.
- Crear, editar, ordenar, ocultar y eliminar secciones desde el mismo sitio.
- Contraseñas almacenadas con `password_hash`, cookies HttpOnly/SameSite, protección CSRF, consultas preparadas, caducidad tras 30 minutos de inactividad y límite de 8 intentos por IP cada 15 minutos.
- Cambio de contraseña desde el panel; los cambios guardados se reflejan en la página pública.
- Base de datos SQLite sin servicios externos ni cuotas incorporadas.

Los textos iniciales son una propuesta para revisar, no una declaración doctrinal aprobada por ICE. No se han inventado ubicación, horarios, responsables ni datos de contacto. Estos campos están vacíos y no se muestran hasta rellenarlos. La sección Reuniones comienza oculta.

## Requisitos
PHP 8.2 o superior, extensiones `pdo_sqlite` y `mbstring`, y certificado HTTPS. No funciona en alojamientos solo estáticos (GitHub Pages, Netlify estático, etc.). No incluye compra de hosting, dominio, correo ni certificado.

## Instalación (una vez)
1. Descomprime el paquete en un equipo o servidor con PHP y las extensiones anteriores.
2. En esa carpeta ejecuta `php bin/instalar.php`.
3. Elige tu usuario y una contraseña propia de al menos 12 caracteres (máximo 72 bytes). No existe usuario ni contraseña predeterminados. En terminal Unix la contraseña no se muestra mientras se escribe. No la compartas por chat.
4. Sube la carpeta completa al servidor, conservando la estructura. Configura la raíz pública del dominio para que apunte a `ice-web/public/`, NO a `ice-web/`.
5. `private/` y `bin/` deben quedar FUERA de la raíz pública. Si tu hosting solo ofrece `public_html`, coloca el contenido de `public/` dentro de `public_html`, y `private/` como carpeta hermana. El archivo `public/admin.php` y `public/index.php` encuentran `../private/app.php`.
6. Concede al usuario del servidor PHP lectura/escritura en `private/` y en su base de datos. No uses permisos 777. En servidores con usuario distinto ajusta propietario/grupo y permisos según tu proveedor (p.ej. carpeta 750 y fichero 640 con grupo correcto). El instalador crea inicialmente el fichero con permisos 600.
7. Activa HTTPS en tu proveedor. Abre `https://tu-dominio/admin.php`, inicia sesión y personaliza el contenido. Las URLs de ejemplo de esta guía no son enlaces de una web publicada.

Si no tienes terminal en el hosting, puedes ejecutar el instalador en tu ordenador con PHP y después subir `private/ice.sqlite`. No subas una base de datos de pruebas ni el informe de pruebas. La carpeta `private/` nunca debe poder descargarse desde internet. El archivo `.htaccess` es una protección adicional para Apache, no sustituye separar la raíz pública (otros servidores lo ignoran).

## Uso
- Portada: modifica los textos y datos; pulsa Guardar portada y contacto.
- Secciones: abre una sección, cambia su título y contenido, elige su orden (los números menores salen primero), marca si se muestra y guarda. A igual orden, se mantiene el orden de creación.
- Añadir sección: escribe título y contenido; pulsa Crear sección.
- Eliminar sección pide confirmación y no se puede deshacer.
- La página acepta texto plano y saltos de línea, no HTML ejecutable.
- Ver página abre el resultado público en otra pestaña.
- Cambia la contraseña en el apartado Contraseña. Para cambiar de usuario o recuperar una cuenta, un administrador técnico del servidor debe intervenir; no hay recuperación por correo.

## Copias y mantenimiento
Haz copias periódicas de `private/ice.sqlite` con la herramienta de copia de seguridad del hosting o `sqlite3 private/ice.sqlite ".backup copia.sqlite"`. Esta base contiene el contenido y el hash de contraseña: consérvala fuera del directorio público y no la publiques ni la compartas.
Mantén PHP actualizado. Los límites de acceso se calculan por REMOTE_ADDR; tras un proxy, el proveedor debe configurar correctamente la IP real sin confiar en cabeceras arbitrarias. No hay MFA ni recuperación por correo. El paquete no ha pasado una auditoría de seguridad independiente.

## Prueba local opcional
Después de instalar: `ICE_DEV=1 php -S 127.0.0.1:8080 -t public` (Linux/macOS).
En otra terminal abre `http://127.0.0.1:8080`. ICE_DEV solo permite HTTP en el servidor de desarrollo de PHP; la administración en producción exige HTTPS. No uses el servidor integrado de PHP para publicar en internet.

## Estructura
public/       archivos accesibles por visitantes
private/      lógica y base de datos; fuera de la raíz pública
bin/          instalador de consola; fuera de la raíz pública

La ilustración de la cruz y el arco es original en CSS; no representa una sede real de ICE. No se han utilizado fotografías de una congregación ajena.
