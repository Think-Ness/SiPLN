<?php
declare(strict_types=1);

namespace App\Web\ImportExcel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class ImportAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer
    ): ResponseInterface {
        // Define all available database fields for mapping
        $dbFields = [
            // Master Santri - Biodata
            ['key' => 'stambuk',          'label' => 'Stambuk (Regno)',      'group' => 'Biodata'],
            ['key' => 'nama',             'label' => 'Nama Lengkap',        'group' => 'Biodata'],
            ['key' => 'kelas',            'label' => 'Kelas',               'group' => 'Biodata'],
            ['key' => 'rayon',            'label' => 'Rayon',               'group' => 'Biodata'],
            ['key' => 'negara',           'label' => 'Negara Asal',         'group' => 'Biodata'],
            ['key' => 'tempat_lahir',     'label' => 'Tempat Lahir',        'group' => 'Biodata'],
            ['key' => 'tanggal_lahir',    'label' => 'Tanggal Lahir',       'group' => 'Biodata'],
            ['key' => 'jenis_kelamin',    'label' => 'Jenis Kelamin',       'group' => 'Biodata'],
            ['key' => 'alamat',           'label' => 'Alamat',              'group' => 'Biodata'],
            ['key' => 'pondok',           'label' => 'Pondok',              'group' => 'Biodata'],
            ['key' => 'kepengurusan',     'label' => 'Kepengurusan',        'group' => 'Biodata'],
            ['key' => 'no_sktt',          'label' => 'NIK / SKTT',          'group' => 'Biodata'],
            ['key' => 'no_ic',            'label' => 'No IC',               'group' => 'Biodata'],
            ['key' => 'ukuran_baju',      'label' => 'Ukuran Baju',         'group' => 'Biodata'],
            ['key' => 'keberadaan_paspor','label' => 'Keberadaan Paspor',   'group' => 'Biodata'],
            ['key' => 'aktif',            'label' => 'Status (Aktif/Tidak)','group' => 'Biodata'],

            // Keluarga
            ['key' => 'nama_ayah',        'label' => 'Nama Ayah',           'group' => 'Keluarga'],
            ['key' => 'nama_ibu',         'label' => 'Nama Ibu',            'group' => 'Keluarga'],
            ['key' => 'no_ayah',          'label' => 'No HP Ayah',          'group' => 'Keluarga'],
            ['key' => 'no_ibu',           'label' => 'No HP Ibu',           'group' => 'Keluarga'],
            ['key' => 'no_hp_alternatif', 'label' => 'No HP Alternatif',    'group' => 'Keluarga'],

            // Dokumen Paspor
            ['key' => 'no_paspor',        'label' => 'No Paspor',           'group' => 'Paspor'],
            ['key' => 'exp_paspor',       'label' => 'Exp Paspor',          'group' => 'Paspor'],
            ['key' => 'tempat_paspor',    'label' => 'Tempat Dikeluarkan',  'group' => 'Paspor'],
            ['key' => 'tgl_paspor',       'label' => 'Tgl Dikeluarkan',     'group' => 'Paspor'],

            // Dokumen ITAS
            ['key' => 'no_itas',          'label' => 'No ITAS',             'group' => 'ITAS'],
            ['key' => 'exp_itas',         'label' => 'Exp ITAS',            'group' => 'ITAS'],
            ['key' => 'level_itas',       'label' => 'Level ITAS',          'group' => 'ITAS'],
        ];

        return $viewRenderer->render(__DIR__ . '/import_template', [
            'dbFields' => $dbFields,
        ]);
    }
}
