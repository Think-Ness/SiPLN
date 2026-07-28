<?php

declare(strict_types=1);

namespace App\Web\JobDesk;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class SettingsAction
{
    public function __invoke(
        ServerRequestInterface $request,
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $instansiId = $_SESSION['instansi_id'] ?? null;
        $role = $_SESSION['role'] ?? '';
        
        $globalProcesses = [];
        if ($role === 'super_admin') {
            $processes = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE instansi_id IS NULL ORDER BY urut ASC")->queryAll();
        } else {
            $processes = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE instansi_id = :instansi_id ORDER BY urut ASC")
                            ->bindValue(':instansi_id', $instansiId)
                            ->queryAll();
            
            $globalProcessesRaw = $db->createCommand("SELECT * FROM jobdesk_master_process WHERE instansi_id IS NULL ORDER BY urut ASC")->queryAll();
            foreach ($globalProcessesRaw as $gp) {
                $gpSteps = $db->createCommand("SELECT * FROM jobdesk_master_steps WHERE process_id = :id ORDER BY urut ASC")
                              ->bindValue(':id', $gp['id'])
                              ->queryAll();
                $gp['steps'] = $gpSteps;
                $globalProcesses[] = $gp;
            }
        }
        
        $processSteps = [];
        foreach ($processes as $p) {
            $steps = $db->createCommand("SELECT * FROM jobdesk_master_steps WHERE process_id = :id ORDER BY urut ASC")
                        ->bindValue(':id', $p['id'])
                        ->queryAll();
            $processSteps[$p['id']] = $steps;
        }

        // Fetch tarif per process
        $processTarif = [];
        try {
            $tarifRows = $db->createCommand("SELECT * FROM jobdesk_process_tarif")->queryAll();
            foreach ($tarifRows as $t) {
                $processTarif[$t['process_id']] = $t;
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return $viewRenderer->render(__DIR__ . '/settings', [
            'processes'       => $processes,
            'processSteps'    => $processSteps,
            'processTarif'    => $processTarif,
            'role'            => $role,
            'globalProcesses' => $globalProcesses
        ]);
    }
}
