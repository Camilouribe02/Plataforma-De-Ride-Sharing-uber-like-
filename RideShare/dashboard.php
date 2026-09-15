<?php
session_start(); require_once "conexion.php";
if(empty($_SESSION['usuario_id'])){header("Location:index.php");exit;}
$uid=(int)$_SESSION['usuario_id'];
$s=$conexion->prepare("SELECT * FROM usuarios WHERE id=? LIMIT 1");$s->bind_param("i",$uid);$s->execute();$me=$s->get_result()->fetch_assoc();
if(!$me){header("Location:logout.php");exit;}
$esConductor=$me['rol']==='conductor';
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>RideShare | <?= $esConductor?'Conductor':'Pasajero' ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="assets/style.css">
</head><body class="app-body">
<header class="appbar">
  <div class="brand">RIDESHARE</div>
  <div class="user-menu">
    <button id="menuBtn" class="icon-btn">☰</button>
    <div id="menu" class="dropdown">
      <div class="menu-user"><b><?=htmlspecialchars($me['nombre'].' '.$me['apellido'])?></b><small><?=htmlspecialchars($me['correo'])?></small></div>
      <a href="#" data-panel="info">Información</a>
      <?php if($esConductor): ?><a href="#" data-panel="surveys">Encuestas</a><?php endif; ?>
      <a href="#" data-panel="history">Historial de <?= $esConductor?'rutas':'vehículos' ?></a>
      <a href="logout.php" class="danger">Cerrar sesión</a>
    </div>
  </div>
</header>
<main class="app-main">
<?php if(!$esConductor): ?>
<section class="hero-mini"><div><span class="tag">PASAJERO</span><h1>¿A dónde vamos hoy?</h1><p>Elige tu destino en Santander y solicita tu vehículo.</p></div></section>
<div class="ride-layout">
 <section class="card booking-card">
  <div id="bookingContent">
  <div id="profileWarn" class="alert error hidden">Debes tener una foto de perfil para solicitar un vehículo.</div>
  <label>📍 Punto de recogida</label><div class="search-wrap"><input id="origin" placeholder="Usa mi ubicación actual" autocomplete="off"><button id="locBtn" class="small-btn">Mi ubicación</button></div>
  <label>🎯 Destino <span class="hint">Escribe y selecciona un lugar en Santander</span></label>
  <div class="search-wrap"><input id="destination" placeholder="Escribe una dirección, universidad, clínica, centro comercial..." autocomplete="off"><div id="suggestions" class="suggestions"></div></div>
  <div class="type-row"><button class="choice active" data-type="carro">🚗 Carro</button><button class="choice" data-type="moto">🏍️ Moto</button></div>
  <div class="payment-row"><button class="pay active" data-pay="efectivo">💵 Efectivo</button><button class="pay" data-pay="tarjeta">💳 Tarjeta</button><button class="pay" data-pay="nequi">📱 Nequi</button></div>
  <div id="cardForm" class="subcard hidden"><b>Datos de tarjeta</b><input placeholder="Número de tarjeta" inputmode="numeric" maxlength="19"><div class="grid2"><input placeholder="MM/AA" maxlength="5"><input placeholder="CVV" type="password" maxlength="4"></div><small>Por seguridad esta demo no guarda el número ni el CVV.</small></div>
  <div class="price-box"><span>Valor estimado</span><strong id="price">$0</strong><small id="distance">Selecciona un destino</small></div>
  <button id="requestBtn" class="primary">Solicitar vehículo <span>→</span></button>
  </div>
  <div id="rideOverlay" class="ride-side-panel hidden"></div>
 </section>
 <section class="map-card"><div id="map"></div></section>
</div>
<div id="chatModal" class="modal hidden"><div class="modal-box"><button class="close" data-close>×</button><h3>Chat con el conductor</h3><div id="chatMessages" class="chat-messages"></div><form id="chatForm"><input id="chatInput" placeholder="Escribe un mensaje..." required><button>Enviar</button></form></div></div>
<div id="cancelModal" class="modal hidden"><div class="modal-box"><button class="close" data-close>×</button><h3>Cancelar solicitud</h3><p>¿Por qué deseas cancelar el servicio?</p><form id="cancelForm"><select id="cancelReason" class="plain-input"><option>Cambié de planes</option><option>Encontré otro transporte</option><option>El conductor está tardando</option><option>Destino equivocado</option><option>Otro motivo</option></select><button class="danger-btn">Confirmar cancelación</button></form></div></div>
<div id="profileModal" class="modal hidden"><div class="modal-box profile-modal-box"><button class="close" data-close>×</button><div class="profile-modal-photo-wrap"><img id="profilePhoto" class="profile-modal-photo" src="assets/avatar.svg" alt="Foto de perfil"></div><span id="profileRole" class="tag">CONDUCTOR</span><h3 id="profileName">Conductor</h3><div class="profile-details"><div><span>📞 Número</span><b id="profilePhone">—</b></div><div><span>🔖 Placa</span><b id="profilePlate">—</b></div><div><span>🚘 Tipo de vehículo</span><b id="profileVehicle">—</b></div><div><span>🎨 Color del vehículo</span><b id="profileColor">—</b></div></div></div></div>
<div id="rateModal" class="modal hidden"><div class="modal-box"><button class="close" data-close>×</button><h3>¿Cómo fue tu viaje?</h3><div id="stars" class="stars">★★★★★</div><textarea id="comment" placeholder="Comentario (opcional)"></textarea><button id="rateBtn" class="primary">Enviar encuesta</button><button id="skipRate" class="link-btn">× Cerrar</button></div></div>
<?php else: ?>
<section class="hero-mini"><div><span class="tag">CONDUCTOR</span><h1>Hola, <?=htmlspecialchars($me['nombre'])?> 👋</h1><p>Espera solicitudes y gestiona tus recorridos.</p></div><div class="driver-status <?=($me['pico_placa']?'blocked':'')?>"><b><?= $me['pico_placa']?'⛔ Pico y placa':'🟢 Disponible' ?></b><small><?=htmlspecialchars(($me['tipo_vehiculo']??'').' · '.($me['placa']??''))?></small></div></section>
<div class="driver-layout">
 <section class="card requests-card"><div id="driverNotice"></div><div id="requestList"><div class="empty">Buscando solicitudes compatibles…</div></div><div id="driverRide" class="ride-side-panel hidden"></div></section>
 <section class="map-card"><div id="map"></div></section>
</div>
<div id="chatModal" class="modal hidden"><div class="modal-box"><button class="close" data-close>×</button><h3>Chat con el pasajero</h3><div id="chatMessages" class="chat-messages"></div><form id="chatForm"><input id="chatInput" placeholder="Escribe un mensaje..." required><button>Enviar</button></form></div></div>
<?php endif; ?>
  <div id="infoPanel" class="side-modal hidden">
    <div class="side-panel">
      <button class="side-close" data-side-close>×</button>
      <div class="side-icon">👤</div>
      <span class="tag">MI CUENTA</span>
      <h2>Información personal</h2>
      <div class="profile-card">
        <div class="profile-avatar">
          <img src="<?= $me['foto_perfil'] ? 'uploads/'.htmlspecialchars($me['foto_perfil'], ENT_QUOTES) : 'assets/avatar.svg' ?>" alt="Foto de perfil">
        </div>
        <div><strong><?=htmlspecialchars($me['nombre'].' '.$me['apellido'])?></strong><small><?=htmlspecialchars($me['correo'])?></small></div>
      </div>
      <div class="profile-grid">
        <div><small>Teléfono</small><b><?=htmlspecialchars($me['telefono'] ?: 'No registrado')?></b></div>
        <div><small>Tipo de cuenta</small><b><?= $esConductor?'Conductor':'Pasajero' ?></b></div>
        <?php if($esConductor): ?>
        <div><small>Vehículo</small><b><?=htmlspecialchars($me['tipo_vehiculo'] ?: 'No registrado')?></b></div>
        <div><small>Placa</small><b><?=htmlspecialchars($me['placa'] ?: 'No registrada')?></b></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div id="surveysPanel" class="side-modal hidden">
    <div class="side-panel">
      <button class="side-close" data-side-close>×</button>
      <div class="side-icon">⭐</div>
      <span class="tag">OPINIONES</span>
      <h2>Encuestas de pasajeros</h2>
      <div id="surveysContent" class="history-content"><div class="history-loading">Cargando encuestas…</div></div>
    </div>
  </div>
  <div id="historyPanel" class="side-modal hidden">
    <div class="side-panel">
      <button class="side-close" data-side-close>×</button>
      <div class="side-icon">🧾</div>
      <span class="tag">HISTORIAL</span>
      <h2><?= $esConductor?'Mis rutas':'Mis viajes' ?></h2>
      <div id="historyContent" class="history-content"><div class="history-loading">Cargando historial…</div></div>
    </div>
  </div>
</main>
<script>window.RIDE_CONFIG={role:"<?= $esConductor?'conductor':'pasajero' ?>",userId:<?= $uid ?>,foto:"<?=htmlspecialchars($me['foto_perfil']??'',ENT_QUOTES)?>",pico:<?= (int)$me['pico_placa']?>,vehiculo:"<?=htmlspecialchars($me['tipo_vehiculo']??'',ENT_QUOTES)?>"};</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script src="assets/app.js?v=20260915c"></script>
</body></html>