<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
if (!extension_loaded('pdo_sqlite')||!extension_loaded('mbstring')) exit("Requiere PHP 8.2+, pdo_sqlite y mbstring.\n");
$file=__DIR__.'/../private/ice.sqlite';
if (is_file($file)) exit("Ya existe la base de datos. No se ha modificado.\n");
function prompt(string $label,bool $secret=false): string {
 fwrite(STDOUT,$label);
 if ($secret && DIRECTORY_SEPARATOR==='/') { system('stty -echo'); }
 try { $value=trim((string)fgets(STDIN)); } finally { if ($secret&&DIRECTORY_SEPARATOR==='/') { system('stty echo'); fwrite(STDOUT,"\n"); } }
 return $value;
}
$user=prompt('Usuario administrador (3–60 caracteres): ');
if(!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/',$user)) exit("Usuario no válido.\n");
$password=prompt('Contraseña nueva (mínimo 12 caracteres): ',true);
$confirm=prompt('Repite la contraseña: ',true);
if(mb_strlen($password)<12||strlen($password)>72||$password!==$confirm) exit("Las contraseñas deben coincidir y tener entre 12 caracteres y 72 bytes.\n");
umask(0077);
$db=new PDO('sqlite:'.$file,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->beginTransaction();
$db->exec('CREATE TABLE admins(id INTEGER PRIMARY KEY, username TEXT UNIQUE NOT NULL,password_hash TEXT NOT NULL); CREATE TABLE settings(key TEXT PRIMARY KEY,value TEXT NOT NULL); CREATE TABLE sections(id INTEGER PRIMARY KEY AUTOINCREMENT,title TEXT NOT NULL,body TEXT NOT NULL,visible INTEGER NOT NULL DEFAULT 1,position INTEGER NOT NULL DEFAULT 0); CREATE TABLE attempts(bucket TEXT PRIMARY KEY,count INTEGER NOT NULL,started INTEGER NOT NULL)');
$q=$db->prepare('INSERT INTO admins(username,password_hash) VALUES(?,?)');$q->execute([$user,password_hash($password,PASSWORD_DEFAULT)]);
$settings=['name'=>'ICE','fullName'=>'Iglesia Cristiana Evangélica','headline'=>'Un lugar para la fe y la comunidad','welcome'=>'Bienvenido a ICE. Un espacio para acercarnos a Dios, compartir la esperanza y caminar juntos.','address'=>'','schedule'=>'','email'=>'','phone'=>''];
$q=$db->prepare('INSERT INTO settings(key,value) VALUES(?,?)');foreach($settings as $k=>$v)$q->execute([$k,$v]);
$q=$db->prepare('INSERT INTO sections(title,body,visible,position) VALUES(?,?,?,?)');
$q->execute(['Nuestra fe','Jesucristo es el centro de nuestra fe. La Biblia nos invita a conocer a Dios y a vivir su amor en nuestra vida diaria.',1,0]);
$q->execute(['Vida en comunidad','Crecer juntos en la fe, acompañarnos y servir a los demás.',1,1]);
$q->execute(['Reuniones','Añade aquí los días, horarios y actividades de tu iglesia.',0,2]);
$db->commit();chmod($file,0600);
fwrite(STDOUT,"Instalación completada. Publica solo la carpeta public/ y entra en /admin.php por HTTPS.\n");
