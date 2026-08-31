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
    <title>RideShare | <?= $esConductor ? 'Panel del conductor' : 'Inicio' ?></title>
    <link rel="stylesheet" href="assets/style.css">
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
                <div class="dash-card">
                    <span>🟢</span>
                    <h3>Estado</h3>
                    <p>Activa tu disponibilidad para comenzar a recibir solicitudes.</p>
                </div>
                <div class="dash-card">
                    <span>📩</span>
                    <h3>Solicitudes</h3>
                    <p>Aquí aparecerán los usuarios que soliciten un viaje.</p>
                </div>
                <div class="dash-card">
                    <span>🚗</span>
                    <h3>Mis viajes</h3>
                    <p>Gestiona los viajes que tienes asignados.</p>
                </div>
                <div class="dash-card">
                    <span>🧾</span>
                    <h3>Historial</h3>
                    <p>Consulta los viajes que has realizado.</p>
                </div>
            </div>
        <?php else: ?>
            <span class="tag">PANEL PRINCIPAL</span>
            <h1>Hola, <?= htmlspecialchars($_SESSION["nombre"]) ?> 👋</h1>
            <p>Bienvenido a RideShare. Desde aquí podrás solicitar y consultar tus viajes.</p>
            <p><small>Ejemplo del sistema: <?= htmlspecialchars($viajeDemo->descripcion()) ?>. Tarifa estimada para 5 km: $<?= number_format($tarifaEjemplo, 0, ",", ".") ?>.</small></p>

            <div class="dashboard-grid">
                <div class="dash-card">
                    <span>📍</span>
                    <h3>Solicitar viaje</h3>
                    <p>Busca un conductor disponible.</p>
                </div>
                <div class="dash-card">
                    <span>🚗</span>
                    <h3>Conductores</h3>
                    <p>Consulta los conductores disponibles.</p>
                </div>
                <div class="dash-card">
                    <span>🧾</span>
                    <h3>Historial</h3>
                    <p>Aquí aparecerán tus viajes realizados.</p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
