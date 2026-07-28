<?php

declare(strict_types=1);

namespace App\Web\Santri;

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
        
        $sql = "
            SELECT s.kds, p.no_paspor, s.nama, s.kelas, s.negara as daerah, s.rayon, s.status_santri,
                   p.exp_paspor, i.exp_itas, i.level_itas as lvl
            FROM master_santri s
            LEFT JOIN mtb_paspor p ON s.kds = p.kds
            LEFT JOIN mtb_itas i ON s.kds = i.kds
            LIMIT 50
        ";
        $santris = $db->createCommand($sql)->queryAll();

        $instansi = $db->createCommand("SELECT def_kepengurusan, def_pondok, def_jenis_kelamin FROM master_instansi WHERE kode = " . (int)($_SESSION['instansi_id'] ?? 0) . " LIMIT 1")->queryOne();

        $allowedFieldsJson = $db->createCommand("SELECT setting_value FROM app_settings WHERE setting_key = 'pindahan_allowed_fields'")->queryScalar();
        $allowedFields = $allowedFieldsJson ? json_decode($allowedFieldsJson, true) : ['kelas', 'rayon', 'pondok', 'kamar'];

        // Ambil data kasus Job Desk aktif beserta nama proses dan tahap saat ini
        $activeJobDeskRows = $db->createCommand("
            SELECT 
                j.kds,
                p.nama_proses,
                (
                    SELECT jcs.step_nama 
                    FROM jobdesk_case_steps jcs 
                    WHERE jcs.case_id = j.id AND jcs.status != 'selesai'
                    ORDER BY jcs.step_urut ASC 
                    LIMIT 1
                ) AS tahap_saat_ini
            FROM jobdesk_cases j
            LEFT JOIN jobdesk_master_process p ON j.process_id = p.id
            WHERE j.status = 'aktif'
        ")->queryAll();

        $activeJobDeskMap = [];
        foreach ($activeJobDeskRows as $r) {
            $activeJobDeskMap[$r['kds']] = [
                'nama_proses'    => $r['nama_proses'] ?? 'Proses Tidak Diketahui',
                'tahap_saat_ini' => $r['tahap_saat_ini'] ?? null,
            ];
        }

        return $viewRenderer->render(__DIR__ . '/template', [
            'santris' => $santris,
            'instansiDefaults' => $instansi ?: [],
            'allowedFields' => $allowedFields,
            'activeJobDeskKdsSet' => $activeJobDeskMap
        ]);
    }
}
