<?php declare(strict_types=1);
namespace App\Web\SuratGenerator;
use App\Shared\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use App\Shared\AuditLogger;

final class GenerateAction
{
    public function __invoke(ServerRequestInterface $request, ConnectionInterface $db): ResponseInterface {
        $body = $request->getParsedBody();
        $tipeSurat = $body['tipe_surat'] ?? 'Surat/Dokumen';
        $kds = $body['kds'] ?? null;
        
        AuditLogger::log($db, 'CREATE', 'SURAT_GENERATOR', $kds, null, "Mencetak $tipeSurat untuk KDS: " . ($kds ?? '-'));
        
        return JsonResponse::create(['success' => true, 'message' => 'Surat siap dicetak. Gunakan fitur print browser.']);
    }
}
