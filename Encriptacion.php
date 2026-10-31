<?php
$texto = isset($_POST['texto']) ? $_POST['texto'] : "Mensaje secreto de prueba";
$clave = "clave123456789012"; // 16 caracteres para AES-128

// Generar un Vector de Inicialización (IV) aleatorio
$iv_length = openssl_cipher_iv_length("AES-128-CBC");
$iv = openssl_random_pseudo_bytes($iv_length);

// Cifrar
$cifrado = openssl_encrypt($texto, "AES-128-CBC", $clave, 0, $iv);
$cifradoBase64 = base64_encode($cifrado);
$ivBase64 = base64_encode($iv);

// Descifrar
$descifrado = openssl_decrypt($cifrado, "AES-128-CBC", $clave, 0, $iv);
?>
<!DOCTYPE html>
<html>
<head><title>Encriptación OpenSSL</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Cifrado Simétrico (AES-128-CBC)</h2>
    <form method="POST">
        <label>Texto a cifrar:</label><br>
        <input type="text" name="texto" value="<?php echo htmlspecialchars($texto); ?>" style="width: 300px; padding: 5px;"><br><br>
        <input type="submit" value="Cifrar y Descifrar">
    </form>
    <hr>
    <p><strong>Texto Original:</strong> <?php echo htmlspecialchars($texto); ?></p>
    <p><strong>Texto Cifrado (Base64):</strong> <?php echo $cifradoBase64; ?></p>
    <p><strong>IV utilizado (Base64):</strong> <?php echo $ivBase64; ?></p>
    <p><strong>Texto Descifrado:</strong> <?php echo htmlspecialchars($descifrado); ?></p>
</body>
</html>