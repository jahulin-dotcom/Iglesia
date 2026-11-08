<?php
declare(strict_types=1);
const DB_FILE = __DIR__ . '/ice.sqlite';
function db(): PDO {
 static $db;
 if ($db) return $db;
 if (!is_file(DB_FILE)) { http_response_code(503); exit('Sitio pendiente de instalación. Consulta LEEME.md.'); }
 $db = new PDO('sqlite:' . DB_FILE, null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
 $db->exec('PRAGMA busy_timeout=5000'); return $db;
}
function h(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function setting(string $key): string { $q=db()->prepare('SELECT value FROM settings WHERE key=?'); $q->execute([$key]); return (string)$q->fetchColumn(); }
function sections(bool $visible=true): array { return db()->query('SELECT * FROM sections'.($visible?' WHERE visible=1':'').' ORDER BY position,id')->fetchAll(); }
function headersSecurity(): void {
 header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: strict-origin-when-cross-origin'); header('X-Frame-Options: DENY');
 header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
}
function startSession(): void {
 $https=isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';
 // El modo desarrollo solo es posible en el servidor CLI y debe activarse expresamente.
 $dev=PHP_SAPI==='cli-server'&&getenv('ICE_DEV')==='1';
 if (!$https&&!$dev) { http_response_code(403); exit('El área de administración requiere HTTPS. Activa el certificado SSL en tu hosting.'); }
 ini_set('session.use_strict_mode','1'); ini_set('session.use_only_cookies','1');
 session_name('ice_admin'); session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!$dev,'httponly'=>true,'samesite'=>'Strict']); session_start();
 header('Cache-Control: no-store, private');
 if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
 if (isset($_SESSION['admin']) && time()-(int)($_SESSION['active']??0)>1800) { unset($_SESSION['admin']); session_regenerate_id(true); }
 if (isset($_SESSION['admin'])) $_SESSION['active']=time();
}
function csrf(): string { return '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">'; }
function checkCsrf(): void { if(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))) { http_response_code(403); exit('La sesión del formulario caducó. Recarga la página.'); } }
function redirectAdmin(): never { header('Location: admin.php',true,303); exit; }
function shortText(string $key,int $max): string { $v=trim((string)($_POST[$key]??'')); if(mb_strlen($v)>$max) throw new RuntimeException('El campo supera el límite de '.$max.' caracteres.'); return $v; }
