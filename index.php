<?php
function updateConsole($message) {
    echo "<script>console.log('$message');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['key'])) {
    $action = $_POST['action'];
    $key = $_POST['key'];
    $file = $_FILES['file'];

    // Vérifier la longueur de la clé (doit être 64 caractères pour AES-256)
    if (strlen($key) !== 64) {
        updateConsole('Erreur : La clé de chiffrement doit être de 64 caractères hexadécimaux.');
        exit('Erreur : La clé de chiffrement doit être de 64 caractères hexadécimaux.');
    }

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileContent = file_get_contents($file['tmp_name']);
        $output = '';

        if ($action === 'encrypt') {
            updateConsole('Chiffrement en cours... 🔒');
            $output = encrypt($fileContent, $key);
            $filename = 'encrypted_' . $file['name'];
            downloadFile($output, $filename);
            exit();
        } elseif ($action === 'decrypt') {
            updateConsole('Déchiffrement en cours... 🔓');
            $output = decrypt($fileContent, $key);
            $filename = 'decrypted_' . $file['name'];
            downloadFile($output, $filename);
            exit();
        }
    } else {
        updateConsole('Erreur lors du téléchargement du fichier.');
        exit('Erreur lors du téléchargement du fichier.');
    }
}

function encrypt($data, $key) {
    $key = hex2bin($key);
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($data, $key) {
    $key = hex2bin($key);
    $data = base64_decode($data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

function downloadFile($data, $filename) {
    // Nettoyer le tampon de sortie
    ob_clean();

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Length: ' . strlen($data));
    ob_end_flush();

    echo $data;
    exit(); // Assurez-vous que rien d'autre n'est envoyé après le téléchargement du fichier
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛡️ AES-256 Protection des données</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .form-control, .form-control-file, .btn {
            background-color: #2c2c2c;
            color: #ffffff;
            border: 1px solid #444;
        }
        .form-control:focus, .btn:focus {
            box-shadow: none;
            border-color: #555;
        }
        .btn-primary {
            background-color: #4a90e2;
            border-color: #4a90e2;
        }
        .btn-secondary {
            background-color: #7b7b7b;
            border-color: #7b7b7b;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #357ab8;
            border-color: #357ab8;
        }
        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #5e5e5e;
            border-color: #5e5e5e;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #1e1e1e;
            margin-top: 20px;
            border-top: 1px solid #444;
        }
        .console {
            background-color: #121212;
            color: #ffffff;
            padding: 10px;
            border-radius: 5px;
            height: 200px;
            overflow-y: scroll;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">🛡️ AES-256 Protection des données 🔒</h2>
    <form id="encryption-form" class="mt-4" action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="fileInput">Choisissez un fichier 📁 :</label>
            <input type="file" class="form-control-file" id="fileInput" name="file" required>
        </div>
        <div class="form-group">
            <label for="keyInput">Entrez une clé de chiffrement 🔑 :</label>
            <input type="password" class="form-control" id="keyInput" name="key" required>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-info" onclick="generateKey()">Générer une clé 🔑</button>
        </div>
        <button type="submit" class="btn btn-primary" name="action" value="encrypt" onclick="updateConsole('Chiffrement en cours... 🔒')">Chiffrer</button>
        <button type="submit" class="btn btn-secondary" name="action" value="decrypt" onclick="updateConsole('Déchiffrement en cours... 🔓')">Déchiffrer</button>
    </form>
    <div class="console" id="console">
        <p>Console de progression :</p>
    </div>
</div>
<footer>
    <p>Développeur Darshw3b 👨‍💻</p>
</footer>

<script>
    function updateConsole(message) {
        const consoleElement = document.getElementById('console');
        const p = document.createElement('p');
        p.textContent = message;
        consoleElement.appendChild(p);
    }

    function generateKey() {
        const key = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        document.getElementById('keyInput').value = key;
        updateConsole('Clé générée : ' + key + ' 🔑');
    }
</script>
</body>
</html>
