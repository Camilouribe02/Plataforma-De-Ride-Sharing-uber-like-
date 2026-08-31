<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare | Iniciar sesión</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth-page">

    <section class="hero">
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <span class="brand">RIDESHARE</span>

            <h1>Viaja con estilo,<br><span>llega a tu destino.</span></h1>

            <p>Una plataforma de viajes compartidos segura, rápida y confiable.</p>

            <div class="features">
                <div class="feature">
                    <strong>✓</strong>
                    <div><b>Seguro</b><small>Conductores verificados.</small></div>
                </div>
                <div class="feature">
                    <strong>↯</strong>
                    <div><b>Rápido</b><small>Encuentra un viaje en segundos.</small></div>
                </div>
                <div class="feature">
                    <strong>★</strong>
                    <div><b>Confiable</b><small>Viajes y calificaciones.</small></div>
                </div>
            </div>
        </div>
    </section>

    <section class="login-side">
        <div class="top-actions">
            <span>ES</span>
            <button type="button" id="themeBtn" aria-label="Cambiar tema">☼</button>
        </div>

        <div class="login-card">
            <div class="mobile-brand">RIDESHARE</div>

            <h2>Bienvenido <span>de nuevo</span></h2>
            <p class="subtitle">Inicia sesión para continuar</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert error">Correo o contraseña incorrectos.</div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <div class="alert success">Sesión cerrada correctamente.</div>
            <?php endif; ?>

            <?php if (isset($_GET['reset'])): ?>
                <div class="alert success">Contraseña actualizada correctamente. Ya puedes iniciar sesión.</div>
            <?php endif; ?>

            <form action="login.php" method="POST" id="loginForm">
                <label for="correo">Correo electrónico</label>
                <div class="input-box">
                    <span>✉</span>
                    <input type="email" id="correo" name="correo"
                           placeholder="ejemplo@correo.com" required>
                </div>

                <label for="password">Contraseña</label>
                <div class="input-box">
                    <span>▣</span>
                    <input type="password" id="password" name="password"
                           placeholder="Ingresa tu contraseña" required>
                    <button type="button" class="eye" onclick="togglePassword()">◉</button>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="recordarme">
                        <span>Recordarme</span>
                    </label>
                    <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
                </div>

                <button class="btn-primary" type="submit">
                    Iniciar sesión <span>→</span>
                </button>
            </form>

            <div class="register-text">
                ¿No tienes una cuenta?
                <a href="registro.php">Regístrate aquí</a>
            </div>
        </div>

        <footer>© 2026 RideShare. Todos los derechos reservados.</footer>
    </section>
</main>

<script src="assets/app.js"></script>
</body>
</html>
