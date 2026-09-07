<?php
session_start();
require_once "patrones/Patrones.php";

$acceso = new PanelSeguroProxy(new PanelReal());
if (!$acceso->permitir()) {
    header("Location: index.php");
    exit;
}

$esConductor = isset($_SESSION["rol"]) && $_SESSION["rol"] === "conductor";
$strategy = $esConductor ? new TarifaCarro() : new TarifaMoto();
$tarifaEjemplo = (new CalculadoraTarifa($strategy))->calcular(5);
$viajeDemo = new EquipajeDecorator(new ViajeBasico());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare | <?= $esConductor ? 'Panel del conductor' : 'Solicitar viaje' ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <?php if (!$esConductor): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>
</head>
<body>
<div class="dashboard">
    <nav>
        <div class="brand">RIDESHARE</div>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="welcome">
        <?php if ($esConductor): ?>
            <span class="tag">PANEL DEL CONDUCTOR</span>
            <h1>Hola, <?= htmlspecialchars($_SESSION["nombre"]) ?> 🚗</h1>
            <p>Bienvenido, conductor. Desde aquí podrás gestionar tus viajes y solicitudes.</p>
            <div class="dashboard-grid">
                <div class="dash-card"><span>🟢</span><h3>Estado</h3><p>Activa tu disponibilidad para comenzar a recibir solicitudes.</p></div>
                <div class="dash-card"><span>📩</span><h3>Solicitudes</h3><p>Aquí aparecerán los usuarios que soliciten un viaje.</p></div>
                <div class="dash-card"><span>🚗</span><h3>Mis viajes</h3><p>Gestiona los viajes que tienes asignados.</p></div>
                <div class="dash-card"><span>🧾</span><h3>Historial</h3><p>Consulta los viajes que has realizado.</p></div>
            </div>
        <?php else: ?>
            <div class="ride-header">
                <div>
                    <span class="tag">SOLICITAR UN VIAJE</span>
                    <h1>Hola, <?= htmlspecialchars($_SESSION["nombre"]) ?> 👋</h1>
                    <p>Confirma tu ubicación, selecciona el destino y el tipo de vehículo que necesitas.</p>
                </div>
                <div class="secure-badge">📍 Ubicación segura</div>
            </div>

            <div class="ride-layout">
                <aside class="ride-panel">
                    <div class="step-title"><span>1</span><div><h3>¿Desde dónde sales?</h3><small>Usamos tu ubicación actual</small></div></div>
                    <div class="location-box current-location-box">
                        <div class="location-icon">●</div>
                        <div><b id="currentLocationText">Obteniendo tu ubicación...</b><small id="currentLocationStatus">Debes permitir el acceso a tu ubicación para continuar.</small></div>
                        <button type="button" id="refreshLocation" class="icon-btn" title="Actualizar ubicación">↻</button>
                    </div>

                    <div class="step-title"><span>2</span><div><h3>¿A dónde vas?</h3><small>Solo destinos dentro de Santander, Colombia</small></div></div>
                    <div class="destination-wrapper">
                        <div class="destination-input">
                            <span>⌕</span>
                            <input type="text" id="destinationInput" placeholder="Busca un lugar en Santander" autocomplete="off" disabled>
                        </div>
                        <div id="destinationResults" class="destination-results"></div>
                    </div>
                    <div id="selectedDestination" class="selected-destination hidden"></div>

                    <div class="step-title"><span>3</span><div><h3>Elige tu vehículo</h3><small>Selecciona la opción que prefieras</small></div></div>
                    <div class="vehicle-options" id="vehicleOptions">
                        <button type="button" class="vehicle-card active" data-vehicle="Moto" data-price="Moto">
                            <span class="vehicle-emoji">🏍️</span><span><b>Moto</b><small>Más rápida y económica</small></span><strong id="motoPrice">Seleccionar</strong>
                        </button>
                        <button type="button" class="vehicle-card" data-vehicle="Carro" data-price="Carro">
                            <span class="vehicle-emoji">🚗</span><span><b>Carro</b><small>Más cómodo para tu viaje</small></span><strong id="carPrice">Seleccionar</strong>
                        </button>
                    </div>

                    <div class="selected-vehicle-box hidden" id="selectedVehicleBox">
                        <div><span id="selectedVehicleIcon">🏍️</span><div><small>Vehículo seleccionado</small><b id="selectedVehicleName">Moto</b></div></div>
                        <button type="button" id="changeVehicle">Cambiar</button>
                    </div>

                    <div class="trip-summary" id="tripSummary">
                        <div><span>Distancia</span><b id="distanceText">-- km</b></div>
                        <div><span>Tiempo estimado</span><b id="durationText">-- min</b></div>
                        <div class="fare-total"><span>Tarifa estimada</span><b id="fareText">Selecciona una ruta</b><small id="fareDetail">El valor cambia según la distancia y el vehículo.</small></div>
                    </div>
                    <button type="button" id="requestRide" class="request-ride" disabled>Completa la ubicación para solicitar</button>
                    <p class="location-warning" id="locationWarning">📍 La ubicación actual es necesaria para solicitar un viaje.</p>
                </aside>
                <div class="map-container">
                    <div id="map"></div>
                    <div class="map-overlay"><span class="pulse-dot"></span> Ubicación actual</div><div class="map-title-card"><b>Tu ruta</b><small>Santander, Colombia</small></div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if (!$esConductor): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/app.js"></script>
<?php endif; ?>
</body>
</html>
