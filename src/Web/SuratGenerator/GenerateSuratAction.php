<?php
declare(strict_types=1);

namespace App\Web\SuratGenerator;

use App\Shared\JsonResponse;
use App\Shared\AuditLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class GenerateSuratAction
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
        $mailingId = (int)($body['mailing_id'] ?? 0);
        $tipeSurat = $body['tipe_surat'] ?? '';
        $nomorSurat = $body['nomor_surat'] ?? '';
        $tanggalSurat = $body['tanggal_surat'] ?? date('Y-m-d');
        $hal = $body['hal'] ?? '';
        $isi = $body['isi'] ?? '';
        $kepada = $body['kepada'] ?? '';
        $tempat = $body['tempat'] ?? '';
        $withLampiran = (int)($body['with_lampiran'] ?? 1);
        $jsonDynamicData = $body['json_dynamic_data'] ?? null;
        if ($jsonDynamicData && !is_string($jsonDynamicData)) {
            $jsonDynamicData = json_encode($jsonDynamicData);
        }

        $customInputsData = $body['custom_inputs_data'] ?? null;
        if ($customInputsData && !is_string($customInputsData)) {
            $customInputsData = json_encode($customInputsData);
        }

        if ($mailingId === 0 || empty($tipeSurat)) {
            return JsonResponse::create(['success' => false, 'message' => 'Data tidak lengkap.'], 400);
        }

        if (empty($tipeSurat)) {
            return JsonResponse::create(['success' => false, 'message' => 'Tipe surat tidak boleh kosong.'], 400);
        }

        // Cek mode mailing dan jumlah santri
        $mailingInfo = $db->createCommand("SELECT mode FROM surat_mailing WHERE id = :mid", [':mid' => $mailingId])->queryOne();
        $mode = $mailingInfo['mode'] ?? 'Sekaligus';
        $santriCount = (int)$db->createCommand("SELECT COUNT(*) FROM surat_mailing_santri WHERE mailing_id = :mid", [':mid' => $mailingId])->queryScalar();

        // Auto-generate nomor surat if empty: get MAX for this type this year
        if (empty($nomorSurat)) {
            $year = date('Y');
            $maxNo = (int)$db->createCommand(
                "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(IFNULL(nomor_akhir, nomor_surat), '/', 1) AS UNSIGNED)), 0) 
                 FROM surat_generated 
                 WHERE tipe_surat = :tipe AND YEAR(tanggal_surat) = :year",
                [':tipe' => $tipeSurat, ':year' => $year]
            )->queryScalar();

            $nextNo = $maxNo + 1;
            $bulanRomawi = $this->bulanRomawi((int)date('m'));
            $nomorSurat = str_pad((string)$nextNo, 3, '0', STR_PAD_LEFT) . "/$tipeSurat/PLN/$bulanRomawi/$year";
        }

        // Kalkulasi nomor_akhir jika Perseorangan
        $nomorAkhir = null;
        if ($mode === 'Perseorangan' && $santriCount > 1) {
            $baseNoStr = explode('/', $nomorSurat)[0];
            $baseNo = (int)$baseNoStr;
            $endNo = $baseNo + $santriCount - 1;
            
            $suffix = substr($nomorSurat, strlen($baseNoStr));
            $nomorAkhir = str_pad((string)$endNo, 3, '0', STR_PAD_LEFT) . $suffix;
        }

        // Check if already generated for this mailing + tipe
        $existing = $db->createCommand(
            "SELECT id FROM surat_generated WHERE mailing_id = :mid AND tipe_surat = :tipe",
            [':mid' => $mailingId, ':tipe' => $tipeSurat]
        )->queryOne();

        if ($existing) {
            // Update existing
            $db->createCommand(
                "UPDATE surat_generated SET nomor_surat = :nomor, nomor_akhir = :nomor_akhir, tanggal_surat = :tgl, hal = :hal, isi = :isi, kepada = :kepada, tempat = :tempat, json_dynamic_data = :json_data, custom_inputs_data = :custom_inputs
                 WHERE id = :id",
                [
                    ':nomor' => $nomorSurat,
                    ':nomor_akhir' => $nomorAkhir,
                    ':tgl' => $tanggalSurat,
                    ':hal' => $hal,
                    ':isi' => $isi,
                    ':kepada' => $kepada,
                    ':tempat' => $tempat,
                    ':json_data' => $jsonDynamicData,
                    ':custom_inputs' => $customInputsData,
                    ':id' => $existing['id'],
                ]
            )->execute();
            $suratId = (int)$existing['id'];
        } else {
            // Insert new
            $db->createCommand(
                "INSERT INTO surat_generated (mailing_id, tipe_surat, nomor_surat, nomor_akhir, tanggal_surat, hal, isi, kepada, tempat, json_dynamic_data, custom_inputs_data)
                 VALUES (:mid, :tipe, :nomor, :nomor_akhir, :tgl, :hal, :isi, :kepada, :tempat, :json_data, :custom_inputs)",
                [
                    ':mid' => $mailingId,
                    ':tipe' => $tipeSurat,
                    ':nomor' => $nomorSurat,
                    ':nomor_akhir' => $nomorAkhir,
                    ':tgl' => $tanggalSurat,
                    ':hal' => $hal,
                    ':isi' => $isi,
                    ':kepada' => $kepada,
                    ':tempat' => $tempat,
                    ':json_data' => $jsonDynamicData,
                    ':custom_inputs' => $customInputsData,
                ]
            )->execute();
            $suratId = (int)$db->getLastInsertID();
        }

        // Kita sekarang menggunakan template dari path dinamis Instansi + Kantor + Tipe Surat,
        // jadi kita asumsikan template selalu ada dan biarkan DownloadSuratAction yang memverifikasi.
        $hasTemplate = true;

        $tipeLabel = ['SP' => 'Surat Permohonan', 'SK' => 'Surat Keterangan', 'SJ' => 'Surat Jaminan', 'ST' => 'Surat Tugas'];
        $label = $tipeLabel[$tipeSurat] ?? str_replace('_', ' ', $tipeSurat);
        AuditLogger::log($db, 'CREATE', 'SURAT_GENERATED', (string)$suratId, null,
            "Generate {$label} nomor $nomorSurat untuk mailing #$mailingId");

        return JsonResponse::create([
            'success' => true,
            'message' => "{$label} berhasil di-generate.",
            'is_docx' => $hasTemplate,
            'download_url' => $hasTemplate ? API_URL . "/api/surat/download/{$mailingId}?tipe={$tipeSurat}&with_lampiran={$withLampiran}" : null,
            'surat' => [
                'id' => $suratId,
                'tipe_surat' => $tipeSurat,
                'nomor_surat' => $nomorSurat,
                'tanggal_surat' => $tanggalSurat,
                'hal' => $hal,
                'isi' => $isi,
                'kepada' => $kepada,
                'tempat' => $tempat,
            ],
        ]);
    }

    private function bulanRomawi(int $bulan): string
    {
        $romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        return $romawi[$bulan - 1] ?? (string)$bulan;
    }
}
