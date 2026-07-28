<?php
declare(strict_types=1);

namespace App\Web\AnggotaKamar;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action
{
    public function __invoke(
        WebViewRenderer $viewRenderer,
        ConnectionInterface $db
    ): ResponseInterface {
        $byPondok = $db->createCommand(
            "SELECT pondok, rayon, COUNT(*) as total FROM master_santri WHERE aktif=1 GROUP BY pondok, rayon ORDER BY pondok, rayon"
        )->queryAll();

        $grouped = [];
        foreach ($byPondok as $row) {
            $grouped[$row['pondok']][] = $row;
        }

        return $viewRenderer->render(__DIR__ . '/template', ['grouped' => $grouped]);
    }
}
