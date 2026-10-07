<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $title }} {{ $year }}</title></head>
<body>
@include('pdf.frame', ['meta' => 'Tahun '.$year.' · '.$opd])
<div class="eyebrow">Laporan · Tahun {{ $year }}</div>
<h1>{{ $title }}</h1>
<div class="muted">{{ $desc }} — {{ $opd }}</div>

<table class="cards"><tr>
    @foreach ($summary as [$l, $v])
        <td><div class="l">{{ $l }}</div><div class="v">{{ $v }}</div></td>
    @endforeach
</tr></table>

@php $statusCols = collect($head)->filter(fn ($h) => str_contains($h, 'Status'))->keys()->all(); @endphp
<table class="data">
    <thead><tr><th width="18">No</th>@foreach ($head as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($rows as $i => $row)
        <tr>
            <td class="c muted">{{ $i + 1 }}</td>
            @foreach ($row as $k => $cell)
                <td>
                    @if (in_array($k, $statusCols) && isset($tones[$cell]))
                        <span class="pill" style="background:#{{ $tones[$cell][0] }};color:#{{ $tones[$cell][1] }}">{{ $cell }}</span>
                    @else
                        {{ $cell }}
                    @endif
                </td>
            @endforeach
        </tr>
    @empty
        <tr><td colspan="{{ count($head) + 1 }}" class="c muted" style="padding:20px">Tidak ada data untuk filter ini</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
