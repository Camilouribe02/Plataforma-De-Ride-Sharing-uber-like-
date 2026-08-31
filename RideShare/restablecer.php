<?php
session_start();
require_once "conexion.php";
require_once "patrones/Patrones.php";

if (empty($_SESSION['recuperacion_verificada']) || empty($_SESSION['recuperacion_id'])) {
    header('Location: recuperar.php');
    exit;
}

$id = (int)$_SESSION['recuperacion_id'];
$mensaje = "";
$tipo = "";

$stmt = $conexion->prepare("SELECT id, codigo_expira FROM usuarios WHERE id = ? AND estado = 'activo' LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$estadoCuenta = $usuario ? new CuentaActiva() : new CuentaInactiva();

if (!$usuario || empty($usuario['codigo_expira']) || strtotime($usuario['codigo_expira']) < time()) {
    unset($_SESSION['recuperacion_id'], $_SESSION['recuperacion_correo'], $_SESSION['recuperacion_verificada']);
    $mensaje = "La verificación expiró. Solicita un código nuevo.";
    $tipo = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener mínimo 8 caracteres.";
        $tipo = "error";
    } elseif ($password !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo = "error";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare(
            "UPDATE usuarios SET password = ?, codigo_recuperacion = NULL, codigo_expira = NULL, intentos_codigo = 0 WHERE id = ?"
        );
        $actualizar->bind_param("si", $hash, $id);

        if ($actualizar->execute()) {
            unset($_SESSION['recuperacion_id'], $_SESSION['recuperacion_correo'], $_SESSION['recuperacion_verificada']);
            header('Location: index.php?reset=1');
            exit;
        }
        $mensaje = "No fue posible actualizar la contraseña.";
        $tipo = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare | Nueva contraseña</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="simple-page">
    <div class="simple-card">
        <div class="mobile-brand">RIDESHARE</div>
        <h2>Nueva <span>contraseña</span></h2>
        <p class="subtitle">Tu correo fue verificado. Ahora crea una nueva contraseña segura.</p>

        <?php if ($mensaje): ?>
            <div class="alert <?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($usuario): ?>
            <form method="POST">
                <label for="password">Nueva contraseña</label>
                <input class="plain-input" id="password" type="password" name="password" minlength="8" placeholder="Mínimo 8 caracteres" required>

                <label for="confirmar">Confirmar contraseña</label>
                <input class="plain-input" id="confirmar" type="password" name="confirmar" minlength="8" placeholder="Repite tu contraseña" required>

                <button class="btn-primary" type="submit">Cambiar contraseña <span>→</span></button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
