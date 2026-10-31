<?php
// 1. Detección automática de la ruta de openssl.cnf en Wamp64
$fallbackCnf = 'C:\wamp64\bin\apache\apache2.4.58\conf\openssl.cnf'; 
if (!file_exists($fallbackCnf)) {
    // Si no está en la ruta por defecto, la busca dinámicamente
    $posiblesRutas = glob('C:\wamp64\bin\apache\*\conf\openssl.cnf');
    if (!empty($posiblesRutas)) {
        $fallbackCnf = $posiblesRutas[0];
    }
}

$configArgs = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
if (file_exists($fallbackCnf)) {
    $configArgs['config'] = $fallbackCnf;
}

// 2. Generar el par de llaves
$privateKey = openssl_pkey_new($configArgs);
if (!$privateKey) {
    die("Error al generar llave privada: " . openssl_error_string());
}

// 3. Exportar las llaves para mostrarlas o guardarlas
openssl_pkey_export($privateKey, $privateKeyPem, null, $configArgs);
$publicKeyDetails = openssl_pkey_get_details($privateKey);
$publicKeyPem = $publicKeyDetails['key'];

// 4. Formulario para recibir los datos (Requisito E.2.2 del PDF)
$mensajeOriginal = "Mensaje por defecto";
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['mensaje'])) {
    $mensajeOriginal = $_POST['mensaje'];
}

// 5. Firmar el mensaje
$firma = '';
if (!openssl_sign($mensajeOriginal, $firma, $privateKey, OPENSSL_ALGO_SHA256)) {
    die("Error al firmar: " . openssl_error_string());
}

// Convertir firma a Base64 para que se pueda ver en texto
$firmaBase64 = base64_encode($firma);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laboratorio OpenSSL - Firma Digital</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9;}
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        textarea { width: 100%; height: 100px; margin-bottom: 10px; }
        input[type="submit"] { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        pre { background: #eee; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container">
    <h2>Firma Digital con OpenSSL</h2>
    
    <form method="POST">
        <label>Escribe un mensaje para firmar:</label><br>
        <textarea name="mensaje" required><?php echo htmlspecialchars($mensajeOriginal); ?></textarea><br>
        <input type="submit" value="Generar Firma Digital">
    </form>

    <hr>

    <h3>Resultados:</h3>
    <p><strong>Mensaje original:</strong> <?php echo htmlspecialchars($mensajeOriginal); ?></p>
    
    <p><strong>Firma Generada (Base64):</strong></p>
    <pre><?php echo $firmaBase64; ?></pre>

    <p><strong>Llave Pública generada:</strong></p>
    <pre><?php echo $publicKeyPem; ?></pre>
    
    <?php if(isset($configArgs['config'])): ?>
        <p style="font-size: 12px; color: green;">✅ Usando archivo de configuración: <?php echo $configArgs['config']; ?></p>
    <?php else: ?>
        <p style="font-size: 12px; color: red;">⚠️ No se encontró openssl.cnf, usando configuración por defecto.</p>
    <?php endif; ?>
</div>
</body>
</html>