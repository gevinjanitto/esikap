<?php

use App\Http\Controllers\AnnualDocController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CascadingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\RencanaAksiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\RpjmdController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/opd/{opd}', [DashboardController::class, 'opd'])->name('dashboard.opd');

    Route::middleware('menu:master')->group(function () {
        Route::get('/master/{res}', [MasterController::class, 'index'])->name('master.index');
        Route::post('/master/{res}', [MasterController::class, 'store'])->name('master.store');
        Route::put('/master/{res}/{id}', [MasterController::class, 'update'])->name('master.update');
        Route::delete('/master/{res}/{id}', [MasterController::class, 'destroy'])->name('master.destroy');
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::get('/indikator', [IndicatorController::class, 'index'])->name('indikator.index');
    Route::post('/indikator', [IndicatorController::class, 'store'])->name('indikator.store');
    Route::put('/indikator/{indicator}', [IndicatorController::class, 'update'])->name('indikator.update');
    Route::delete('/indikator/{indicator}', [IndicatorController::class, 'destroy'])->name('indikator.destroy');

    Route::middleware('menu:rpjmd')->group(function () {
        Route::get('/rpjmd', [RpjmdController::class, 'index'])->name('rpjmd.index');
        Route::post('/rpjmd', [RpjmdController::class, 'store'])->name('rpjmd.store');
        Route::put('/rpjmd/{rpjmd}', [RpjmdController::class, 'update'])->name('rpjmd.update');
        Route::post('/rpjmd/sasaran/{sasaran}/opd', [RpjmdController::class, 'assign'])->name('rpjmd.assign');
        Route::delete('/rpjmd/sasaran/{sasaran}/opd/{opd}', [RpjmdController::class, 'unassign'])->name('rpjmd.unassign');
    });

    Route::post('/tree/{type}', [TreeController::class, 'store'])->name('tree.store');
    Route::put('/tree/{type}/{id}', [TreeController::class, 'update'])->name('tree.update');
    Route::delete('/tree/{type}/{id}', [TreeController::class, 'destroy'])->name('tree.destroy');

    Route::get('/cascading', [CascadingController::class, 'index'])->name('cascading');

    Route::middleware('menu:renstra')->group(function () {
        Route::get('/renstra', [RenstraController::class, 'index'])->name('renstra.index');
        Route::post('/renstra', [RenstraController::class, 'store'])->name('renstra.store');
        Route::get('/renstra/{renstra}', [RenstraController::class, 'show'])->name('renstra.show');
    });

    Route::get('/tahunan/{kind}', [AnnualDocController::class, 'index'])->name('annual.index')->whereIn('kind', ['renja', 'pk']);
    Route::post('/tahunan/{kind}', [AnnualDocController::class, 'store'])->name('annual.store')->whereIn('kind', ['renja', 'pk']);
    Route::get('/tahunan/{kind}/{id}', [AnnualDocController::class, 'show'])->name('annual.show')->whereIn('kind', ['renja', 'pk']);
    Route::put('/tahunan/{kind}/{id}', [AnnualDocController::class, 'update'])->name('annual.update')->whereIn('kind', ['renja', 'pk']);
    Route::get('/tahunan/{kind}/{id}/cetak', [AnnualDocController::class, 'print'])->name('annual.print')->whereIn('kind', ['renja', 'pk']);
    Route::post('/tahunan/{kind}/{id}/item', [AnnualDocController::class, 'addItem'])->name('annual.item.store')->whereIn('kind', ['renja', 'pk']);
    Route::delete('/tahunan/{kind}/{id}/item/{item}', [AnnualDocController::class, 'removeItem'])->name('annual.item.destroy')->whereIn('kind', ['renja', 'pk']);

    Route::middleware('menu:renaksi')->group(function () {
        Route::get('/rencana-aksi', [RencanaAksiController::class, 'index'])->name('renaksi.index');
        Route::post('/rencana-aksi', [RencanaAksiController::class, 'store'])->name('renaksi.store');
        Route::put('/rencana-aksi/{renaksi}', [RencanaAksiController::class, 'update'])->name('renaksi.update');
        Route::delete('/rencana-aksi/{renaksi}', [RencanaAksiController::class, 'destroy'])->name('renaksi.destroy');
    });

    Route::middleware('menu:realisasi')->group(function () {
        Route::get('/realisasi', [RealisasiController::class, 'index'])->name('realisasi.index');
        Route::post('/realisasi', [RealisasiController::class, 'store'])->name('realisasi.store');
        Route::put('/realisasi/{realisasi}', [RealisasiController::class, 'update'])->name('realisasi.update');
    });

    Route::middleware('menu:evaluasi')->group(function () {
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
        Route::post('/evaluasi', [EvaluasiController::class, 'store'])->name('evaluasi.store');
        Route::get('/evaluasi/{evaluasi}', [EvaluasiController::class, 'show'])->name('evaluasi.show');
        Route::put('/evaluasi/{evaluasi}', [EvaluasiController::class, 'update'])->name('evaluasi.update');
        Route::post('/evaluasi/{evaluasi}/rekomendasi', [EvaluasiController::class, 'addRekomendasi'])->name('rekomendasi.store');
        Route::put('/rekomendasi/{rekomendasi}/tindak-lanjut', [EvaluasiController::class, 'tindakLanjut'])->name('rekomendasi.tl');
        Route::put('/rekomendasi/{rekomendasi}/verifikasi', [EvaluasiController::class, 'verifikasi'])->name('rekomendasi.verify');
    });

    Route::get('/dokumen', [DocumentController::class, 'index'])->name('dokumen.index');
    Route::post('/dokumen', [DocumentController::class, 'store'])->name('dokumen.store');
    Route::get('/dokumen/{document}/unduh', [DocumentController::class, 'download'])->name('dokumen.download');
    Route::put('/dokumen/{document}/arsip', [DocumentController::class, 'archive'])->name('dokumen.archive');

    Route::post('/bukti/{type}/{id}', [EvidenceController::class, 'store'])->name('evidence.store');
    Route::get('/bukti/{evidence}/unduh', [EvidenceController::class, 'download'])->name('evidence.download');
    Route::delete('/bukti/{evidence}', [EvidenceController::class, 'destroy'])->name('evidence.destroy');

    Route::post('/workflow/{type}/{id}/{action}', [ApprovalController::class, 'transition'])->name('workflow');
    Route::get('/persetujuan', [ApprovalController::class, 'index'])->name('approval.index')->middleware('menu:approval');

    Route::middleware('menu:laporan')->group(function () {
        Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{type}/export', [ReportController::class, 'export'])->name('laporan.export');
        Route::get('/laporan/{type}/cetak', [ReportController::class, 'print'])->name('laporan.print');
    });

    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index')->middleware('menu:audit');

    Route::get('/notifikasi', [NotifController::class, 'index'])->name('notif.index');
    Route::post('/notifikasi/baca', [NotifController::class, 'readAll'])->name('notif.readAll');
    Route::get('/notifikasi/{notif}', [NotifController::class, 'open'])->name('notif.open');
});
