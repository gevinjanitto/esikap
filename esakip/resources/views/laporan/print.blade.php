<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><title>{{ $title }} {{ $year }}</title>
    <style>
        @page{size:A4 landscape;margin:14mm}body{font-family:Arial,sans-serif;font-size:11px;color:#111;margin:24px}
        h1{font-size:16px;margin:0}.sub{color:#555;margin:4px 0 14px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #bbb;padding:5px 6px;text-align:left;vertical-align:top}
        th{background:#E11D2E;color:#fff;font-weight:600}tr:nth-child(even) td{background:#fafafa}.foot{margin-top:12px;color:#666;font-size:10px}
        .bar{position:fixed;top:12px;right:12px}.bar button{padding:8px 14px;background:#0B0F19;color:#fff;border:0;border-radius:99px;cursor:pointer}@media print{.bar{display:none}}
    </style>
</head>
<body>
<div class="bar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
<h1>{{ strtoupper($title) }}</h1>
<p class="sub">Tahun {{ $year }} · {{ $opd }} · e-SAKIP Pemda</p>
<table>
    <thead><tr><th>No</th>@foreach ($head as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($rows as $i => $r)
        <tr><td>{{ $i + 1 }}</td>@foreach ($r as $c)<td>{{ $c }}</td>@endforeach</tr>
    @empty
        <tr><td colspan="{{ count($head) + 1 }}" style="text-align:center">Tidak ada data</td></tr>
    @endforelse
    </tbody>
</table>
<p class="foot">Dicetak {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }} · Design &amp; Develop by MaiHarta</p>
<script>setTimeout(() => window.print(), 600)</script>
</body>
</html>
