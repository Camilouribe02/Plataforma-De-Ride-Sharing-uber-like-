<?php
session_start();
require_once "conexion.php";
require_once "patrones/Patrones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$correo = trim($_POST["correo"] ?? "");
$password = $_POST["password"] ?? "";

if ($correo === "" || $password === "") {
    header("Location: index.php?error=1");
    exit;
}

$auth = new AutenticacionFacade($conexion);
$usuario = $auth->iniciarSesion($correo, $password);

if ($usuario) {
    (new CrearSesionCommand($usuario))->ejecutar();
    header("Location: dashboard.php");
    exit;
}

header("Location: index.php?error=1");
exit;
?>
