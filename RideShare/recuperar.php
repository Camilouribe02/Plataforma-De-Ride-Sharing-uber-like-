<?php
session_start();
require_once "conexion.php";
require_once "email.php";
require_once "patrones/Patrones.php";

$mensaje = "";
$tipo = "";
$correoMostrar = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['accion'])) {
    $correo = strtolower(trim($_POST["correo"] ?? ""));

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Ingresa un correo electrónico válido.";
        $tipo = "error";
    } else {
        $stmt = $conexion->prepare("SELECT id, nombre, correo FROM usuarios WHERE correo = ? AND estado = 'activo' LIMIT 1");
        
        if ($stmt) {
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();

            if ($usuario) {
                $codigo = (string)random_int(100000, 999999);
                $codigoHash = hash('sha256', $codigo);
                $expira = date('Y-m-d H:i:s', time() + 600);

                // Invalidamos códigos anteriores para esa cuenta.
                $actualizar = $conexion->prepare(
                    "UPDATE usuarios SET codigo_recuperacion = ?, codigo_expira = ?, intentos_codigo = 0 WHERE id = ?"
                );

                if ($actualizar) {
                    $actualizar->bind_param("ssi", $codigoHash, $expira, $usuario["id"]);

                    if ($actualizar->execute()) {
                        try {
                            $eventos = new EventoRecuperacion();
                            $eventos->suscribir(new RegistroEventoObserver());
                            $correoService = new SMTPAdapter();
                            $correoService->enviarCodigo($usuario['correo'], $usuario['nombre'], $codigo);
                            $eventos->notificar("codigo_recuperacion_enviado", ["usuario_id" => $usuario["id"]]);
                            $_SESSION['recuperacion_id'] = (int)$usuario['id'];
                            $_SESSION['recuperacion_correo'] = $usuario['correo'];
                            $correoMostrar = $usuario['correo'];
                            header('Location: recuperar.php?verificar=1');
                            exit;
                        } catch (Exception $e) {
                            $limpiar = $conexion->prepare("UPDATE usuarios SET codigo_recuperacion = NULL, codigo_expira = NULL, intentos_codigo = 0 WHERE id = ?");
                            if ($limpiar) {
                                $limpiar->bind_param("i", $usuario['id']);
                                $limpiar->execute();
                            }
                            $mensaje = "No fue posible enviar el correo. Revisa la configuración de config_email.php.";
                            $tipo = "error";
                        }
                    } else {
                        $mensaje = "No fue posible generar el código. Inténtalo nuevamente.";
                        $tipo = "error";
                    }
                } else {
                    $mensaje = "Error en la base de datos: Verifica que existan las columnas 'codigo_recuperacion', 'codigo_expira' e 'intentos_codigo' en la tabla 'usuarios'.";
                    $tipo = "error";
                }
            } else {
                // Mensaje genérico para no revelar cuentas existentes.
                $mensaje = "Si el correo está registrado, recibirás un código de recuperación.";
                $tipo = "success";
            }
        } else {
            $mensaje = "Error al consultar la base de datos.";
            $tipo = "error";
        }
    }
}

$verificar = isset($_GET['verificar']) && isset($_SESSION['recuperacion_id']);
if ($verificar) {
    $correoMostrar = $_SESSION['recuperacion_correo'] ?? '';
    $mensaje = $mensaje ?: "Te enviamos un código de 6 dígitos a tu correo.";
    $tipo = $tipo ?: "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'verificar') {
    $codigo = trim($_POST['codigo'] ?? '');
    $id = (int)($_SESSION['recuperacion_id'] ?? 0);

    if ($id <= 0 || !preg_match('/^\d{6}$/', $codigo)) {
        $mensaje = "Ingresa un código de 6 dígitos válido.";
        $tipo = "error";
        $verificar = true;
    } else {
        $stmt = $conexion->prepare("SELECT id, codigo_recuperacion, codigo_expira, intentos_codigo FROM usuarios WHERE id = ? AND estado = 'activo' LIMIT 1");
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();

            if (!$usuario || empty($usuario['codigo_recuperacion']) || empty($usuario['codigo_expira']) || strtotime($usuario['codigo_expira']) < time()) {
                $mensaje = "El código expiró. Solicita uno nuevo.";
                $tipo = "error";
                $verificar = true;
            } elseif ((int)$usuario['intentos_codigo'] >= 5) {
                $mensaje = "Has superado el número de intentos. Solicita un código nuevo.";
                $tipo = "error";
                $verificar = true;
            } elseif (!hash_equals($usuario['codigo_recuperacion'], hash('sha256', $codigo))) {
                $nuevoIntento = (int)$usuario['intentos_codigo'] + 1;
                $up = $conexion->prepare("UPDATE usuarios SET intentos_codigo = ? WHERE id = ?");
                if ($up) {
                    $up->bind_param("ii", $nuevoIntento, $id);
                    $up->execute();
                }
                $mensaje = "El código es incorrecto. Revisa tu correo e inténtalo nuevamente.";
                $tipo = "error";
                $verificar = true;
            } else {
                $_SESSION['recuperacion_verificada'] = true;
                header('Location: restablecer.php');
                exit;
            }
        } else {
            $mensaje = "Error al verificar el código en la base de datos.";
            $tipo = "error";
            $verificar = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare | Recuperar contraseña</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="simple-page">
    <div class="simple-card recovery">
        <a class="back" href="index.php">← Volver al inicio</a>
        <div class="mobile-brand">RIDESHARE</div>

        <?php if (!$verificar): ?>
            <h2>Recupera tu <span>contraseña</span></h2>
            <p class="subtitle">Escribe el correo asociado a tu cuenta y te enviaremos un código de verificación.</p>

            <?php if ($mensaje): ?>
                <div class="alert <?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="correo">Correo electrónico</label>
                <input class="plain-input" id="correo" type="email" name="correo" placeholder="ejemplo@gmail.com" required>
                <button class="btn-primary" type="submit">Enviar código <span>→</span></button>
            </form>
        <?php else: ?>
            <h2>Verifica tu <span>correo</span></h2>
            <p class="subtitle">Enviamos un código de 6 dígitos a <strong><?= htmlspecialchars($correoMostrar) ?></strong>.</p>

            <?php if ($mensaje): ?>
                <div class="alert <?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="accion" value="verificar">
                <label for="codigo">Código de verificación</label>
                <input class="plain-input code-input" id="codigo" type="text" name="codigo" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" required>
                <button class="btn-primary" type="submit">Verificar código <span>→</span></button>
            </form>

            <p class="register-text">El código es válido durante <strong>10 minutos</strong>.</p>
            <p class="register-text"><a href="recuperar.php">Solicitar otro código</a></p>
        <?php endif; ?>

        <p class="register-text">¿Recuerdas tu contraseña? <a href="index.php">Inicia sesión</a></p>
    </div>
</div>
</body>
</html>