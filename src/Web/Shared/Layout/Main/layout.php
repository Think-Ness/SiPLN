<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var \App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$assetManager->register(MainAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$isActive = function(string $route) use ($currentPath) {
    $route = '/' . ltrim($route, '/');
    if ($route === '/') return $currentPath === '/' ? 'active' : '';
    // /job-desk ONLY matches /job-desk and /job-desk/{id} but NOT sub-routes like /settings or /keuangan
    if ($route === '/job-desk') {
        if (!str_starts_with($currentPath, '/job-desk')) return '';
        if (str_starts_with($currentPath, '/job-desk/settings')) return '';
        if (str_starts_with($currentPath, '/job-desk/keuangan')) return '';
        if (str_starts_with($currentPath, '/job-desk/pengeluaran')) return '';
        return 'active';
    }
    if ($route === '/job-desk/keuangan') return str_starts_with($currentPath, '/job-desk/keuangan') ? 'active' : '';
    if ($route === '/job-desk/pengeluaran-operasional') return str_starts_with($currentPath, '/job-desk/pengeluaran') ? 'active' : '';
    
    if ($route === '/pemberkasan') {
        if (!str_starts_with($currentPath, '/pemberkasan')) return '';
        if (str_starts_with($currentPath, '/pemberkasan/cetak-berkas')) return '';
        return 'active';
    }
    
    return str_starts_with($currentPath, $route) ? 'active' : '';
};

// Detect which group has an active menu item for auto-expanding
$activeGroups = [];
if ($isActive('/') || $isActive('/auto-rekap') || $isActive('/kalender-expiry') || $isActive('/job-desk') || ($isActive('/pemberkasan') && !$isActive('/pemberkasan/cetak-berkas')) || $isActive('/pemberkasan/cetak-berkas')) $activeGroups[] = 'main';
if ($isActive('/master-data') || $isActive('/inaktif-data') || $isActive('/request-edit')) $activeGroups[] = 'masterdata';
if ($isActive('/master-print')) $activeGroups[] = 'masterprint';
if ($isActive('/anggaran') || $isActive('/job-desk/keuangan') || $isActive('/job-desk/pengeluaran-operasional')) $activeGroups[] = 'keuangan';
if ($isActive('/job-desk/settings') || $isActive('/profil-instansi') || $isActive('/anggota-kamar') || $isActive('/surat-generator') || $isActive('/surat-templates')) $activeGroups[] = 'lainlain';
if ($isActive('/manajemen-instansi') || $isActive('/manajemen-user') || $isActive('/pengaturan') || $isActive('/audit-log')) $activeGroups[] = 'sistem';

// Fetch dynamic menus from DB
$dynamicMenus = [];
try {
    $dbConfigFile = dirname(__DIR__, 5) . '/config/common/di/db.php';
    $dbContent = file_get_contents($dbConfigFile);
    if (preg_match("/new Driver\(\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)'\s*\)/", $dbContent, $matches)) {
        $dsn = stripslashes($matches[1]);
        $user = stripslashes($matches[2]);
        $pass = stripslashes($matches[3]);
        $pdo = new \PDO($dsn, $user, $pass);
    } else {
        $pdo = new \PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
    }
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM master_menus WHERE is_active = 1 ORDER BY urut");
    $allMenus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($allMenus as $m) {
        $dynamicMenus[$m['kategori']][] = $m;
    }

    // Auto-refresh session permissions
    if (isset($_SESSION['user_id'])) {
        $stmtUser = $pdo->prepare("SELECT permissions FROM users WHERE id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $userRow = $stmtUser->fetch(\PDO::FETCH_ASSOC);
        if ($userRow && !empty($userRow['permissions'])) {
            $_SESSION['permissions'] = json_decode($userRow['permissions'], true) ?? [];
        }
    }
} catch (\Exception $e) {
    // DB error fallback
}

$hasPermission = function($key) {
    $role = $_SESSION['role'] ?? '';
    // Hanya Super Admin yang otomatis dapat akses penuh.
    if ($role === 'super_admin') return true;
    
    // Manajemen Instansi mutlak hanya untuk Super Admin
    if ($key === 'menu_manajemen_instansi') return false;
    
    if (empty($key)) return true;
    return in_array($key, $_SESSION['permissions'] ?? []);
};

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Santri Luar Negeri">
    <meta name="csrf-token" content="<?= $csrf ?? '' ?>">
    <title><?= Html::encode($this->getTitle() ?: 'Sistem Informasi') ?></title>
    <link rel="icon" href="<?= ASSET_URL ?>/assets/logopln.png" type="image/png">
    <link rel="manifest" href="<?= ASSET_URL ?>/manifest.json">
    <meta name="theme-color" content="#1a2035">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SiPLN">
    <link rel="apple-touch-icon" href="<?= ASSET_URL ?>/assets/icons/icon-192x192.png">
    <link href="<?= ASSET_URL ?>/assets/offline/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= ASSET_URL ?>/assets/offline/css/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= ASSET_URL ?>/assets/offline/css/inter.css" rel="stylesheet">
    <link href="<?= ASSET_URL ?>/assets/offline/css/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 230px;
            --sidebar-bg: #1a2035;
            --sidebar-hover: #283152;
            --sidebar-active: #3461ff;
            --header-height: 54px;
        }
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f0f2f5; overflow-x: hidden; }

        /* ===== SIDEBAR CORE ===== */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: width .3s;
            overflow: hidden;
        }
        .sidebar-brand {
            flex-shrink: 0;
            padding: 16px 16px 12px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        /* scrollable nav fills remaining height */
        .sidebar-nav {
            flex: 1 1 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 6px 0 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }
        .sidebar-footer {
            flex-shrink: 0;
            padding: 10px 14px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        /* ===== COLLAPSED STATE ===== */
        body.sidebar-collapsed { --sidebar-width: 70px; }
        body.sidebar-collapsed .sidebar { width: 70px; }
        body.sidebar-collapsed .nav-text,
        body.sidebar-collapsed .section-label,
        body.sidebar-collapsed .section-arrow,
        body.sidebar-collapsed .brand-title,
        body.sidebar-collapsed .brand-sub,
        body.sidebar-collapsed .user-info { display: none !important; }
        body.sidebar-collapsed .sidebar .nav-link { justify-content: center; padding: 10px; margin: 2px 8px; }
        body.sidebar-collapsed .sidebar .nav-link i.nav-icon { margin: 0 !important; font-size: 1.25rem; }
        body.sidebar-collapsed .sidebar-brand .d-flex,
        body.sidebar-collapsed .sidebar-footer .d-flex { justify-content: center !important; }
        body.sidebar-collapsed .section-hdr { justify-content: center; padding: 8px 0 4px; }
        body.sidebar-collapsed .section-dot { display: block !important; width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.25); margin: 0 auto; }
        /* always show items when fully collapsed */
        body.sidebar-collapsed .sidebar-group-content { max-height: 1000px !important; }

        /* ===== SECTION GROUPS ===== */
        .section-hdr {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 12px 14px 4px;
            cursor: pointer;
            user-select: none;
        }
        .section-hdr:hover .section-label { color: rgba(255,255,255,.7); }
        .section-label {
            font-size: .63rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.38);
            font-weight: 600;
            flex: 1;
            transition: color .2s;
        }
        .section-arrow {
            color: rgba(255,255,255,.28);
            font-size: .7rem;
            transition: transform .25s, color .2s;
        }
        .section-hdr:hover .section-arrow { color: rgba(255,255,255,.6); }
        .section-arrow.open { transform: rotate(180deg); }
        .section-dot { display: none; }

        /* Collapsible animation */
        .sidebar-group-content {
            overflow: hidden;
            transition: max-height .3s ease;
        }
        .sidebar-group-content.collapsed { max-height: 0 !important; }

        /* ===== NAV LINKS ===== */
        .sidebar .nav-link {
            color: rgba(255,255,255,.7);
            padding: 7px 14px;
            font-size: .82rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 6px;
            margin: 1px 8px;
            transition: all .2s;
            white-space: nowrap;
        }
        .sidebar .nav-link:hover { background: var(--sidebar-hover); color: white; }
        .sidebar .nav-link.active { background: var(--sidebar-active); color: white; font-weight: 600; }
        .nav-icon { font-size: 1rem; width: 18px; flex-shrink: 0; }
        .nav-text { flex: 1; min-width: 0; }

        /* ===== NOTIFICATION BADGES ===== */
        .nav-badge {
            font-size: .58rem;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 20px;
            line-height: 1.3;
            min-width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ===== HEADER + CONTENT ===== */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e8ecf0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            transition: left .3s;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 24px;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left .3s;
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }

        /* ===== SIDEBAR OVERLAY (Mobile) ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,.5);
            z-index: 1050;
            opacity: 0;
            transition: opacity .3s;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ===== MODERN PROFESSIONAL DOWNLOAD DOCK ===== */
        .download-dock-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1060;
            font-family: inherit;
        }

        /* 1. Compact Pill */
        .download-pill {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(203, 213, 225, 0.85);
            border-radius: 9999px;
            padding: 6px 14px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
            max-width: 380px;
        }
        .download-pill:hover {
            background: #ffffff;
            box-shadow: 0 14px 30px -5px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .pill-icon-wrapper {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pill-info {
            font-size: 0.8rem;
            max-width: 150px;
        }
        .btn-dock-icon {
            background: transparent;
            border: none;
            padding: 3px 6px;
            border-radius: 50%;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .btn-dock-icon:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* 2. Expanded Card */
        .download-card {
            width: 370px;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.85);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-header-gradient {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 12px 16px;
        }
        .status-pulse-dot {
            width: 9px;
            height: 9px;
            background-color: #38bdf8;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
            animation: pulse-ring 1.8s infinite;
            flex-shrink: 0;
        }
        .status-pulse-dot.paused {
            background-color: #f59e0b;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            animation: none;
        }
        .status-pulse-dot.success {
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: none;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(56, 189, 248, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
        }
        .btn-dock-header {
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: #e2e8f0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .btn-dock-header:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* Santri Download Items */
        .santri-download-list {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 2px;
        }
        .santri-dl-item {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 8px 10px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .santri-dl-item:hover {
            background: #f1f5f9;
            border-color: #e2e8f0;
        }
        .santri-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ===== HEADER ACTION BUTTONS & ICONS ===== */
        .header-action-btn {
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }
        .header-action-btn:hover {
            transform: translateY(-1px);
        }
        .header-icon-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .header-icon-btn:hover {
            background-color: rgba(0,0,0,0.04) !important;
        }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            /* Sidebar: offcanvas slide-in */
            .sidebar {
                transform: translateX(-100%);
                z-index: 1100;
                width: 270px !important;
                transition: transform .3s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .sidebar.open-mobile {
                transform: translateX(0);
                box-shadow: 0 0 50px rgba(0,0,0,0.5);
            }
            /* Hide collapsed state overrides on mobile */
            body.sidebar-collapsed .sidebar {
                width: 270px !important;
                transform: translateX(-100%);
            }
            body.sidebar-collapsed .sidebar.open-mobile {
                transform: translateX(0);
            }
            body.sidebar-collapsed .nav-text,
            body.sidebar-collapsed .section-label,
            body.sidebar-collapsed .section-arrow,
            body.sidebar-collapsed .brand-title,
            body.sidebar-collapsed .brand-sub,
            body.sidebar-collapsed .user-info { display: block !important; }
            body.sidebar-collapsed .sidebar .nav-link { justify-content: flex-start; padding: 7px 14px; margin: 1px 8px; }
            body.sidebar-collapsed .sidebar .nav-link i.nav-icon { margin: 0 !important; font-size: 1rem; }

            /* Header: full width with smooth blur */
            .main-header {
                left: 0 !important;
                padding: 0 10px;
                background: rgba(255, 255, 255, 0.96);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            /* Content: full width, smaller touch-friendly padding */
            .main-content {
                margin-left: 0 !important;
                padding: 12px 8px;
            }

            /* Header Action Buttons on Mobile: Icon-Only Rounded Pills */
            .header-action-btn {
                width: 34px !important;
                height: 34px !important;
                padding: 0 !important;
                border-radius: 50% !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }
            .header-action-btn i {
                font-size: 0.95rem;
                margin: 0 !important;
            }

            /* Download widget: fit mobile screen */
            .download-dock-container {
                left: 10px;
                right: 10px;
                bottom: 10px;
            }
            .download-card {
                width: 100% !important;
            }
            .download-pill {
                max-width: 100% !important;
            }

            /* Page header: stack vertically */
            .page-header-responsive {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.75rem !important;
            }
            .page-header-responsive .d-flex.gap-2,
            .page-header-responsive > div:last-child {
                width: 100%;
            }
            .page-header-responsive .btn {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.75rem !important;
            }

            /* Modal: fullscreen on mobile */
            .modal-dialog.modal-mobile-fullscreen {
                max-width: 100% !important;
                margin: 0 !important;
                min-height: 100vh;
            }
            .modal-dialog.modal-mobile-fullscreen .modal-content {
                min-height: 100vh;
                border-radius: 0 !important;
            }

            /* Riwayat Unduhan modal */
            #modalRiwayatUnduh .modal-dialog {
                max-width: 100% !important;
                margin: 0.5rem !important;
            }

            /* Filter accordion mobile */
            .accordion .col-md-3 { margin-bottom: 0.25rem; }

            /* Table smooth touch momentum scrolling */
            .table-responsive {
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
            }
            table { font-size: 0.78rem !important; }
        }

        /* Small phone adjustments */
        @media (max-width: 480px) {
            .main-content { padding: 10px 4px; }
            h4 { font-size: 1.05rem !important; }
            .card-body { padding: 0.75rem !important; }
            .stat-card .fs-2 { font-size: 1.4rem !important; }
        }
    </style>
    <?php $this->head() ?>
</head>
<body>
<script>if(localStorage.getItem('sidebarState')==='collapsed')document.body.classList.add('sidebar-collapsed');</script>
<?php $this->beginBody() ?>

<!-- â•â•â•â•â•â•â•â•â•â•â• SIDEBAR â•â•â•â•â•â•â•â•â•â•â• -->
<div class="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">
                <i class="bi bi-mortarboard-fill text-white" style="font-size:.9rem;"></i>
            </div>
            <div>
                <p class="brand-title mb-0" style="color:white;font-weight:700;font-size:.95rem;">Sistem Informasi</p>
                <p class="brand-sub mb-0" style="color:rgba(255,255,255,.45);font-size:.72rem;">Santri Luar Negeri</p>
            </div>
        </div>
    </div>

    <!-- Scrollable Nav -->
    <nav class="sidebar-nav">

        <!-- â”€â”€ DYNAMIC MENUS â”€â”€ -->
        <?php
        $groups = [
            'main' => ['label' => 'Main Menu', 'id' => 'main'],
            'masterdata' => ['label' => 'Master Data', 'id' => 'masterdata'],
            'masterprint' => ['label' => 'Master Print', 'id' => 'masterprint'],
            'keuangan' => ['label' => 'Keuangan', 'id' => 'keuangan'],
            'lainlain' => ['label' => 'Lain-lain', 'id' => 'lainlain'],
            'sistem' => ['label' => 'Manajemen Sistem', 'id' => 'sistem'],
        ];
        foreach ($groups as $groupKey => $groupInfo):
            if (empty($dynamicMenus[$groupKey])) continue;
            
            // Check if user has permission to see at least one menu in this group
            $hasAnyPerm = false;
            foreach ($dynamicMenus[$groupKey] as $m) {
                if ($hasPermission($m['permission_key'])) {
                    $hasAnyPerm = true;
                    break;
                }
            }
            if (!$hasAnyPerm) continue;
        ?>
        <div class="section-hdr" onclick="toggleGroup('<?= $groupInfo['id'] ?>')">
            <span class="section-dot"></span>
            <span class="section-label"><?= $groupInfo['label'] ?></span>
            <i class="bi bi-chevron-down section-arrow" id="arr-<?= $groupInfo['id'] ?>"></i>
        </div>
        <div class="sidebar-group-content" id="grp-<?= $groupInfo['id'] ?>" style="max-height:600px;">
            <?php if ($groupKey === 'main'): ?>
            <a href="<?= rtrim(API_URL, '/') . $urlGenerator->generate('home') ?>" class="nav-link <?= $isActive('/') ?>">
                <i class="bi bi-speedometer2 nav-icon"></i><span class="nav-text">Dashboard</span>
            </a>
            <?php endif; ?>

            <?php foreach ($dynamicMenus[$groupKey] as $m): 
                if (!$hasPermission($m['permission_key'])) continue;
                
                // Determine route name from URL
                $routeName = ltrim($m['url'], '/');
                if ($routeName === 'job-desk/settings') $routeName = 'job-desk.settings';
                if ($routeName === 'job-desk/keuangan') $routeName = 'job-desk.keuangan';
                if ($routeName === 'job-desk/pengeluaran-operasional') $routeName = 'job-desk.pengeluaran-operasional';
                if ($routeName === 'master-print/menu-print') $routeName = 'master-print.menu';
                if ($routeName === 'pemberkasan/cetak-berkas') $routeName = 'pemberkasan.cetak-berkas';
            ?>
            <a href="<?= rtrim(API_URL, '/') . $urlGenerator->generate($routeName) ?>" class="nav-link <?= $isActive($m['url']) ?>">
                <i class="<?= Html::encode($m['icon']) ?> nav-icon"></i>
                <span class="nav-text"><?= Html::encode($m['nama_menu']) ?></span>
                
                <?php if ($m['url'] === '/request-edit'): ?>
                <div class="ms-auto d-flex gap-1">
                    <span class="nav-badge bg-danger text-white d-none" id="badge-approval" title="Menunggu Persetujuan Anda">0</span>
                    <span class="nav-badge bg-success text-white d-none" id="badge-approved" title="Ajuan Disetujui">0</span>
                    <span class="nav-badge bg-secondary text-white d-none" id="badge-rejected" title="Ajuan Ditolak">0</span>
                </div>
                <?php endif; ?>
                
                <?php if ($m['url'] === '/audit-log'): ?>
                <div class="ms-auto">
                    <span class="nav-badge bg-danger text-white d-none" id="badge-auditlog" title="Aktivitas Baru">0</span>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>

            <?php if ($groupKey === 'main'): ?>
            <a href="<?= rtrim(API_URL, '/') . $urlGenerator->generate('kalender-expiry') ?>" class="nav-link <?= $isActive('/kalender-expiry') ?>">
                <i class="bi bi-calendar3 nav-icon"></i><span class="nav-text">Kalender Expiry</span>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>


    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:28px;height:28px;">
                <i class="bi bi-person text-white" style="font-size:.75rem;"></i>
            </div>
            <div class="user-info text-truncate" style="min-width:0;">
                <p class="mb-0 text-white text-truncate" style="font-size:.75rem;font-weight:600;"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Guest') ?></p>
                <p class="mb-0 text-truncate" style="font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;"><?= htmlspecialchars($_SESSION['role'] ?? 'Unknown') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Overlay Backdrop (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- â•â•â•â•â•â•â•â•â•â•â• HEADER â•â•â•â•â•â•â•â•â•â•â• -->
<!-- ===== HEADER ===== -->
<div class="main-header">
    <div class="d-flex align-items-center gap-2 gap-md-3">
        <button id="sidebarToggle" class="btn btn-sm text-secondary p-1 border-0 bg-transparent rounded-circle header-icon-btn" title="Buka/Tutup Menu">
            <i class="bi bi-list" style="font-size:1.45rem;"></i>
        </button>
        <span class="badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center px-2 py-1" style="font-size:0.72rem;">
            <i class="bi bi-circle-fill text-success me-1" style="font-size:.42rem;"></i> <span class="d-none d-sm-inline">Online</span>
        </span>
        <div class="d-none d-md-flex align-items-center ms-2 px-3 py-1 bg-light rounded-pill border shadow-xs" style="max-width: 260px;" title="<?= htmlspecialchars($_SESSION['nama_instansi'] ?? 'Global System') ?>">
            <i class="bi bi-building text-primary me-2 flex-shrink-0" style="font-size:0.85rem;"></i>
            <span class="small fw-bold text-dark text-truncate" style="font-size:0.8rem;"><?= htmlspecialchars($_SESSION['nama_instansi'] ?? 'Global System') ?></span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-1 gap-sm-2 gap-md-3">
        <button id="btnShowDownloadWidget" class="btn btn-sm text-primary p-0 border-0 bg-transparent position-relative" title="Status Unduhan Latar Belakang" style="display:none;" onclick="toggleDownloadWidgetFromHeader()">
            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center shadow-xs" style="width:34px;height:34px;transition:all 0.2s;">
                <i class="bi bi-cloud-arrow-down-fill text-primary" style="font-size:1rem;"></i>
            </div>
            <span id="badgeDownloadActive" class="position-absolute top-0 start-100 translate-middle p-1 bg-primary border border-light rounded-circle d-none"></span>
        </button>

        <!-- Guidebook / Ketentuan (Icon di Mobile, Lengkap di Desktop) -->
        <a href="https://pln-monitoring-murex.vercel.app/guidebook.html" target="_blank" class="btn btn-sm btn-outline-info fw-bold header-action-btn shadow-xs" title="Buka Ketentuan / Guidebook">
            <i class="bi bi-book"></i>
            <span class="d-none d-md-inline ms-1.5">Ketentuan</span>
        </a>

        <!-- Monitoring Live (Icon di Mobile, Lengkap di Desktop) -->
        <a href="https://pln-monitoring-murex.vercel.app/" target="_blank" class="btn btn-sm btn-outline-primary fw-bold header-action-btn shadow-xs" title="Monitoring Cloud Live">
            <i class="bi bi-activity"></i>
            <span class="d-none d-md-inline ms-1.5">Monitoring</span>
        </a>

        <!-- Instansi Mobile Pill Icon (Tampil di Mobile) -->
        <div class="d-flex d-md-none align-items-center justify-content-center bg-light border rounded-circle shadow-xs" style="width:34px;height:34px;" title="<?= htmlspecialchars($_SESSION['nama_instansi'] ?? 'Global System') ?>" data-bs-toggle="tooltip">
            <i class="bi bi-building text-primary" style="font-size:0.85rem;"></i>
        </div>

        <!-- Tombol Logout -->
        <a href="<?= API_URL ?>/logout" class="btn btn-sm btn-light border text-danger rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width:34px;height:34px;" title="Keluar Sistem">
            <i class="bi bi-power" style="font-size:1rem;"></i>
        </a>
    </div>
</div>

<!-- â• â• â• â• â• â• â• â• â• â• â•  MAIN CONTENT â• â• â• â• â• â• â• â• â• â• â•  -->
<div class="main-content">
    <?= $content ?>
</div>

<!-- Modal Riwayat Unduhan -->
<div class="modal fade" id="modalRiwayatUnduh" tabindex="-1" aria-labelledby="modalRiwayatUnduhLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRiwayatUnduhLabel"><i class="bi bi-clock-history"></i> Detail Riwayat Unduhan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Nama Santri</th>
                                <th>Jenis Dokumen</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatUnduhTableBody">
                            <tr><td colspan="4" class="text-center py-3">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Widget Background Download Progress (Professional Dual-Mode Dock) -->
<div id="bgDownloadWidget" class="download-dock-container d-none">
    <!-- 1. Compact Floating Capsule (Pill Mode - Minimal & Non-Intrusive) -->
    <div id="bgDownloadPill" class="download-pill shadow-lg d-flex align-items-center gap-2" onclick="toggleExpandWidget(true)" title="Klik untuk melihat detail unduhan">
        <div class="pill-icon-wrapper" id="pillIconWrapper">
            <i class="bi bi-cloud-arrow-down-fill text-primary" id="pillDownloadIcon"></i>
        </div>
        <div class="pill-info text-truncate">
            <div class="pill-title fw-bold text-truncate" id="pillDownloadTitle">Mengunduh...</div>
            <div class="pill-count text-muted" id="pillDownloadCount" style="font-size:0.7rem;">0/0 Berkas</div>
        </div>
        <div class="pill-progress-mini px-1">
            <div class="progress" style="width: 44px; height: 5px; border-radius: 3px; background-color: #e2e8f0;">
                <div id="pillProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%; border-radius: 3px;"></div>
            </div>
        </div>
        <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" id="pillDownloadPct" style="font-size:0.75rem;">0%</span>
        <div class="pill-actions ms-auto d-flex align-items-center gap-1" onclick="event.stopPropagation()">
            <button type="button" class="btn-dock-icon" title="Lihat Detail" onclick="toggleExpandWidget(true)">
                <i class="bi bi-chevron-up"></i>
            </button>
            <button type="button" class="btn-dock-icon text-muted" title="Sembunyikan ke Header" onclick="closeDockWidget()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- 2. Expanded Detail Card -->
    <div id="bgDownloadCard" class="download-card shadow-2xl d-none">
        <!-- Card Header -->
        <div class="card-header-gradient d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 250px;">
                <div class="status-pulse-dot" id="headerPulseDot"></div>
                <div class="text-truncate">
                    <div class="fw-bold text-white fs-6 text-truncate" id="bgDownloadTitle">Mengunduh Berkas</div>
                    <small class="text-white-50 text-truncate d-block" id="bgDownloadSubtitle" style="font-size:0.72rem;">Proses latar belakang server</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn-dock-header" title="Minimize ke Kapsul" onclick="toggleExpandWidget(false)">
                    <i class="bi bi-dash-lg"></i>
                </button>
                <button type="button" class="btn-dock-header" title="Tutup" onclick="closeDockWidget()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Card Body -->
        <div class="download-card-body p-3">
            <!-- Progress Section -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span id="bgDownloadText" class="fw-semibold text-secondary small">Selesai: 0/0</span>
                    <span id="bgDownloadPct" class="badge bg-primary text-white fw-bold px-2 py-1">0%</span>
                </div>
                <div class="progress" style="height: 7px; border-radius: 6px; background-color: #e2e8f0;">
                    <div id="bgDownloadBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 6px;"></div>
                </div>
            </div>

            <!-- Global Action Toolbar -->
            <div id="bgDownloadGlobalActions" class="d-flex gap-2 justify-content-between mb-3">
                <button id="btnPauseResumeAll" class="btn btn-sm btn-light border flex-fill text-secondary fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('pause', 'ALL')">
                    <i class="bi bi-pause-fill me-1"></i> Pause All
                </button>
                <button id="btnCancelDownload" class="btn btn-sm btn-light border text-danger flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('cancel', 'ALL')">
                    <i class="bi bi-x-circle me-1"></i> Batalkan Sisa
                </button>
            </div>

            <!-- Santri List Header -->
            <div class="d-flex justify-content-between align-items-center px-1 mb-2">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">Antrean Santri</span>
                <span class="badge bg-light text-secondary border" id="bgDownloadTotalItems" style="font-size: 0.68rem;">0 Santri</span>
            </div>

            <!-- Santri List Scroll Area -->
            <div id="bgDownloadSantriList" class="santri-download-list custom-scroll">
                <!-- Items populated via JS -->
            </div>
        </div>
    </div>
</div>

<script>
let isDockExpanded = false; // default to compact pill so it doesn't block screen
let dockClosedByUser = false;

function toggleExpandWidget(expand) {
    isDockExpanded = expand;
    const pill = document.getElementById('bgDownloadPill');
    const card = document.getElementById('bgDownloadCard');
    if (expand) {
        if (pill) pill.classList.add('d-none');
        if (card) card.classList.remove('d-none');
    } else {
        if (card) card.classList.add('d-none');
        if (pill) pill.classList.remove('d-none');
    }
}

function closeDockWidget() {
    dockClosedByUser = true;
    const widget = document.getElementById('bgDownloadWidget');
    if (widget) widget.classList.add('d-none');
}

function toggleDownloadWidgetFromHeader() {
    dockClosedByUser = false;
    const widget = document.getElementById('bgDownloadWidget');
    if (widget) {
        widget.classList.remove('d-none');
        toggleExpandWidget(true); // Open full card when clicked from header
    }
}
</script>

<script src="<?= ASSET_URL ?>/assets/offline/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>
<script>
// â”€â”€ Sidebar expand/collapse toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function isMobile() { return window.innerWidth <= 768; }

document.getElementById('sidebarToggle').addEventListener('click', function() {
    if (isMobile()) {
        // Mobile: offcanvas slide-in
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open-mobile');
        overlay.classList.toggle('active');
    } else {
        // Desktop: collapse/expand
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarState', document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
    }
});

// Close sidebar when overlay is clicked (mobile)
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('open-mobile');
    this.classList.remove('active');
});

// Auto-close sidebar when a nav-link is clicked (mobile)
document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (isMobile()) {
            document.querySelector('.sidebar').classList.remove('open-mobile');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }
    });
});

// Handle resize: clean up mobile classes when switching to desktop
window.addEventListener('resize', function() {
    if (!isMobile()) {
        document.querySelector('.sidebar').classList.remove('open-mobile');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }
});

// â”€â”€ Collapsible category groups â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleGroup(id) {
    if (document.body.classList.contains('sidebar-collapsed')) return;
    const grp = document.getElementById('grp-' + id);
    const arr = document.getElementById('arr-' + id);
    if (!grp) return;
    const isOpen = !grp.classList.contains('collapsed');
    grp.classList.toggle('collapsed', isOpen);
    if (arr) arr.classList.toggle('open', !isOpen);
    const saved = JSON.parse(localStorage.getItem('sidebarGroups') || '{}');
    saved[id] = !isOpen; // true = open
    localStorage.setItem('sidebarGroups', JSON.stringify(saved));
}

// Restore group open/close state on page load
(function() {
    const saved = JSON.parse(localStorage.getItem('sidebarGroups') || '{}');
    const activeGroups = <?= json_encode($activeGroups) ?>;
    ['main','masterdata','masterprint','keuangan','lainlain','sistem'].forEach(function(id) {
        const grp = document.getElementById('grp-' + id);
        const arr = document.getElementById('arr-' + id);
        if (!grp) return;
        // Active group always stays open; otherwise use saved state (default: open)
        const open = activeGroups.includes(id) ? true : (saved[id] !== undefined ? saved[id] : true);
        grp.classList.toggle('collapsed', !open);
        if (arr) arr.classList.toggle('open', open);
    });
})();

// â”€â”€ Notification Badges â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', function() {

    let lastPendingCount = -1;
    let lastApprovedCount = -1;
    let lastRejectedCount = -1;

    function fetchNotificationBadges() {
        // Badge: Persetujuan Data (pending, approved, rejected)
        fetch('<?= API_URL ?>/api/request-edit/pending-count')
            .then(function(r) { 
                if (r.status === 401) { window.location.href = '<?= API_URL ?>/login'; throw new Error('Unauthorized'); }
                return r.json(); 
            })
            .then(function(data) {
                if (data) {
                    var badgePending = document.getElementById('badge-approval');
                    if (badgePending) {
                        if (data.pending > 0) {
                            badgePending.textContent = data.pending > 99 ? '99+' : data.pending;
                            badgePending.classList.remove('d-none');
                        } else {
                            badgePending.classList.add('d-none');
                        }
                    }
                    var badgeApproved = document.getElementById('badge-approved');
                    if (badgeApproved) {
                        if (data.approved > 0) {
                            badgeApproved.textContent = data.approved > 99 ? '99+' : data.approved;
                            badgeApproved.classList.remove('d-none');
                        } else {
                            badgeApproved.classList.add('d-none');
                        }
                    }
                    var badgeRejected = document.getElementById('badge-rejected');
                    if (badgeRejected) {
                        if (data.rejected > 0) {
                            badgeRejected.textContent = data.rejected > 99 ? '99+' : data.rejected;
                            badgeRejected.classList.remove('d-none');
                        } else {
                            badgeRejected.classList.add('d-none');
                        }
                    }

                    // Auto-refresh logic if on Persetujuan Data page and NEW data arrived
                    if (window.location.pathname.includes('/request-edit') || window.location.pathname.includes('/persetujuan')) {
                        let hasNewData = false;
                        if (lastPendingCount !== -1 && data.pending > lastPendingCount) hasNewData = true;
                        if (lastApprovedCount !== -1 && data.approved > lastApprovedCount) hasNewData = true;
                        if (lastRejectedCount !== -1 && data.rejected > lastRejectedCount) hasNewData = true;

                        if (hasNewData) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: 'Ada pembaruan data usulan baru!',
                                text: 'Memuat ulang halaman...',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            setTimeout(() => window.location.reload(), 2000);
                        }

                        lastPendingCount = data.pending;
                        lastApprovedCount = data.approved;
                        lastRejectedCount = data.rejected;
                    }
                }
            }).catch(function() {});

        // Badge: Log Aktivitas (unread count)
        fetch('<?= API_URL ?>/api/audit-log/unread-count')
            .then(function(r) { 
                if (r.status === 401) { window.location.href = '<?= API_URL ?>/login'; throw new Error('Unauthorized'); }
                return r.json(); 
            })
            .then(function(data) {
                if (data) {
                    var badge = document.getElementById('badge-audit');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }
                }
            }).catch(function() {});
    }

    // Panggil saat pertama kali load
    fetchNotificationBadges();
    
    // Polling setiap 10 detik agar terasa "Real-Time"
    setInterval(fetchNotificationBadges, 10000);

    // Clear audit badge when the link is clicked (before navigation)
    var auditLink = document.getElementById('link-audit-log');
    if (auditLink) {
        auditLink.addEventListener('click', function() {
            var badge = document.getElementById('badge-audit');
            if (badge) badge.classList.add('d-none');
        });
    }

    // â”€â”€ Global drag-to-select checkboxes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    var isDragging = false;
    var dragToggleState = true;
    document.addEventListener('mousedown', function(e) {
        if (e.target.closest('button, a, select, textarea') && !e.target.closest('td:first-child')) return;
        if (e.target.tagName && e.target.tagName.toLowerCase() === 'input' && !e.target.classList.contains('row-cb') && !e.target.classList.contains('kds-checkbox') && e.target.type !== 'checkbox') return;
        var tr = e.target.closest('tr');
        if (!tr) return;
        var cb = tr.querySelector('input[type="checkbox"].row-cb, input[type="checkbox"].kds-checkbox');
        if (cb) {
            isDragging = true;
            if (!e.target.classList.contains('row-cb') && !e.target.classList.contains('kds-checkbox')) { cb.checked = !cb.checked; cb.dispatchEvent(new Event('change', { bubbles: true })); }
            dragToggleState = cb.checked;
            if (!e.target.closest('input, select, textarea')) e.preventDefault();
        }
    });
    document.addEventListener('mouseover', function(e) {
        if (!isDragging) return;
        var tr = e.target.closest('tr');
        if (tr) {
            var cb = tr.querySelector('input[type="checkbox"].row-cb, input[type="checkbox"].kds-checkbox');
            if (cb && cb.checked !== dragToggleState) { cb.checked = dragToggleState; cb.dispatchEvent(new Event('change', { bubbles: true })); }
        }
    });
    document.addEventListener('mouseup', function() { isDragging = false; });

    let lastPercent = -1;
    let autoHideTimer = null;

    function fetchDownloadStatus() {
        fetch('<?= API_URL ?>/api/capel/download-status')
            .then(r => {
                if (r.status === 401) { window.location.href = '<?= API_URL ?>/login'; throw new Error('Unauthorized'); }
                return r.json();
            })
            .then(res => {
                const widget = document.getElementById('bgDownloadWidget');
                const btnShow = document.getElementById('btnShowDownloadWidget');
                const badgeActive = document.getElementById('badgeDownloadActive');
                if (!widget) return;
                
                // Show header navbar button if there is any download history/activity
                if (res.total > 0 && btnShow) {
                    btnShow.style.display = 'block';
                }
                if (res.active && badgeActive) {
                    badgeActive.classList.remove('d-none');
                } else if (badgeActive) {
                    badgeActive.classList.add('d-none');
                }
                
                let effectiveTotal = res.total - (res.failed || 0);

                // --- 1. ACTIVE STATE ---
                if (res.active) {
                    if (autoHideTimer) { clearTimeout(autoHideTimer); autoHideTimer = null; }
                    
                    if (!dockClosedByUser) {
                        widget.classList.remove('d-none');
                        toggleExpandWidget(isDockExpanded);
                    }

                    // Compact Pill updates
                    const pillTitle = document.getElementById('pillDownloadTitle');
                    const pillCount = document.getElementById('pillDownloadCount');
                    const pillPct = document.getElementById('pillDownloadPct');
                    const pillBar = document.getElementById('pillProgressBar');
                    const pillIcon = document.getElementById('pillDownloadIcon');
                    const pillIconWrapper = document.getElementById('pillIconWrapper');

                    if (res.paused_all) {
                        if (pillTitle) pillTitle.textContent = 'Semua Di-jeda';
                        if (pillIcon) pillIcon.className = 'bi bi-pause-circle text-warning';
                        if (pillIconWrapper) pillIconWrapper.style.backgroundColor = '#fef3c7';
                    } else {
                        if (pillTitle) pillTitle.textContent = res.current ? 'Mengunduh: ' + res.current : 'Mengunduh Berkas...';
                        if (pillIcon) pillIcon.className = 'bi bi-cloud-arrow-down-fill text-primary';
                        if (pillIconWrapper) pillIconWrapper.style.backgroundColor = '#eff6ff';
                    }

                    if (pillCount) pillCount.textContent = res.completed + '/' + effectiveTotal + ' Berkas';
                    if (pillPct) pillPct.textContent = res.percent + '%';
                    if (pillBar) pillBar.style.width = res.percent + '%';

                    // Expanded Card updates
                    const cardTitle = document.getElementById('bgDownloadTitle');
                    const cardSubtitle = document.getElementById('bgDownloadSubtitle');
                    const cardPulse = document.getElementById('headerPulseDot');
                    const cardText = document.getElementById('bgDownloadText');
                    const cardPct = document.getElementById('bgDownloadPct');
                    const cardBar = document.getElementById('bgDownloadBar');
                    const cardActions = document.getElementById('bgDownloadGlobalActions');
                    const totalItems = document.getElementById('bgDownloadTotalItems');

                    if (cardTitle) cardTitle.textContent = res.current ? 'Mengunduh: ' + res.current : 'Mengunduh Berkas';
                    if (cardSubtitle) cardSubtitle.textContent = res.total + ' total berkas antrean server';
                    if (cardPulse) cardPulse.className = res.paused_all ? 'status-pulse-dot paused' : 'status-pulse-dot';
                    if (cardText) cardText.textContent = 'Selesai: ' + res.completed + '/' + effectiveTotal + ' Berkas';
                    if (cardPct) cardPct.textContent = res.percent + '%';
                    if (cardBar) cardBar.style.width = res.percent + '%';

                    if (cardActions) {
                        if (res.paused_all) {
                            cardActions.innerHTML = `
                                <button class="btn btn-sm btn-light border text-primary flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('resume', 'ALL')">
                                    <i class="bi bi-play-fill me-1"></i> Lanjutkan Semua
                                </button>
                                <button class="btn btn-sm btn-light border text-danger flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('cancel', 'ALL')">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan Sisa
                                </button>
                            `;
                        } else {
                            cardActions.innerHTML = `
                                <button class="btn btn-sm btn-light border text-secondary flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('pause', 'ALL')">
                                    <i class="bi bi-pause-fill me-1"></i> Jeda Semua
                                </button>
                                <button class="btn btn-sm btn-light border text-danger flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="doDownloadAction('cancel', 'ALL')">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan Sisa
                                </button>
                            `;
                        }
                    }

                    if (totalItems && res.santri_list) {
                        totalItems.textContent = res.santri_list.length + ' Santri';
                    }

                    lastPercent = res.percent;

                // --- 2. FINISHED / IDLE STATE ---
                } else if (res.total > 0 && lastPercent !== -1) {
                    const pillTitle = document.getElementById('pillDownloadTitle');
                    const pillIcon = document.getElementById('pillDownloadIcon');
                    const pillIconWrapper = document.getElementById('pillIconWrapper');
                    const pillCount = document.getElementById('pillDownloadCount');
                    const pillPct = document.getElementById('pillDownloadPct');
                    const pillBar = document.getElementById('pillProgressBar');

                    const cardTitle = document.getElementById('bgDownloadTitle');
                    const cardSubtitle = document.getElementById('bgDownloadSubtitle');
                    const cardPulse = document.getElementById('headerPulseDot');
                    const cardText = document.getElementById('bgDownloadText');
                    const cardPct = document.getElementById('bgDownloadPct');
                    const cardBar = document.getElementById('bgDownloadBar');
                    const cardActions = document.getElementById('bgDownloadGlobalActions');

                    let statusText = 'Selesai Semua';
                    let isAllFail = (res.failed > 0 && res.completed === 0);
                    let isPartialFail = (res.failed > 0 && res.completed > 0);

                    if (isAllFail) {
                        statusText = 'Pengunduhan Dibatalkan';
                        if (pillIcon) pillIcon.className = 'bi bi-x-circle-fill text-danger';
                        if (pillIconWrapper) pillIconWrapper.style.backgroundColor = '#fee2e2';
                        if (cardPulse) cardPulse.className = 'status-pulse-dot paused';
                    } else if (isPartialFail) {
                        statusText = 'Selesai (Ada Gagal)';
                        if (pillIcon) pillIcon.className = 'bi bi-exclamation-circle-fill text-warning';
                        if (pillIconWrapper) pillIconWrapper.style.backgroundColor = '#fef3c7';
                        if (cardPulse) cardPulse.className = 'status-pulse-dot paused';
                    } else {
                        statusText = 'Selesai Semua';
                        if (pillIcon) pillIcon.className = 'bi bi-check-circle-fill text-success';
                        if (pillIconWrapper) pillIconWrapper.style.backgroundColor = '#dcfce7';
                        if (cardPulse) cardPulse.className = 'status-pulse-dot success';
                    }

                    if (pillTitle) pillTitle.textContent = statusText;
                    if (pillCount) pillCount.textContent = res.completed + '/' + effectiveTotal + ' Berkas';
                    if (pillPct) pillPct.textContent = res.percent + '%';
                    if (pillBar) pillBar.style.width = res.percent + '%';

                    if (cardTitle) cardTitle.textContent = statusText;
                    if (cardSubtitle) cardSubtitle.textContent = 'Proses unduhan berkas telah selesai';
                    if (cardText) cardText.textContent = 'Selesai: ' + res.completed + '/' + effectiveTotal + ' Berkas';
                    if (cardPct) cardPct.textContent = res.percent + '%';
                    if (cardBar) cardBar.style.width = res.percent + '%';

                    if (cardActions) {
                        cardActions.innerHTML = `
                            <button class="btn btn-sm btn-light border text-primary flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="openRiwayatUnduh()">
                                <i class="bi bi-clock-history me-1"></i> Detail
                            </button>
                            <button class="btn btn-sm btn-light border text-danger flex-fill fw-semibold py-1" style="font-size: 0.78rem;" onclick="clearDownloadHistory()">
                                <i class="bi bi-trash me-1"></i> Bersihkan
                            </button>
                        `;
                    }

                    lastPercent = -1; // Reset until new download begins

                    // Auto hide if minimized in pill mode after 10 seconds
                    if (!isDockExpanded) {
                        autoHideTimer = setTimeout(() => {
                            if (widget && !isDockExpanded) widget.classList.add('d-none');
                        }, 10000);
                    }

                } else if (res.total === 0) {
                    widget.classList.add('d-none');
                }

                // Render Santri Items List
                if (res.total > 0 && res.santri_list) {
                    const listContainer = document.getElementById('bgDownloadSantriList');
                    if (listContainer) {
                        listContainer.innerHTML = '';
                        res.santri_list.forEach(item => {
                            let statusBadge = '';
                            let actionBtns = '';

                            if (item.status === 'Mengunduh') {
                                statusBadge = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.65rem;"><span class="spinner-border spinner-border-sm me-1" style="width:0.55rem;height:0.55rem;border-width:1px;"></span>Mengunduh</span>`;
                                actionBtns = `
                                    <button onclick="doDownloadAction('pause', '${item.kode_santri}')" class="btn-dock-icon text-secondary" title="Jeda"><i class="bi bi-pause-fill"></i></button>
                                    <button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn-dock-icon text-danger" title="Batalkan"><i class="bi bi-x"></i></button>
                                `;
                            } else if (item.status === 'Jeda') {
                                statusBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.65rem;"><i class="bi bi-pause me-1"></i>Jeda</span>`;
                                actionBtns = `
                                    <button onclick="doDownloadAction('resume', '${item.kode_santri}')" class="btn-dock-icon text-success" title="Lanjutkan"><i class="bi bi-play-fill"></i></button>
                                    <button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn-dock-icon text-danger" title="Batalkan"><i class="bi bi-x"></i></button>
                                `;
                            } else if (item.status === 'Menunggu') {
                                statusBadge = `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:0.65rem;">Menunggu</span>`;
                                actionBtns = `
                                    <button onclick="doDownloadAction('pause', '${item.kode_santri}')" class="btn-dock-icon text-secondary" title="Jeda"><i class="bi bi-pause-fill"></i></button>
                                    <button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn-dock-icon text-danger" title="Batalkan"><i class="bi bi-x"></i></button>
                                `;
                            } else if (item.status === 'Selesai') {
                                statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;"><i class="bi bi-check2 me-1"></i>Selesai</span>`;
                            } else if (item.status === 'Dibatalkan') {
                                statusBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:0.65rem;">Dibatalkan</span>`;
                            } else if (item.status === 'Selesai (Ada Gagal)') {
                                statusBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.65rem;">Ada Gagal</span>`;
                            }

                            let itemEffective = item.total - (item.failed || 0);
                            let initial = (item.nama && item.nama.trim().length > 0) ? item.nama.trim().charAt(0).toUpperCase() : 'S';

                            listContainer.innerHTML += `
                                <div class="santri-dl-item">
                                    <div class="d-flex align-items-center text-truncate me-2" style="max-width: 220px;">
                                        <div class="santri-avatar me-2">${initial}</div>
                                        <div class="text-truncate">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.78rem;">${item.nama}</div>
                                            <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.68rem;">
                                                <span>${item.completed}/${itemEffective} Berkas</span>
                                                ${statusBadge}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        ${actionBtns}
                                    </div>
                                </div>
                            `;
                        });
                    }
                }
            })
            .catch(() => {});
    }
    
    // Poll every 3 seconds
    setInterval(fetchDownloadStatus, 3000);
    window.openRiwayatUnduh = function() {
        const modal = new bootstrap.Modal(document.getElementById('modalRiwayatUnduh'));
        modal.show();
        
        document.getElementById('riwayatUnduhTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat detail...</td></tr>';
        
        fetch('<?= API_URL ?>/api/capel/download-history')
            .then(r => r.json())
            .then(res => {
                let html = '';
                if (!res.files || res.files.length === 0) {
                    html = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada riwayat unduhan aktif.</td></tr>';
                } else {
                    res.files.forEach(f => {
                        let statusBadge = '';
                        if (f.status === 'downloading') statusBadge = '<span class="badge bg-primary"><i class="bi bi-arrow-repeat spinner-border spinner-border-sm" style="width:0.7rem;height:0.7rem;border-width:1px;"></i> Mengunduh</span>';
                        else if (f.status === 'completed') statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai</span>';
                        else if (f.status === 'failed') statusBadge = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Gagal</span>';
                        else if (f.status === 'paused') statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-pause-circle"></i> Jeda</span>';
                        else statusBadge = '<span class="badge bg-secondary"><i class="bi bi-hourglass"></i> Menunggu</span>';
                        
                        let ket = f.error_msg ? `<span class="text-danger">${f.error_msg}</span>` : '-';
                        let nama = f.nama || f.kode_santri;
                        
                        html += `<tr>
                            <td class="fw-bold">${nama}</td>
                            <td>${f.jenis_dokumen}</td>
                            <td>${statusBadge}</td>
                            <td>${ket}</td>
                        </tr>`;
                    });
                }
                document.getElementById('riwayatUnduhTableBody').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('riwayatUnduhTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
            });
    };

    window.clearDownloadHistory = function() {
        fetch('<?= API_URL ?>/api/capel/download-clear', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        }).then(() => {
            document.getElementById('bgDownloadWidget').classList.add('d-none');
            fetchDownloadStatus();
        });
    };
    fetchDownloadStatus();

    window.doDownloadAction = function(action, kode) {
        fetch('<?= API_URL ?>/api/capel/download-action', { 
            method: 'POST',
            body: JSON.stringify({action: action, kode_santri: kode}),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        }).then(() => fetchDownloadStatus());
    };
    
    const btnShow = document.getElementById('btnShowDownloadWidget');
    if (btnShow) {
        btnShow.addEventListener('click', function() {
            const widget = document.getElementById('bgDownloadWidget');
            if (widget) {
                widget.classList.remove('d-none');
                if (widgetMinimized) toggleMinimizeWidget(); // expand if minimized
            }
        });
    }
});
</script>

<?php if (isset($_SESSION['user_id'])): ?>
<script>
// Presence Heartbeat
const getPresenceUrl = () => {
    const path = window.location.pathname;
    const publicIdx = path.indexOf('/public');
    const base = publicIdx !== -1 ? path.substring(0, publicIdx + 7) : '';
    return base + '/presence.php';
};

setInterval(() => {
    fetch(getPresenceUrl(), {
        method: 'GET',
        credentials: 'same-origin'
    }).catch(err => console.error('Presence ping failed', err));
}, 900000); // every 15 minutes

// Initial ping
setTimeout(() => {
    fetch(getPresenceUrl(), {
        method: 'GET',
        credentials: 'same-origin'
    }).catch(e => {});
}, 2000);

// Logout ping on close/navigate
window.addEventListener('pagehide', function() {
    navigator.sendBeacon(getPresenceUrl() + '?action=offline');
});
</script>
<?php endif; ?>

<script>
// ── PWA & Service Worker Registration ─────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swPath = '<?= ASSET_URL ?>/sw.js';
        navigator.serviceWorker.register(swPath).then((reg) => {
            console.log('SiPLN PWA ServiceWorker ready with scope:', reg.scope);
        }).catch((err) => {
            console.warn('ServiceWorker registration error:', err);
        });
    });
}

// PWA Install Prompt Handler
let deferredPwaPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPwaPrompt = e;
    
    // Tampilkan tombol instalasi di header dan sidebar
    const btnHeader = document.getElementById('btnPwaInstallHeader');
    const containerSidebar = document.getElementById('sidebarInstallContainer');
    if (btnHeader) {
        btnHeader.classList.remove('d-none');
        btnHeader.classList.add('d-inline-flex');
    }
    if (containerSidebar) {
        containerSidebar.classList.remove('d-none');
    }
});

window.triggerPwaInstall = async function() {
    if (deferredPwaPrompt) {
        deferredPwaPrompt.prompt();
        const choiceResult = await deferredPwaPrompt.userChoice;
        if (choiceResult.outcome === 'accepted') {
            console.log('User accepted PWA installation');
        } else {
            console.log('User dismissed PWA installation');
        }
        deferredPwaPrompt = null;
    } else {
        // Panduan jika browser butuh instalasi manual atau sudah terinstall
        Swal.fire({
            title: '<strong>Install SiPLN ke Desktop</strong>',
            icon: 'info',
            html: `
                <div class="text-start" style="font-size: 0.88rem; line-height: 1.6;">
                    <p class="mb-2">Untuk menginstal aplikasi SiPLN langsung ke Desktop Anda:</p>
                    <div class="p-3 bg-light border rounded mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle">1</span>
                            <span>Periksa bilah alamat URL di kanan atas browser Anda.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary rounded-circle">2</span>
                            <span>Klik ikon <strong>Install / Pasang ( <i class="bi bi-display text-primary"></i> / <i class="bi bi-download text-primary"></i> )</strong>.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle">3</span>
                            <span>Atau klik titik tiga <strong>( ⋮ )</strong> &rarr; pilih <strong>"Simpan dan bagikan" / "Aplikasi"</strong> &rarr; klik <strong>"Install SiPLN"</strong>.</span>
                        </div>
                    </div>
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>Setelah dipasang, shortcut SiPLN akan otomatis tersedia di <strong>Desktop</strong> komputer Anda!</div>
                    </div>
                </div>
            `,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Mengerti',
            confirmButtonColor: '#3461ff'
        });
    }
};

window.addEventListener('appinstalled', () => {
    deferredPwaPrompt = null;
    const btnHeader = document.getElementById('btnPwaInstallHeader');
    const containerSidebar = document.getElementById('sidebarInstallContainer');
    if (btnHeader) btnHeader.classList.add('d-none');
    if (containerSidebar) containerSidebar.classList.add('d-none');
    
    Swal.fire({
        title: 'Berhasil Terpasang!',
        text: 'Aplikasi SiPLN berhasil diinstal ke Desktop Anda. Sekarang Anda dapat membukanya langsung dari Desktop kapan saja!',
        icon: 'success',
        confirmButtonColor: '#3461ff'
    });
});
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
