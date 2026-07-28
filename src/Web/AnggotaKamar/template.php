<?php
declare(strict_types=1);
use Yiisoft\View\WebView;
use App\Shared\ApplicationParams;
/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var array $grouped
 */
$this->setTitle('Anggota Kamar | Sistem Informasi');
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; background: #0dcaf015;">
            <i class="bi bi-people fs-5 text-info"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -.5px;">Anggota Kamar / Asrama</h4>
            <div class="text-muted small fw-medium mt-1">Distribusi jumlah santri berdasarkan asrama dan rayon</div>
        </div>
    </div>
</div>

<?php if (empty($grouped)): ?>
<div class="card border-0 shadow-sm rounded-4 text-center py-5 mb-4">
    <div class="mb-3">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa;">
            <i class="bi bi-house-x text-muted" style="font-size: 2.5rem;"></i>
        </div>
    </div>
    <h5 class="fw-bold text-dark">Belum Ada Data</h5>
    <p class="text-muted small mb-0">Belum ada data santri aktif untuk ditampilkan pada halaman ini.</p>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($grouped as $pondok => $rayons): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-light border-bottom-0 pt-4 pb-2 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-house-door text-info me-2"></i>Pondok: <?= htmlspecialchars($pondok) ?></h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0" style="font-size: .85rem;">
                    <thead class="table-light shadow-sm text-muted">
                        <tr><th class="ps-4">Rayon</th><th class="text-end pe-4">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; foreach ($rayons as $r): $total += $r['total']; ?>
                        <tr>
                            <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($r['rayon'] ?: '(tidak ada rayon)') ?></td>
                            <td class="text-end pe-4"><span class="badge bg-info bg-opacity-25 text-info px-2 py-1 rounded-pill"><?= $r['total'] ?> Santri</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr><td class="ps-4">Total Keseluruhan</td><td class="text-end pe-4 text-primary"><?= $total ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
