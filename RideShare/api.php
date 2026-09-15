<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (empty($_SESSION['usuario_id'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sesión no válida.']); exit; }
$uid=(int)$_SESSION['usuario_id'];
$action=$_POST['action'] ?? '';

function out($data){ echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
function getUser($db,$id){$s=$db->prepare('SELECT * FROM usuarios WHERE id=? LIMIT 1');$s->bind_param('i',$id);$s->execute();return $s->get_result()->fetch_assoc();}
function getRide($db,$id){
 $sql="SELECT v.*, p.nombre pasajero_nombre,p.apellido pasajero_apellido,p.telefono pasajero_telefono,p.foto_perfil pasajero_foto,
 c.nombre conductor_nombre,c.apellido conductor_apellido,c.telefono conductor_telefono,c.foto_perfil conductor_foto,c.placa,c.tipo_vehiculo,c.color_vehiculo
 FROM viajes v JOIN usuarios p ON p.id=v.pasajero_id LEFT JOIN usuarios c ON c.id=v.conductor_id WHERE v.id=? LIMIT 1";
 $s=$db->prepare($sql);$s->bind_param('i',$id);$s->execute();return $s->get_result()->fetch_assoc();
}

$me=getUser($conexion,$uid); if(!$me) out(['ok'=>false,'error'=>'Usuario no encontrado.']);

switch($action){
 case 'request':
  if($me['rol']!=='pasajero') out(['ok'=>false,'error'=>'Solo un pasajero puede solicitar viajes.']);
  $tipo=$_POST['tipo']??'carro'; $pago=$_POST['pago']??'efectivo';
  if(!in_array($tipo,['carro','moto'],true)||!in_array($pago,['efectivo','tarjeta','nequi'],true)) out(['ok'=>false,'error'=>'Datos de viaje inválidos.']);
  $origen=trim($_POST['origen']??'Mi ubicación');$destino=trim($_POST['destino']??'');
  $olat=(float)($_POST['olat']??0);$olng=(float)($_POST['olng']??0);$dlat=(float)($_POST['dlat']??0);$dlng=(float)($_POST['dlng']??0);$precio=(int)($_POST['precio']??0);
  if(!$destino||!$olat||!$olng||!$dlat||!$dlng) out(['ok'=>false,'error'=>'Selecciona un destino válido.']);
  $q=$conexion->prepare("SELECT id FROM viajes WHERE pasajero_id=? AND estado IN ('solicitado','aceptado','en_curso') LIMIT 1");$q->bind_param('i',$uid);$q->execute();if($q->get_result()->fetch_assoc())out(['ok'=>false,'error'=>'Ya tienes un viaje activo.']);
  $s=$conexion->prepare("INSERT INTO viajes(pasajero_id,tipo_vehiculo,origen,destino,origen_lat,origen_lng,destino_lat,destino_lng,medio_pago,precio,estado) VALUES(?,?,?,?,?,?,?,?,?,?, 'solicitado')");
  $s->bind_param('isssddddsi',$uid,$tipo,$origen,$destino,$olat,$olng,$dlat,$dlng,$pago,$precio);$s->execute();out(['ok'=>true,'viaje_id'=>$conexion->insert_id]);
 case 'status':
  if($me['rol']==='pasajero'){$s=$conexion->prepare("SELECT id FROM viajes WHERE pasajero_id=? AND (estado IN ('solicitado','aceptado','en_curso') OR (estado='finalizado' AND finalizado_en >= NOW() - INTERVAL 1 DAY) OR (estado='cancelado' AND motivo_cancelacion='Cancelado por conductor' AND cancelado_en >= NOW() - INTERVAL 1 DAY)) ORDER BY id DESC LIMIT 1");}
  else {$s=$conexion->prepare("SELECT id FROM viajes WHERE conductor_id=? AND estado IN ('aceptado','en_curso') ORDER BY id DESC LIMIT 1");}
  $s->bind_param('i',$uid);$s->execute();$r=$s->get_result()->fetch_assoc();out(['ok'=>true,'viaje'=>$r?getRide($conexion,$r['id']):null]);
 case 'driver_requests':
  if($me['rol']!=='conductor')out(['ok'=>false,'error'=>'No eres conductor.']);
  $tipo=$me['tipo_vehiculo']??'';
  $s=$conexion->prepare("SELECT v.*,u.nombre pasajero_nombre,u.apellido pasajero_apellido,u.telefono pasajero_telefono,u.foto_perfil pasajero_foto FROM viajes v JOIN usuarios u ON u.id=v.pasajero_id WHERE v.estado='solicitado' AND v.tipo_vehiculo=? ORDER BY v.id DESC LIMIT 20");$s->bind_param('s',$tipo);$s->execute();out(['ok'=>true,'solicitudes'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
 case 'accept':
  if($me['rol']!=='conductor')out(['ok'=>false,'error'=>'No autorizado.']);$id=(int)$_POST['viaje_id'];
  $conexion->begin_transaction();$s=$conexion->prepare("UPDATE viajes SET conductor_id=?,estado='aceptado',aceptado_en=NOW() WHERE id=? AND estado='solicitado' AND tipo_vehiculo=?");$s->bind_param('iis',$uid,$id,$me['tipo_vehiculo']);$s->execute();if($s->affected_rows!==1){$conexion->rollback();out(['ok'=>false,'error'=>'La solicitud ya fue tomada.']);}$conexion->commit();out(['ok'=>true]);
 case 'start':
  $id=(int)$_POST['viaje_id'];$s=$conexion->prepare("UPDATE viajes SET estado='en_curso',iniciado_en=NOW() WHERE id=? AND conductor_id=? AND estado='aceptado'");$s->bind_param('ii',$id,$uid);$s->execute();out(['ok'=>$s->affected_rows===1,'error'=>$s->affected_rows?'':'No se pudo iniciar.']);
 case 'finish':
  $id=(int)$_POST['viaje_id'];$s=$conexion->prepare("UPDATE viajes SET estado='finalizado',finalizado_en=NOW() WHERE id=? AND conductor_id=? AND estado='en_curso'");$s->bind_param('ii',$id,$uid);$s->execute();out(['ok'=>$s->affected_rows===1]);
 case 'cancel_driver':
  if($me['rol']!=='conductor')out(['ok'=>false,'error'=>'Solo el conductor puede cancelar este viaje.']);
  $id=(int)($_POST['viaje_id']??0);
  $s=$conexion->prepare("UPDATE viajes SET estado='cancelado',motivo_cancelacion='Cancelado por conductor',cancelado_en=NOW() WHERE id=? AND conductor_id=? AND estado IN ('aceptado','en_curso')");
  $s->bind_param('ii',$id,$uid);$s->execute();out(['ok'=>$s->affected_rows===1,'error'=>$s->affected_rows?'':'No se pudo cancelar el viaje.']);
 case 'cancel':
  if($me['rol']!=='pasajero') out(['ok'=>false,'error'=>'Solo el pasajero puede cancelar esta solicitud.']);
  $id=(int)($_POST['viaje_id']??0);
  $mot=trim($_POST['motivo']??'Sin motivo');
  if($id<=0) out(['ok'=>false,'error'=>'Solicitud inválida.']);
  $s=$conexion->prepare("UPDATE viajes SET estado='cancelado',motivo_cancelacion=?,cancelado_en=NOW() WHERE id=? AND pasajero_id=? AND estado IN ('solicitado','aceptado')");
  $s->bind_param('sii',$mot,$id,$uid);
  $s->execute();
  if($s->affected_rows===1) out(['ok'=>true,'message'=>'Viaje cancelado correctamente.']);
  out(['ok'=>false,'error'=>'El viaje ya no se puede cancelar o no existe.']);
 case 'delete_request':
  if($me['rol']!=='conductor')out(['ok'=>false]);$id=(int)$_POST['viaje_id'];$s=$conexion->prepare("UPDATE viajes SET estado='rechazado' WHERE id=? AND estado='solicitado'");$s->bind_param('i',$id);$s->execute();out(['ok'=>true]);
 case 'surveys':
  if($me['rol']!=='conductor')out(['ok'=>false,'error'=>'Solo el conductor puede ver encuestas.']);
  $s=$conexion->prepare("SELECT c.estrellas,c.comentario,c.creado_en,CONCAT(u.nombre,' ',u.apellido) pasajero_nombre,v.origen,v.destino FROM calificaciones c JOIN usuarios u ON u.id=c.pasajero_id JOIN viajes v ON v.id=c.viaje_id WHERE c.conductor_id=? ORDER BY c.id DESC LIMIT 50");
  $s->bind_param('i',$uid);$s->execute();out(['ok'=>true,'encuestas'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
 case 'history':
  $col=$me['rol']==='conductor'?'conductor_id':'pasajero_id';$s=$conexion->prepare("SELECT creado_en,origen,destino,estado,precio,medio_pago FROM viajes WHERE $col=? ORDER BY id DESC LIMIT 50");$s->bind_param('i',$uid);$s->execute();out(['ok'=>true,'historial'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
 case 'send_chat':
  $id=(int)$_POST['viaje_id'];$msg=trim($_POST['mensaje']??'');if(!$msg)out(['ok'=>false]);$v=getRide($conexion,$id);if(!$v||((int)$v['pasajero_id']!==$uid&&(int)($v['conductor_id']??0)!==$uid))out(['ok'=>false,'error'=>'No autorizado.']);$s=$conexion->prepare('INSERT INTO mensajes(viaje_id,emisor_id,mensaje) VALUES(?,?,?)');$s->bind_param('iis',$id,$uid,$msg);$s->execute();out(['ok'=>true]);
 case 'chat':
  $id=(int)$_POST['viaje_id'];$v=getRide($conexion,$id);if(!$v||((int)$v['pasajero_id']!==$uid&&(int)($v['conductor_id']??0)!==$uid))out(['ok'=>false]);$s=$conexion->prepare("SELECT m.id,m.mensaje,m.emisor_id,CONCAT(u.nombre,' ',u.apellido) AS emisor,m.creado_en FROM mensajes m JOIN usuarios u ON u.id=m.emisor_id WHERE m.viaje_id=? ORDER BY m.id ASC");$s->bind_param('i',$id);$s->execute();out(['ok'=>true,'mensajes'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
 case 'rate':
  $id=(int)$_POST['viaje_id'];$stars=max(1,min(5,(int)($_POST['estrellas']??5)));$comment=trim($_POST['comentario']??'');$v=getRide($conexion,$id);if(!$v||$v['pasajero_id']!=$uid||!in_array($v['estado'],['finalizado','cancelado'],true))out(['ok'=>false]);$s=$conexion->prepare('INSERT INTO calificaciones(viaje_id,pasajero_id,conductor_id,estrellas,comentario) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE estrellas=VALUES(estrellas),comentario=VALUES(comentario)');$s->bind_param('iiiis',$id,$uid,$v['conductor_id'],$stars,$comment);$s->execute();out(['ok'=>true]);
 default: out(['ok'=>false,'error'=>'Acción no reconocida.']);
}
