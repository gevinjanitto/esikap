<?php

namespace App\Support;

use App\Models\Period;
use App\Models\Rpjmd;

class Esakip
{
    public const STATUS = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-600 ring-slate-200'],
        'diajukan' => ['Diajukan', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'revisi' => ['Revisi', 'bg-amber-50 text-amber-700 ring-amber-200'],
        'disetujui' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'ditetapkan' => ['Ditetapkan', 'bg-red-600 text-white ring-red-600'],
        'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-200'],
        'diarsipkan' => ['Diarsipkan', 'bg-zinc-100 text-zinc-500 ring-zinc-200'],
        'tercapai' => ['Tercapai', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'perlu_perhatian' => ['Perlu Perhatian', 'bg-amber-50 text-amber-700 ring-amber-200'],
        'tidak_tercapai' => ['Tidak Tercapai', 'bg-red-50 text-red-700 ring-red-200'],
        'belum_mulai' => ['Belum Mulai', 'bg-slate-100 text-slate-600 ring-slate-200'],
        'berjalan' => ['Berjalan', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'terlambat' => ['Terlambat', 'bg-red-50 text-red-700 ring-red-200'],
        'belum' => ['Belum Ditindaklanjuti', 'bg-slate-100 text-slate-600 ring-slate-200'],
        'proses' => ['Dalam Proses', 'bg-amber-50 text-amber-700 ring-amber-200'],
        'terverifikasi' => ['Terverifikasi', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'final' => ['Final', 'bg-red-600 text-white ring-red-600'],
        'aktif' => ['Aktif', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'nonaktif' => ['Nonaktif', 'bg-zinc-100 text-zinc-500 ring-zinc-200'],
    ];

    public const RENAKSI_STATUS = ['belum_mulai' => 'Belum Mulai', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'terlambat' => 'Terlambat'];

    public static function label(?string $s): string
    {
        return self::STATUS[$s][0] ?? ucfirst((string) $s);
    }

    public static function badge(?string $s): string
    {
        return self::STATUS[$s][1] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
    }

    public static function capaian(string $formula, float $target, float $real): float
    {
        if ($target == 0) {
            return 0;
        }
        $c = $formula === 'negatif' ? (2 * $target - $real) / $target * 100 : $real / $target * 100;

        return round(max($c, 0), 2);
    }

    public static function statusCapaian(float $c): string
    {
        $t = config('esakip.thresholds');

        return $c >= $t['tercapai'] ? 'tercapai' : ($c >= $t['perhatian'] ? 'perlu_perhatian' : 'tidak_tercapai');
    }

    public static function predikat(float $n): string
    {
        return match (true) {
            $n > 90 => 'AA', $n > 80 => 'A', $n > 70 => 'BB', $n > 60 => 'B', $n > 50 => 'CC', $n > 30 => 'C', default => 'D',
        };
    }

    public static function activePeriod(): ?Period
    {
        return Period::where('is_active', true)->first() ?? Period::latest('id')->first();
    }

    public static function activeRpjmd(): ?Rpjmd
    {
        $p = self::activePeriod();

        return $p ? Rpjmd::where('period_id', $p->id)->where('status', '!=', 'diarsipkan')->orderByRaw("status = 'ditetapkan' desc")->latest('id')->first() : null;
    }

    public static function years(): array
    {
        $p = self::activePeriod();

        return $p ? range($p->start_year, $p->end_year) : [(int) date('Y')];
    }

    public static function currentYear(): int
    {
        $years = self::years();
        $y = (int) date('Y');

        return in_array($y, $years) ? $y : end($years);
    }

    public static function canMenu(string $key): bool
    {
        $u = auth()->user();
        $item = collect(config('esakip.menu'))->firstWhere('key', $key);
        if (! $u || ! $item) {
            return false;
        }

        return $item['roles'] === '*' || in_array($u->role, $item['roles']) || $u->role === 'super_admin';
    }

    public static function canEdit(string $area, $opdId = null): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }
        $roles = [
            'master' => ['super_admin', 'admin_pemda'],
            'rpjmd' => ['super_admin', 'admin_pemda', 'bappeda'],
            'opd' => ['super_admin', 'admin_pemda', 'operator_opd'],
            'evaluasi' => ['super_admin', 'evaluator'],
            'tindak_lanjut' => ['super_admin', 'operator_opd', 'kepala_opd'],
            'dokumen' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'operator_opd', 'evaluator'],
        ][$area] ?? [];
        if (! in_array($u->role, $roles)) {
            return false;
        }

        return $opdId === null || ! $u->isOpdScoped() || (int) $opdId === (int) $u->opd_id;
    }

    public static function canViewOpd($opdId): bool
    {
        $u = auth()->user();

        return $u && (! $u->isOpdScoped() || $opdId === null || (int) $opdId === (int) $u->opd_id);
    }

    public static function num($n, int $dec = 2): string
    {
        if ($n === null || $n === '') {
            return '-';
        }
        $s = number_format((float) $n, $dec, ',', '.');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }
}
