<?php
require_once __DIR__ . '/config_email.php';

function smtp_read($socket) {
    $data = '';
    while (($line = fgets($socket, 515)) !== false) {
        $data .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') break;
    }
    return $data;
}

function smtp_expect($socket, $codes) {
    $response = smtp_read($socket);
    $code = (int)substr(trim($response), 0, 3);
    if (!in_array($code, (array)$codes, true)) {
        throw new Exception('SMTP respondió con código ' . $code . '.');
    }
    return $response;
}

function smtp_command($socket, $command, $codes) {
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $codes);
}

function enviarCodigoPorCorreo($destino, $nombre, $codigo) {
    if (SMTP_USER === 'TU_CORREO_GMAIL@gmail.com' || SMTP_APP_PASSWORD === 'TU_CONTRASENA_DE_APLICACION') {
        throw new Exception('Configura primero config_email.php con tu Gmail y una contraseña de aplicación.');
    }

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false
        ]
    ]);

    $socket = stream_socket_client(
        'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        throw new Exception('No se pudo conectar con el servidor de correo: ' . $errstr);
    }

    try {
        smtp_expect($socket, 220);
        smtp_command($socket, 'EHLO localhost', 250);
        smtp_command($socket, 'AUTH LOGIN', 334);
        smtp_command($socket, base64_encode(SMTP_USER), 334);
        smtp_command($socket, base64_encode(SMTP_APP_PASSWORD), 235);
        smtp_command($socket, 'MAIL FROM:<' . SMTP_USER . '>', 250);
        smtp_command($socket, 'RCPT TO:<' . $destino . '>', 250);
        smtp_command($socket, 'DATA', 354);

        $asunto = 'Tu código de recuperación - RideShare';
        $html = '<!doctype html><html><body style="margin:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033;">'
              . '<div style="max-width:600px;margin:30px auto;background:#fff;border-radius:18px;padding:35px;box-shadow:0 8px 30px rgba(0,0,0,.08);">'
              . '<div style="font-size:22px;font-weight:800;letter-spacing:1px;">RIDESHARE</div>'
              . '<h1 style="font-size:26px;margin-bottom:8px;">Recuperación de contraseña</h1>'
              . '<p>Hola <strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>, recibimos una solicitud para cambiar la contraseña de tu cuenta.</p>'
              . '<p style="margin-top:28px;">Tu código de verificación es:</p>'
              . '<div style="font-size:38px;letter-spacing:9px;font-weight:800;text-align:center;background:#f1f4f8;border-radius:14px;padding:18px;margin:18px 0;">' . $codigo . '</div>'
              . '<p>Este código es válido durante <strong>10 minutos</strong>. Si no solicitaste este cambio, puedes ignorar este correo.</p>'
              . '<p style="color:#7a8494;font-size:13px;margin-top:30px;">Este mensaje fue enviado automáticamente por RideShare.</p>'
              . '</div></body></html>';

        $headers = 'From: ' . MAIL_FROM_NAME . ' <' . SMTP_USER . ">\r\n"
                 . 'To: <' . $destino . ">\r\n"
                 . 'Subject: ' . '=?UTF-8?B?' . base64_encode($asunto) . "?=\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "Content-Transfer-Encoding: 8bit\r\n\r\n";

        $message = $headers . $html;
        $message = preg_replace('/\r?\n\./', "\r\n..", $message);
        fwrite($socket, $message . "\r\n.\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
    } finally {
        fclose($socket);
    }
}
