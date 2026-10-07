<?php

return [
    'app_name' => 'e-SAKIP Pemda',

    'roles' => [
        'super_admin' => 'Super Admin',
        'admin_pemda' => 'Admin Pemda',
        'bappeda' => 'Bappeda / Bapperida',
        'tim_sakip' => 'Bagian Organisasi / Tim SAKIP',
        'operator_opd' => 'Operator OPD',
        'kepala_opd' => 'Kepala OPD',
        'evaluator' => 'Inspektorat / Evaluator',
        'pimpinan' => 'Pimpinan Daerah',
    ],

    'thresholds' => ['tercapai' => 90, 'perhatian' => 75],

    'periode' => ['TW1' => 'Triwulan I', 'TW2' => 'Triwulan II', 'TW3' => 'Triwulan III', 'TW4' => 'Triwulan IV'],

    'opd_types' => ['Dinas' => 'Dinas', 'Badan' => 'Badan', 'Sekretariat' => 'Sekretariat', 'Inspektorat' => 'Inspektorat', 'Kecamatan' => 'Kecamatan', 'Satuan' => 'Satuan', 'Kantor' => 'Kantor'],

    'indicator_types' => ['impact' => 'Impact', 'outcome' => 'Outcome', 'output' => 'Output'],

    'formulas' => [
        'positif' => 'Positif (semakin tinggi semakin baik): Realisasi / Target x 100%',
        'negatif' => 'Negatif (semakin rendah semakin baik): (2 x Target - Realisasi) / Target x 100%',
    ],

    'doc_types' => ['RPJMD' => 'RPJMD', 'RENSTRA' => 'Renstra OPD', 'RENJA' => 'Renja', 'PK' => 'Perjanjian Kinerja', 'RENAKSI' => 'Rencana Aksi', 'LKJIP' => 'LKjIP / Laporan Kinerja', 'EVALUASI' => 'Laporan Evaluasi', 'LAINNYA' => 'Lainnya'],

    'upload' => ['max_kb' => 10240, 'mimes' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip'],

    'menu' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'dashboard', 'roles' => '*'],
        ['key' => 'master', 'label' => 'Master Data', 'icon' => 'database', 'roles' => ['super_admin', 'admin_pemda'], 'children' => [
            ['label' => 'Periode', 'route' => 'master.index', 'params' => ['res' => 'periode']],
            ['label' => 'OPD', 'route' => 'master.index', 'params' => ['res' => 'opd']],
            ['label' => 'Unit Kerja', 'route' => 'master.index', 'params' => ['res' => 'unit']],
            ['label' => 'Satuan', 'route' => 'master.index', 'params' => ['res' => 'satuan']],
            ['label' => 'Pengguna', 'route' => 'users.index', 'params' => []],
            ['label' => 'Pustaka Indikator', 'route' => 'indikator.index', 'params' => []],
        ]],
        ['key' => 'rpjmd', 'label' => 'RPJMD', 'icon' => 'landmark', 'route' => 'rpjmd.index', 'roles' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'pimpinan', 'evaluator']],
        ['key' => 'cascading', 'label' => 'Cascading', 'icon' => 'network', 'route' => 'cascading', 'roles' => '*'],
        ['key' => 'renstra', 'label' => 'Renstra OPD', 'icon' => 'compass', 'route' => 'renstra.index', 'roles' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator']],
        ['key' => 'renja', 'label' => 'Renja Tahunan', 'icon' => 'calendar-range', 'route' => 'annual.index', 'params' => ['kind' => 'renja'], 'roles' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator']],
        ['key' => 'pk', 'label' => 'Perjanjian Kinerja', 'icon' => 'handshake', 'route' => 'annual.index', 'params' => ['kind' => 'pk'], 'roles' => ['super_admin', 'admin_pemda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator', 'pimpinan']],
        ['key' => 'renaksi', 'label' => 'Rencana Aksi', 'icon' => 'list-checks', 'route' => 'renaksi.index', 'roles' => ['super_admin', 'admin_pemda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator']],
        ['key' => 'realisasi', 'label' => 'Monitoring & Realisasi', 'icon' => 'activity', 'route' => 'realisasi.index', 'roles' => ['super_admin', 'admin_pemda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator', 'bappeda']],
        ['key' => 'evaluasi', 'label' => 'Evaluasi Kinerja', 'icon' => 'clipboard-check', 'route' => 'evaluasi.index', 'roles' => ['super_admin', 'admin_pemda', 'tim_sakip', 'operator_opd', 'kepala_opd', 'evaluator', 'pimpinan']],
        ['key' => 'dokumen', 'label' => 'Dokumen', 'icon' => 'folder-open', 'route' => 'dokumen.index', 'roles' => '*'],
        ['key' => 'approval', 'label' => 'Persetujuan', 'icon' => 'stamp', 'route' => 'approval.index', 'roles' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'kepala_opd', 'pimpinan']],
        ['key' => 'laporan', 'label' => 'Pelaporan', 'icon' => 'file-text', 'route' => 'laporan.index', 'roles' => ['super_admin', 'admin_pemda', 'bappeda', 'tim_sakip', 'kepala_opd', 'evaluator', 'pimpinan']],
        ['key' => 'audit', 'label' => 'Audit Trail', 'icon' => 'shield-check', 'route' => 'audit.index', 'roles' => ['super_admin', 'admin_pemda', 'tim_sakip', 'evaluator']],
    ],
];
