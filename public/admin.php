<?php
require __DIR__.'/../private/app.php'; headersSecurity(); startSession(); $error='';$notice=$_SESSION['notice']??'';unset($_SESSION['notice']);
$logged=isset($_SESSION['admin']);
if($_SERVER['REQUEST_METHOD']==='POST'){
 checkCsrf();$action=(string)($_POST['action']??'');
 try {
  if($action==='login'){
   $bucket=hash('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown'));
   db()->exec('DELETE FROM attempts WHERE started < '.(time()-900));
   $q=db()->prepare('SELECT count FROM attempts WHERE bucket=?');$q->execute([$bucket]);
   if((int)$q->fetchColumn()>=8)throw new RuntimeException('Demasiados intentos. Espera 15 minutos antes de volver a probar.');
   $q=db()->prepare('SELECT * FROM admins WHERE username=?');$q->execute([shortText('username',60)]);$admin=$q->fetch();
   $password=(string)($_POST['password']??'');
   // Comparación con un hash también cuando el usuario no existe.
   $hash=$admin?$admin['password_hash']:'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
   if(!password_verify($password,$hash)||!$admin){
    $q=db()->prepare('INSERT INTO attempts(bucket,count,started) VALUES(?,1,?) ON CONFLICT(bucket) DO UPDATE SET count=count+1');$q->execute([$bucket,time()]);
    throw new RuntimeException('Usuario o contraseña incorrectos.');
   }
   $q=db()->prepare('DELETE FROM attempts WHERE bucket=?');$q->execute([$bucket]);
   if(password_needs_rehash($hash,PASSWORD_DEFAULT)) { $q=db()->prepare('UPDATE admins SET password_hash=? WHERE id=?');$q->execute([password_hash($password,PASSWORD_DEFAULT),$admin['id']]); }
   session_regenerate_id(true);$_SESSION['admin']=(int)$admin['id'];$_SESSION['active']=time();$_SESSION['csrf']=bin2hex(random_bytes(32));redirectAdmin();
  }
  if(!$logged) { http_response_code(401);throw new RuntimeException('Inicia sesión para editar.'); }
  if($action==='logout') { $_SESSION=[];session_destroy();header('Location: admin.php',true,303);exit; }
  if($action==='settings') {
   $limits=['name'=>60,'fullName'=>160,'headline'=>160,'welcome'=>1200,'address'=>300,'schedule'=>600,'email'=>200,'phone'=>80];$values=[];
   foreach($limits as $k=>$max)$values[$k]=shortText($k,$max);
   if(!$values['name']||!$values['fullName']||!$values['headline'])throw new RuntimeException('Completa nombre, denominación y título de portada.');
   if($values['email']&&!filter_var($values['email'],FILTER_VALIDATE_EMAIL))throw new RuntimeException('El correo no tiene un formato válido.');
   db()->beginTransaction();$q=db()->prepare('UPDATE settings SET value=? WHERE key=?');foreach($values as $k=>$v)$q->execute([$v,$k]);db()->commit();
   $_SESSION['notice']='Portada y datos guardados.';redirectAdmin();
  }
  if($action==='section') {
   $title=shortText('title',150);$body=shortText('body',8000);$id=(int)($_POST['id']??0);$visible=isset($_POST['visible'])?1:0;$position=max(0,min(999,(int)($_POST['position']??0)));
   if(!$title)throw new RuntimeException('Escribe un título para la sección.');
   if($id){$q=db()->prepare('UPDATE sections SET title=?,body=?,visible=?,position=? WHERE id=?');$q->execute([$title,$body,$visible,$position,$id]);}
   else{if((int)db()->query('SELECT COUNT(*) FROM sections')->fetchColumn()>=40)throw new RuntimeException('Puedes mantener hasta 40 secciones.');$q=db()->prepare('INSERT INTO sections(title,body,visible,position) VALUES(?,?,?,?)');$q->execute([$title,$body,$visible,$position]);}
   $_SESSION['notice']='Sección guardada.';redirectAdmin();
  }
  if($action==='delete') { $q=db()->prepare('DELETE FROM sections WHERE id=?');$q->execute([(int)($_POST['id']??0)]);$_SESSION['notice']='Sección eliminada.';redirectAdmin(); }
  if($action==='password') {
   $q=db()->prepare('SELECT password_hash FROM admins WHERE id=?');$q->execute([$_SESSION['admin']]);
   if(!password_verify((string)($_POST['current']??''),(string)$q->fetchColumn()))throw new RuntimeException('La contraseña actual no es correcta.');
   $pass=(string)($_POST['new']??'');
   if(mb_strlen($pass)<12||strlen($pass)>72||$pass!==(string)($_POST['confirm']??''))throw new RuntimeException('La nueva contraseña debe coincidir y tener entre 12 caracteres y 72 bytes.');
   $q=db()->prepare('UPDATE admins SET password_hash=? WHERE id=?');$q->execute([password_hash($pass,PASSWORD_DEFAULT),$_SESSION['admin']]);session_regenerate_id(true);$_SESSION['notice']='Contraseña actualizada.';redirectAdmin();
  }
 }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();$error=($e instanceof RuntimeException&&!($e instanceof PDOException))?$e->getMessage():'No se han podido guardar los cambios. Inténtalo de nuevo.';}
}
function inputField(string $key,string $label,int $max,bool $area=false): void { $v=($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='settings')?(string)($_POST[$key]??''):setting($key);echo '<label>'.h($label);if($area)echo '<textarea name="'.h($key).'" maxlength="'.$max.'" rows="3">'.h($v).'</textarea>';else echo '<input name="'.h($key).'" maxlength="'.$max.'" value="'.h($v).'"'.($key==='email'?' type="email"':'').'>';echo '</label>'; }
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Administración · ICE</title><link rel="stylesheet" href="assets/site.css"><script src="assets/admin.js" defer></script></head><body class="admin-body"><header class="admin-header"><a href="./" class="brand"><b>ICE</b><span>Administración</span></a><?php if($logged): ?><form method="post"><?=csrf()?><input type="hidden" name="action" value="logout"><button class="button secondary">Cerrar sesión</button></form><?php endif; ?></header><main class="admin-main">
<?php if($error): ?><div class="message error" role="alert"><?=h($error)?></div><?php endif; ?><?php if($notice): ?><div class="message success" role="status"><?=h($notice)?></div><?php endif; ?>
<?php if(!$logged): ?><section class="login"><p class="eyebrow">ÁREA PRIVADA</p><h1>Editar tu iglesia.</h1><p>Accede con tu cuenta de administrador.</p><form method="post"><?=csrf()?><input type="hidden" name="action" value="login"><label>Usuario<input name="username" autocomplete="username" maxlength="60" required></label><label>Contraseña<input type="password" name="password" autocomplete="current-password" maxlength="200" required></label><button class="button primary">Entrar en modo edición</button></form><a class="text-link" href="./">Volver a la página</a></section>
<?php else: ?><div class="editor-title"><div><p class="eyebrow">TU SITIO</p><h1>Panel de edición</h1></div><a class="button secondary" href="./" target="_blank" rel="noopener">Ver página ↗</a></div><nav class="editor-nav"><a href="#portada">Portada</a><a href="#secciones">Secciones</a><a href="#seguridad">Contraseña</a></nav>
<section class="editor-card" id="portada"><h2>Portada y contacto</h2><form method="post"><?=csrf()?><input type="hidden" name="action" value="settings"><div class="form-grid"><?php inputField('name','Nombre corto',60);inputField('fullName','Nombre completo',160); ?></div><?php inputField('headline','Título de portada',160);inputField('welcome','Bienvenida',1200,true);inputField('schedule','Días y horarios',600,true);inputField('address','Dirección',300,true); ?><div class="form-grid"><?php inputField('email','Correo electrónico',200);inputField('phone','Teléfono',80); ?></div><button class="button primary">Guardar portada y contacto</button><p class="form-note">Los datos de contacto vacíos no aparecen en la página.</p></form></section>
<section id="secciones"><div class="section-heading"><h2>Secciones de la página</h2><a class="text-link" href="#nueva">Añadir sección</a></div><?php foreach(sections(false) as $s): $failed=($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='section'&&(int)($_POST['id']??0)===(int)$s['id']);$v=$failed?array_merge($s,$_POST,['visible'=>isset($_POST['visible'])?1:0]):$s; ?><details class="editor-card" <?= $failed?'open':'' ?>><summary><?=h($s['title'])?><span><?= $s['visible']?'Visible':'Oculta' ?></span></summary><form method="post"><?=csrf()?><input type="hidden" name="action" value="section"><input type="hidden" name="id" value="<?=$s['id']?>"><label>Título<input name="title" maxlength="150" required value="<?=h($v['title'])?>"></label><label>Contenido<textarea name="body" rows="6" maxlength="8000"><?=h($v['body'])?></textarea></label><div class="form-grid"><label>Orden<input type="number" name="position" min="0" max="999" value="<?=h((string)$v['position'])?>"></label><label class="checkbox"><input type="checkbox" name="visible" <?=$v['visible']?'checked':''?>> Mostrar en la página</label></div><button class="button primary">Guardar sección</button></form><form class="delete-form" method="post" data-confirm="¿Eliminar esta sección? Esta acción no se puede deshacer."><?=csrf()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>"><button class="button danger">Eliminar sección</button></form></details><?php endforeach; ?>
<details class="editor-card" id="nueva" <?=($_POST['action']??'')==='section'&&empty($_POST['id'])?'open':''?>><summary>Nueva sección<span>+</span></summary><form method="post"><?=csrf()?><input type="hidden" name="action" value="section"><label>Título<input name="title" maxlength="150" required value="<?=($_POST['action']??'')==='section'&&empty($_POST['id'])?h((string)($_POST['title']??'')):''?>"></label><label>Contenido<textarea name="body" rows="5" maxlength="8000"><?=($_POST['action']??'')==='section'&&empty($_POST['id'])?h((string)($_POST['body']??'')):''?></textarea></label><label>Orden<input type="number" name="position" min="0" max="999" value="<?=count(sections(false))?>"></label><label class="checkbox"><input type="checkbox" name="visible" checked> Mostrar en la página</label><button class="button primary">Crear sección</button></form></details></section>
<section class="editor-card" id="seguridad"><h2>Cambiar contraseña</h2><form method="post"><?=csrf()?><input type="hidden" name="action" value="password"><label>Contraseña actual<input type="password" name="current" autocomplete="current-password" required></label><label>Nueva contraseña<input type="password" name="new" autocomplete="new-password" minlength="12" required></label><label>Repite la nueva contraseña<input type="password" name="confirm" autocomplete="new-password" minlength="12" required></label><button class="button primary">Actualizar contraseña</button></form></section><?php endif; ?></main></body></html>
