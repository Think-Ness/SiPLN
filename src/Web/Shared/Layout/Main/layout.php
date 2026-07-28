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
if ($isActive('/job-desk/settings') || $isActive('/profil-instansi') || $isActive('/anggota-kamar') || $isActive('/surat-generator')) $activeGroups[] = 'lainlain';
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

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            /* Sidebar: offcanvas slide-in */
            .sidebar {
                transform: translateX(-100%);
                z-index: 1100;
                width: 260px !important;
                transition: transform .3s ease;
            }
            .sidebar.open-mobile {
                transform: translateX(0);
            }
            /* Hide collapsed state overrides on mobile */
            body.sidebar-collapsed .sidebar {
                width: 260px !important;
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

            /* Header: full width */
            .main-header {
                left: 0 !important;
                padding: 0 12px;
            }
            /* Content: full width, smaller padding */
            .main-content {
                margin-left: 0 !important;
                padding: 14px 10px;
            }

            /* Hide non-essential header items */
            .main-header .d-none.d-md-flex { display: none !important; }
            .main-header .text-muted.small { display: none !important; }

            /* Download widget: fit mobile screen */
            #bgDownloadWidget {
                left: 8px;
                right: 8px;
                padding: 8px !important;
            }
            #bgDownloadWidget .toast {
                width: 100% !important;
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
                flex: 1;
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

            /* Table font smaller */
            table { font-size: 0.78rem !important; }
        }

        /* Small phone adjustments */
        @media (max-width: 480px) {
            .main-content { padding: 10px 6px; }
            h4 { font-size: 1.1rem !important; }
            .card-body { padding: 0.75rem !important; }
            .stat-card .fs-2 { font-size: 1.5rem !important; }
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
<div class="main-header">
    <div class="d-flex align-items-center gap-3">
        <button id="sidebarToggle" class="btn btn-sm text-secondary p-0 border-0 bg-transparent">
            <i class="bi bi-list" style="font-size:1.5rem;"></i>
        </button>
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i> Online
        </span>
        <div class="d-none d-md-flex align-items-center ms-3 px-3 py-1 bg-light rounded-pill border">
            <i class="bi bi-building text-primary me-2"></i>
            <span class="small fw-bold text-dark"><?= htmlspecialchars($_SESSION['nama_instansi'] ?? 'Global System') ?></span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button id="btnShowDownloadWidget" class="btn btn-sm text-primary p-0 border-0 bg-transparent position-relative" title="Status Unduhan Latar Belakang" style="display:none;">
            <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
            <span id="badgeDownloadActive" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none"></span>
        </button>
        <a href="https://pln-monitoring-murex.vercel.app/guidebook.html" target="_blank" class="btn btn-sm btn-outline-info fw-bold" style="border-radius: 20px;" title="Buka Guidebook">
            <i class="bi bi-book me-1"></i> Guidebook
        </a>
        <a href="https://pln-monitoring-murex.vercel.app/" target="_blank" class="btn btn-sm btn-outline-primary fw-bold" style="border-radius: 20px;">
            <i class="bi bi-activity me-1"></i> Monitoring
        </a>
        <a href="<?= API_URL ?>/logout" class="text-danger" title="Keluar Sistem"><i class="bi bi-power fs-5"></i></a>
    </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• MAIN CONTENT â•â•â•â•â•â•â•â•â•â•â• -->
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

<!-- Widget Background Download Progress -->
<div id="bgDownloadWidget" class="position-fixed bottom-0 end-0 p-3 d-none" style="z-index: 1050; transition: all 0.3s;">
    <div class="toast show bg-white shadow border-0" role="alert" aria-live="assertive" aria-atomic="true" id="bgDownloadToast" style="width: 350px;">
        <div class="toast-header bg-primary text-white border-0" style="cursor: pointer;" onclick="toggleMinimizeWidget(event)">
            <i class="bi bi-cloud-arrow-down me-2" id="bgDownloadIcon"></i>
            <strong class="me-auto text-truncate" id="bgDownloadTitle" style="max-width: 200px;">Mengunduh Berkas</strong>
            <span id="bgDownloadMinPct" class="badge bg-light text-primary me-2 d-none">0%</span>
            <button type="button" class="btn text-white p-0 me-2 border-0" id="btnMinimizeWidget" style="background:transparent;"><i class="bi bi-dash-lg"></i></button>
            <button type="button" class="btn-close btn-close-white" onclick="event.stopPropagation(); document.getElementById('bgDownloadWidget').classList.add('d-none')"></button>
        </div>
        <div class="toast-body p-0" id="bgDownloadBody">
            <div class="p-2 border-bottom">
                <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                    <span id="bgDownloadText" class="fw-bold">Selesai: 0/0</span>
                    <span id="bgDownloadPct" class="fw-bold">0%</span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div id="bgDownloadBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="bgDownloadGlobalActions" class="d-flex gap-2 justify-content-end mt-2">
                    <button id="btnPauseResumeAll" class="btn btn-sm btn-outline-secondary py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('pause', 'ALL')"><i class="bi bi-pause-fill"></i> Pause All</button>
                    <button id="btnCancelDownload" class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('cancel', 'ALL')"><i class="bi bi-x"></i> Batalkan Sisa</button>
                </div>
            </div>
            <!-- Daftar Santri -->
            <div id="bgDownloadSantriList" style="max-height: 200px; overflow-y: auto; background: #f8f9fa;">
                <!-- items go here -->
            </div>
        </div>
    </div>
</div>

<script>
let widgetMinimized = false;
function toggleMinimizeWidget(e) {
    if(e && e.target.classList.contains('btn-close')) return;
    widgetMinimized = !widgetMinimized;
    const body = document.getElementById('bgDownloadBody');
    const minPct = document.getElementById('bgDownloadMinPct');
    const title = document.getElementById('bgDownloadTitle');
    
    if (widgetMinimized) {
        body.classList.add('d-none');
        minPct.classList.remove('d-none');
        title.classList.add('d-none');
    } else {
        body.classList.remove('d-none');
        minPct.classList.add('d-none');
        title.classList.remove('d-none');
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
                
                // Show header button if there is any history/activity
                if (res.total > 0 && btnShow) {
                    btnShow.style.display = 'block';
                }
                if (res.active && badgeActive) {
                    badgeActive.classList.remove('d-none');
                } else if (badgeActive) {
                    badgeActive.classList.add('d-none');
                }
                
                // Only show if active, or if it just finished (to show 100% briefly)
                if (res.active) {
                    widget.classList.remove('d-none');
                    let currentTxt = 'Mengunduh Berkas';
                    if (res.paused_all) {
                        currentTxt = 'Semua Di-jeda';
                        document.getElementById('bgDownloadIcon').className = 'bi bi-pause-circle me-2';
                        document.getElementById('bgDownloadBar').classList.remove('progress-bar-animated');
                        document.getElementById('bgDownloadGlobalActions').innerHTML = `
                            <button class="btn btn-sm btn-outline-primary py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('resume', 'ALL')"><i class="bi bi-play-fill"></i> Resume All</button>
                            <button class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('cancel', 'ALL')"><i class="bi bi-x"></i> Batalkan Sisa</button>
                        `;
                    } else {
                        if (res.current) currentTxt = 'Mengunduh: ' + res.current;
                        document.getElementById('bgDownloadIcon').className = 'bi bi-cloud-arrow-down me-2';
                        document.getElementById('bgDownloadBar').classList.add('progress-bar-animated');
                        document.getElementById('bgDownloadGlobalActions').innerHTML = `
                            <button class="btn btn-sm btn-outline-secondary py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('pause', 'ALL')"><i class="bi bi-pause-fill"></i> Pause All</button>
                            <button class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.75rem;" onclick="doDownloadAction('cancel', 'ALL')"><i class="bi bi-x"></i> Batalkan Sisa</button>
                        `;
                    }
                    document.getElementById('bgDownloadTitle').textContent = currentTxt;
                    let effectiveTotal = res.total - (res.failed || 0);
                    document.getElementById('bgDownloadText').textContent = 'Selesai: ' + res.completed + '/' + effectiveTotal;
                    document.getElementById('bgDownloadPct').textContent = res.percent + '%';
                    document.getElementById('bgDownloadMinPct').textContent = res.percent + '%';
                    document.getElementById('bgDownloadBar').style.width = res.percent + '%';
                    
                    lastPercent = res.percent;
                } else if (res.total > 0 && lastPercent !== -1) {
                    // Just finished or cancelled!
                    if (res.failed > 0 && res.completed === 0) {
                        document.getElementById('bgDownloadTitle').textContent = 'Pengunduhan Dibatalkan';
                        document.getElementById('bgDownloadBar').classList.remove('bg-primary');
                        document.getElementById('bgDownloadBar').classList.add('bg-danger');
                    } else if (res.failed > 0) {
                        document.getElementById('bgDownloadTitle').textContent = 'Selesai (Sebagian Gagal)';
                        document.getElementById('bgDownloadBar').classList.remove('bg-primary');
                        document.getElementById('bgDownloadBar').classList.add('bg-warning');
                    } else {
                        document.getElementById('bgDownloadTitle').textContent = 'Selesai Semua';
                        document.getElementById('bgDownloadBar').classList.remove('bg-danger', 'bg-warning');
                        document.getElementById('bgDownloadBar').classList.add('bg-primary');
                    }
                    
                    document.getElementById('bgDownloadPct').textContent = res.percent + '%';
                    document.getElementById('bgDownloadMinPct').textContent = res.percent + '%';
                    document.getElementById('bgDownloadBar').style.width = res.percent + '%';
                    
                    let effectiveTotal2 = res.total - (res.failed || 0);
                    document.getElementById('bgDownloadText').textContent = 'Selesai: ' + res.completed + '/' + effectiveTotal2;
                    
                    // Add Bersihkan Riwayat button and Lihat Riwayat button
                    document.getElementById('bgDownloadGlobalActions').innerHTML = `
                        <button class="btn btn-sm btn-outline-primary py-0 w-50" style="font-size: 0.75rem;" onclick="openRiwayatUnduh()"><i class="bi bi-clock-history"></i> Detail</button>
                        <button class="btn btn-sm btn-outline-danger py-0 w-50" style="font-size: 0.75rem;" onclick="clearDownloadHistory()"><i class="bi bi-trash"></i> Bersihkan</button>
                    `;
                    
                    lastPercent = -1; // Prevent showing again until new downloads start
                    
                } else if (res.total === 0) {
                    widget.classList.add('d-none');
                }
                
                // ALWAYS render list if total > 0
                if (res.total > 0) {
                    const listContainer = document.getElementById('bgDownloadSantriList');
                    listContainer.innerHTML = '';
                    if (res.santri_list && res.santri_list.length > 0) {
                        res.santri_list.forEach(item => {
                            let statusBadge = '';
                            let actionBtns = '';
                            if (item.status === 'Mengunduh') {
                                statusBadge = '<span class="badge bg-primary ms-1" style="font-size:0.65rem;">Mengunduh</span>';
                                actionBtns = `<button onclick="doDownloadAction('pause', '${item.kode_santri}')" class="btn btn-sm btn-link text-secondary p-0 me-2" title="Pause"><i class="bi bi-pause-circle"></i></button>`;
                                actionBtns += `<button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn btn-sm btn-link text-danger p-0" title="Cancel"><i class="bi bi-x-circle"></i></button>`;
                            } else if (item.status === 'Jeda') {
                                statusBadge = '<span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Jeda</span>';
                                actionBtns = `<button onclick="doDownloadAction('resume', '${item.kode_santri}')" class="btn btn-sm btn-link text-success p-0 me-2" title="Resume"><i class="bi bi-play-circle"></i></button>`;
                                actionBtns += `<button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn btn-sm btn-link text-danger p-0" title="Cancel"><i class="bi bi-x-circle"></i></button>`;
                            } else if (item.status === 'Menunggu') {
                                statusBadge = '<span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Menunggu</span>';
                                actionBtns = `<button onclick="doDownloadAction('pause', '${item.kode_santri}')" class="btn btn-sm btn-link text-secondary p-0 me-2" title="Pause"><i class="bi bi-pause-circle"></i></button>`;
                                actionBtns += `<button onclick="doDownloadAction('cancel', '${item.kode_santri}')" class="btn btn-sm btn-link text-danger p-0" title="Cancel"><i class="bi bi-x-circle"></i></button>`;
                            } else if (item.status === 'Selesai') {
                                statusBadge = '<span class="badge bg-success ms-1" style="font-size:0.65rem;">Selesai</span>';
                            } else if (item.status === 'Dibatalkan') {
                                statusBadge = '<span class="badge bg-danger ms-1" style="font-size:0.65rem;">Dibatalkan</span>';
                            } else if (item.status === 'Selesai (Ada Gagal)') {
                                statusBadge = '<span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Selesai (Ada Gagal)</span>';
                            }
                            
                            let itemEffective = item.total - (item.failed || 0);
                            listContainer.innerHTML += `
                                <div class="p-2 border-bottom d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                                    <div class="text-truncate me-2" style="max-width: 180px;">
                                        <strong>${item.nama}</strong><br>
                                        <span class="text-muted">${item.completed}/${itemEffective} Berkas</span> ${statusBadge}
                                    </div>
                                    <div class="d-flex align-items-center">
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

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
