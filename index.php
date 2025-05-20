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

$message = ''; // To store the alert message

function checkFile($filePath) {
    if (file_exists($filePath)) {
        return "Check Passed: File exists at $filePath";
    } else {
        return "Check Failed: File does not exist at $filePath";
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
    $status = checkFile($dest);
    return "Encryption completed.<br>$status";
}

function decryptFile($file, $dest) {
    $key = openssl_digest(php_uname(), 'MD5', TRUE);
    $cipher = "aes-256-cbc";
    $data = file_get_contents($file['tmp_name']);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    $original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $dest .= ".png"; // Force the extension to PNG
    file_put_contents($dest, $original_plaintext);
    $status = checkFile($dest);
    return "Decryption completed. File saved as PNG.<br>$status";
}

// Handle encryption
if (isset($_POST['encrypt']) && isset($_FILES['fileToEncrypt'])) {
    if ($_FILES['fileToEncrypt']['error'] === UPLOAD_ERR_OK) {
        $newFileName = $uploadDir . md5(time()) . '.enc';
        $message = encryptFile($_FILES['fileToEncrypt'], $newFileName);
    } else {
        $message = "Error uploading file for encryption.";
    }
}

// Handle decryption
if (isset($_POST['decrypt']) && isset($_FILES['fileToDecrypt'])) {
    if ($_FILES['fileToDecrypt']['error'] === UPLOAD_ERR_OK) {
        $baseFileName = basename($_FILES['fileToDecrypt']['name'], '.enc');
        $newFileName = $decryptDir . $baseFileName;
        $message = decryptFile($_FILES['fileToDecrypt'], $newFileName);
    } else {
        $message = "Error uploading file for decryption.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Encryption & Decryption Tool</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; }
        .footer { margin-top: 20px; text-align: center; padding: 10px; background-color: #fff; }
        .modal-header { background-color: #007bff; color: white; }
        .modal-footer { background-color: #f1f1f1; }
        .modal-content { border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Image Encryption & Decryption Tool</h1>
    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fileToEncrypt">Upload image to encrypt:</label>
                    <input type="file" class="form-control-file" id="fileToEncrypt" name="fileToEncrypt">
                </div>
                <button type="submit" name="encrypt" class="btn btn-primary btn-block">Encrypt Image</button>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fileToDecrypt">Upload encrypted file to decrypt:</label>
                    <input type="file" class="form-control-file" id="fileToDecrypt" name="fileToDecrypt">
                </div>
                <button type="submit" name="decrypt" class="btn btn-success btn-block">Decrypt File</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo isset($message) ? $message : ''; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        <?php if (!empty($message)) { ?>
        $('#alertModal').modal('show');
        <?php } ?>
    });
</script>
</body>
</html>
