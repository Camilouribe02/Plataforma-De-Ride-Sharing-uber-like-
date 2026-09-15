<?php
session_start();
if (!isset($_SESSION["usuario_id"])) { header("Location: index.php"); exit; }
$nombre = $_SESSION["nombre"] ?? "Usuario";
$rol = strtolower($_SESSION["rol"] ?? "pasajero");
$rolTexto = $rol === "conductor" ? "conductor" : "usuario";
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>RideShare | Bienvenida</title><link rel="stylesheet" href="estiloslogin/style.css?v=20260912">
<style>
body{margin:0;background:#f7f7f7;font-family:Arial,sans-serif}.welcome{min-height:100vh;display:grid;place-items:center;padding:25px}.card{background:#fff;border-radius:24px;padding:45px 35px;text-align:center;box-shadow:0 15px 45px #0001;max-width:520px;width:100%}.logo{font-weight:900;letter-spacing:3px;color:#e50914}.icon{font-size:55px;margin:20px 0 5px}.card h1{margin:10px 0;font-size:32px}.card p{color:#666;font-size:17px}.role{font-weight:bold;color:#e50914;text-transform:capitalize}.btn{display:inline-block;margin-top:25px;padding:14px 30px;border-radius:10px;background:#e50914;color:white;text-decoration:none;font-weight:bold}
</style></head><body><main class="welcome"><section class="card"><div class="logo">RIDESHARE</div><div class="icon">✓</div><h1>¡Bienvenido, <?=htmlspecialchars($nombre)?>!</h1><p>Has iniciado sesión correctamente como <span class="role"><?=htmlspecialchars($rolTexto)?></span>.</p><a class="btn" href="logout.php">Cerrar sesión</a></section></main></body></html>