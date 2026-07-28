<?php
/**
 * Firebase Configuration
 * 
 * Konfigurasi untuk sinkronisasi data ke Firebase Firestore.
 * Ganti nilai-nilai di bawah dengan kredensial project Firebase Anda.
 * 
 * CARA SETUP:
 * 1. Buka https://console.firebase.google.com
 * 2. Buat project baru (atau gunakan yang sudah ada)
 * 3. Aktifkan Firestore Database (mode test untuk sementara)
 * 4. Buka Project Settings > General > Web API Key
 * 5. Buka Project Settings > Service Accounts > Firebase Admin SDK > Generate new private key
 * 6. Buka Authentication > Sign-in method > Enable Email/Password
 * 7. Buat user baru di Authentication untuk sync service
 */

return [
    // ============================================
    // FIREBASE PROJECT CONFIG
    // ============================================
    
    // Project ID (dari Firebase Console > Project Settings)
    // 'project_id' => 'luar-negeri-db',
    'project_id' => 'database-luar-negeri',
    
    // Web API Key (dari Firebase Console > Project Settings > General)
    // 'api_key' => 'AIzaSyDJ5OMlpLyFHG_MnmVdjWfIBxFut7mda_g',
    'api_key' => 'AIzaSyBhOhDCn2fPzRNoC_pCQ5xcZDiJJkTCoqY',
    
    // ============================================
    // SYNC SERVICE ACCOUNT (Firebase Auth)
    // ============================================
    // Buat user khusus di Firebase Authentication untuk sync
    'sync_email' => 'sigapsayangibu@gmail.com',
    'sync_password' => '123_123',
    
    // ============================================
    // SYNC SETTINGS
    // ============================================
    
    // Aktifkan/nonaktifkan sync (false = sync dimatikan)
    'enabled' => true,
    
    // Timeout untuk HTTP request (detik)
    'timeout' => 15,
];
