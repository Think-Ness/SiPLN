<?php
declare(strict_types=1);

namespace App\Web\Pengaturan;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        // Only Super Admin allowed, or you can allow instansi admin too if needed
        // For now let's check role, usually session check is in middleware, but just in case:
        $role = $_SESSION['role'] ?? '';
        
        // Fetch current setting
        $sql = "SELECT setting_value FROM app_settings WHERE setting_key = 'pindahan_allowed_fields'";
        $setting = $db->createCommand($sql)->queryScalar();
        
        $allowedFields = [];
        if ($setting) {
            $allowedFields = json_decode($setting, true) ?? [];
        } else {
            // Default if not found
            $allowedFields = ['kelas', 'rayon', 'pondok', 'kamar'];
        }

        // List of all possible fields that can be toggled
        $availableFields = [
            // Biodata Lengkap
            'stambuk' => 'Regno (Stambuk)',
            'no_sktt' => 'NIK (SKTT)',
            'nama' => 'Nama Lengkap',
            'kelas' => 'Kelas',
            'rayon' => 'Rayon',
            'negara' => 'Negara Asal',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'alamat' => 'Alamat Lengkap',
            
            // Dokumen Keimigrasian
            'no_paspor' => 'No Paspor Baru',
            'exp_paspor' => 'Tanggal Exp Paspor',
            'no_itas' => 'No ITAS',
            'exp_itas' => 'Tanggal Exp ITAS',
            'level_itas' => 'Level ITAS',
            'no_ic' => 'No IC Santri',
            'tempat_paspor' => 'Tempat Dikeluarkan Paspor',
            'tgl_paspor' => 'Tanggal Dikeluarkan Paspor',
            
            // Orang Tua & Darurat
            'nama_ayah' => 'Nama Ayah',
            'nama_ibu' => 'Nama Ibu',
            'no_ayah' => 'No Telp Ayah',
            'no_ibu' => 'No Telp Ibu',
            'no_hp_alternatif' => 'No Telp Alternatif / Wali',
            
            // Riwayat & Media
            'edit_foto_personal' => 'Edit Foto Personal',
            'edit_riwayat_paspor' => 'Hapus/Edit Riwayat Paspor',
            'edit_riwayat_itas' => 'Hapus/Edit Riwayat ITAS',
            'edit_berkas_santri' => 'Hapus/Upload Berkas Santri',
            
            // Atribut Internal
            'aktif' => 'Status Keaktifan Santri',
            'pondok' => 'Lokasi Pondok',
            'kepengurusan' => 'Kepengurusan (Asal Cabang)',
            'keberadaan_paspor' => 'Keberadaan Paspor',
            'ukuran_baju' => 'Ukuran Baju',
            'jenis_kelamin' => 'Jenis Kelamin'
        ];

        // Load Firebase config for display
        $firebaseConfig = [];
        $firebaseConfigPath = dirname(__DIR__, 3) . '/config/firebase.php';
        if (file_exists($firebaseConfigPath)) {
            $firebaseConfig = require $firebaseConfigPath;
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'allowedFields' => $allowedFields,
            'availableFields' => $availableFields,
            'role' => $role,
            'firebaseConfig' => $firebaseConfig,
        ]);
    }
}
