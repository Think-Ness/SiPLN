<?php
declare(strict_types=1);

namespace App\Web\Santri;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final class UpdateDocAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        \Yiisoft\Router\CurrentRoute $currentRoute
    ): ResponseInterface {
        $type = $currentRoute->getArgument('type'); // 'paspor' atau 'itas'
        $id = (int) $currentRoute->getArgument('id');
        
        $rawBody = (string) $request->getBody();
        $data = json_decode($rawBody, true) ?? $request->getParsedBody() ?? [];

        if (!$id || !$type) {
            return JsonResponse::create(['success' => false, 'message' => 'Invalid request.'], 400);
        }

        try {
            if ($type === 'paspor') {
                $updateData = [];
                if (isset($data['no_paspor'])) $updateData['no_paspor'] = $data['no_paspor'];
                if (isset($data['tempat_dikeluarkan'])) $updateData['tempat_dikeluarkan'] = $data['tempat_dikeluarkan'];
                if (isset($data['tanggal_dikeluarkan'])) $updateData['tanggal_dikeluarkan'] = $data['tanggal_dikeluarkan'];
                if (isset($data['exp_paspor'])) $updateData['exp_paspor'] = $data['exp_paspor'];
                
                if (!empty($updateData)) {
                    $db->createCommand()->update('mtb_paspor', $updateData, ['id' => $id])->execute();
                }
            } elseif ($type === 'itas') {
                $updateData = [];
                if (isset($data['no_itas'])) $updateData['no_itas'] = $data['no_itas'];
                if (isset($data['level_itas'])) $updateData['level_itas'] = (int)$data['level_itas'];
                if (isset($data['exp_itas'])) $updateData['exp_itas'] = $data['exp_itas'];
                
                if (!empty($updateData)) {
                    $db->createCommand()->update('mtb_itas', $updateData, ['id' => $id])->execute();
                }
            } else {
                return JsonResponse::create(['success' => false, 'message' => 'Tipe dokumen tidak valid.'], 400);
            }

            return JsonResponse::create(['success' => true, 'message' => 'Riwayat dokumen berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return JsonResponse::create(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }
}
