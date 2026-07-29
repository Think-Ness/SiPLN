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
            
            $statusField = $fields['status'] ?? null;
            $status = true; // default true if exist and no status field
            if ($statusField !== null) {
                if (isset($statusField['booleanValue'])) {
                    $status = $statusField['booleanValue'];
                } elseif (isset($statusField['stringValue'])) {
                    $sVal = strtolower(trim($statusField['stringValue']));
                    if ($sVal === 'false' || $sVal === '0' || $sVal === 'nonaktif' || $sVal === 'inactive' || $sVal === 'off') {
                        $status = false;
                    }
                } elseif (isset($statusField['integerValue'])) {
                    $status = (int)$statusField['integerValue'] !== 0;
                }
            }
            
            $registeredDevice = isset($fields['device_id']['stringValue']) ? trim($fields['device_id']['stringValue']) : '';
            
            // Periksa juga jika admin menggunakan field 'is_used' atau 'terpakai'
            $isUsedField = $fields['is_used'] ?? $fields['terpakai'] ?? null;
            $isUsed = false;
            if ($isUsedField !== null) {
                if (isset($isUsedField['booleanValue'])) $isUsed = $isUsedField['booleanValue'];
                elseif (isset($isUsedField['stringValue'])) {
                    $uVal = strtolower(trim($isUsedField['stringValue']));
                    if ($uVal === 'true' || $uVal === '1' || $uVal === 'ya' || $uVal === 'yes') $isUsed = true;
                }
            }
            
            if (!$status) {
                $error = "Lisensi ini telah dicabut atau dinonaktifkan oleh pusat.";
            } elseif ($isUsed && $registeredDevice === '') {
                 $error = "Kode Aktivasi ini sudah terdaftar dan digunakan. Silakan hubungi pusat.";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f6fa;
            background-image: linear-gradient(135deg, #e4eef6 0%, #ffffff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #2c3e50;
            position: relative;
            overflow: hidden;
        }
        
        /* Modern Corporate Grid Background */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: 60px 60px;
            background-image: 
                linear-gradient(to right, rgba(0, 162, 233, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 162, 233, 0.05) 1px, transparent 1px);
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 16px;
            border: 1px solid rgba(0, 162, 233, 0.15);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.05),
                0 1px 3px rgba(0, 0, 0, 0.02),
                inset 0 2px 0 rgba(0, 162, 233, 1);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
        }

        .icon-lock {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #ffffff, #f0f7fb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px solid #00A2E9;
            box-shadow: 0 10px 20px rgba(0, 162, 233, 0.15);
        }
        
        .icon-lock svg {
            width: 32px;
            height: 32px;
            fill: #00A2E9;
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            color: #1a252f;
            margin-top: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        p {
            color: #5c6b7a;
            font-size: 14px;
            margin-bottom: 35px;
            line-height: 1.6;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        input[type="text"] {
            width: 100%;
            padding: 16px 16px 16px 50px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
            color: #1e293b;
            font-family: 'Inter', monospace;
            font-weight: 600;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #00A2E9;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 162, 233, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: #94a3b8;
            transition: fill 0.3s;
        }
        
        input[type="text"]:focus + .input-icon {
            fill: #00A2E9;
        }

        button {
            width: 100%;
            padding: 16px;
            background: #00A2E9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(0, 162, 233, 0.3);
        }

        button:hover {
            background: #0088c4;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 162, 233, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }

        .error {
            color: #e11d48;
            background: #fff1f2;
            border: 1px solid #fda4af;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        
        .error svg {
            min-width: 18px;
            width: 18px;
            fill: #e11d48;
        }

        .hwid-info {
            font-family: 'Inter', monospace;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 35px;
            letter-spacing: 0.5px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .hwid-info span {
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="icon-lock">
        <!-- Shield SVG -->
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
        </svg>
    </div>
    
    <h2>Otorisasi Perangkat</h2>
    <p>Akses Terbatas. Protokol keamanan SiPLN diaktifkan. Silakan autentikasi perangkat Anda.</p>
    
    <?php if ($error): ?>
        <div class="error">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
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
        DEVICE_FINGERPRINT : <span><?= htmlspecialchars($hardwareId) ?></span>
    </div>
</div>

</body>
</html>
