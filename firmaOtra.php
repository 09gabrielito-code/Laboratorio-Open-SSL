<?php
// 1. BLOQUE INTELIGENTE PARA WAMP
$configArgs = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'digest_alg' => 'sha256'
);
$fallbackCnf = 'C:\wamp64\bin\apache\apache2.4.58\conf\openssl.cnf';
if (!file_exists($fallbackCnf)) {
    $posiblesRutas = glob('C:\wamp64\bin\apache\*\conf\openssl.cnf');
    if (!empty($posiblesRutas)) $fallbackCnf = $posiblesRutas[0];
}
if (file_exists($fallbackCnf)) {
    $configArgs['config'] = $fallbackCnf;
}

// 2. Recibir datos del formulario (o valores por defecto)
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : "estudiante.utp.ac.pa";
$correo = isset($_POST['correo']) ? $_POST['correo'] : "estudiante@utp.ac.pa";
$organizacion = isset($_POST['organizacion']) ? $_POST['organizacion'] : "UTP";

$dn = array(
    "countryName" => "PA",
    "stateOrProvinceName" => "Panama",
    "localityName" => "Ciudad de Panama",
    "organizationName" => $organizacion,
    "organizationalUnitName" => "Desarrollo Web VII",
    "commonName" => $nombre,
    "emailAddress" => $correo
);

// 3. Generar llave y CSR
$privkey = openssl_pkey_new($configArgs);
$csr = openssl_csr_new($dn, $privkey, $configArgs);

// 4. Exportar resultados
openssl_csr_export($csr, $csrOut);
openssl_pkey_export($privkey, $privKeyOut, null, $configArgs);
?>
<!DOCTYPE html>
<html>
<head><title>Generación de CSR con Formulario</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>Generación de Certificado (CSR) - UTP</h2>
    
    <form method="POST" style="background: #eee; padding: 15px; border-radius: 5px;">
        <label>Nombre Común (Common Name):</label><br>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" style="width: 300px; padding: 5px;"><br><br>
        
        <label>Correo Electrónico:</label><br>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>" style="width: 300px; padding: 5px;"><br><br>

        <label>Organización:</label><br>
        <input type="text" name="organizacion" value="<?php echo htmlspecialchars($organizacion); ?>" style="width: 300px; padding: 5px;"><br><br>
        
        <input type="submit" value="Generar Certificado CSR" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
    </form>
    
    <hr>
    <h3>Datos del Certificado Generado (DN):</h3>
    <ul>
        <?php foreach($dn as $key => $val) echo "<li><strong>$key:</strong> " . htmlspecialchars($val) . "</li>"; ?>
    </ul>
    
    <h3>Solicitud de Certificado (CSR):</h3>
    <textarea rows="8" cols="80" style="width: 100%;"><?php echo $csrOut; ?></textarea>

    <h3>Llave Privada Generada:</h3>
    <textarea rows="8" cols="80" style="width: 100%;"><?php echo $privKeyOut; ?></textarea>
</body>
</html>