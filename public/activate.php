<?php
session_start();
$root = dirname(__DIR__);

// Helper Function: Get Hardware ID
function getHardwareId() {
    // Works on Windows (XAMPP)
    $output = @shell_exec('wmic csproduct get uuid');
    if ($output) {
        $lines = explode("\n", trim($output));
        if (isset($lines[1])) {
            return trim($lines[1]);
        }
    }
    // Fallback if wmic fails
    return md5(php_uname('n') . php_uname('a'));
}

$hardwareId = getHardwareId();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['activation_code'] ?? '');
    
    if (empty($code)) {
        $error = "Kode Aktivasi tidak boleh kosong.";
    } else {
        $url = "https://firestore.googleapis.com/v1/projects/database-luar-negeri/databases/(default)/documents/aktivasi/" . urlencode($code);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $fields = $data['fields'] ?? [];
            
            $status = isset($fields['status']['booleanValue']) ? $fields['status']['booleanValue'] : true; // default true if exist
            $registeredDevice = isset($fields['device_id']['stringValue']) ? $fields['device_id']['stringValue'] : '';
            
            if (!$status) {
                $error = "Lisensi ini telah dicabut atau dinonaktifkan oleh pusat.";
            } elseif ($registeredDevice !== '' && $registeredDevice !== $hardwareId) {
                $error = "Kode Aktivasi ini sudah terdaftar dan digunakan di perangkat lain. Silakan hubungi pusat.";
            } else {
                // Register this device!
                $patchUrl = $url . "?updateMask.fieldPaths=device_id&updateMask.fieldPaths=status";
                $patchData = json_encode([
                    'fields' => [
                        'device_id' => ['stringValue' => $hardwareId],
                        'status' => ['booleanValue' => true]
                    ]
                ]);
                
                $chPatch = curl_init($patchUrl);
                curl_setopt($chPatch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($chPatch, CURLOPT_POSTFIELDS, $patchData);
                curl_setopt($chPatch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chPatch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_exec($chPatch);
                curl_close($chPatch);
                
                // Write License Locally
                $licenseData = [
                    'code' => $code,
                    'device_id' => $hardwareId,
                    'signature' => hash_hmac('sha256', $code . $hardwareId, 'SiPLN_Secret_Salt_2026')
                ];
                
                if (!is_dir($root . '/config')) mkdir($root . '/config', 0777, true);
                file_put_contents($root . '/config/license.json', json_encode($licenseData));
                
                $_SESSION['last_license_check'] = time();
                
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Kode Aktivasi tidak ditemukan. Pastikan Anda memasukkan kode yang benar.";
        }
    }
}

// Ensure style is modern
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aktivasi Perangkat SiPLN</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #00A2E9;
            margin-top: 0;
        }
        p {
            color: #555;
            font-size: 14px;
            margin-bottom: 25px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #00A2E9;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #0088c4;
        }
        .error {
            color: #d9534f;
            background: #fdf7f7;
            border: 1px solid #d9534f;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }
        .hwid-info {
            font-size: 11px;
            color: #aaa;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Aktivasi Perangkat</h2>
    <p>Aplikasi web ini dilindungi oleh lisensi perangkat.<br>Silakan masukkan Kode Aktivasi Cabang Anda.</p>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="activation_code" placeholder="Masukkan Kode Aktivasi..." required autocomplete="off">
        <button type="submit">Aktivasi Perangkat</button>
    </form>
    
    <div class="hwid-info">
        Hardware ID: <?= htmlspecialchars($hardwareId) ?>
    </div>
</div>

</body>
</html>
