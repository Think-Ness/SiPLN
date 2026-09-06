<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPLN | Login Pengguna</title>
    <link rel="icon" href="<?= ASSET_URL ?>/assets/logopln.png" type="image/png">
    
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

        @media (max-width: 900px) {
            .auth-container {
                flex-direction: column;
                margin-bottom: 60px;
            }
            .brand-panel {
                flex: none;
                width: 100%;
                max-width: 450px;
                height: auto;
                min-height: 450px;
                border-radius: 28px;
                padding: 3rem 2.5rem;
            }
            .login-panel {
                flex: none;
                width: 100%;
                max-width: 450px;
                height: auto;
                margin-left: 0;
                margin-top: -40px;
                border-radius: 0 0 28px 28px;
                padding: 4.5rem 2.5rem 3rem;
            }
            .logo-showcase {
                width: 120px;
                height: 120px;
                margin-bottom: 2rem;
            }
            .logo-content img {
                max-height: 60px;
            }
        }
    </style>
</head>
<body>

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

            // Konversi username → email (jika belum ada @)
            const email = user.includes('@') ? user : user + EMAIL_DOMAIN;

            // === LANGKAH 1: Login ke Firebase Auth ===
            firebase.auth().signInWithEmailAndPassword(email, pass)
                .then((userCredential) => {
                    // === LANGKAH 2: Ambil ID Token dari Firebase ===
                    return userCredential.user.getIdToken();
                })
                .then((idToken) => {
                    // === LANGKAH 3: Kirim Token ke Backend PHP untuk buat Session ===
                    const attemptLogin = (token) => {
                        return fetch('<?= API_URL ?>/api/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': token
                            },
                            body: JSON.stringify({ 
                                firebase_token: idToken,
                                username: user 
                            })
                        });
                    };

                    return attemptLogin(csrf).then(res => {
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
                    });
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
                    let msg = 'Gagal menghubungi server.';
                    if (err.code === 'auth/user-not-found' || err.code === 'auth/wrong-password' || err.code === 'auth/invalid-credential') {
                        msg = 'Username atau Password salah.';
                    } else if (err.code === 'auth/too-many-requests') {
                        msg = 'Terlalu banyak percobaan login gagal. Silakan coba lagi nanti.';
                    } else if (err.code === 'auth/network-request-failed') {
                        msg = 'Tidak dapat terhubung ke internet. Pastikan koneksi internet Anda aktif.';
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
    </script>
</body>
</html>
