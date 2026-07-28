<?php
declare(strict_types=1);

namespace App\Web\RequestEdit;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;
use App\Shared\AuditLogger;

final class ApproveAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ConnectionInterface $db,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $id = (int) $currentRoute->getArgument('id');
        
        $req = $db->createCommand("SELECT * FROM santri_edit_requests WHERE id = :id AND status = 'pending'", [':id' => $id])->queryOne();

        if (!$req) {
            return JsonResponse::create(['success' => false, 'message' => 'Request tidak ditemukan atau sudah diproses.'], 404);
        }

        $myKepengurusan = $_SESSION['def_kepengurusan'] ?? '';
        if ($myKepengurusan !== '' && trim(strtolower($req['target_kepengurusan'])) !== trim(strtolower($myKepengurusan))) {
            return JsonResponse::create(['success' => false, 'message' => 'Anda tidak berhak menyetujui request ini.'], 403);
        }

        $oldSantri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $req['kds']])->queryOne();

        $changes = json_decode($req['requested_changes'], true);
        
        $body = $request->getParsedBody();
        $approvedKeys = $body['approved_keys'] ?? null;

        if (is_array($changes) && !empty($changes)) {
            // Filter changes if partial approval
            if ($approvedKeys !== null && is_array($approvedKeys)) {
                $filteredChanges = [];
                foreach ($changes as $k => $v) {
                    // Only keep keys that are explicitly approved
                    // except for internal ones which we will handle below
                    if (in_array($k, $approvedKeys)) {
                        $filteredChanges[$k] = $v;
                    }
                }
                
                // Re-add document internal data and display keys if the group was approved
                if (in_array('_paspor_data', $approvedKeys)) {
                    if (isset($changes['_paspor_data'])) $filteredChanges['_paspor_data'] = $changes['_paspor_data'];
                    if (isset($changes['_paspor_is_new'])) $filteredChanges['_paspor_is_new'] = $changes['_paspor_is_new'];
                    if (isset($changes['_paspor_latest_id'])) $filteredChanges['_paspor_latest_id'] = $changes['_paspor_latest_id'];
                    foreach (['No Paspor', 'Tempat Keluar Paspor', 'Tgl Keluar Paspor', 'Exp Paspor'] as $dk) {
                        if (isset($changes[$dk])) $filteredChanges[$dk] = $changes[$dk];
                    }
                }
                
                if (in_array('_itas_data', $approvedKeys)) {
                    if (isset($changes['_itas_data'])) $filteredChanges['_itas_data'] = $changes['_itas_data'];
                    if (isset($changes['_itas_is_new'])) $filteredChanges['_itas_is_new'] = $changes['_itas_is_new'];
                    if (isset($changes['_itas_latest_id'])) $filteredChanges['_itas_latest_id'] = $changes['_itas_latest_id'];
                    foreach (['No ITAS', 'Level ITAS', 'Exp ITAS'] as $dk) {
                        if (isset($changes[$dk])) $filteredChanges[$dk] = $changes[$dk];
                    }
                }
                
                $changes = $filteredChanges;
                
                // Update requested_changes in DB so history reflects only what was approved
                $db->createCommand()->update('santri_edit_requests', [
                    'requested_changes' => json_encode($changes)
                ], ['id' => $id])->execute();
            }

            // Handle paspor
            if (isset($changes['_paspor_data'])) {
                $pData = $changes['_paspor_data'];
                $pIsNew = $changes['_paspor_is_new'] ?? 1;
                $pLatestId = $changes['_paspor_latest_id'] ?? null;
                if ($pLatestId) {
                    if ($pIsNew === 1) {
                        $db->createCommand()->update('mtb_paspor', ['aktif' => 0], ['kds' => $req['kds']])->execute();
                        $db->createCommand()->insert('mtb_paspor', array_merge($pData, ['kds' => $req['kds']]))->execute();
                    } else {
                        $db->createCommand()->update('mtb_paspor', $pData, ['id' => $pLatestId])->execute();
                    }
                } else {
                    $db->createCommand()->insert('mtb_paspor', array_merge($pData, ['kds' => $req['kds']]))->execute();
                }
                unset($changes['_paspor_data'], $changes['_paspor_is_new'], $changes['_paspor_latest_id']);
            }

            // Handle ITAS
            if (isset($changes['_itas_data'])) {
                $iData = $changes['_itas_data'];
                $iIsNew = $changes['_itas_is_new'] ?? 1;
                $iLatestId = $changes['_itas_latest_id'] ?? null;
                if ($iLatestId) {
                    if ($iIsNew === 1) {
                        $db->createCommand()->update('mtb_itas', ['aktif' => 0], ['kds' => $req['kds']])->execute();
                        $db->createCommand()->insert('mtb_itas', array_merge($iData, ['kds' => $req['kds']]))->execute();
                    } else {
                        $db->createCommand()->update('mtb_itas', $iData, ['id' => $iLatestId])->execute();
                    }
                } else {
                    $db->createCommand()->insert('mtb_itas', array_merge($iData, ['kds' => $req['kds']]))->execute();
                }
                unset($changes['_itas_data'], $changes['_itas_is_new'], $changes['_itas_latest_id']);
            }

            // Remove any other internal keys used for display
            foreach (['No Paspor', 'Tempat Keluar Paspor', 'Tgl Keluar Paspor', 'Exp Paspor', 'No ITAS', 'Level ITAS', 'Exp ITAS'] as $k) {
                unset($changes[$k]);
            }

            if (!empty($changes)) {
                $db->createCommand()->update('master_santri', $changes, ['kds' => $req['kds']])->execute();
            }
        }

        $db->createCommand()->update('santri_edit_requests', ['status' => 'approved'], ['id' => $id])->execute();
        
        $newSantri = $db->createCommand("SELECT * FROM master_santri WHERE kds = :kds", [':kds' => $req['kds']])->queryOne();

        AuditLogger::log($db, 'APPROVE', 'REQUEST_EDIT', $id, $oldSantri, $newSantri, "Menyetujui perubahan data santri KDS: {$req['kds']}");

        return JsonResponse::create(['success' => true, 'message' => 'Request berhasil disetujui, data santri telah diperbarui.']);
    }
}
