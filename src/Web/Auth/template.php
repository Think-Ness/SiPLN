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
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f8fc 0%, #e1e9f4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Background Orbs */
        .orb-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.2), rgba(13, 202, 240, 0.2));
            border-radius: 50%;
            top: -100px;
            right: -100px;
            filter: blur(80px);
            z-index: 0;
        }

        .orb-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.15), rgba(32, 201, 151, 0.15));
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
            filter: blur(60px);
            z-index: 0;
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .brand-icon {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
            margin: 0 auto 1.5rem auto;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-group:focus-within .input-icon {
            color: #0d6efd;
        }

        .btn-login {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border: none;
            border-radius: 0.75rem;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.25);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            box-shadow: 0 12px 20px rgba(13, 110, 253, 0.35);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <!-- Background Orbs -->
    <div class="orb-1"></div>
    <div class="orb-2"></div>

    <!-- Login Container -->
    <div class="login-card">
        <div class="text-center mb-3">
            <img src="<?= ASSET_URL ?>/assets/logopln.png" alt="Logo PLN" style="max-height: 80px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
        </div>
        
        <h3 class="text-center fw-bolder text-dark mb-1">Masuk Sistem</h3>
        <p class="text-center text-muted small mb-4">Platform Kolaborasi Multi-Instansi</p>

        <form id="loginForm">
            <!-- CSRF Token -->
            <meta name="csrf-token" content="<?= $csrf ?? '' ?>">

            <div class="form-group position-relative mb-3">
                <i class="bi bi-person-fill input-icon"></i>
                <input type="text" class="form-control pe-5" id="username" placeholder="Masukkan Username" autocomplete="off" required>
                <span class="position-absolute top-50 end-0 translate-middle-y text-muted fw-semibold me-3" id="domain-suffix" style="pointer-events: none; transition: opacity 0.2s; font-size: 0.9rem;">@pln.local</span>
            </div>

            <div class="form-group position-relative mb-4">
                <i class="bi bi-key-fill input-icon"></i>
                <input type="password" class="form-control" id="password" placeholder="Masukkan Password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100 text-white" id="btnLogin">
                Otentikasi Masuk <i class="bi bi-arrow-right-short ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-4 text-muted" style="font-size: 0.7rem;">
            &copy; <?= date('Y') ?> Sistem Kepengurusan Santri Luar Negeri <br> Pondok Modern Darussalam Gontor
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
                            confirmButtonColor: '#0d6efd',
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
                        confirmButtonColor: '#0d6efd',
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

