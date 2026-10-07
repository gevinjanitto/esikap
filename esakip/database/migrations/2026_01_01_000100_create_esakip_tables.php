<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedSmallInteger('start_year');
            $t->unsignedSmallInteger('end_year');
            $t->boolean('is_active')->default(false);
            $t->timestamps();
        });

        Schema::create('opds', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30);
            $t->string('name');
            $t->string('singkatan', 30)->nullable();
            $t->string('type', 30)->default('Dinas');
            $t->string('status', 20)->default('aktif');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('units', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->string('code', 30);
            $t->string('name');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('satuans', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20);
            $t->string('name');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('rpjmds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('period_id')->constrained('periods');
            $t->string('name');
            $t->text('visi')->nullable();
            $t->string('nomor_dokumen')->nullable();
            $t->date('tanggal_dokumen')->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('misis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rpjmd_id')->constrained('rpjmds')->cascadeOnDelete();
            $t->string('code', 30);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('tujuan_daerahs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('misi_id')->constrained('misis')->cascadeOnDelete();
            $t->string('code', 30);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('sasaran_daerahs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tujuan_daerah_id')->constrained('tujuan_daerahs')->cascadeOnDelete();
            $t->string('code', 30);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('opd_sasaran_daerah', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sasaran_daerah_id')->constrained('sasaran_daerahs')->cascadeOnDelete();
            $t->foreignId('opd_id')->constrained('opds');
            $t->string('peran', 20)->default('utama');
            $t->timestamps();
            $t->unique(['sasaran_daerah_id', 'opd_id']);
        });

        Schema::create('renstras', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->foreignId('period_id')->constrained('periods');
            $t->string('nomor_dokumen')->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('tujuan_opds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('renstra_id')->constrained('renstras')->cascadeOnDelete();
            $t->foreignId('sasaran_daerah_id')->nullable()->constrained('sasaran_daerahs')->nullOnDelete();
            $t->string('code', 30);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('sasaran_opds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tujuan_opd_id')->constrained('tujuan_opds')->cascadeOnDelete();
            $t->string('code', 30);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->foreignId('sasaran_opd_id')->constrained('sasaran_opds')->cascadeOnDelete();
            $t->string('code', 50);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('kegiatans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $t->string('code', 50);
            $t->text('name');
            $t->timestamps();
        });

        Schema::create('indicators', function (Blueprint $t) {
            $t->id();
            $t->morphs('owner');
            $t->unsignedBigInteger('opd_id')->nullable()->index();
            $t->string('code', 30)->nullable();
            $t->text('name');
            $t->string('type', 20)->default('outcome');
            $t->foreignId('satuan_id')->constrained('satuans');
            $t->text('definition');
            $t->string('formula', 20)->default('positif');
            $t->decimal('baseline', 15, 2)->nullable();
            $t->string('data_source')->nullable();
            $t->timestamps();
        });

        Schema::create('indicator_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->decimal('value', 15, 2);
            $t->timestamps();
            $t->unique(['indicator_id', 'year']);
        });

        Schema::create('renjas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->unsignedSmallInteger('year');
            $t->text('catatan')->nullable();
            $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('renja_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('renja_id')->constrained('renjas')->cascadeOnDelete();
            $t->foreignId('indicator_id')->constrained('indicators');
            $t->decimal('target', 15, 2);
            $t->decimal('pagu', 18, 2)->nullable();
            $t->text('keterangan')->nullable();
            $t->timestamps();
        });

        Schema::create('perjanjian_kinerjas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->unsignedSmallInteger('year');
            $t->string('pihak_pertama')->nullable();
            $t->string('jabatan_pertama')->nullable();
            $t->string('pihak_kedua')->nullable();
            $t->string('jabatan_kedua')->nullable();
            $t->text('catatan')->nullable();
            $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('pk_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('perjanjian_kinerja_id')->constrained('perjanjian_kinerjas')->cascadeOnDelete();
            $t->foreignId('indicator_id')->constrained('indicators');
            $t->decimal('target', 15, 2);
            $t->decimal('pagu', 18, 2)->nullable();
            $t->text('keterangan')->nullable();
            $t->timestamps();
        });

        Schema::create('rencana_aksis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->foreignId('indicator_id')->constrained('indicators');
            $t->unsignedSmallInteger('year');
            $t->text('aktivitas');
            $t->string('pic');
            $t->string('triwulan', 10);
            $t->string('target');
            $t->string('status', 20)->default('belum_mulai');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('realisasis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->foreignId('indicator_id')->constrained('indicators');
            $t->unsignedSmallInteger('year');
            $t->string('periode', 10);
            $t->decimal('target', 15, 2);
            $t->decimal('realisasi', 15, 2);
            $t->decimal('capaian', 8, 2);
            $t->decimal('deviasi', 15, 2);
            $t->string('status_capaian', 20);
            $t->text('analisis')->nullable();
            $t->string('status', 20)->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->unique(['indicator_id', 'year', 'periode']);
        });

        Schema::create('evidences', function (Blueprint $t) {
            $t->id();
            $t->morphs('evidenceable');
            $t->string('file_path')->nullable();
            $t->string('original_name')->nullable();
            $t->string('link')->nullable();
            $t->string('mime', 120)->nullable();
            $t->unsignedBigInteger('size')->default(0);
            $t->string('keterangan')->nullable();
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->timestamps();
        });

        Schema::create('evaluasis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opds');
            $t->unsignedSmallInteger('year');
            $t->unsignedBigInteger('evaluator_id')->nullable();
            $t->decimal('nilai', 5, 2)->default(0);
            $t->string('predikat', 5)->nullable();
            $t->text('catatan')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamps();
        });

        Schema::create('rekomendasis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('evaluasi_id')->constrained('evaluasis')->cascadeOnDelete();
            $t->text('uraian');
            $t->date('batas_waktu')->nullable();
            $t->text('tindak_lanjut')->nullable();
            $t->text('catatan_verifikasi')->nullable();
            $t->string('status', 20)->default('belum');
            $t->timestamps();
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('jenis', 30);
            $t->string('nomor')->nullable();
            $t->date('tanggal')->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->unsignedBigInteger('opd_id')->nullable()->index();
            $t->unsignedBigInteger('period_id')->nullable();
            $t->string('file_path');
            $t->string('original_name');
            $t->unsignedBigInteger('size')->default(0);
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->timestamp('archived_at')->nullable();
            $t->string('archive_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('approval_logs', function (Blueprint $t) {
            $t->id();
            $t->morphs('approvable');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('role', 30)->nullable();
            $t->string('action', 20);
            $t->string('from_status', 20)->nullable();
            $t->string('to_status', 20);
            $t->text('note')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('action', 30)->index();
            $t->string('model_type', 60)->nullable()->index();
            $t->unsignedBigInteger('model_id')->nullable();
            $t->string('label')->nullable();
            $t->json('before')->nullable();
            $t->json('after')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });

        Schema::create('notifs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->string('message');
            $t->string('link')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['notifs', 'audit_logs', 'approval_logs', 'documents', 'rekomendasis', 'evaluasis', 'evidences', 'realisasis', 'rencana_aksis', 'pk_items', 'perjanjian_kinerjas', 'renja_items', 'renjas', 'indicator_targets', 'indicators', 'kegiatans', 'programs', 'sasaran_opds', 'tujuan_opds', 'renstras', 'opd_sasaran_daerah', 'sasaran_daerahs', 'tujuan_daerahs', 'misis', 'rpjmds', 'satuans', 'units', 'opds', 'periods'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
