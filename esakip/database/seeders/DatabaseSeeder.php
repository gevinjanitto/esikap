<?php

namespace Database\Seeders;

use App\Models\ApprovalLog;
use App\Models\Document;
use App\Models\Evaluasi;
use App\Models\Indicator;
use App\Models\Notif;
use App\Models\Opd;
use App\Models\Period;
use App\Models\PerjanjianKinerja;
use App\Models\Realisasi;
use App\Models\RencanaAksi;
use App\Models\Renja;
use App\Models\Renstra;
use App\Models\Rpjmd;
use App\Models\Satuan;
use App\Models\Unit;
use App\Models\User;
use App\Support\Esakip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    private array $sat = [];

    private array $u = [];

    private array $years = [2025, 2026, 2027, 2028, 2029];

    public function run(): void
    {
        if (User::exists()) {
            return;
        }

        $period = Period::create(['name' => 'RPJMD 2025-2029', 'start_year' => 2025, 'end_year' => 2029, 'is_active' => true]);

        foreach ([['PSN', 'Persen (%)'], ['ORG', 'Orang'], ['DOK', 'Dokumen'], ['IDX', 'Indeks'], ['UNT', 'Unit'], ['FAS', 'Fasilitas'], ['THN', 'Tahun'], ['KM', 'Kilometer'], ['NIL', 'Nilai'], ['KH', 'Per 100.000 KH'], ['DSA', 'Desa']] as [$c, $n]) {
            $this->sat[$c] = Satuan::create(['code' => $c, 'name' => $n])->id;
        }

        $opd = [];
        foreach ([
            ['dinkes', '1.02.0.00', 'Dinas Kesehatan', 'DINKES', 'Dinas'],
            ['disdik', '1.01.0.00', 'Dinas Pendidikan dan Kebudayaan', 'DISDIKBUD', 'Dinas'],
            ['pupr', '1.03.0.00', 'Dinas Pekerjaan Umum dan Penataan Ruang', 'DPUPR', 'Dinas'],
            ['dinsos', '1.06.0.00', 'Dinas Sosial', 'DINSOS', 'Dinas'],
            ['kominfo', '2.16.0.00', 'Dinas Komunikasi dan Informatika', 'DISKOMINFO', 'Dinas'],
            ['distan', '3.27.0.00', 'Dinas Pertanian dan Pangan', 'DISTAN', 'Dinas'],
            ['bapperida', '5.01.0.00', 'Badan Perencanaan, Riset dan Inovasi Daerah', 'BAPPERIDA', 'Badan'],
            ['inspektorat', '6.01.0.00', 'Inspektorat Daerah', 'INSPEKTORAT', 'Inspektorat'],
            ['setda', '4.01.0.00', 'Sekretariat Daerah', 'SETDA', 'Sekretariat'],
        ] as [$k, $c, $n, $s, $t]) {
            $opd[$k] = Opd::create(['code' => $c, 'name' => $n, 'singkatan' => $s, 'type' => $t, 'status' => 'aktif']);
        }
        foreach ([['dinkes', 'Bidang Pelayanan Kesehatan'], ['dinkes', 'Bidang Kesehatan Masyarakat'], ['dinkes', 'Bidang Pencegahan dan Pengendalian Penyakit'], ['dinkes', 'UPT Puskesmas Kota'], ['disdik', 'Bidang Pembinaan SD'], ['disdik', 'Bidang Pembinaan SMP'], ['pupr', 'Bidang Bina Marga'], ['setda', 'Bagian Organisasi']] as $i => [$k, $n]) {
            Unit::create(['opd_id' => $opd[$k]->id, 'code' => 'U'.str_pad($i + 1, 2, '0', STR_PAD_LEFT), 'name' => $n]);
        }

        foreach ([
            ['superadmin', 'Super Administrator', 'super_admin', null, 'Administrator Sistem'],
            ['admin', 'Ni Luh Putu Ayu Lestari', 'admin_pemda', 'setda', 'Pengelola Data Pemda'],
            ['bappeda', 'I Gede Made Suartana, S.T., M.T.', 'bappeda', 'bapperida', 'Kabid Perencanaan Makro'],
            ['sakip', 'Ni Made Sri Wahyuni, S.Sos', 'tim_sakip', 'setda', 'Kabag Organisasi'],
            ['operator.dinkes', 'I Komang Adi Pranata, S.Kom', 'operator_opd', 'dinkes', 'Analis Perencanaan'],
            ['kepala.dinkes', 'dr. I Made Sudarsana, M.Kes', 'kepala_opd', 'dinkes', 'Kepala Dinas Kesehatan'],
            ['operator.disdik', 'Ni Kadek Dwi Antari, S.Pd', 'operator_opd', 'disdik', 'Analis Perencanaan'],
            ['kepala.disdik', 'Drs. I Nyoman Sutama, M.Pd', 'kepala_opd', 'disdik', 'Kepala Dinas Pendidikan dan Kebudayaan'],
            ['operator.pupr', 'I Putu Eka Wiradana, S.T.', 'operator_opd', 'pupr', 'Perencana Ahli Pertama'],
            ['kepala.pupr', 'Ir. I Wayan Mertayasa, M.T.', 'kepala_opd', 'pupr', 'Kepala Dinas PUPR'],
            ['inspektorat', 'Ni Wayan Sukmawati, S.E., Ak.', 'evaluator', 'inspektorat', 'Auditor Ahli Madya'],
            ['bupati', 'I Ketut Suwardana, S.H., M.H.', 'pimpinan', null, 'Bupati'],
        ] as [$un, $name, $role, $o, $jab]) {
            $this->u[$un] = User::create([
                'name' => $name, 'email' => $un.'@esakip.go.id', 'username' => $un, 'role' => $role,
                'opd_id' => $o ? $opd[$o]->id : null, 'jabatan' => $jab, 'password' => 'password123', 'is_active' => true,
            ]);
        }

        $rpjmd = Rpjmd::create([
            'period_id' => $period->id, 'name' => 'RPJMD Kabupaten 2025-2029', 'status' => 'ditetapkan', 'created_by' => $this->u['bappeda']->id,
            'visi' => 'Terwujudnya Daerah yang Maju, Sejahtera dan Berdaya Saing Berlandaskan Tata Kelola Pemerintahan yang Baik',
            'nomor_dokumen' => 'Perda No. 3 Tahun 2025', 'tanggal_dokumen' => '2025-06-16',
        ]);
        $this->logs('rpjmd', $rpjmd, 'bappeda', 'bupati', 'bappeda');

        $sd = [];
        $tree = [
            ['M1', 'Meningkatkan kualitas sumber daya manusia yang sehat, cerdas dan berkarakter', [
                ['T1.1', 'Meningkatkan derajat kesehatan masyarakat', [
                    ['kes', 'S1.1.1', 'Meningkatnya kualitas pelayanan kesehatan', [['Indeks kualitas pelayanan kesehatan', 'IDX', 82, [85, 87, 88, 89, 90]], ['Usia Harapan Hidup', 'THN', 71.2, [71.5, 71.7, 71.9, 72.1, 72.3]]], [['dinkes', 'utama'], ['dinsos', 'pendukung']]],
                ]],
                ['T1.2', 'Meningkatkan kualitas pendidikan', [
                    ['dik', 'S1.2.1', 'Meningkatnya akses dan mutu pendidikan', [['Rata-rata lama sekolah', 'THN', 8.2, [8.4, 8.5, 8.7, 8.8, 9.0]], ['Harapan lama sekolah', 'THN', 12.9, [13.0, 13.1, 13.2, 13.3, 13.4]]], [['disdik', 'utama']]],
                ]],
            ]],
            ['M2', 'Mewujudkan infrastruktur wilayah yang berkualitas dan berkelanjutan', [
                ['T2.1', 'Meningkatkan kualitas infrastruktur dasar wilayah', [
                    ['jln', 'S2.1.1', 'Meningkatnya kemantapan jalan kabupaten', [['Persentase jalan kabupaten dalam kondisi mantap', 'PSN', 72, [75, 78, 80, 83, 85]]], [['pupr', 'utama']]],
                ]],
            ]],
            ['M3', 'Mewujudkan tata kelola pemerintahan yang bersih, efektif dan melayani', [
                ['T3.1', 'Meningkatkan akuntabilitas dan kualitas pelayanan publik', [
                    ['akip', 'S3.1.1', 'Meningkatnya akuntabilitas kinerja instansi pemerintah', [['Nilai SAKIP Pemerintah Daerah', 'NIL', 68, [70, 72, 74, 76, 78]], ['Indeks SPBE', 'IDX', 2.6, [2.8, 3.0, 3.2, 3.4, 3.5]]], [['setda', 'utama'], ['inspektorat', 'pendukung'], ['kominfo', 'pendukung']]],
                ]],
            ]],
            ['M4', 'Meningkatkan perekonomian daerah berbasis potensi lokal', [
                ['T4.1', 'Meningkatkan ketahanan pangan daerah', [
                    ['tani', 'S4.1.1', 'Meningkatnya produksi dan produktivitas pertanian', [['Pertumbuhan PDRB sektor pertanian', 'PSN', 3.1, [3.3, 3.5, 3.7, 3.9, 4.0]]], [['distan', 'utama'], ['bapperida', 'pendukung']]],
                ]],
            ]],
        ];
        foreach ($tree as [$mc, $mn, $tujuans]) {
            $misi = $rpjmd->misis()->create(['code' => $mc, 'name' => $mn]);
            foreach ($tujuans as [$tc, $tn, $sasarans]) {
                $tujuan = $misi->tujuans()->create(['code' => $tc, 'name' => $tn]);
                foreach ($sasarans as [$key, $sc, $sn, $inds, $assign]) {
                    $s = $tujuan->sasarans()->create(['code' => $sc, 'name' => $sn]);
                    foreach ($inds as $k => [$in, $isat, $base, $tg]) {
                        $this->ind($s, 'sasaran_daerah', null, "IKU-$sc.".($k + 1), $in, $isat, $tg, 'positif', $base, 'impact');
                    }
                    foreach ($assign as [$ok, $peran]) {
                        $s->opds()->attach($opd[$ok]->id, ['peran' => $peran]);
                    }
                    $sd[$key] = $s;
                }
            }
        }

        $renDinkes = $this->renstra($opd['dinkes'], $period, 'operator.dinkes', 'ditetapkan', [
            ['TO-1', 'Meningkatnya akses dan mutu pelayanan kesehatan', $sd['kes'], [
                ['SO-1.1', 'Meningkatnya kualitas pelayanan kesehatan dasar dan rujukan', [
                    ['Persentase fasilitas kesehatan memenuhi standar', 'PSN', [88, 90, 92, 94, 95], 'positif'],
                    ['Angka kematian ibu', 'KH', [98, 95, 92, 90, 88], 'negatif'],
                ], [
                    ['1.02.02', 'Program Pemenuhan Upaya Kesehatan Perorangan dan Upaya Kesehatan Masyarakat', [['Persentase pelayanan kesehatan memenuhi standar', 'PSN', [90, 92, 93, 94, 95]]], [
                        ['1.02.02.2.01', 'Pembinaan fasilitas kesehatan', [['Jumlah fasilitas kesehatan yang dibina', 'FAS', [40, 50, 55, 60, 65]]]],
                        ['1.02.02.2.02', 'Penyediaan layanan kesehatan untuk UKM dan UKP', [['Jumlah puskesmas terakreditasi paripurna', 'UNT', [20, 22, 24, 25, 26]]]],
                    ]],
                    ['1.02.03', 'Program Peningkatan Kapasitas Sumber Daya Manusia Kesehatan', [['Persentase tenaga kesehatan bersertifikat kompetensi', 'PSN', [70, 75, 80, 85, 90]]], [
                        ['1.02.03.2.01', 'Pengembangan mutu dan peningkatan kompetensi teknis SDM kesehatan', [['Jumlah tenaga kesehatan yang dilatih', 'ORG', [250, 300, 320, 340, 360]]]],
                    ]],
                ]],
            ]],
            ['TO-2', 'Meningkatnya derajat kesehatan masyarakat', $sd['kes'], [
                ['SO-2.1', 'Menurunnya prevalensi stunting', [['Prevalensi stunting balita', 'PSN', [18, 16, 14, 12, 10], 'negatif']], [
                    ['1.02.04', 'Program Pemberdayaan Masyarakat Bidang Kesehatan', [['Persentase desa siaga aktif', 'PSN', [70, 75, 80, 85, 90]]], [
                        ['1.02.04.2.01', 'Advokasi, pemberdayaan, kemitraan dan peningkatan peran serta masyarakat', [['Jumlah posyandu aktif', 'UNT', [380, 400, 420, 440, 460]]]],
                    ]],
                ]],
            ]],
        ]);

        $renDisdik = $this->renstra($opd['disdik'], $period, 'operator.disdik', 'disetujui', [
            ['TO-1', 'Meningkatnya mutu dan pemerataan pendidikan dasar', $sd['dik'], [
                ['SO-1.1', 'Meningkatnya angka partisipasi dan mutu lulusan pendidikan dasar', [
                    ['Angka Partisipasi Murni SD/MI', 'PSN', [97, 97.5, 98, 98.5, 99], 'positif'],
                    ['Persentase sekolah terakreditasi minimal B', 'PSN', [80, 84, 88, 92, 95], 'positif'],
                ], [
                    ['1.01.02', 'Program Pengelolaan Pendidikan', [['Persentase satuan pendidikan memenuhi SPM', 'PSN', [75, 80, 85, 88, 90]]], [
                        ['1.01.02.2.01', 'Pengelolaan Pendidikan Sekolah Dasar', [['Jumlah ruang kelas SD yang direhabilitasi', 'UNT', [20, 25, 30, 30, 35]]]],
                        ['1.01.02.2.02', 'Pengelolaan Pendidikan Sekolah Menengah Pertama', [['Jumlah sekolah SMP penerima bantuan sarana', 'UNT', [10, 12, 14, 16, 18]]]],
                    ]],
                ]],
            ]],
        ]);

        $renPupr = $this->renstra($opd['pupr'], $period, 'operator.pupr', 'diajukan', [
            ['TO-1', 'Meningkatnya kualitas jaringan jalan kabupaten', $sd['jln'], [
                ['SO-1.1', 'Meningkatnya kondisi jalan mantap', [['Persentase panjang jalan dalam kondisi baik', 'PSN', [60, 63, 66, 70, 73], 'positif']], [
                    ['1.03.10', 'Program Penyelenggaraan Jalan', [['Panjang jalan yang ditingkatkan kualitasnya', 'KM', [20, 25, 28, 30, 32]]], [
                        ['1.03.10.2.01', 'Rekonstruksi dan rehabilitasi jalan', [['Panjang jalan yang direkonstruksi', 'KM', [10, 12, 14, 15, 16]]]],
                    ]],
                ]],
            ]],
        ]);

        $mult = [1.02, 0.97, 0.84, 1.05, 0.71, 0.93, 1.0, 0.79, 0.99, 1.04, 0.66, 0.95, 0.88, 1.01, 0.82, 0.98, 0.74];
        $i = 0;
        foreach ([[$opd['dinkes'], 'operator.dinkes'], [$opd['disdik'], 'operator.disdik'], [$opd['pupr'], 'operator.pupr']] as [$o, $op]) {
            foreach (Indicator::with('targets')->where('opd_id', $o->id)->get() as $ind) {
                foreach ([2025 => ['TW1', 'TW2', 'TW3', 'TW4'], 2026 => ['TW1', 'TW2']] as $year => $tws) {
                    foreach ($tws as $k => $tw) {
                        $annual = (float) $ind->targetFor($year);
                        $isCount = in_array($ind->satuan_id, [$this->sat['FAS'], $this->sat['UNT'], $this->sat['ORG'], $this->sat['KM']]);
                        $target = $isCount ? round($annual * ($k + 1) / 4) : $annual;
                        $m = $mult[$i++ % count($mult)];
                        $real = $ind->formula === 'negatif' ? round($target * (2 - $m), 2) : round($target * $m, 2);
                        $cap = Esakip::capaian($ind->formula, $target, $real);
                        $status = $year === 2025 ? 'ditetapkan' : ($tw === 'TW1' ? 'ditetapkan' : ($i % 4 === 0 ? 'draft' : 'diajukan'));
                        $re = Realisasi::create([
                            'opd_id' => $o->id, 'indicator_id' => $ind->id, 'year' => $year, 'periode' => $tw, 'target' => $target, 'realisasi' => $real,
                            'capaian' => $cap, 'deviasi' => $real - $target, 'status_capaian' => Esakip::statusCapaian($cap), 'status' => $status,
                            'created_by' => $this->u[$op]->id,
                            'analisis' => $cap >= 90 ? 'Capaian sesuai rencana didukung koordinasi lintas bidang dan ketersediaan anggaran.' : 'Capaian belum optimal karena keterlambatan pengadaan dan keterbatasan SDM; telah disusun langkah percepatan.',
                        ]);
                        if ($status === 'ditetapkan') {
                            $this->logs('realisasi', $re, $op, str_replace('operator', 'kepala', $op), 'sakip');
                        } elseif ($status === 'diajukan') {
                            $this->log('realisasi', $re, $op, 'submit', 'draft', 'diajukan');
                        }
                        if ($i % 4 === 0) {
                            $re->evidences()->create(['link' => 'https://data.go.id', 'original_name' => 'Laporan Data Dukung '.$tw.' '.$year, 'uploaded_by' => $this->u[$op]->id]);
                        }
                    }
                }
            }
        }

        $this->annual(Renja::class, $opd['dinkes'], 'operator.dinkes', 'ditetapkan', ['catatan' => 'Renja disusun selaras dengan Renstra 2025-2029 dan RKPD 2026.']);
        $this->annual(Renja::class, $opd['disdik'], 'operator.disdik', 'diajukan', ['catatan' => 'Mohon review Kepala Dinas.']);
        $this->annual(PerjanjianKinerja::class, $opd['dinkes'], 'operator.dinkes', 'ditetapkan', ['pihak_pertama' => 'dr. I Made Sudarsana, M.Kes', 'jabatan_pertama' => 'Kepala Dinas Kesehatan', 'pihak_kedua' => 'I Ketut Suwardana, S.H., M.H.', 'jabatan_kedua' => 'Bupati']);
        $this->annual(PerjanjianKinerja::class, $opd['pupr'], 'operator.pupr', 'draft', ['pihak_pertama' => 'Ir. I Wayan Mertayasa, M.T.', 'jabatan_pertama' => 'Kepala Dinas PUPR', 'pihak_kedua' => 'I Ketut Suwardana, S.H., M.H.', 'jabatan_kedua' => 'Bupati']);

        $dinkesInd = Indicator::where('opd_id', $opd['dinkes']->id)->get();
        foreach ([
            ['Pembinaan fasilitas kesehatan tingkat pertama wilayah utara', 'Kabid Pelayanan Kesehatan', 'TW1', '12 fasilitas', 'selesai'],
            ['Pembinaan fasilitas kesehatan tingkat pertama wilayah selatan', 'Kabid Pelayanan Kesehatan', 'TW2', '13 fasilitas', 'berjalan'],
            ['Supervisi akreditasi puskesmas', 'Kasi Mutu Pelayanan', 'TW2', '6 puskesmas', 'terlambat'],
            ['Pelatihan tenaga kesehatan kegawatdaruratan', 'Kasi SDM Kesehatan', 'TW2', '80 orang', 'berjalan'],
            ['Penimbangan serentak dan intervensi gizi balita', 'Kabid Kesehatan Masyarakat', 'TW3', '400 posyandu', 'belum_mulai'],
            ['Pembinaan fasilitas kesehatan rujukan', 'Kabid Pelayanan Kesehatan', 'TW3', '15 fasilitas', 'belum_mulai'],
            ['Evaluasi standar pelayanan minimal puskesmas', 'Kasi Mutu Pelayanan', 'TW1', '26 puskesmas', 'selesai'],
            ['Kampanye desa siaga aktif', 'Kasi Promkes', 'TW2', '20 desa', 'terlambat'],
        ] as $k => [$akt, $pic, $tw, $tg, $st]) {
            RencanaAksi::create(['opd_id' => $opd['dinkes']->id, 'indicator_id' => $dinkesInd[$k % $dinkesInd->count()]->id, 'year' => 2026, 'aktivitas' => $akt, 'pic' => $pic, 'triwulan' => $tw, 'target' => $tg, 'status' => $st, 'created_by' => $this->u['operator.dinkes']->id]);
        }
        $disdikInd = Indicator::where('opd_id', $opd['disdik']->id)->get();
        foreach ([['Rehabilitasi ruang kelas SDN gugus 1', 'Kabid Pembinaan SD', 'TW2', '10 ruang', 'berjalan'], ['Pendampingan akreditasi sekolah', 'Kabid Pembinaan SMP', 'TW3', '15 sekolah', 'belum_mulai']] as $k => [$akt, $pic, $tw, $tg, $st]) {
            RencanaAksi::create(['opd_id' => $opd['disdik']->id, 'indicator_id' => $disdikInd[$k]->id, 'year' => 2026, 'aktivitas' => $akt, 'pic' => $pic, 'triwulan' => $tw, 'target' => $tg, 'status' => $st, 'created_by' => $this->u['operator.disdik']->id]);
        }

        foreach ([
            ['dinkes', 78.5, 'Implementasi SAKIP sudah baik, cascading kinerja telah sampai level kegiatan. Perlu penguatan pemanfaatan hasil monitoring untuk perbaikan program.', [
                ['Lengkapi bukti dukung realisasi triwulanan untuk seluruh indikator kegiatan.', 'terverifikasi', 'Bukti dukung telah diunggah pada modul realisasi.'],
                ['Sempurnakan definisi operasional indikator Persentase desa siaga aktif.', 'proses', 'Sedang dibahas bersama Bidang Kesmas.'],
                ['Manfaatkan hasil evaluasi triwulan sebagai dasar realokasi anggaran.', 'belum', null],
            ]],
            ['disdik', 72.1, 'Indikator sasaran telah selaras dengan RPJMD. Rencana aksi belum mencakup seluruh indikator kinerja utama.', [
                ['Susun rencana aksi untuk seluruh IKU dan tetapkan PIC per aktivitas.', 'selesai', 'Rencana aksi 2026 telah disusun dan diunggah.'],
                ['Tingkatkan kualitas analisis capaian pada laporan kinerja.', 'belum', null],
            ]],
            ['pupr', 64.3, 'Keterkaitan program dengan sasaran belum sepenuhnya logis. Target indikator perlu dikaji ulang.', [
                ['Reviu ulang pohon kinerja hingga level kegiatan.', 'belum', null],
            ]],
        ] as [$k, $nilai, $cat, $reks]) {
            $ev = Evaluasi::create(['opd_id' => $opd[$k]->id, 'year' => 2025, 'evaluator_id' => $this->u['inspektorat']->id, 'nilai' => $nilai, 'predikat' => Esakip::predikat($nilai), 'catatan' => $cat, 'status' => 'final']);
            foreach ($reks as [$ur, $st, $tl]) {
                $ev->rekomendasis()->create(['uraian' => $ur, 'status' => $st, 'tindak_lanjut' => $tl, 'batas_waktu' => '2026-09-30']);
            }
        }

        foreach ([['RPJMD Kabupaten 2025-2029', 'RPJMD', null, 'Perda No. 3 Tahun 2025'], ['Renstra Dinas Kesehatan 2025-2029', 'RENSTRA', 'dinkes', '050/12/DINKES/2025'], ['Perjanjian Kinerja Dinas Kesehatan 2026', 'PK', 'dinkes', '800/05/DINKES/2026'], ['LKjIP Pemerintah Daerah 2025', 'LKJIP', null, '050/88/SETDA/2026']] as [$t, $j, $o, $no]) {
            $path = 'documents/'.\Illuminate\Support\Str::slug($t).'.pdf';
            Storage::disk('local')->put($path, self::pdf($t));
            Document::create(['title' => $t, 'jenis' => $j, 'nomor' => $no, 'tanggal' => '2026-01-15', 'version' => 1, 'opd_id' => $o ? $opd[$o]->id : null, 'period_id' => $period->id, 'file_path' => $path, 'original_name' => basename($path), 'size' => Storage::disk('local')->size($path), 'uploaded_by' => $this->u['admin']->id]);
        }

        foreach ([['kepala.pupr', 'Renstra OPD Diajukan', 'Renstra Dinas PUPR menunggu review Anda', route('renstra.show', $renPupr, false)], ['sakip', 'Renstra OPD Disetujui', 'Renstra Dinas Pendidikan siap ditetapkan', route('renstra.show', $renDisdik, false)], ['kepala.dinkes', 'Realisasi Diajukan', 'Realisasi TW2 2026 menunggu review', route('realisasi.index', ['tahun' => 2026, 'periode' => 'TW2'], false)]] as [$un, $t, $m, $l]) {
            Notif::create(['user_id' => $this->u[$un]->id, 'title' => $t, 'message' => $m, 'link' => $l]);
        }
    }

    private function ind($owner, string $type, $opdId, ?string $code, string $name, string $sat, array $targets, string $formula = 'positif', $baseline = null, string $itype = 'outcome'): Indicator
    {
        $i = Indicator::create([
            'owner_type' => $type, 'owner_id' => $owner->id, 'opd_id' => $opdId, 'code' => $code, 'name' => $name, 'type' => $itype,
            'satuan_id' => $this->sat[$sat], 'formula' => $formula, 'baseline' => $baseline, 'data_source' => 'Laporan OPD / BPS',
            'definition' => 'Definisi operasional: '.$name.' diukur berdasarkan data administrasi resmi pada akhir periode pelaporan.',
        ]);
        foreach ($targets as $k => $v) {
            $i->targets()->create(['year' => $this->years[$k], 'value' => $v]);
        }

        return $i;
    }

    private function renstra(Opd $opd, Period $period, string $op, string $status, array $tujuans): Renstra
    {
        $r = Renstra::create(['opd_id' => $opd->id, 'period_id' => $period->id, 'status' => $status, 'created_by' => $this->u[$op]->id, 'nomor_dokumen' => '050/'.$opd->singkatan.'/2025']);
        foreach ($tujuans as [$tc, $tn, $sdaerah, $sasarans]) {
            $t = $r->tujuans()->create(['code' => $tc, 'name' => $tn, 'sasaran_daerah_id' => $sdaerah->id]);
            foreach ($sasarans as $si => [$sc, $sn, $inds, $programs]) {
                $s = $t->sasarans()->create(['code' => $sc, 'name' => $sn]);
                foreach ($inds as $k => [$in, $isat, $tg, $f]) {
                    $this->ind($s, 'sasaran_opd', $opd->id, "IKS-$sc.".($k + 1), $in, $isat, $tg, $f);
                }
                foreach ($programs as [$pc, $pn, $pinds, $kegs]) {
                    $p = $s->programs()->create(['code' => $pc, 'name' => $pn, 'opd_id' => $opd->id]);
                    foreach ($pinds as [$in, $isat, $tg]) {
                        $this->ind($p, 'program', $opd->id, null, $in, $isat, $tg);
                    }
                    foreach ($kegs as [$kc, $kn, $kinds]) {
                        $kg = $p->kegiatans()->create(['code' => $kc, 'name' => $kn]);
                        foreach ($kinds as [$in, $isat, $tg]) {
                            $this->ind($kg, 'kegiatan', $opd->id, null, $in, $isat, $tg, 'positif', null, 'output');
                        }
                    }
                }
            }
        }
        $kepala = str_replace('operator', 'kepala', $op);
        match ($status) {
            'ditetapkan' => $this->logs('renstra', $r, $op, $kepala, 'sakip'),
            'disetujui' => [$this->log('renstra', $r, $op, 'submit', 'draft', 'diajukan'), $this->log('renstra', $r, $kepala, 'approve', 'diajukan', 'disetujui')],
            'diajukan' => $this->log('renstra', $r, $op, 'submit', 'draft', 'diajukan'),
            default => null,
        };

        return $r;
    }

    private function annual(string $class, Opd $opd, string $op, string $status, array $extra): void
    {
        $doc = $class::create(['opd_id' => $opd->id, 'year' => 2026, 'status' => $status, 'created_by' => $this->u[$op]->id] + $extra);
        foreach (Indicator::with('targets')->where('opd_id', $opd->id)->whereIn('owner_type', ['sasaran_opd', 'program'])->get() as $k => $ind) {
            $doc->items()->create(['indicator_id' => $ind->id, 'target' => $ind->targetFor(2026) ?? 0, 'pagu' => [1250000000, 875000000, 2340000000, 560000000][$k % 4]]);
        }
        $type = $class === Renja::class ? 'renja' : 'pk';
        if ($status === 'ditetapkan') {
            $this->logs($type, $doc, $op, str_replace('operator', 'kepala', $op), 'sakip');
        } elseif ($status === 'diajukan') {
            $this->log($type, $doc, $op, 'submit', 'draft', 'diajukan');
        }
    }

    private function logs(string $type, $m, string $by, string $rev, string $est): void
    {
        $this->log($type, $m, $by, 'submit', 'draft', 'diajukan');
        $this->log($type, $m, $rev, 'approve', 'diajukan', 'disetujui', 'Sudah sesuai, disetujui.');
        $this->log($type, $m, $est, 'establish', 'disetujui', 'ditetapkan', 'Ditetapkan sebagai dokumen final.');
    }

    private function log(string $type, $m, string $user, string $action, string $from, string $to, ?string $note = null): void
    {
        ApprovalLog::create(['approvable_type' => $type, 'approvable_id' => $m->id, 'user_id' => $this->u[$user]->id, 'role' => $this->u[$user]->role, 'action' => $action, 'from_status' => $from, 'to_status' => $to, 'note' => $note]);
    }

    public static function pdf(string $title): string
    {
        $text = str_replace(['(', ')'], '', $title);
        $stream = "BT /F1 18 Tf 60 760 Td ($text) Tj 0 -30 Td /F1 11 Tf (Dokumen contoh sistem e-SAKIP Pemda) Tj ET";
        $objs = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>', '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>', '<< /Length '.strlen($stream)." >>\nstream\n$stream\nendstream", '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $pdf = "%PDF-1.4\n";
        $off = [];
        foreach ($objs as $i => $o) {
            $off[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n$o\nendobj\n";
        }
        $x = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objs) + 1)."\n0000000000 65535 f \n";
        foreach ($off as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objs) + 1)." /Root 1 0 R >>\nstartxref\n$x\n%%EOF";
    }
}
