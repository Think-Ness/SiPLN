<?php
declare(strict_types=1);

namespace App\Web\Capel;

use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Router\CurrentRoute;

final class RejectAction
{
    public function __invoke(
        ServerRequestInterface $request,
        CurrentRoute $currentRoute,
        ConnectionInterface $db
    ): ResponseInterface {
        $id = $currentRoute->getArgument('id');

        $db->createCommand("UPDATE mtb_capel_draft SET status_approval = 'Rejected' WHERE id = :id", [':id' => $id])->execute();

        return JsonResponse::create(['success' => true, 'message' => 'Pendaftaran CAPEL berhasil ditolak.']);
    }
}
