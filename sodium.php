<?php
// 1. Definimos el mensaje y generamos una llave y un nonce (número único)
$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : "Datos sensibles de la UTP protegidos con Libsodium";
$llave = sodium_crypto_secretbox_keygen(); // Llave secreta de 32 bytes
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // Vector único

// 2. Ciframos el mensaje (Garantiza Confidencialidad e Integridad)
$cifrado = sodium_crypto_secretbox($mensaje, $nonce, $llave);

// Juntamos el nonce y el cifrado en un solo bloque y lo pasamos a Base64
$cifradoBase64 = base64_encode($nonce . $cifrado);

// 3. Desciframos para comprobar
$decodificado = base64_decode($cifradoBase64);
$nonceExtraido = mb_substr($decodificado, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
$cifradoExtraido = mb_substr($decodificado, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
$descifrado = sodium_crypto_secretbox_open($cifradoExtraido, $nonceExtraido, $llave);
?>
<!DOCTYPE html>
<html>
<head><title>Cifrado con Libsodium</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Cifrado Autenticado con Libsodium (Sodium)</h2>
    <p>Esta librería es el estándar moderno recomendado por OWASP.</p>
    
    <form method="POST" style="background: #f4f4f9; padding: 15px; border: 1px solid #ccc;">
        <label>Mensaje a cifrar con Sodium:</label><br>
        <input type="text" name="mensaje" value="<?php echo htmlspecialchars($mensaje); ?>" style="width: 400px; padding: 5px;"><br><br>
        <input type="submit" value="Cifrar con Sodium" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer;">
    </form>
    <hr>
    
    <h3>Resultados:</h3>
    <p><strong>Mensaje Original:</strong> <?php echo htmlspecialchars($mensaje); ?></p>
    <p><strong>Texto Cifrado (Base64):</strong> <br><textarea rows="3" cols="70"><?php echo $cifradoBase64; ?></textarea></p>
    <p><strong>Texto Descifrado:</strong> <?php echo htmlspecialchars($descifrado); ?></p>
    
    <p style="color: green; font-weight: bold;">✅ Libsodium cifra y garantiza la integridad en un solo paso.</p>
</body>
</html>