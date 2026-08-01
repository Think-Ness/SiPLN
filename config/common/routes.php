<?php

declare(strict_types=1);

use App\Web;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // Rute Publik (Authentication)
    Route::get('/login')
        ->action(Web\Auth\LoginAction::class)
        ->name('login'),
    Route::post('/api/login')
        ->action(Web\Auth\DoLoginAction::class)
        ->name('api.login'),
    Route::get('/logout')
        ->action(Web\Auth\LogoutAction::class)
        ->name('logout'),

    // Rute Privat yang Dilindungi
    Group::create()
        ->middleware(Web\Auth\AuthMiddleware::class)
        ->routes(

            // Manajemen Paspor
            Route::get('/manajemen-paspor')
                ->action(Web\ManajemenPaspor\Action::class)
                ->name('manajemen-paspor'),
            Route::get('/api/manajemen-paspor/list')
                ->action(Web\ManajemenPaspor\ApiListAction::class)
                ->name('api.manajemen-paspor.list'),
            Route::post('/api/manajemen-paspor/pinjam')
                ->action(Web\ManajemenPaspor\PinjamAction::class)
                ->name('api.manajemen-paspor.pinjam'),
            Route::post('/api/manajemen-paspor/kembalikan')
                ->action(Web\ManajemenPaspor\KembalikanAction::class)
                ->name('api.manajemen-paspor.kembalikan'),
            Route::get('/api/manajemen-paspor/log')
                ->action(Web\ManajemenPaspor\LogAction::class)
                ->name('api.manajemen-paspor.log'),
            Route::get('/api/manajemen-paspor/export')
                ->action(Web\ManajemenPaspor\ExportAction::class)
                ->name('api.manajemen-paspor.export'),

            // Pendaftaran Capel
            Route::post('/api/capel/sync')
                ->action(Web\Capel\SyncAction::class)
                ->name('api.capel.sync'),
            Route::get('/pendaftaran-capel')
                ->action(Web\Capel\ListAction::class)
                ->name('pendaftaran-capel'),
            Route::get('/api/capel/list')
                ->action(Web\Capel\ApiListAction::class)
                ->name('api.capel.list'),
            Route::post('/api/capel/{id:\d+}/approve')
                ->action(Web\Capel\ApproveAction::class)
                ->name('api.capel.approve'),
            Route::post('/api/capel/sync-headers')
                ->action(Web\Capel\SyncHeadersAction::class)
                ->name('api.capel.sync-headers'),
            Route::post('/api/capel/save-display-columns')
                ->action(Web\Capel\SaveDisplayColumnsAction::class)
                ->name('api.capel.save-display-columns'),
            Route::post('/api/capel/bulk-approve')
                ->action(Web\Capel\BulkApproveAction::class)
                ->name('api.capel.bulk-approve'),
            Route::post('/api/capel/bulk-delete')
                ->action(Web\Capel\BulkDeleteAction::class)
                ->name('api.capel.bulk-delete'),
            Route::post('/api/capel/bulk-cancel')
                ->action(Web\Capel\BulkCancelAction::class)
                ->name('api.capel.bulk-cancel'),
            Route::get('/api/capel/download-status')
                ->action(Web\Capel\DownloadStatusAction::class)
                ->name('api.capel.download-status'),
            Route::post('/api/capel/download-cancel')
                ->action(Web\Capel\DownloadCancelAction::class)
                ->name('api.capel.download-cancel'),
            Route::post('/api/capel/download-action')
                ->action(Web\Capel\DownloadActionAction::class)
                ->name('api.capel.download-action'),
            Route::post('/api/capel/download-clear')
                ->action(Web\Capel\DownloadClearAction::class)
                ->name('api.capel.download-clear'),
            Route::get('/api/capel/download-history')
                ->action(Web\Capel\DownloadHistoryAction::class)
                ->name('api.capel.download-history'),
            Route::post('/api/capel/{id:\d+}/reject')
                ->action(Web\Capel\RejectAction::class)
                ->name('api.capel.reject'),

            // Manajemen Instansi (Khusus Super Admin)
            Route::get('/manajemen-instansi')
                ->action(Web\ManajemenInstansi\ListAction::class)
                ->name('manajemen-instansi'),
            Route::post('/api/manajemen-instansi/store')
                ->action(Web\ManajemenInstansi\StoreAction::class)
                ->name('manajemen-instansi.store'),
            Route::post('/api/manajemen-instansi/{kode:\d+}/delete')
                ->action(Web\ManajemenInstansi\DeleteAction::class)
                ->name('manajemen-instansi.delete'),

            // Manajemen Pengguna (Super Admin & Admin Instansi)
            Route::get('/manajemen-user')
                ->action(Web\ManajemenUser\ListAction::class)
                ->name('manajemen-user'),
            Route::post('/api/manajemen-user/store')
                ->action(Web\ManajemenUser\StoreAction::class)
                ->name('manajemen-user.store'),
            Route::post('/api/manajemen-user/{id:\d+}/delete')
                ->action(Web\ManajemenUser\DeleteAction::class)
                ->name('manajemen-user.delete'),

            // Dashboard
            Route::get('/')
                ->action(Web\HomePage\Action::class)
                ->name('home'),
            Route::get('/api/dashboard')
                ->action(Web\HomePage\ApiAction::class)
                ->name('api.dashboard'),

            // Master Data Santri - CRUD
            Route::get('/master-data')
                ->action(Web\Santri\ListAction::class)
                ->name('master-data'),
            Route::get('/api/master-data')
                ->action(Web\Santri\ApiListAction::class)
                ->name('api.master-data'),
            Route::post('/api/sync/preview')
                ->action(Web\Santri\PreviewAction::class)
                ->name('api.sync.preview'),
            Route::post('/api/sync/pull')
                ->action(Web\Santri\PullAction::class)
                ->name('api.sync.pull'),
            Route::post('/api/santri/store')
                ->action(Web\Santri\StoreAction::class)
                ->name('santri.store'),
            Route::post('/api/santri/{kds}/update')
                ->action(Web\Santri\UpdateAction::class)
                ->name('santri.update'),
            Route::post('/api/santri/bulk-edit')
                ->action(Web\Santri\BulkEditAction::class)
                ->name('santri.bulk-edit'),
            Route::post('/api/santri/{kds}/delete')
                ->action(Web\Santri\DeleteAction::class)
                ->name('santri.delete'),
            Route::get('/api/santri/{kds}')
                ->action(Web\Santri\ShowAction::class)
                ->name('santri.show'),
            Route::get('/santri/{kds}/photo')
                ->action(Web\Santri\ViewPhotoAction::class)
                ->name('santri.photo'),
            Route::delete('/api/santri/doc/{type}/{id}/delete')
                ->action(\App\Web\Santri\DeleteDocAction::class)
                ->name('api.santri.doc.delete'),
            Route::post('/api/santri/doc/{type}/{id}/update')
                ->action(\App\Web\Santri\UpdateDocAction::class)
                ->name('api.santri.doc.update'),
            Route::post('/api/santri/doc/{type}/{id}/upload')
                ->action(\App\Web\Santri\UploadDocAction::class)
                ->name('api.santri.doc.upload'),
            Route::get('/api/santri/doc/{type}/{id}')
                ->action(\App\Web\Santri\ViewDocumentAction::class)
                ->name('api.santri.doc.view'),
            Route::get('/api/santri/view-berkas/{kds:\d+}/{filename:.+}')
                ->action(\App\Web\Santri\ViewBerkasAction::class)
                ->name('api.santri.view-berkas'),
            Route::post('/api/santri/berkas/{kds:\d+}/upload')
                ->action(\App\Web\Santri\UploadBerkasAction::class)
                ->name('api.santri.berkas.upload'),
            Route::post('/api/santri/berkas/{kds:\d+}/rename')
                ->action(\App\Web\Santri\RenameBerkasAction::class)
                ->name('api.santri.berkas.rename'),
            Route::post('/api/santri/berkas/{kds:\d+}/delete')
                ->action(\App\Web\Santri\DeleteBerkasAction::class)
                ->name('api.santri.berkas.delete'),
            Route::post('/api/santri/auto-upload-itas')
                ->action(\App\Web\Santri\AutoUploadItasAction::class)
                ->name('api.santri.auto-upload-itas'),

            // Import Excel
            Route::get('/import-excel')
                ->action(Web\ImportExcel\ImportAction::class)
                ->name('import-excel'),
            Route::post('/api/import-excel/parse')
                ->action(Web\ImportExcel\ParseAction::class)
                ->name('import-excel.parse'),
            Route::post('/api/import-excel/execute')
                ->action(Web\ImportExcel\ExecuteAction::class)
                ->name('import-excel.execute'),

            // Auto Rekap
            Route::get('/auto-rekap')
                ->action(Web\AutoRekap\Action::class)
                ->name('auto-rekap'),
            Route::get('/kalender-expiry')
                ->action(Web\AutoRekap\CalendarAction::class)
                ->name('kalender-expiry'),
            Route::get('/auto-rekap/print')
                ->action(Web\AutoRekap\PrintAction::class)
                ->name('auto-rekap.print'),
            Route::get('/api/auto-rekap')
                ->action(Web\AutoRekap\ApiAction::class)
                ->name('api.auto-rekap'),
            Route::get('/api/auto-rekap/calendar')
                ->action(Web\AutoRekap\CalendarApiAction::class)
                ->name('api.auto-rekap.calendar'),

            // Job Desk Perpanjangan ITAS
            Route::get('/job-desk')
                ->action(Web\JobDesk\Action::class)
                ->name('job-desk'),
            Route::get('/job-desk/report')
                ->action(Web\JobDesk\ReportAction::class)
                ->name('job-desk.report'),
            Route::get('/job-desk/settings')
                ->action(Web\JobDesk\SettingsAction::class)
                ->name('job-desk.settings'),
            Route::post('/api/job-desk/settings/save')
                ->action(Web\JobDesk\SaveSettingsAction::class)
                ->name('job-desk.settings.save'),
            Route::post('/api/job-desk/settings/copy-global')
                ->action(Web\JobDesk\CopyGlobalAction::class)
                ->name('job-desk.settings.copy-global'),
            Route::get('/job-desk/{id:\d+}')
                ->action(Web\JobDesk\DetailAction::class)
                ->name('job-desk.detail'),
            Route::post('/api/job-desk/create')
                ->action(Web\JobDesk\CreateAction::class)
                ->name('job-desk.create'),
            Route::post('/api/job-desk/step/bulk-update')
                ->action(Web\JobDesk\BulkUpdateStepAction::class)
                ->name('job-desk.step.bulk-update'),
            Route::post('/api/job-desk/step/{id:\d+}/update')
                ->action(Web\JobDesk\UpdateStepAction::class)
                ->name('job-desk.step.update'),
            Route::post('/api/job-desk/step/{id:\d+}/catatan')
                ->action(Web\JobDesk\AddCatatanAction::class)
                ->name('job-desk.step.catatan'),
            Route::post('/api/job-desk/catatan/{id:\d+}/update')
                ->action(Web\JobDesk\UpdateCatatanAction::class)
                ->name('job-desk.catatan.update'),
            Route::post('/api/job-desk/catatan/{id:\d+}/delete')
                ->action(Web\JobDesk\DeleteCatatanAction::class)
                ->name('job-desk.catatan.delete'),
            Route::post('/api/job-desk/case/bulk-delete')
                ->action(Web\JobDesk\BulkDeleteAction::class)
                ->name('job-desk.case.bulk-delete'),
            Route::post('/api/job-desk/case/{id:\d+}/delete')
                ->action(Web\JobDesk\DeleteAction::class)
                ->name('job-desk.case.delete'),

            // Job Desk Payment Routes
            Route::get('/job-desk/keuangan')
                ->action(Web\JobDesk\KeuanganDashboardAction::class)
                ->name('job-desk.keuangan'),
            Route::get('/job-desk/pengeluaran-operasional')
                ->action(Web\JobDesk\PengeluaranDashboardAction::class)
                ->name('job-desk.pengeluaran-operasional'),
            Route::post('/api/job-desk/payment/bulk-update')
                ->action([Web\JobDesk\PaymentAction::class, 'bulkUpdate'])
                ->name('job-desk.payment.bulk-update'),
            Route::get('/api/job-desk/payment/{id:\d+}')
                ->action([Web\JobDesk\PaymentAction::class, 'get'])
                ->name('job-desk.payment.get'),
            Route::post('/api/job-desk/payment/{id:\d+}')
                ->action([Web\JobDesk\PaymentAction::class, 'update'])
                ->name('job-desk.payment.update'),
            Route::post('/api/job-desk/payment/{id:\d+}/upload')
                ->action([Web\JobDesk\PaymentAction::class, 'upload'])
                ->name('job-desk.payment.upload'),
            Route::get('/api/job-desk/payment/history/{kds}')
                ->action([Web\JobDesk\PaymentAction::class, 'history'])
                ->name('job-desk.payment.history'),
            Route::post('/api/job-desk/payment/{id:\d+}/cicil')
                ->action([Web\JobDesk\PaymentAction::class, 'cicil'])
                ->name('job-desk.payment.cicil'),
            Route::get('/api/job-desk/payment/{id:\d+}/installments')
                ->action([Web\JobDesk\PaymentAction::class, 'installments'])
                ->name('job-desk.payment.installments'),
            Route::post('/api/job-desk/payment/instansi-bulk-update')
                ->action([Web\JobDesk\PaymentAction::class, 'bulkUpdate'])
                ->name('job-desk.payment.instansi-bulk-update'),

            // Pengeluaran Operasional Birokrasi
            Route::get('/api/job-desk/pengeluaran')
                ->action([Web\JobDesk\PengeluaranAction::class, 'list'])
                ->name('job-desk.pengeluaran.list'),
            Route::post('/api/job-desk/pengeluaran/create')
                ->action([Web\JobDesk\PengeluaranAction::class, 'create'])
                ->name('job-desk.pengeluaran.create'),
            Route::post('/api/job-desk/pengeluaran/{id:\d+}/update')
                ->action([Web\JobDesk\PengeluaranAction::class, 'update'])
                ->name('job-desk.pengeluaran.update'),
            Route::post('/api/job-desk/pengeluaran/{id:\d+}/delete')
                ->action([Web\JobDesk\PengeluaranAction::class, 'delete'])
                ->name('job-desk.pengeluaran.delete'),
            Route::get('/api/job-desk/pengeluaran/view-nota/{id:\d+}')
                ->action([Web\JobDesk\PengeluaranAction::class, 'viewNota'])
                ->name('job-desk.pengeluaran.view-nota'),
            
            // Kategori Pengeluaran
            Route::post('/api/job-desk/pengeluaran/kategori/create')
                ->action([Web\JobDesk\PengeluaranAction::class, 'createKategori'])
                ->name('job-desk.pengeluaran.kategori.create'),
            Route::post('/api/job-desk/pengeluaran/kategori/{id:\d+}/delete')
                ->action([Web\JobDesk\PengeluaranAction::class, 'deleteKategori'])
                ->name('job-desk.pengeluaran.kategori.delete'),

            // Data Santri Inaktif
            Route::get('/inaktif-data')
                ->action(Web\InaktifData\Action::class)
                ->name('inaktif-data'),
            Route::post('/api/inaktif-data/{kds}/hard-delete')
                ->action(Web\InaktifData\HardDeleteAction::class)
                ->name('api.inaktif-data.hard-delete'),
            Route::post('/api/inaktif-data/bulk-hard-delete')
                ->action(Web\InaktifData\BulkHardDeleteAction::class)
                ->name('api.inaktif-data.bulk-hard-delete'),
            Route::post('/api/santri/{kds}/toggle-aktif')
                ->action(Web\Santri\ToggleAktifAction::class)
                ->name('santri.toggle-aktif'),
            Route::post('/api/santri/bulk-toggle')
                ->action(Web\Santri\BulkToggleAktifAction::class)
                ->name('santri.bulk-toggle'),

            // Pemberkasan
            Route::get('/pemberkasan')
                ->action(Web\Pemberkasan\Action::class)
                ->name('pemberkasan'),
            Route::get('/pemberkasan/cetak-berkas')
                ->action(Web\Pemberkasan\CetakBerkasAction::class)
                ->name('pemberkasan.cetak-berkas'),
            Route::post('/api/pemberkasan/merge')
                ->action(Web\Pemberkasan\MergeAction::class)
                ->name('api.pemberkasan.merge'),
            Route::get('/api/pemberkasan/open-folder')
                ->action(Web\Pemberkasan\OpenFolderAction::class)
                ->name('api.pemberkasan.open-folder'),
                
            // Master Print
            Route::get('/master-print/menu-print')
                ->action(Web\MasterPrint\MenuPrintAction::class)
                ->name('master-print.menu'),
            Route::post('/master-print/export')
                ->action(Web\MasterPrint\ExportAction::class)
                ->name('master-print.export'),

            Route::get('/api/pemberkasan')
                ->action(Web\Pemberkasan\ApiAction::class)
                ->name('api.pemberkasan'),
            Route::post('/api/berkas/upload')
                ->action(Web\Pemberkasan\UploadAction::class)
                ->name('berkas.upload'),
            Route::post('/api/berkas/{id}/update')
                ->action(Web\Pemberkasan\UpdateAction::class)
                ->name('berkas.update'),
            Route::post('/api/berkas/{id}/delete')
                ->action(Web\Pemberkasan\DeleteAction::class)
                ->name('berkas.delete'),
            Route::get('/berkas/{id}/view')
                ->action(Web\Pemberkasan\ViewAction::class)
                ->name('berkas.view'),

            // Profil Instansi
            Route::get('/profil-instansi')
                ->action(Web\ProfilInstansi\Action::class)
                ->name('profil-instansi'),
            Route::post('/api/instansi/update')
                ->action(Web\ProfilInstansi\UpdateAction::class)
                ->name('instansi.update'),
            Route::get('/profil-instansi/kop-surat/view')
                ->action(Web\ProfilInstansi\ViewKopAction::class)
                ->name('instansi.kop-surat'),

            // Anggota Kamar
            Route::get('/anggota-kamar')
                ->action(Web\AnggotaKamar\Action::class)
                ->name('anggota-kamar'),

            // Surat Generator (upgraded)
            Route::get('/surat-generator')
                ->action(Web\SuratGenerator\Action::class)
                ->name('surat-generator'),
            Route::post('/api/surat/create-mailing')
                ->action(Web\SuratGenerator\CreateMailingAction::class)
                ->name('surat.create-mailing'),
            Route::post('/api/surat/generate')
                ->action(Web\SuratGenerator\GenerateSuratAction::class)
                ->name('surat.generate'),
            Route::get('/api/surat/mailing/{id:\d+}')
                ->action(Web\SuratGenerator\MailingDetailAction::class)
                ->name('surat.mailing.detail'),
            Route::post('/api/surat/mailing/{id:\d+}/delete')
                ->action(Web\SuratGenerator\DeleteMailingAction::class)
                ->name('surat.mailing.delete'),
            Route::post('/api/surat/mailing/bulk-delete')
                ->action(Web\SuratGenerator\BulkDeleteMailingAction::class)
                ->name('surat.mailing.bulk-delete'),
            Route::get('/api/surat/jenis-pengajuan')
                ->action([Web\SuratGenerator\JenisPengajuanAction::class, 'list'])
                ->name('surat.jenis-pengajuan.list'),
            Route::get('/api/surat/download/{id:\d+}')
                ->action(\App\Web\SuratGenerator\DownloadSuratAction::class)
                ->name('surat.download'),
            Route::get('/api/surat/download-template')
                ->action(\App\Web\SuratGenerator\DownloadTemplateAction::class)
                ->name('surat.download-template'),
            Route::post('/api/surat/jenis-pengajuan/store')
                ->action([Web\SuratGenerator\JenisPengajuanAction::class, 'store'])
                ->name('surat.jenis-pengajuan.store'),
            Route::post('/api/surat/jenis-pengajuan/{id:\d+}/update')
                ->action([Web\SuratGenerator\JenisPengajuanAction::class, 'update'])
                ->name('surat.jenis-pengajuan.update'),
            Route::post('/api/surat/jenis-pengajuan/{id:\d+}/delete')
                ->action([Web\SuratGenerator\JenisPengajuanAction::class, 'delete'])
                ->name('surat.jenis-pengajuan.delete'),

            // Dynamic Surat Templates
            Route::get('/surat-templates')
                ->action([Web\SuratGenerator\TemplateManagementAction::class, 'index'])
                ->name('surat-templates'),
            Route::get('/api/surat/templates')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'list'])
                ->name('surat.templates.list'),
            Route::post('/api/surat/templates/upload')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'upload'])
                ->name('surat.templates.upload'),
            Route::post('/api/surat/templates/{id:\d+}/delete')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'delete'])
                ->name('surat.templates.delete'),
            Route::get('/api/surat/templates/{id:\d+}/open')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'openInWord'])
                ->name('surat.templates.open'),
            Route::get('/api/surat/instansi-tujuan')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'instansiTujuanList'])
                ->name('surat.instansi-tujuan.list'),
            Route::get('/api/surat/instansi-tujuan/{nama}/templates')
                ->action([Web\SuratGenerator\TemplateApiAction::class, 'instansiTujuanTemplates'])
                ->name('surat.instansi-tujuan.templates'),

            // Pengaturan Sistem
            Route::get('/pengaturan')
                ->action(Web\Pengaturan\Action::class)
                ->name('pengaturan'),
            Route::post('/api/pengaturan/update')
                ->action(Web\Pengaturan\UpdateAction::class)
                ->name('pengaturan.update'),
            Route::post('/api/pengaturan/migrate')
                ->action(Web\Pengaturan\RunMigrationAction::class)
                ->name('pengaturan.migrate'),

            // Audit Log
            Route::get('/audit-log')
                ->action(Web\AuditLog\Action::class)
                ->name('audit-log'),
            Route::get('/api/audit-log/unread-count')
                ->action(Web\AuditLog\UnreadCountAction::class)
                ->name('audit-log.unread'),

            // Request Edit (Approval)
            Route::get('/request-edit')
                ->action(Web\RequestEdit\Action::class)
                ->name('request-edit'),
            Route::post('/api/request-edit/{id}/approve')
                ->action(Web\RequestEdit\ApproveAction::class)
                ->name('request-edit.approve'),
            Route::post('/api/request-edit/{id}/reject')
                ->action(Web\RequestEdit\RejectAction::class)
                ->name('request-edit.reject'),
            Route::post('/api/request-edit/{id}/cancel')
                ->action(Web\RequestEdit\CancelAction::class)
                ->name('request-edit.cancel'),
            Route::get('/api/request-edit/pending-count')
                ->action(Web\RequestEdit\PendingCountAction::class)
                ->name('request-edit.pending-count'),

            // Anggaran Operasional
            Route::get('/anggaran')
                ->action(Web\Anggaran\Action::class)
                ->name('anggaran'),
            Route::post('/api/anggaran/store')
                ->action([Web\Anggaran\ApiAction::class, 'store'])
                ->name('api.anggaran.store'),
            Route::post('/api/anggaran/{id:\d+}/update')
                ->action([Web\Anggaran\ApiAction::class, 'update'])
                ->name('api.anggaran.update'),
            Route::post('/api/anggaran/{id:\d+}/delete')
                ->action([Web\Anggaran\ApiAction::class, 'delete'])
                ->name('api.anggaran.delete'),
            Route::post('/api/anggaran/{id:\d+}/approve')
                ->action([Web\Anggaran\ApiAction::class, 'approve'])
                ->name('api.anggaran.approve'),
            Route::get('/api/anggaran/{id:\d+}/nota')
                ->action([Web\Anggaran\NotaApiAction::class, 'list'])
                ->name('api.anggaran.nota.list'),
            Route::post('/api/anggaran/{id:\d+}/nota/store')
                ->action([Web\Anggaran\NotaApiAction::class, 'store'])
                ->name('api.anggaran.nota.store'),
            Route::post('/api/anggaran/nota/{id:\d+}/delete')
                ->action([Web\Anggaran\NotaApiAction::class, 'delete'])
                ->name('api.anggaran.nota.delete'),
            Route::get('/api/anggaran/view-nota/{id:\d+}')
                ->action([Web\Anggaran\NotaApiAction::class, 'viewNota'])
                ->name('api.anggaran.view-nota'),

            // Firebase Sync
            Route::post('/api/firebase/sync-all')
                ->action(Web\Sync\SyncAllAction::class)
                ->name('api.firebase.sync-all'),
            Route::get('/api/firebase/status')
                ->action(Web\Sync\StatusAction::class)
                ->name('api.firebase.status'),
            Route::methods(['GET', 'POST'], '/api/firebase/presence')
                ->action(Web\Sync\PresenceAction::class)
                ->name('api.firebase.presence'),

        ),
];

