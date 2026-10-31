# Seguridad en PHP con OpenSSL y Libsodium

Laboratorio de **Desarrollo Web VII: PHP y MySQL** – Universidad Tecnológica de Panamá (UTP), Facultad de Ingeniería de Sistemas Computacionales. II Semestre 2026. Docente: Ing. Irina Fong.

**Tema:** Programación segura según OWASP – Protege los datos (categoría *Cryptographic Failures*).

| Autor(es) | Cédula | Grupo |
|---|---|---|
| Gabriel Chifundo | 3-760-1894 | _[completar]_ |
| Jorge Sanchez | 8-1034-569 | _[completar]_ |

---

## 1. Descripción

Cuatro scripts con formulario que usan la extensión **OpenSSL** de PHP (llaves RSA, firma digital, CSR, verificación de integridad y cifrado AES-128-CBC) y un ejemplo con **Libsodium** (cifrado autenticado con `sodium_crypto_secretbox`).

| Script | Qué hace | Funciones clave |
|---|---|---|
| `firma7.php` | Genera un par RSA de 2048 bits, lo guarda en `keys/` y firma un mensaje | `openssl_pkey_new`, `openssl_pkey_export`, `openssl_pkey_get_details`, `openssl_sign` |
| `firmaOtra.php` | Genera una solicitud de certificado (CSR) y su llave privada en `keysCert/` | `openssl_csr_new`, `openssl_csr_export` |
| `FirmarMensaje.php` | Firma un mensaje y verifica su integridad | `openssl_pkey_get_public`, `openssl_verify` |
| `Encriptacion.php` | Cifra y descifra un texto con AES-128-CBC | `openssl_encrypt`, `openssl_decrypt` |
| Ejemplo Sodium | Cifrado autenticado de un mensaje | `sodium_crypto_secretbox`, `sodium_crypto_secretbox_open` |

La documentación técnica completa de cada función (propósito, parámetros, valor devuelto y proceso criptográfico) está en el informe `Informe_Laboratorio_OpenSSL.docx`.

## 2. Entorno de pruebas

| Componente | Versión |
|---|---|
| WampServer | 3.4.0 (64 bits) |
| Apache | 2.4.65 |
| PHP | 8.3.28 |
| OpenSSL | 3.5.1 |
| libsodium | 1.0.18 |
| MySQL / MariaDB | 8.4.7 / 11.4.9 |

También funciona en XAMPP.

## 3. Estructura del proyecto

```
SeguridadOpenSSL/
├── keys/                  # llaves RSA generadas por firma7.php
├── keysCert/              # CSR y llave privada generados por firmaOtra.php
├── firma7.php
├── firmaOtra.php
├── FirmarMensaje.php
├── Encriptacion.php
├── (script de Sodium)     # ajustar al nombre real del archivo
├── capturas/              # evidencias de ejecución
└── README.md
```

## 4. Cómo ejecutarlo

1. Copiar la carpeta del proyecto en `C:\wamp64\www\` (o `C:\xampp\htdocs\`).
2. Iniciar los servicios (Apache) desde WampServer/XAMPP.
3. Verificar las extensiones con un `phpinfo()`, o con:
   ```php
   <?php var_dump(extension_loaded('openssl'), extension_loaded('sodium'));
   ```
   Ambas deben dar `true`.
4. Abrir en el navegador, por ejemplo: `http://localhost/SeguridadOpenSSL/firma7.php`.
5. Asegurarse de que las carpetas `keys/` y `keysCert/` existan y tengan permiso de escritura.

### Si falla la ruta de `openssl.cnf`

Algunos equipos no encuentran el archivo si la ruta apunta a `Program Files`. Hay dos soluciones (de la guía del laboratorio):

**Solución 1:** quitar el parámetro `config` y dejar que PHP lo resuelva:

```php
$configArgs = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
```

**Solución 2:** detectar la ruta dinámicamente:

```php
$fallbackCnf = 'C:\xampp\php\extras\ssl\openssl.cnf';
$configArgs = array(
    'config' => file_exists($fallbackCnf) ? $fallbackCnf : null,
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
```

En WampServer, PHP usó el archivo `C:\wamp64\bin\apache\apache2.4.65\conf\openssl.cnf` sin configuración adicional (ver captura 4).

## 5. Evidencias

### 5.1 Entorno

![WampServer](capturas/02_wampserver.png)
![OpenSSL 3.5.1](capturas/01_openssl_version.png)
![Sodium habilitado](capturas/03_sodium_habilitado.png)

### 5.2 `firma7.php` – llaves RSA y firma digital

Mensaje "Hola mundo" firmado con la llave privada; se muestran la firma en Base64 y la llave pública.

![firma7](capturas/04_firma7_resultado.png)
![private_key.pem](capturas/05_private_key_pem.png)
![public_key.pem](capturas/06_public_key_pem.png)

### 5.3 `firmaOtra.php` – CSR

![CSR](capturas/07_firmaOtra_csr.png)
![keysCert](capturas/08_keysCert_archivos.png)

### 5.4 `FirmarMensaje.php` – integridad

![Verificación](capturas/09_verificacion_firma.png)

### 5.5 `Encriptacion.php` – AES-128-CBC

![AES](capturas/10_cifrado_aes128cbc.png)

### 5.6 Libsodium – cifrado autenticado

![Sodium](capturas/11_sodium_secretbox.png)

## 6. OpenSSL vs. Libsodium

| Característica | OpenSSL | Libsodium |
|---|---|---|
| Propósito | Certificados, protocolos de red, RSA | Cifrado moderno, firmas, hashing |
| Complejidad | Alta (algoritmo, modo, IV, relleno) | Baja, parámetros seguros por defecto |
| Integridad al cifrar | No en CBC (requiere GCM o HMAC) | Siempre (Poly1305) |
| Certificados / CSR | Sí | No |
| Uso recomendado | HTTPS, llaves y certificados | Contraseñas (Argon2id) y datos sensibles en BD |

## 7. Notas de seguridad

- Las llaves de este repositorio son **solo de laboratorio**. No se deben reutilizar.
- En un sistema real la llave privada no se muestra en pantalla, no se guarda dentro de la raíz web ni se sube a Git; se protege con contraseña y permisos.
- La clave de AES no debe estar escrita en el código: usar variables de entorno o un gestor de secretos.
- AES-CBC no autentica el texto cifrado. Para datos sensibles preferir Sodium (`secretbox`) o AES-GCM.
- Un CSR no es un certificado: debe ser firmado por una autoridad certificadora.

## 8. Entrega

- `Informe_Laboratorio_OpenSSL.docx` → exportar a **PDF** (formato de entrega exigido).
- Código y capturas en este repositorio.
