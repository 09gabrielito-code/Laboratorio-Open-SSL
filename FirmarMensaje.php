<?php
// 1. Configuración inteligente (evita el error de Wamp)
$configArgs = array("private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA);
$fallbackCnf = 'C:\wamp64\bin\apache\apache2.4.58\conf\openssl.cnf';
if (!file_exists($fallbackCnf)) {
    $posiblesRutas = glob('C:\wamp64\bin\apache\*\conf\openssl.cnf');
    if (!empty($posiblesRutas)) $fallbackCnf = $posiblesRutas[0];
}
if (file_exists($fallbackCnf)) {
    $configArgs['config'] = $fallbackCnf;
}

$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : "Mensaje original de la UTP";

// 2. Generar llaves
$privateKey = openssl_pkey_new($configArgs);
openssl_pkey_export($privateKey, $privateKeyPem, null, $configArgs);
$publicKeyDetails = openssl_pkey_get_details($privateKey);
$publicKeyPem = $publicKeyDetails['key'];

// 3. Firmar el mensaje original
openssl_sign($mensaje, $firma, $privateKey, OPENSSL_ALGO_SHA256);
$firmaBase64 = base64_encode($firma);

// 4. Verificar el mensaje original
$pubKeyResource = openssl_pkey_get_public($publicKeyPem);
$firmaBinaria = base64_decode($firmaBase64);
$resultadoOriginal = openssl_verify($mensaje, $firmaBinaria, $pubKeyResource, OPENSSL_ALGO_SHA256);

// 5. Simular alteración del mensaje
$mensajeAlterado = $mensaje . " (modificado por un hacker)";
$resultadoAlterado = openssl_verify($mensajeAlterado, $firmaBinaria, $pubKeyResource, OPENSSL_ALGO_SHA256);
?>
<!DOCTYPE html>
<html>
<head><title>Firma y Verificación - Laboratorio</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Verificación de Integridad con OpenSSL</h2>
    <form method="POST">
        <label>Mensaje a firmar:</label><br>
        <input type="text" name="mensaje" value="<?php echo htmlspecialchars($mensaje); ?>" style="width: 350px; padding: 5px;"><br><br>
        <input type="submit" value="Firmar y Verificar">
    </form>
    <hr>
    <p><strong>Firma Generada (Base64):</strong> <br><textarea rows="3" cols="60"><?php echo $firmaBase64; ?></textarea></p>
    
    <h3>Prueba 1: Verificar mensaje original</h3>
    <?php 
        if ($resultadoOriginal === 1) {
            echo "<p style='color:green; font-weight:bold;'>☑ Firma válida. El mensaje no fue alterado.</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ Error: Firma inválida.</p>";
        }
    ?>

    <h3>Prueba 2: Verificar mensaje ALTERADO</h3>
    <p>Mensaje alterado: <em><?php echo htmlspecialchars($mensajeAlterado); ?></em></p>
    <?php 
        if ($resultadoAlterado === 1) {
            echo "<p style='color:green; font-weight:bold;'>Firma válida.</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ ¡Alerta! La firma no coincide. El mensaje fue alterado maliciosamente.</p>";
        }
    ?>
</body>
</html>