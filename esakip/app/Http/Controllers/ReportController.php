<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Evaluasi;
use App\Models\Indicator;
use App\Models\Opd;
use App\Models\Realisasi;
use App\Models\RencanaAksi;
use App\Support\Esakip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public const TYPES = [
        'capaian' => ['Laporan Capaian Kinerja', 'Target, realisasi, capaian & deviasi per indikator', 'activity'],
        'rpjmd' => ['Laporan RPJMD', 'Misi, tujuan, sasaran, indikator & target daerah', 'landmark'],
        'indikator' => ['Laporan Cascading Indikator', 'Indikator OPD, program & kegiatan beserta target', 'network'],
        'renaksi' => ['Laporan Rencana Aksi', 'Aktivitas, PIC, jadwal & status pelaksanaan', 'list-checks'],
        'evaluasi' => ['Laporan Evaluasi & Tindak Lanjut', 'Nilai, predikat, rekomendasi & status tindak lanjut', 'clipboard-check'],
        'audit' => ['Laporan Audit Trail', 'Jejak aktivitas pengguna dalam sistem', 'shield-check'],
    ];

    public function index()
    {
        return view('laporan.index', ['types' => self::TYPES, 'years' => Esakip::years(), 'opds' => Opd::orderBy('name')->pluck('name', 'id')]);
    }

    private function build(string $type, Request $r): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $opd = $u->isOpdScoped() ? $u->opd_id : $r->get('opd');
        $n = fn ($v) => Esakip::num($v);

        [$head, $rows] = match ($type) {
            'capaian' => [
                ['OPD', 'Indikator', 'Level', 'Satuan', 'Periode', 'Target', 'Realisasi', 'Capaian (%)', 'Deviasi', 'Status Capaian', 'Status Data'],
                Realisasi::with(['opd', 'indicator.satuan'])->where('year', $year)->when($opd, fn ($q) => $q->where('opd_id', $opd))->orderBy('opd_id')->orderBy('periode')->get()
                    ->map(fn ($x) => [$x->opd?->name, $x->indicator?->name, $x->indicator?->level_label, $x->indicator?->satuan?->name, $x->periode, $n($x->target), $n($x->realisasi), $n($x->capaian), $n($x->deviasi), Esakip::label($x->status_capaian), Esakip::label($x->status)]),
            ],
            'rpjmd' => [
                ['Misi', 'Tujuan', 'Sasaran', 'Indikator', 'Satuan', 'Baseline', 'Target', 'OPD Terkait'],
                collect(Esakip::activeRpjmd()?->load('misis.tujuans.sasarans.indicators.targets', 'misis.tujuans.sasarans.indicators.satuan', 'misis.tujuans.sasarans.opds')->misis ?? [])
                    ->flatMap(fn ($m) => $m->tujuans->flatMap(fn ($t) => $t->sasarans->flatMap(fn ($s) => $s->indicators->map(fn ($i) => [
                        $m->code.' '.$m->name, $t->code.' '.$t->name, $s->code.' '.$s->name, $i->name, $i->satuan?->name, $n($i->baseline),
                        $i->targets->map(fn ($tg) => $tg->year.': '.$n($tg->value))->implode(' | '),
                        $s->opds->map(fn ($o) => $o->name.' ('.$o->pivot->peran.')')->implode(', '),
                    ])))),
            ],
            'indikator' => [
                ['OPD', 'Level', 'Kode', 'Indikator', 'Jenis', 'Satuan', 'Formula', 'Definisi Operasional', 'Target '.$year],
                Indicator::with(['opd', 'satuan', 'targets'])->whereNotNull('opd_id')->when($opd, fn ($q) => $q->where('opd_id', $opd))->orderBy('opd_id')->orderBy('owner_type')->get()
                    ->map(fn ($i) => [$i->opd?->name, $i->level_label, $i->code, $i->name, ucfirst($i->type), $i->satuan?->name, ucfirst($i->formula), $i->definition, $n($i->targetFor($year))]),
            ],
            'renaksi' => [
                ['OPD', 'Indikator', 'Aktivitas', 'PIC', 'Triwulan', 'Target', 'Status'],
                RencanaAksi::with(['opd', 'indicator'])->where('year', $year)->when($opd, fn ($q) => $q->where('opd_id', $opd))->orderBy('opd_id')->orderBy('triwulan')->get()
                    ->map(fn ($x) => [$x->opd?->name, $x->indicator?->name, $x->aktivitas, $x->pic, $x->triwulan, $x->target, Esakip::label($x->status)]),
            ],
            'evaluasi' => [
                ['OPD', 'Tahun', 'Nilai', 'Predikat', 'Rekomendasi', 'Batas Waktu', 'Tindak Lanjut', 'Status'],
                Evaluasi::with(['opd', 'rekomendasis'])->when($opd, fn ($q) => $q->where('opd_id', $opd))->get()
                    ->flatMap(fn ($e) => $e->rekomendasis->count() ? $e->rekomendasis->map(fn ($k) => [$e->opd?->name, $e->year, $n($e->nilai), $e->predikat, $k->uraian, $k->batas_waktu?->format('d/m/Y'), $k->tindak_lanjut, Esakip::label($k->status)])
                        : [[$e->opd?->name, $e->year, $n($e->nilai), $e->predikat, '-', '-', '-', '-']]),
            ],
            'audit' => [
                ['Waktu', 'Pengguna', 'Aksi', 'Objek', 'Keterangan', 'IP'],
                ($u->isOpdScoped() ? collect() : AuditLog::with('user')->whereYear('created_at', '>=', $year - 1)->latest('id')->limit(2000)->get())
                    ->map(fn ($a) => [$a->created_at->format('d/m/Y H:i'), $a->user?->name ?? 'Sistem', $a->action, $a->model_type.' #'.$a->model_id, $a->label, $a->ip]),
            ],
        };

        $rows = collect($rows)->values();
        $summary = [['Jumlah Baris Data', $rows->count()]];
        if ($type === 'capaian') {
            $q = Realisasi::where('year', $year)->when($opd, fn ($x) => $x->where('opd_id', $opd));
            $summary = [['Rata-rata Capaian', Esakip::num((clone $q)->avg('capaian'), 1).'%'], ['Tercapai', (clone $q)->where('status_capaian', 'tercapai')->count()], ['Perlu Perhatian', (clone $q)->where('status_capaian', 'perlu_perhatian')->count()], ['Tidak Tercapai', (clone $q)->where('status_capaian', 'tidak_tercapai')->count()]];
        }

        return ['type' => $type, 'title' => self::TYPES[$type][0], 'desc' => self::TYPES[$type][1], 'year' => $year, 'opd' => $opd ? Opd::find($opd)?->name : 'Seluruh OPD', 'head' => $head, 'rows' => $rows, 'summary' => $summary];
    }

    public const TONES = [
        'Tercapai' => ['DCFCE7', '166534'], 'Perlu Perhatian' => ['FEF3C7', '92400E'], 'Tidak Tercapai' => ['FEE2E2', 'B3121F'],
        'Ditetapkan' => ['E11D2E', 'FFFFFF'], 'Disetujui' => ['DCFCE7', '166534'], 'Diajukan' => ['E0F2FE', '075985'], 'Draft' => ['F1F5F9', '475569'],
        'Revisi' => ['FEF3C7', '92400E'], 'Ditolak' => ['FEE2E2', 'B3121F'], 'Selesai' => ['DCFCE7', '166534'], 'Berjalan' => ['E0F2FE', '075985'],
        'Terlambat' => ['FEE2E2', 'B3121F'], 'Belum Mulai' => ['F1F5F9', '475569'], 'Terverifikasi' => ['DCFCE7', '166534'], 'Dalam Proses' => ['FEF3C7', '92400E'],
        'Belum Ditindaklanjuti' => ['F1F5F9', '475569'],
    ];

    public function export(Request $r, string $type)
    {
        $d = $this->build($type, $r);
        AuditLog::record('export', null, null, ['laporan' => $type, 'tahun' => $d['year']]);
        $cols = count($d['head']) + 1;
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols);
        $book = new Spreadsheet;
        $book->getProperties()->setCreator('e-SAKIP Pemda')->setTitle($d['title']);
        $sh = $book->getActiveSheet()->setTitle(mb_substr(ucfirst($type), 0, 30));
        $book->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $sh->setCellValue('A1', 'e-SAKIP PEMDA')->mergeCells("A1:{$last}1");
        $sh->getStyle("A1:{$last}1")->applyFromArray(['font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B0F19']]]);
        $sh->setCellValue('A2', strtoupper($d['title']))->mergeCells("A2:{$last}2");
        $sh->getStyle('A2')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('E11D2E');
        $sh->setCellValue('A3', 'Tahun '.$d['year'].'  •  '.$d['opd'].'  •  Dicetak '.now()->translatedFormat('d F Y H:i').' oleh '.auth()->user()->name)->mergeCells("A3:{$last}3");
        $sh->getStyle('A3')->getFont()->setItalic(true)->getColor()->setRGB('64748B');

        $c = 1;
        foreach ($d['summary'] as [$lbl, $val]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sh->setCellValue($col.'5', $lbl)->setCellValue($col.'6', $val);
            $sh->getStyle($col.'5')->getFont()->setSize(8)->getColor()->setRGB('64748B');
            $sh->getStyle($col.'6')->getFont()->setBold(true)->setSize(14);
            $sh->getStyle($col.'5:'.$col.'6')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF1F2']], 'borders' => ['left' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => 'E11D2E']]]]);
            $c += 1;
        }

        $h = 8;
        $sh->fromArray(array_merge(['No'], $d['head']), null, "A{$h}");
        $sh->getStyle("A{$h}:{$last}{$h}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E11D2E']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sh->getRowDimension($h)->setRowHeight(24);
        $statusCols = collect($d['head'])->keys()->filter(fn ($k) => str_contains($d['head'][$k], 'Status'))->map(fn ($k) => $k + 1)->all();
        foreach ($d['rows'] as $i => $row) {
            $n = $h + 1 + $i;
            $sh->fromArray(array_merge([$i + 1], array_map(fn ($v) => (string) $v, $row)), null, "A{$n}");
            if ($i % 2) {
                $sh->getStyle("A{$n}:{$last}{$n}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
            foreach ($statusCols as $sc) {
                $val = $row[$sc - 1] ?? '';
                if (isset(self::TONES[$val])) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sc + 1).$n;
                    $sh->getStyle($cell)->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => self::TONES[$val][1]]], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::TONES[$val][0]]], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                }
            }
        }
        $end = $h + max(count($d['rows']), 1);
        $sh->getStyle("A{$h}:{$last}{$end}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]], 'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]]);
        $sh->getColumnDimension('A')->setWidth(5);
        foreach ($d['head'] as $k => $hd) {
            $len = max(mb_strlen($hd), ...array_map(fn ($rw) => mb_strlen((string) ($rw[$k] ?? '')), $d['rows']->all() ?: [[]]));
            $sh->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($k + 2))->setWidth(min(max($len + 2, 10), 55));
        }
        $sh->freezePane('A'.($h + 1));
        $sh->setAutoFilter("A{$h}:{$last}{$end}");
        $sh->setCellValue('A'.($end + 2), 'Design & Develop by MaiHarta — www.maiharta.com');
        $sh->getStyle('A'.($end + 2))->getFont()->setSize(8)->getColor()->setRGB('94A3B8');
        $sh->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);

        return response()->streamDownload(fn () => (new Xlsx($book))->save('php://output'), 'laporan-'.$type.'-'.$d['year'].'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function print(Request $r, string $type)
    {
        $d = $this->build($type, $r);
        AuditLog::record('export_pdf', null, null, ['laporan' => $type, 'tahun' => $d['year']]);

        return Pdf::loadView('laporan.pdf', $d + ['tones' => self::TONES])->setPaper('a4', 'landscape')->stream('laporan-'.$type.'-'.$d['year'].'.pdf');
    }
}
