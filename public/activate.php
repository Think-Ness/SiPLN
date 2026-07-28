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
    <title>Sistem Keamanan Pusat - Aktivasi Perangkat SiPLN</title>
    <link rel="icon" href="assets/logopln.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b0f19;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(0, 162, 233, 0.08), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(0, 255, 204, 0.05), transparent 25%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #e0e6ed;
            overflow: hidden;
        }
        
        /* Grid Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
            z-index: -1;
        }

        .security-container {
            position: relative;
        }

        /* Glowing backdrop */
        .security-container::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(45deg, #00A2E9, #00ffcc, #00A2E9);
            z-index: -1;
            border-radius: 12px;
            filter: blur(15px);
            opacity: 0.5;
            animation: pulse-glow 3s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { opacity: 0.3; filter: blur(10px); }
            100% { opacity: 0.6; filter: blur(20px); }
        }

        .login-card {
            background: rgba(13, 20, 36, 0.85);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 10px;
            border: 1px solid rgba(0, 162, 233, 0.3);
            box-shadow: inset 0 0 20px rgba(0, 162, 233, 0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
        }

        .icon-lock {
            width: 60px;
            height: 60px;
            background: rgba(0, 162, 233, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid rgba(0, 162, 233, 0.5);
            box-shadow: 0 0 15px rgba(0, 162, 233, 0.4);
        }
        
        .icon-lock svg {
            width: 30px;
            height: 30px;
            fill: #00A2E9;
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            margin-top: 0;
            font-size: 22px;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(0, 162, 233, 0.8);
        }

        p {
            color: #8fa0b5;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #1e2c4a;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            color: #00ffcc;
            font-family: 'Orbitron', monospace;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #00A2E9;
            box-shadow: 0 0 15px rgba(0, 162, 233, 0.3);
            background: rgba(0, 162, 233, 0.05);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: #4c6382;
            transition: fill 0.3s;
        }
        
        input[type="text"]:focus + .input-icon {
            fill: #00A2E9;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(90deg, #007bb5, #00A2E9);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(0, 162, 233, 0.4);
        }

        button:hover {
            background: linear-gradient(90deg, #00A2E9, #00ffcc);
            box-shadow: 0 5px 25px rgba(0, 255, 204, 0.5);
            transform: translateY(-2px);
        }

        .error {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 13px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .error svg {
            min-width: 16px;
            width: 16px;
            fill: #ff4757;
            margin-top: 2px;
        }

        .hwid-info {
            font-family: 'Orbitron', monospace;
            font-size: 10px;
            color: #3b506c;
            margin-top: 30px;
            letter-spacing: 1px;
            padding-top: 20px;
            border-top: 1px dashed #1e2c4a;
        }
        
        .hwid-info span {
            color: #5d7a9c;
        }
    </style>
</head>
<body>

<div class="security-container">
    <div class="login-card">
        <div class="icon-lock">
            <!-- Shield SVG -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
        </div>
        
        <h2>Otorisasi Sistem</h2>
        <p>Protokol keamanan diaktifkan.<br>Silakan masukkan Kode Akses Perangkat.</p>
        
        <?php if ($error): ?>
            <div class="error">
                <svg viewBox="0 0 24 24"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="activation_code" placeholder="KODE-AKSES-xxx" required autocomplete="off">
                <!-- Key SVG -->
                <svg class="input-icon" viewBox="0 0 24 24"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
            </div>
            <button type="submit">Autentikasi Perangkat</button>
        </form>
        
        <div class="hwid-info">
            SECURE_ID // <span><?= htmlspecialchars($hardwareId) ?></span>
        </div>
    </div>
</div>

</body>
</html>
