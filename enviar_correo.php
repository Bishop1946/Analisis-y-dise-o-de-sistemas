<?php
// Dirección a donde se enviará la información del formulario
$destinatario = "servicioalcliente@cygllano.com"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener los valores del formulario
    $nombre  = trim($_POST["nombre"] ?? '');
    $correo  = trim($_POST["correo"] ?? '');
    $asunto  = trim($_POST["asunto"] ?? '');
    $mensaje = trim($_POST["mensaje"] ?? '');

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($correo) || empty($asunto) || empty($mensaje)) {
        echo "<h2>Por favor, completa todos los campos.</h2>";
        exit;
    }

    // Validar formato del correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<h2>El correo electrónico no es válido.</h2>";
        exit;
    }

    /* ---------------------------------------------------
       VALIDACIÓN DE GOOGLE RECAPTCHA (PASO OBLIGATORIO)
    ----------------------------------------------------- */

    $recaptcha_secret = "6LeC_BcsAAAAAKQQvBrmkuD-0ApAsMphJLO2jcI7"; // <-- coloca tu clave secreta
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response
    ]));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);

    $respuesta = json_decode(curl_exec($verify), true);

    if (!$respuesta['success']) {
        echo "<h2>❌ Error: Debes verificar el reCAPTCHA.</h2>";
        exit;
    }

    /* ---------------------------------------------------
                CONTINÚA NORMALMENTE EL ENVÍO
    ----------------------------------------------------- */

    // Codificar el asunto
    $asunto_codificado = "=?UTF-8?B?" . base64_encode($asunto) . "?=";

    // Contenido del correo
    $contenido  = "Has recibido un nuevo mensaje desde el formulario de contacto:\n\n";
    $contenido .= "👤 Nombre: $nombre\n";
    $contenido .= "📧 Correo: $correo\n";
    $contenido .= "📝 Asunto: $asunto\n";
    $contenido .= "💬 Mensaje:\n$mensaje\n";

    // Cabeceras
    $headers  = "From: $nombre <$correo>\r\n";
    $headers .= "Reply-To: $correo\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Envío
    if (mail($destinatario, $asunto_codificado, $contenido, $headers)) {
        echo "<h2>✅ Tu mensaje ha sido enviado con éxito. Gracias por contactarnos.</h2>";
    } else {
        echo "<h2>❌ Error al enviar el mensaje. Intenta nuevamente más tarde.</h2>";
    }

} else {
    echo "<h2>Acceso no permitido.</h2>";
}
?>

