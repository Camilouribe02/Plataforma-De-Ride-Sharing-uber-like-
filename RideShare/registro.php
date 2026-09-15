<?php
session_start();
require_once "conexion.php";
require_once "patrones/Patrones.php";

$mensaje = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";
    $rolFormulario = $_POST["rol"] ?? "usuario";
    $tipoVehiculo = $_POST["tipo_vehiculo"] ?? "";
    $placa = strtoupper(trim($_POST["placa"] ?? ""));
    $colorVehiculo = trim($_POST["color_vehiculo"] ?? "");
    $fotoNombre = null;

    // En la base de datos el usuario normal se guarda como pasajero.
    $rol = $rolFormulario === "conductor" ? "conductor" : "pasajero";

    if ($nombre === "" || $apellido === "" || $correo === "" || $password === "") {
        $mensaje = "Completa todos los campos obligatorios.";
        $tipo = "error";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Ingresa un correo electrónico válido.";
        $tipo = "error";
    } elseif (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener mínimo 8 caracteres.";
        $tipo = "error";
    } elseif ($password !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo = "error";
    } elseif (!in_array($rolFormulario, ["usuario", "conductor"], true)) {
        $mensaje = "Selecciona un tipo de cuenta válido.";
        $tipo = "error";
    } elseif ($rol === "conductor" && !in_array($tipoVehiculo, ["moto", "carro"], true)) {
        $mensaje = "Si eres conductor, selecciona si tu vehículo es moto o carro.";
        $tipo = "error";
    } elseif ($rol === "conductor" && $placa === "") {
        $mensaje = "Si eres conductor, debes ingresar la placa del vehículo.";
        $tipo = "error";
    } elseif ($rol === "conductor" && !preg_match('/^[A-Z0-9-]{5,10}$/', $placa)) {
        $mensaje = "Ingresa una placa válida.";
        $tipo = "error";
    } else {
        // Foto de perfil opcional. Se guarda con un nombre aleatorio para evitar colisiones.
        if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $foto = $_FILES["foto_perfil"];
            if ($foto["error"] !== UPLOAD_ERR_OK) {
                $mensaje = "No fue posible subir la foto de perfil.";
                $tipo = "error";
            } elseif ($foto["size"] > 3 * 1024 * 1024) {
                $mensaje = "La foto no puede superar los 3 MB.";
                $tipo = "error";
            } elseif (@getimagesize($foto["tmp_name"]) === false) {
                $mensaje = "El archivo seleccionado no es una imagen válida.";
                $tipo = "error";
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($foto["tmp_name"]);
                $permitidos = ["image/jpeg"=>"jpg", "image/png"=>"png", "image/webp"=>"webp"];
                if (!isset($permitidos[$mime])) {
                    $mensaje = "Solo se permiten imágenes JPG, PNG o WEBP.";
                    $tipo = "error";
                } else {
                    $dir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
                    if (!is_dir($dir)) @mkdir($dir, 0755, true);
                    $fotoNombre = "perfil_" . bin2hex(random_bytes(12)) . "." . $permitidos[$mime];
                    if (!move_uploaded_file($foto["tmp_name"], $dir . DIRECTORY_SEPARATOR . $fotoNombre)) {
                        $fotoNombre = null;
                        $mensaje = "No fue posible guardar la foto de perfil.";
                        $tipo = "error";
                    }
                }
            }
        }

        if ($mensaje === "") {
        // Los datos del vehículo solo aplican para conductores.
        if ($rol !== "conductor") {
            $tipoVehiculo = null;
            $placa = null;
            $colorVehiculo = null;
        }

        // Builder + Factory Method: construye el perfil y asigna el tipo de usuario.
        $datos = (new UsuarioBuilder())
            ->nombre($nombre)->apellido($apellido)->correo($correo)->telefono($telefono)
            ->password($password)->tipoVehiculo($tipoVehiculo)->placa($placa)->colorVehiculo($colorVehiculo)->construir();
        $factory = $rol === "conductor" ? new ConductorFactory() : new PasajeroFactory();
        $datos = $factory->prepararRegistro($datos);

        // Abstract Factory: para conductores se crea una familia coherente
        // de objetos del vehículo (vehículo + configuración).
        if ($rol === "conductor") {
            $vehiculoFactory = VehiculoFactoryProvider::obtener($tipoVehiculo);
            $vehiculo = $vehiculoFactory->crearVehiculo($datos);
            $configuracion = $vehiculoFactory->crearConfiguracion($datos);
            $datos = array_merge($datos, $vehiculo->obtenerDatos(), $configuracion->obtenerDatos());
        }

        // Prototype: se crea el perfil base y se obtiene una copia independiente.
        $perfil = new PerfilUsuario($datos);
        $perfilCopia = $perfil->clonar();
        $datos = $perfilCopia->datos;

        $nombre = $datos['nombre']; $apellido = $datos['apellido']; $correo = $datos['correo'];
        $telefono = $datos['telefono']; $tipoVehiculo = $datos['tipo_vehiculo']; $placa = $datos['placa']; $colorVehiculo = $datos['color_vehiculo'] ?? null; $rol = $datos['rol'];

        $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
        $consulta->bind_param("s", $correo);
        $consulta->execute();
        $existe = $consulta->get_result()->fetch_assoc();

        if ($existe) {
            $mensaje = "Ya existe una cuenta con ese correo.";
            $tipo = "error";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (nombre, apellido, correo, telefono, password, rol, tipo_vehiculo, placa, color_vehiculo, foto_perfil)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssssss", $nombre, $apellido, $correo, $telefono, $hash, $rol, $tipoVehiculo, $placa, $colorVehiculo, $fotoNombre);

            if ($stmt->execute()) {
                $mensaje = "Cuenta creada correctamente. Ya puedes iniciar sesión.";
                $tipo = "success";
            } else {
                $mensaje = "No fue posible crear la cuenta.";
                $tipo = "error";
            }
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare | Registrarse</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="simple-page">
    <div class="simple-card">
        <a class="back" href="index.php">← Volver al inicio</a>
        <div class="mobile-brand">RIDESHARE</div>

        <h2>Crear una <span>cuenta</span></h2>
        <p class="subtitle">Regístrate para comenzar a usar RideShare.</p>

        <?php if ($mensaje): ?>
            <div class="alert <?= htmlspecialchars($tipo) ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid-2">
                <div>
                    <label>Nombre</label>
                    <input class="plain-input" type="text" name="nombre" required>
                </div>
                <div>
                    <label>Apellido</label>
                    <input class="plain-input" type="text" name="apellido" required>
                </div>
            </div>

            <label>Correo electrónico</label>
            <input class="plain-input" type="email" name="correo" required>

            <label>Teléfono</label>
            <input class="plain-input" type="text" name="telefono">

            <label>Foto de perfil <span class="hint">Opcional · JPG, PNG o WEBP (máx. 3 MB)</span></label>
            <input class="plain-input" type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg,image/png,image/webp">

            <label>Tipo de cuenta</label>
            <select class="plain-input" name="rol" id="rol" required>
                <option value="usuario" <?= (($_POST["rol"] ?? "usuario") === "usuario") ? "selected" : "" ?>>Usuario</option>
                <option value="conductor" <?= (($_POST["rol"] ?? "") === "conductor") ? "selected" : "" ?>>Conductor</option>
            </select>

            <div id="datos-conductor" style="display: none;">
                <label>Tipo de vehículo</label>
                <select class="plain-input" name="tipo_vehiculo" id="tipo_vehiculo">
                    <option value="">Selecciona una opción</option>
                    <option value="moto" <?= (($_POST["tipo_vehiculo"] ?? "") === "moto") ? "selected" : "" ?>>Moto</option>
                    <option value="carro" <?= (($_POST["tipo_vehiculo"] ?? "") === "carro") ? "selected" : "" ?>>Carro</option>
                </select>

                <label>Placa del vehículo</label>
                <input class="plain-input" type="text" name="placa" id="placa" value="<?= htmlspecialchars($_POST["placa"] ?? "") ?>" placeholder="Ejemplo: ABC123" maxlength="10" style="text-transform: uppercase;">

                <label>Color del vehículo</label>
                <input class="plain-input" type="text" name="color_vehiculo" id="color_vehiculo" value="<?= htmlspecialchars($_POST["color_vehiculo"] ?? "") ?>" placeholder="Ejemplo: Negro, blanco, rojo...">
            </div>

            <label>Contraseña</label>
            <input class="plain-input" type="password" name="password" minlength="8" required>

            <label>Confirmar contraseña</label>
            <input class="plain-input" type="password" name="confirmar" minlength="8" required>

            <button class="btn-primary" type="submit">Crear cuenta →</button>
        </form>

        <script>
            const rol = document.getElementById('rol');
            const datosConductor = document.getElementById('datos-conductor');
            const tipoVehiculo = document.getElementById('tipo_vehiculo');
            const placa = document.getElementById('placa');
            const colorVehiculo = document.getElementById('color_vehiculo');

            function actualizarFormularioConductor() {
                const esConductor = rol.value === 'conductor';
                datosConductor.style.display = esConductor ? 'block' : 'none';
                tipoVehiculo.required = esConductor;
                placa.required = esConductor;
                colorVehiculo.required = esConductor;
                if (!esConductor) {
                    tipoVehiculo.value = '';
                    placa.value = '';
                    colorVehiculo.value = '';
                }
            }

            rol.addEventListener('change', actualizarFormularioConductor);
            placa.addEventListener('input', () => placa.value = placa.value.toUpperCase());
            actualizarFormularioConductor();
        </script>

        <p class="register-text">
            ¿Ya tienes una cuenta?
            <a href="index.php">Inicia sesión</a>
        </p>
    </div>
</div>
</body>
</html>
