<?php
$configArgs = array("private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA);
$data = "Contrato de confidencialidad UTP";

// Generar llave
$privateKey = openssl_pkey_new($configArgs);
openssl_pkey_export($privateKey, $privateKeyPem, null, $configArgs);
$publicKeyDetails = openssl_pkey_get_details($privateKey);
$publicKeyPem = $publicKeyDetails['key'];

// Firmar
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$signatureBase64 = base64_encode($signature);

// Verificar
$pubKeyResource = openssl_pkey_get_public($publicKeyPem);
$ok = openssl_verify($data, base64_decode($signatureBase64), $pubKeyResource, OPENSSL_ALGO_SHA256);
?>
<!DOCTYPE html>
<html>
<head><title>Firma Digital Básica</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Firma Digital Básica</h2>
    <p><strong>Documento:</strong> <?php echo $data; ?></p>
    <p><strong>Firma generada:</strong> <?php echo $signatureBase64; ?></p>
    
    <?php if($ok === 1): ?>
        <h3 style="color: green;">☑ Firma válida. El documento es auténtico.</h3>
    <?php else: ?>
        <h3 style="color: red;">❌ Error en la firma.</h3>
    <?php endif; ?>
</body>
</html>