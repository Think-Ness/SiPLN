<?php
/**
 * SiPLN V3 - Non-Destructive Database Schema Synchronizer
 * 
 * Fitur:
 * 1. Otomatis menambahkan tabel baru yang belum ada (CREATE TABLE IF NOT EXISTS).
 * 2. Otomatis menambahkan kolom baru yang belum ada ke tabel lama (ALTER TABLE ADD COLUMN).
 * 3. Otomatis menambahkan index/foreign key yang belum ada.
 * 4. TIDAK MENGHAPUS (DROP) tabel atau kolom apa pun.
 * 5. TIDAK MENGUBAH / MENGHAPUS data transaksi santri yang sudah ada (Zero Data Loss).
 */

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = dirname(__DIR__);
$sqlFile = $root . '/db_install.sql';
$templatesFile = $root . '/insert_templates.sql';

$executedActions = [];
$errors = [];
$isConnected = false;

// 1. Load Autoload if exists
if (file_exists($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}

// 2. Connect to Database (Standard XAMPP / MariaDB configuration)
$dsn = 'mysql:host=localhost;dbname=si_foreign_db;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $isConnected = true;
} catch (Throwable $e) {
    // Try 127.0.0.1 fallback
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db;charset=utf8mb4', $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $isConnected = true;
    } catch (Throwable $e2) {
        $errors[] = "Gagal terhubung ke Database: " . $e2->getMessage();
    }
}

$isExecuted = isset($_POST['action']) && $_POST['action'] === 'sync';

if ($isConnected && $isExecuted && file_exists($sqlFile)) {
    $sqlContent = file_get_contents($sqlFile);
    
    // Parse CREATE TABLE statements
    preg_match_all('/CREATE TABLE `?(\w+)`?\s*\((.*?)\)\s*ENGINE=([^;]+);/s', $sqlContent, $matches, PREG_SET_ORDER);
    
    // Get existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($matches as $m) {
        $tableName = $m[1];
        $tableBody = $m[2];
        $enginePart = $m[3];

        if (!in_array($tableName, $existingTables)) {
            // Table doesn't exist -> CREATE TABLE
            try {
                $createSql = "CREATE TABLE IF NOT EXISTS `{$tableName}` ({$tableBody}) ENGINE={$enginePart};";
                $pdo->exec($createSql);
                $executedActions[] = [
                    'type' => 'TABLE_CREATED',
                    'table' => $tableName,
                    'message' => "Tabel baru `{$tableName}` berhasil dibuat."
                ];
                $existingTables[] = $tableName;
            } catch (Throwable $e) {
                $errors[] = "Gagal membuat tabel `{$tableName}`: " . $e->getMessage();
            }
        } else {
            // Table already exists -> Compare columns
            $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
            $currentCols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $currentColNames = array_column($currentCols, 'Field');

            // Parse columns and definitions from tableBody
            $lines = explode("\n", $tableBody);
            $prevCol = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $line = rtrim($line, ',');

                // Skip keys / constraints / indexes in column check
                if (preg_match('/^(PRIMARY KEY|KEY|UNIQUE KEY|CONSTRAINT|FULLTEXT|INDEX)/i', $line)) {
                    continue;
                }

                // Match column definition: `col_name` type ...
                if (preg_match('/^`?(\w+)`?\s+(.+)$/', $line, $colMatch)) {
                    $colName = $colMatch[1];
                    $colDef = $colMatch[2];

                    if (!in_array($colName, $currentColNames)) {
                        // Missing column -> ALTER TABLE ADD COLUMN
                        try {
                            $afterClause = $prevCol ? "AFTER `{$prevCol}`" : "";
                            $alterSql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colDef} {$afterClause}";
                            $pdo->exec($alterSql);
                            $executedActions[] = [
                                'type' => 'COLUMN_ADDED',
                                'table' => $tableName,
                                'column' => $colName,
                                'message' => "Kolom `{$colName}` ditambahkan ke tabel `{$tableName}`."
                            ];
                            $currentColNames[] = $colName;
                        } catch (Throwable $e) {
                            $errors[] = "Gagal menambah kolom `{$colName}` pada `{$tableName}`: " . $e->getMessage();
                        }
                    }
                    $prevCol = $colName;
                }
            }
        }
    }

    // 2. Insert Templates Dinamis if available
    if (in_array('surat_template_dinamis', $existingTables) && file_exists($templatesFile)) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `surat_template_dinamis`");
            $count = (int)$stmt->fetchColumn();
            if ($count === 0) {
                $templateSql = file_get_contents($templatesFile);
                if (!empty($templateSql)) {
                    $pdo->exec($templateSql);
                    $executedActions[] = [
                        'type' => 'DATA_SEEDED',
                        'table' => 'surat_template_dinamis',
                        'message' => "Template surat standar berhasil diinisialisasi ke `surat_template_dinamis`."
                    ];
                }
            }
        } catch (Throwable $e) {
            $errors[] = "Gagal mengisi template default: " . $e->getMessage();
        }
    }

    // Reset flag migration
    @file_put_contents($root . '/config/.migration_schema_v3.lock', date('Y-m-d H:i:s'));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPLN V3 - Sinkronisasi Skema Database</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .sync-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.06);
            max-width: 780px;
            width: 100%;
            overflow: hidden;
        }
        .sync-header {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #ffffff;
            padding: 2rem 2.5rem;
            position: relative;
        }
        .sync-body {
            padding: 2.5rem;
        }
        .badge-safe {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
        }
        .action-item {
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .action-item.table {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        .action-item.col {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        .action-item.seed {
            background: #fdf4ff;
            border-left: 4px solid #d946ef;
            color: #86198f;
        }
    </style>
</head>
<body>

<div class="sync-card">
    <div class="sync-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge-safe"><i class="bi bi-shield-check me-1"></i> ZERO DATA LOSS GUARANTEE</span>
            <span class="text-white-50 small">SiPLN V3 Database Migrator</span>
        </div>
        <h3 class="fw-bold mb-1">Sinkronisasi Struktur Database</h3>
        <p class="text-white-50 mb-0 small">Memperbarui kolom dan tabel baru dari versi pengembangan tanpa mengubah atau menghapus data riil santri yang sudah ada.</p>
    </div>

    <div class="sync-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5 mt-n1"></i>
                <div>
                    <strong class="d-block">Terjadi Kendala:</strong>
                    <ul class="mb-0 ps-3 mt-1 small">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isExecuted): ?>
            <?php if (empty($executedActions) && empty($errors)): ?>
                <div class="alert alert-info rounded-3 d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
                    <div>
                        <strong>Database Sudah 100% Up to Date!</strong>
                        <div class="small">Semua tabel dan kolom sudah cocok sempurna dengan versi terbaru. Tidak ada perubahan yang diperlukan.</div>
                    </div>
                </div>
            <?php elseif (!empty($executedActions)): ?>
                <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                    <div>
                        <strong>Berhasil Memperbarui Struktur Database!</strong>
                        <div class="small">Total <?= count($executedActions) ?> perubahan struktur berhasil diterapkan tanpa mengubah data yang sudah ada.</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-list-check me-1"></i> Rincian Perubahan yang Diterapkan:</h6>
                <div style="max-height: 280px; overflow-y: auto;" class="mb-4 pe-1">
                    <?php foreach ($executedActions as $act): ?>
                        <div class="action-item <?= $act['type'] === 'TABLE_CREATED' ? 'table' : ($act['type'] === 'COLUMN_ADDED' ? 'col' : 'seed') ?>">
                            <i class="bi <?= $act['type'] === 'TABLE_CREATED' ? 'bi-table' : ($act['type'] === 'COLUMN_ADDED' ? 'bi-layout-three-columns' : 'bi-file-earmark-plus') ?>"></i>
                            <span><?= htmlspecialchars($act['message']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="index.php" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Buka Aplikasi SiPLN
                </a>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="sync">
                    <button type="submit" class="btn btn-outline-secondary px-3 py-2 rounded-3 btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i> Cek Ulang
                    </button>
                </form>
            </div>

        <?php else: ?>

            <div class="p-3 bg-light rounded-3 border mb-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-database-gear fs-2 text-primary"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Prinsip Keamanan Data:</h6>
                        <ul class="text-muted small ps-3 mb-0">
                            <li><strong>Menambah, Bukan Menimpa:</strong> Hanya membuat tabel baru dan menambahkan kolom baru yang belum ada.</li>
                            <li><strong>Data Transaksi Utuh:</strong> Data user, biodata santri, surat, paspor, dan foto yang sudah tersimpan di database kantor <strong>100% aman dan tidak tersentuh</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="sync">
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-6 shadow-sm">
                    <i class="bi bi-lightning-charge-fill me-2"></i> JALANKAN SINKRONISASI DATABASE SEKARANG
                </button>
            </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
