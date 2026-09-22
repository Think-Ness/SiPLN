<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPLN | Login Pengguna</title>
    <link rel="icon" href="<?= ASSET_URL ?>/assets/logopln.png" type="image/png">
    <link rel="manifest" href="<?= ASSET_URL ?>/manifest.json">
    <meta name="theme-color" content="#1a2035">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SiPLN">
    <link rel="apple-touch-icon" href="<?= ASSET_URL ?>/assets/icons/icon-192x192.png">
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= ASSET_URL ?>/assets/offline/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="<?= ASSET_URL ?>/assets/offline/js/sweetalert2.all.min.js"></script>

    <style>
        body {
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
        }

        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 950px;
            width: 100%;
            position: relative;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.3;
            z-index: 0;
        }

        .brand-panel {
            flex: 0 0 380px;
            height: 620px;
            background: linear-gradient(145deg, #0f172a, #1e293b);
            border-radius: 28px;
            box-shadow: 20px 20px 60px rgba(0, 0, 0, 0.15), 
                        inset 1px 1px 0 rgba(255,255,255,0.05);
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            color: white;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, #38bdf8 0%, rgba(56, 189, 248, 0) 70%);
            top: 25%;
            left: 50%;
            transform: translate(-50%, -50%);
            filter: blur(40px);
            opacity: 0.25;
            animation: pulseGlow 4s infinite alternate;
        }

        @keyframes pulseGlow {
            0% { opacity: 0.15; transform: translate(-50%, -50%) scale(0.9); }
            100% { opacity: 0.35; transform: translate(-50%, -50%) scale(1.1); }
        }

        .logo-showcase {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 2.5rem;
            z-index: 10;
        }

        .logo-glass {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 40px;
            backdrop-filter: blur(12px);
            transform: rotate(12deg);
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.3);
        }

        .logo-content {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-12deg);
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .logo-content img {
            max-height: 75px;
            filter: drop-shadow(0 8px 15px rgba(0,0,0,0.4));
        }

        .auth-container:hover .logo-glass {
            transform: rotate(0deg);
        }
        .auth-container:hover .logo-content {
            transform: rotate(0deg);
        }

        .brand-text {
            position: relative;
            z-index: 10;
        }

        .badge-custom {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        .login-panel {
            flex: 1;
            height: 560px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 0 28px 28px 0;
            margin-left: -40px;
            padding: 4.5rem 4.5rem 4.5rem 5.5rem;
            position: relative;
            z-index: 1;
            box-shadow: 10px 15px 40px rgba(0,0,0,0.05);
            border: 1px solid rgba(255, 255, 255, 1);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-weight: 800;
            font-size: 1.8rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }

        .form-floating-custom {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .form-floating-custom .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            transition: color 0.3s;
            z-index: 10;
        }

        .form-floating-custom .form-control {
            background: #f8fafc;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            padding: 1.5rem 1rem 0.7rem 3.2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            transition: all 0.3s;
            height: 64px;
        }

        .form-floating-custom .form-control:focus {
            background: #ffffff;
            border-color: #38bdf8;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15);
            outline: none;
        }

        .form-floating-custom .form-control:focus ~ .input-icon,
        .form-floating-custom .form-control:not(:placeholder-shown) ~ .input-icon {
            color: #38bdf8;
        }

        .form-floating-custom label {
            position: absolute;
            left: 3.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
            transition: all 0.2s ease-out;
            margin: 0;
            padding: 0;
            font-weight: 500;
        }

        .form-floating-custom .form-control:focus ~ label,
        .form-floating-custom .form-control:not(:placeholder-shown) ~ label {
            top: 18px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .domain-suffix {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .btn-login-submit {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 1.1rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
        }

        .btn-login-submit::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-20deg);
            transition: all 0.5s;
        }

        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.35);
            color: white;
        }

        .btn-login-submit:hover::after {
            left: 150%;
        }

        .page-footer {
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: #94a3b8;
        }

        .pwa-notif-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 370px;
            width: calc(100% - 40px);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(14, 165, 233, 0.35);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
            animation: slideInDown 0.45s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.3s ease;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-icon-box {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #0284c7;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.15);
        }

        .btn-close-notif {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.3rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
            transition: color 0.2s;
        }
        .btn-close-notif:hover { color: #0f172a; }

        @media (max-width: 600px) {
            .pwa-notif-toast {
                top: auto !important;
                bottom: 16px !important;
                right: 14px !important;
                left: 14px !important;
                max-width: none !important;
                width: auto !important;
                border-radius: 16px !important;
                padding: 12px 14px !important;
                animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(35px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .auth-container {
                flex-direction: column;
                margin-bottom: 40px;
                max-width: 440px;
            }
            .brand-panel {
                flex: none;
                width: 100%;
                height: auto;
                min-height: auto;
                border-radius: 24px 24px 0 0;
                padding: 2.5rem 2rem 3rem;
            }
            .login-panel {
                flex: none;
                width: 100%;
                height: auto;
                margin-left: 0;
                margin-top: -24px;
                border-radius: 24px;
                padding: 2.5rem 2rem 2.5rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            }
            .logo-showcase {
                width: 100px;
                height: 100px;
                margin-bottom: 1.25rem;
            }
            .logo-content img {
                max-height: 52px;
            }
            .page-footer {
                position: relative;
                bottom: auto;
                margin-top: 1.5rem;
                font-size: 0.78rem;
            }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 0.75rem;
                align-items: flex-start;
                padding-top: 1rem;
            }
            .auth-container {
                max-width: 100%;
                margin-bottom: 20px;
            }
            .auth-container::before {
                width: 140px;
                height: 140px;
                top: -20px;
                right: -20px;
            }
            .brand-panel {
                border-radius: 20px 20px 0 0;
                padding: 1.75rem 1.25rem 2rem;
            }
            .brand-panel h1 {
                font-size: 1.55rem !important;
            }
            .brand-panel p {
                font-size: 0.8rem !important;
                line-height: 1.4 !important;
            }
            .badge-custom {
                padding: 5px 14px;
                font-size: 0.65rem;
                letter-spacing: 1px;
                margin-bottom: 0.85rem;
            }
            .logo-showcase {
                width: 76px;
                height: 76px;
                margin-bottom: 0.75rem;
            }
            .logo-glass {
                border-radius: 22px;
            }
            .logo-content img {
                max-height: 40px;
            }
            .brand-panel .d-flex.justify-content-between {
                margin-top: 1.25rem !important;
                padding-top: 0.75rem !important;
                font-size: 0.65rem !important;
            }
            .login-panel {
                border-radius: 20px;
                margin-top: -20px;
                padding: 1.75rem 1.25rem 1.5rem;
            }
            .login-title {
                font-size: 1.35rem;
                margin-bottom: 0.25rem;
            }
            .login-subtitle {
                font-size: 0.82rem;
                margin-bottom: 1.25rem;
            }
            .form-floating-custom {
                margin-bottom: 1rem;
            }
            .form-floating-custom .form-control {
                height: 56px;
                font-size: 0.92rem;
                padding: 1.25rem 0.85rem 0.5rem 2.85rem;
                border-radius: 12px;
            }
            .form-floating-custom .input-icon {
                left: 0.95rem;
                font-size: 1.05rem;
            }
            .form-floating-custom label {
                left: 2.85rem;
                font-size: 0.85rem;
            }
            .form-floating-custom .form-control:not(:placeholder-shown) ~ label {
                top: 14px;
                font-size: 0.65rem;
            }
            .domain-suffix {
                right: 0.85rem;
                font-size: 0.78rem;
            }
            .btn-login-submit {
                padding: 0.85rem;
                font-size: 0.9rem;
                border-radius: 12px;
                margin-top: 1.25rem;
            }
            .page-footer {
                margin-top: 1.25rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Browser Recommendation Notification Banner (Hidden if already installed) -->
    <div id="pwaInstallNotif" class="pwa-notif-toast d-none">
        <div class="d-flex align-items-start gap-3">
            <div class="notif-icon-box flex-shrink-0">
                <i class="bi bi-display"></i>
            </div>
            <div class="flex-grow-1" style="min-width: 0;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 0.68rem; letter-spacing: 0.04em;">REKOMENDASI APLIKASI</span>
                    <button type="button" class="btn-close-notif" onclick="closePwaNotif()" title="Tutup Notifikasi">&times;</button>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Pasang SiPLN ke Desktop</h6>
                <p class="text-muted mb-2" style="font-size: 0.76rem; line-height: 1.35;">
                    Buka SiPLN langsung dari Desktop tanpa bar browser untuk akses yang lebih cepat & praktis.
                </p>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary fw-semibold px-3 py-1 rounded-pill" style="font-size: 0.78rem;" onclick="triggerPwaInstall()">
                        <i class="bi bi-download me-1"></i> Pasang Sekarang
                    </button>
                    <button type="button" class="btn btn-sm btn-light border text-muted px-2 py-1 rounded-pill" style="font-size: 0.75rem;" onclick="closePwaNotif()">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-container">
        
        <!-- Brand Panel (Left) -->
        <div class="brand-panel">
            <div class="brand-text text-center mt-2">
                <div class="badge-custom">
                    <i class="bi bi-shield-check me-2 fs-6"></i> SECURE GATEWAY
                </div>
                
                <div class="logo-showcase">
                    <div class="logo-glass"></div>
                    <div class="logo-content">
                        <img src="<?= ASSET_URL ?>/assets/logopln.png" alt="Logo">
                    </div>
                </div>
                
                <h1 class="fw-bold mb-2 text-white" style="font-size: 2.2rem; letter-spacing: -0.5px;">
                    SiPLN <span style="color: #38bdf8;">V3</span>
                </h1>
                <p class="text-light opacity-75 small lh-lg mb-0" style="font-size: 0.9rem;">
                    Sistem Kepengurusan Santri Luar Negeri<br>
                    Pondok Modern Darussalam Gontor.
                </p>
            </div>

            <div class="d-flex justify-content-between position-relative z-index-10 mt-5 pt-4" style="font-family: 'Courier New', monospace; font-size: 0.75rem; color: rgba(255,255,255,0.4); border-top: 1px solid rgba(255,255,255,0.1);">
                <span>VER_3.0.0</span>
                <span class="d-flex align-items-center"><span style="display:inline-block; width:6px; height:6px; background:#10b981; border-radius:50%; margin-right:6px; box-shadow: 0 0 8px #10b981;"></span> SYSTEM ACTIVE</span>
            </div>
        </div>

        <!-- Login Panel (Right) -->
        <div class="login-panel">
            <h2 class="login-title">Selamat Datang!</h2>
            <p class="login-subtitle">Silakan masuk untuk mengakses sistem.</p>

            <form id="loginForm">
                <!-- CSRF Token -->
                <meta name="csrf-token" content="<?= $csrf ?? '' ?>">

                <div class="form-floating-custom">
                    <input type="text" class="form-control" id="username" placeholder=" " autocomplete="off" required>
                    <i class="bi bi-person input-icon"></i>
                    <label for="username">Username</label>
                    <span class="domain-suffix" id="domain-suffix">@pln.local</span>
                </div>

                <div class="form-floating-custom">
                    <input type="password" class="form-control" id="password" placeholder=" " required>
                    <i class="bi bi-shield-lock input-icon"></i>
                    <label for="password">Password</label>
                </div>

                <button type="submit" class="btn-login-submit" id="btnLogin">
                    MASUK KE SISTEM <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </form>
        </div>
        
        <!-- Global Footer -->
        <div class="page-footer">
            &copy; <?= date('Y') ?> Pondok Modern Darussalam Gontor.
        </div>
    </div>

    <!-- Firebase Auth SDK (Compat mode for simplicity) -->
    <script src="<?= ASSET_URL ?>/assets/offline/js/firebase-app-compat.js"></script>
    <script src="<?= ASSET_URL ?>/assets/offline/js/firebase-auth-compat.js"></script>

    <!-- Logic Script -->
    <script>
        // Inisialisasi Firebase (config dari firebase.php)
        const firebaseConfig = {
            apiKey: "<?= (require dirname(__DIR__, 3) . '/config/firebase.php')['api_key'] ?>",
            projectId: "<?= (require dirname(__DIR__, 3) . '/config/firebase.php')['project_id'] ?>",
            authDomain: "<?= (require dirname(__DIR__, 3) . '/config/firebase.php')['project_id'] ?>.firebaseapp.com",
        };
        firebase.initializeApp(firebaseConfig);

        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const domainSuffix = document.getElementById('domain-suffix');

            // Sembunyikan suffix jika user mengetik '@' (misal email manual)
            usernameInput.addEventListener('input', function() {
                if (this.value.includes('@')) {
                    domainSuffix.style.opacity = '0';
                } else {
                    domainSuffix.style.opacity = '1';
                }
            });

            document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnLogin');
            const user = document.getElementById('username').value.trim();
            const pass = document.getElementById('password').value;
            let csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const EMAIL_DOMAIN = '@pln.local';
            const email = user.includes('@') ? user : (user + EMAIL_DOMAIN);

            if (!user || !pass) {
                Swal.fire('Peringatan', 'Username dan Password wajib diisi.', 'warning');
                return;
            }

            if (user.includes('@') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user)) {
                Swal.fire('Peringatan', 'Format email tidak valid.', 'warning');
                return;
            }

            // Loading state
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memverifikasi...';
            btn.disabled = true;

            // Fungsi pembantu untuk kirim request login ke backend PHP
            const sendBackendLogin = (payload, currentCsrf) => {
                const attemptLogin = (token) => {
                    return fetch('<?= API_URL ?>/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': token
                        },
                        body: JSON.stringify(payload)
                    });
                };

                return attemptLogin(currentCsrf).then(res => {
                    // Jika 422 (CSRF kedaluwarsa), ambil token baru secara diam-diam dan coba lagi
                    if (res.status === 422) {
                        return fetch(window.location.href, { cache: 'no-store' })
                            .then(htmlRes => htmlRes.text())
                            .then(htmlText => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(htmlText, 'text/html');
                                const newCsrf = doc.querySelector('meta[name="csrf-token"]')?.content;
                                if (newCsrf) {
                                    csrf = newCsrf;
                                    document.querySelector('meta[name="csrf-token"]').content = newCsrf;
                                    return attemptLogin(newCsrf);
                                }
                                return res;
                            });
                    }
                    return res;
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            try { return JSON.parse(text); } 
                            catch(e) { return { success: false, message: "Terjadi kesalahan server atau sesi telah usang. Silakan muat ulang halaman." }; }
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                            customClass: { popup: 'rounded-4 shadow-lg' }
                        }).then(() => {
                            // Determine base path from current URL (strip /login)
                            let currentUrl = window.location.pathname;
                            let basePath = currentUrl.endsWith('/login') ? currentUrl.slice(0, -6) : '';
                            if (basePath === '') basePath = '/';
                            window.location.href = basePath;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Masuk',
                            text: data.message,
                            confirmButtonText: data.reload ? 'Muat Ulang' : 'Coba Lagi',
                            confirmButtonColor: '#0ea5e9',
                            customClass: { popup: 'rounded-4 shadow-lg' }
                        }).then(() => {
                            if (data.reload) {
                                window.location.reload();
                            }
                        });
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Masuk',
                        text: err.message || 'Gagal menghubungi server.',
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#0ea5e9',
                        customClass: { popup: 'rounded-4 shadow-lg' }
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            };

            // === LANGKAH 1: Login via Firebase Auth (dengan Auto Fallback ke Backend) ===
            firebase.auth().signInWithEmailAndPassword(email, pass)
                .then((userCredential) => {
                    return userCredential.user.getIdToken();
                })
                .then((idToken) => {
                    sendBackendLogin({ firebase_token: idToken, username: user }, csrf);
                })
                .catch(err => {
                    // Jika Firebase SDK gagal karena kendala jaringan (DNS WiFi / AdBlock / Tailscale route),
                    // lakukan fallback otomatis verifikasi langsung ke server backend PHP
                    if (err.code === 'auth/network-request-failed' || err.code === 'auth/internal-error') {
                        console.warn('Firebase network unreachable, falling back to server auth:', err.message);
                        sendBackendLogin({ username: user, password: pass }, csrf);
                        return;
                    }

                    let msg = 'Gagal menghubungi server.';
                    if (err.code === 'auth/user-not-found' || err.code === 'auth/wrong-password' || err.code === 'auth/invalid-credential') {
                        msg = 'Username atau Password salah.';
                    } else if (err.code === 'auth/too-many-requests') {
                        msg = 'Terlalu banyak percobaan login gagal. Silakan coba lagi nanti.';
                    } else if (err.message) {
                        msg = err.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Masuk',
                        text: msg,
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#0ea5e9',
                        customClass: { popup: 'rounded-4 shadow-lg' }
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            });
        });

        // ── PWA & Standalone Detection ─────────────────────────────
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
            || window.navigator.standalone === true 
            || document.referrer.includes('android-app://');

        function checkPwaBannerVisibility() {
            // Jika sudah dibuka dari aplikasi desktop (standalone), jangan tampilkan notifikasi sama sekali
            if (isStandalone) {
                const notif = document.getElementById('pwaInstallNotif');
                if (notif) notif.remove();
                return;
            }
            
            // Cek apakah user sudah menutup notifikasi di sesi ini
            if (sessionStorage.getItem('dismissed_install_notif') === '1') {
                return;
            }
            
            const notif = document.getElementById('pwaInstallNotif');
            if (notif) {
                setTimeout(() => {
                    notif.classList.remove('d-none');
                }, 700);
            }
        }

        window.closePwaNotif = function() {
            const notif = document.getElementById('pwaInstallNotif');
            if (notif) {
                notif.style.opacity = '0';
                notif.style.transform = 'translateY(-20px)';
                setTimeout(() => notif.remove(), 300);
            }
            sessionStorage.setItem('dismissed_install_notif', '1');
        };

        checkPwaBannerVisibility();

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const swPath = '<?= ASSET_URL ?>/sw.js';
                navigator.serviceWorker.register(swPath).then((reg) => {
                    console.log('SiPLN PWA ready with scope:', reg.scope);
                }).catch((err) => {
                    console.warn('ServiceWorker error:', err);
                });
            });
        }

        let deferredPwaPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPwaPrompt = e;
        });

        window.triggerPwaInstall = async function() {
            if (deferredPwaPrompt) {
                deferredPwaPrompt.prompt();
                const choiceResult = await deferredPwaPrompt.userChoice;
                if (choiceResult.outcome === 'accepted') {
                    console.log('User installed SiPLN');
                }
                deferredPwaPrompt = null;
            } else {
                const isIos = /iPhone|iPad|iPod/i.test(navigator.userAgent);
                const isAndroid = /Android/i.test(navigator.userAgent);

                let installTitle = 'Pasang SiPLN ke Layar Utama HP';
                let installHtml = '';

                if (isIos) {
                    installTitle = 'Pasang SiPLN di iPhone / iPad';
                    installHtml = `
                        <div class="text-start" style="font-size: 0.88rem; line-height: 1.6;">
                            <p class="mb-2 text-secondary">Untuk menambahkan aplikasi SiPLN ke layar utama iPhone/iPad:</p>
                            <div class="p-3 bg-light border rounded mb-3">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                                    <div>Ketuk tombol <strong>Bagikan / Share ( <i class="bi bi-box-arrow-up text-primary fs-6"></i> )</strong> di bilah navigasi Safari.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">2</span>
                                    <div>Gulir ke bawah dan pilih <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong>.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">3</span>
                                    <div>Ketuk <strong>Tambah</strong> di pojok kanan atas.</div>
                                </div>
                            </div>
                            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0" style="font-size: 0.8rem;">
                                <i class="bi bi-phone-fill fs-5 text-success"></i>
                                <div>Aplikasi SiPLN akan langsung hadir di Layar Utama HP Anda layaknya aplikasi native!</div>
                            </div>
                        </div>
                    `;
                } else if (isAndroid) {
                    installTitle = 'Pasang SiPLN di Android';
                    installHtml = `
                        <div class="text-start" style="font-size: 0.88rem; line-height: 1.6;">
                            <p class="mb-2 text-secondary">Untuk memasang aplikasi SiPLN ke HP Android Anda:</p>
                            <div class="p-3 bg-light border rounded mb-3">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                                    <div>Ketuk ikon menu titik tiga <strong>( ⋮ )</strong> di pojok kanan atas Chrome / browser HP.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">2</span>
                                    <div>Pilih <strong>"Instal aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">3</span>
                                    <div>Ketuk <strong>Instal / Tambah</strong> pada pop-up konfirmasi.</div>
                                </div>
                            </div>
                            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0" style="font-size: 0.8rem;">
                                <i class="bi bi-phone-fill fs-5 text-success"></i>
                                <div>Ikon <strong>SiPLN</strong> akan langsung muncul di Layar Utama HP & terbuka dalam layar penuh!</div>
                            </div>
                        </div>
                    `;
                } else {
                    installTitle = 'Pasang SiPLN ke Desktop';
                    installHtml = `
                        <div class="text-start" style="font-size: 0.88rem; line-height: 1.6;">
                            <p class="mb-2 text-secondary">Untuk memasang aplikasi SiPLN langsung ke Desktop Anda:</p>
                            <div class="p-3 bg-light border rounded mb-3">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                                    <div>Klik tombol menu titik tiga <strong>( ⋮ / ...)</strong> di pojok kanan atas browser Anda.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">2</span>
                                    <div>Pilih menu <strong>"Aplikasi" (Apps)</strong> atau <strong>"Simpan dan bagikan"</strong>.</div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle px-2 py-1">3</span>
                                    <div>Klik <strong>"Install SiPLN"</strong> atau <strong>"Instal situs ini sebagai aplikasi"</strong>.</div>
                                </div>
                            </div>
                            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0" style="font-size: 0.8rem;">
                                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                                <div>Setelah terpasang, ikon <strong>SiPLN</strong> akan langsung hadir di Desktop Anda & terbuka mandiri tanpa kolom URL!</div>
                            </div>
                        </div>
                    `;
                }

                Swal.fire({
                    title: `<strong>${installTitle}</strong>`,
                    icon: 'info',
                    html: installHtml,
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
                    confirmButtonColor: '#0ea5e9'
                });
            }
        };

        window.addEventListener('appinstalled', () => {
            deferredPwaPrompt = null;
            const notif = document.getElementById('pwaInstallNotif');
            if (notif) notif.remove();
            
            Swal.fire({
                title: 'Berhasil Terpasang!',
                text: 'Aplikasi SiPLN berhasil diinstal ke perangkat Anda!',
                icon: 'success',
                confirmButtonColor: '#0ea5e9'
            });
        });
    </script>
</body>
</html>
