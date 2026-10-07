<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $cfg['title'] }} {{ $doc->opd->name }} {{ $doc->year }}</title>
<style>
    .doc-title { text-align: center; margin: 6px 0 2px; font-size: 17px; letter-spacing: 1px; }
    .doc-sub { text-align: center; font-size: 11px; color: #64748B; margin-bottom: 16px; }
    .party { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 10px -8px 12px; }
    .party td { width: 50%; background: #F8FAFC; border-radius: 8px; padding: 10px 12px; vertical-align: top; }
    .party .tag { font-size: 8px; font-weight: bold; color: #E11D2E; letter-spacing: 1.2px; }
    .party .nm { font-size: 12px; font-weight: bold; margin-top: 3px; }
    p.txt { font-size: 10.5px; line-height: 1.55; text-align: justify; }
    .sign { width: 100%; margin-top: 34px; } .sign td { width: 50%; text-align: center; font-size: 10.5px; vertical-align: top; }
    .sign .space { height: 60px; } .sign .nm { font-weight: bold; text-decoration: underline; }
    .total td { background: #FFF1F2 !important; font-weight: bold; }
</style>
</head>
<body>
@include('pdf.frame', ['meta' => $cfg['label'].' · '.$doc->year])
<div class="eyebrow" style="text-align:center">Status: {{ \App\Support\Esakip::label($doc->status) }}</div>
<h1 class="doc-title">{{ strtoupper($cfg['title']) }} TAHUN {{ $doc->year }}</h1>
<div class="doc-sub">{{ strtoupper($doc->opd->name) }}</div>

@if ($kind === 'pk')
    <p class="txt">Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta berorientasi pada hasil, kami yang bertanda tangan di bawah ini:</p>
    <table class="party"><tr>
        <td><div class="tag">PIHAK PERTAMA</div><div class="nm">{{ $doc->pihak_pertama }}</div><div class="muted">{{ $doc->jabatan_pertama }}</div></td>
        <td><div class="tag">PIHAK KEDUA</div><div class="nm">{{ $doc->pihak_kedua }}</div><div class="muted">{{ $doc->jabatan_kedua }} — selaku atasan Pihak Pertama</div></td>
    </tr></table>
    <p class="txt">Pihak Pertama berjanji akan mewujudkan target kinerja yang seharusnya sesuai lampiran perjanjian ini dalam rangka mencapai target kinerja jangka menengah sebagaimana ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja tersebut menjadi tanggung jawab Pihak Pertama.</p>
@elseif ($doc->catatan)
    <p class="txt">{{ $doc->catatan }}</p>
@endif

<table class="data">
    <thead><tr><th width="18">No</th><th>Indikator Kinerja</th><th>Level</th><th class="r">Target</th><th>Satuan</th><th class="r">Anggaran (Rp)</th></tr></thead>
    <tbody>
    @foreach ($doc->items as $i => $it)
        <tr><td class="c muted">{{ $i + 1 }}</td><td>{{ $it->indicator->name }}</td><td>{{ $it->indicator->level_label }}</td><td class="r"><b>{{ \App\Support\Esakip::num($it->target) }}</b></td><td>{{ $it->indicator->satuan?->name }}</td><td class="r">{{ \App\Support\Esakip::num($it->pagu, 0) }}</td></tr>
    @endforeach
    <tr class="total"><td colspan="5" class="r">JUMLAH ANGGARAN</td><td class="r">{{ \App\Support\Esakip::num($doc->items->sum('pagu'), 0) }}</td></tr>
    </tbody>
</table>

@if ($kind === 'pk')
    <table class="sign"><tr>
        <td>&nbsp;<br>PIHAK KEDUA<br>{{ $doc->jabatan_kedua }}<div class="space"></div><span class="nm">{{ $doc->pihak_kedua }}</span></td>
        <td>{{ now()->translatedFormat('d F Y') }}<br>PIHAK PERTAMA<br>{{ $doc->jabatan_pertama }}<div class="space"></div><span class="nm">{{ $doc->pihak_pertama }}</span></td>
    </tr></table>
@endif
</body>
</html>
