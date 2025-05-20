<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$uploadDir = "C:/xampp/htdocs/cripto/image/";
$decryptDir = "C:/xampp/htdocs/cripto/decrypted/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (!is_dir($decryptDir)) {
    mkdir($decryptDir, 0777, true);
}

function checkFile($filePath) {
    if (file_exists($filePath)) {
        echo "Check Passed: File exists at $filePath<br>";
    } else {
        echo "Check Failed: File does not exist at $filePath<br>";
    }
}

function encryptFile($file, $dest) {
    $key = openssl_digest(php_uname(), 'MD5', TRUE);
    $cipher = "aes-256-cbc";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $plaintext = file_get_contents($file['tmp_name']);
    $ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $encryptedData = $iv . $ciphertext;
    file_put_contents($dest, $encryptedData);
    echo "Encryption Status: ";
    checkFile($dest);
    return "Encryption completed.<br>";
}

function decryptFile($file, $dest) {
    $key = openssl_digest(php_uname(), 'MD5', TRUE);
    $cipher = "aes-256-cbc";
    $data = file_get_contents($file['tmp_name']);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    $original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    file_put_contents($dest, $original_plaintext);
    echo "Decryption Status: ";
    checkFile($dest);
    return "Decryption completed.<br>";
}

// Encryption handling
if (isset($_POST['encrypt']) && isset($_FILES['fileToEncrypt'])) {
    $newFileName = $uploadDir . md5(time()) . '.enc';
    echo encryptFile($_FILES['fileToEncrypt'], $newFileName);
}

// Decryption handling
if (isset($_POST['decrypt']) && isset($_FILES['fileToDecrypt'])) {
    if ($_FILES['fileToDecrypt']['error'] === UPLOAD_ERR_OK) {
        $baseFileName = basename($_FILES['fileToDecrypt']['name'], '.enc');
        $newFileName = $decryptDir . $baseFileName;
        echo decryptFile($_FILES['fileToDecrypt'], $newFileName);
    } else {
        echo "Error uploading file for decryption: " . $_FILES['fileToDecrypt']['error'];
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <p>Upload image to encrypt:<br>
    <input type="file" name="fileToEncrypt">
    <input type="submit" name="encrypt" value="Encrypt Image"></p>
</form>

<form method="post" enctype="multipart/form-data">
    <p>Upload encrypted file to decrypt:<br>
    <input type="file" name="fileToDecrypt">
    <input type="submit" name="decrypt" value="Decrypt File"></p>
</form>
