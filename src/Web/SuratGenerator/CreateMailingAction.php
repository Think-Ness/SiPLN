<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class CreateMailingAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (empty($body)) {
            $rawBody = $request->getBody()->getContents();
            if (!empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }
        $kdsList = $body['kds_list'] ?? [];
        $jenisPengajuanId = (int)($body['jenis_pengajuan_id'] ?? 0);
        $mode = $body['mode'] ?? 'perseorangan';
        $tanggalSurat = $body['tanggal_surat'] ?? date('Y-m-d');
        $catatan = $body['catatan'] ?? '';
        $sortBy = $body['sort_by'] ?? 'nama ASC';

        try {
            $db->createCommand("SELECT sort_by FROM surat_mailing LIMIT 1")->queryOne();
        } catch (\Exception $e) {
            $db->createCommand("ALTER TABLE surat_mailing ADD COLUMN sort_by VARCHAR(50) DEFAULT 'nama ASC' AFTER catatan")->execute();
        }

        if ($jenisPengajuanId === 0) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap. Pilih jenis pengajuan terlebih dahulu.'], 400);
        }

        if (!in_array($mode, ['sekaligus', 'perseorangan'])) {
            $mode = 'perseorangan';
        }

        $instansiId = $_SESSION['instansi_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (($_SESSION['role'] ?? '') === 'super_admin') {
            $submittedInstansi = $body['instansi_id'] ?? '';
            $instansiId = $submittedInstansi !== '' ? $submittedInstansi : null;
        }

        // Generate kode mailing: ML-YYYYMMDD-XXXX
        $maxId = (int)$db->createCommand("SELECT COALESCE(MAX(id), 0) FROM surat_mailing")->queryScalar();
        $kodeMailing = 'ML-' . date('Ymd') . '-' . str_pad((string)($maxId + 1), 4, '0', STR_PAD_LEFT);

        // Insert mailing
        $db->createCommand(
            "INSERT INTO surat_mailing (kode_mailing, jenis_pengajuan_id, mode, tanggal_surat, catatan, sort_by, instansi_id, created_by)
             VALUES (:kode, :jenis, :mode, :tgl, :catatan, :sort_by, :inst, :user)",
            [
                ':kode' => $kodeMailing,
                ':jenis' => $jenisPengajuanId,
                ':mode' => $mode,
                ':tgl' => $tanggalSurat,
                ':catatan' => $catatan,
                ':sort_by' => $sortBy,
                ':inst' => $instansiId,
                ':user' => $userId,
            ]
        )->execute();

        $mailingId = (int)$db->getLastInsertID();

        // Insert santri
        foreach ($kdsList as $kds) {
            $db->createCommand(
                "INSERT INTO surat_mailing_santri (mailing_id, kds) VALUES (:mid, :kds)",
                [':mid' => $mailingId, ':kds' => (int)$kds]
            )->execute();
        }

        AuditLogger::log($db, 'CREATE', 'SURAT_MAILING', (string)$mailingId, null, 
            "Membuat mailing $kodeMailing ({$mode}) dengan " . count($kdsList) . " santri");

        // Return complete mailing data
        $mailing = $db->createCommand(
            "SELECT m.*, jp.jenis_pengajuan, jp.hal, jp.isi, jp.kepada, jp.tempat, jp.surat_dibutuhkan, jp.kantor
             FROM surat_mailing m
             LEFT JOIN surat_jenis_pengajuan jp ON m.jenis_pengajuan_id = jp.id
             WHERE m.id = :id",
            [':id' => $mailingId]
        )->queryOne();

        $orderClause = $sortBy === 'manual' ? 'ms.id ASC' : $sortBy;

        $santriData = $db->createCommand(
            "SELECT s.kds, s.nama, s.kelas, s.pondok, s.negara, s.kewarganegaraan,
                    s.tempat_lahir, s.tanggal_lahir, s.alamat,
                    p.no_paspor, p.exp_paspor, i.exp_itas
             FROM surat_mailing_santri ms
             JOIN master_santri s ON ms.kds = s.kds
             LEFT JOIN (SELECT kds, no_paspor, exp_paspor FROM mtb_paspor WHERE id IN (SELECT MAX(id) FROM mtb_paspor GROUP BY kds)) p ON s.kds = p.kds
             LEFT JOIN (SELECT kds, exp_itas FROM mtb_itas WHERE aktif = 1) i ON s.kds = i.kds
             WHERE ms.mailing_id = :mid
             ORDER BY $orderClause",
            [':mid' => $mailingId]
        )->queryAll();

        $kantor = $mailing['kantor'] ?? '';
        $templateSchemas = [];
        if ($kantor) {
            $schemas = $db->createCommand(
                "SELECT nama_template, json_data_collection 
                 FROM surat_template_dinamis 
                 WHERE instansi_tujuan = :kantor",
                [':kantor' => $kantor]
            )->queryAll();
            
            foreach ($schemas as $row) {
                $safeTplName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $row['nama_template']);
                $templateSchemas[$safeTplName] = $row['json_data_collection'] ? json_decode($row['json_data_collection'], true) : null;
            }
        }

        return JsonResponse::create([
            'success' => true,
            'message' => "Mailing $kodeMailing berhasil dibuat.",
            'mailing' => $mailing,
            'santris' => $santriData,
            'templateSchemas' => $templateSchemas,
        ]);
    }
}

