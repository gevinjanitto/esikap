<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\PerjanjianKinerja;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\Rekomendasi;
use App\Models\RencanaAksi;
use App\Models\Renja;
use App\Models\Renstra;
use App\Models\Rpjmd;
use App\Models\SasaranDaerah;
use App\Models\SasaranOpd;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'sasaran_daerah' => SasaranDaerah::class,
            'sasaran_opd' => SasaranOpd::class,
            'program' => Program::class,
            'kegiatan' => Kegiatan::class,
            'rpjmd' => Rpjmd::class,
            'renstra' => Renstra::class,
            'renja' => Renja::class,
            'pk' => PerjanjianKinerja::class,
            'realisasi' => Realisasi::class,
            'rencana_aksi' => RencanaAksi::class,
            'rekomendasi' => Rekomendasi::class,
        ]);

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $u = auth()->user();
            $view->with('unreadNotifs', $u ? $u->notifs()->whereNull('read_at')->limit(6)->get() : collect());
            $view->with('unreadCount', $u ? $u->notifs()->whereNull('read_at')->count() : 0);
        });
    }
}
