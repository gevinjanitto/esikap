<style>
    @page { margin: 26mm 14mm 18mm 14mm; }
    * { font-family: 'Helvetica', sans-serif; }
    body { color: #0B0F19; font-size: 10px; }
    .hdr { position: fixed; top: -19mm; left: 0; right: 0; height: 14mm; }
    .hdr td { vertical-align: middle; }
    .logo { width: 30px; height: 30px; background: #0B0F19; border-radius: 8px; color: #fff; text-align: center; font-weight: bold; font-size: 15px; line-height: 30px; position: relative; }
    .logo span { position: absolute; right: -4px; bottom: -4px; width: 13px; height: 13px; border-radius: 7px; background: #E11D2E; }
    .brand { font-size: 13px; font-weight: bold; letter-spacing: .5px; }
    .brand b { color: #E11D2E; }
    .muted { color: #64748B; }
    .accent { height: 3px; background: #E11D2E; margin-top: 4px; }
    .ftr { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 8px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 5px; }
    .pagenum:before { content: counter(page); }
    h1 { font-size: 19px; margin: 0 0 2px; letter-spacing: -.3px; }
    .eyebrow { color: #E11D2E; font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
    .cards { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 12px -6px 14px; }
    .cards td { background: #FFF1F2; border-left: 3px solid #E11D2E; border-radius: 6px; padding: 8px 10px; }
    .cards .v { font-size: 16px; font-weight: bold; }
    .cards .l { font-size: 8px; color: #64748B; text-transform: uppercase; letter-spacing: .6px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #0B0F19; color: #fff; font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; padding: 7px 6px; text-align: left; }
    table.data th:first-child { border-top-left-radius: 6px; } table.data th:last-child { border-top-right-radius: 6px; }
    table.data td { padding: 6px; border-bottom: 1px solid #EEF0F4; vertical-align: top; }
    table.data tr:nth-child(even) td { background: #F8FAFC; }
    .pill { display: inline-block; padding: 2px 7px; border-radius: 9px; font-size: 8px; font-weight: bold; white-space: nowrap; }
    .r { text-align: right; } .c { text-align: center; }
</style>
<div class="hdr">
    <table width="100%"><tr>
        <td width="38"><div class="logo">e<span></span></div></td>
        <td><div class="brand">e-SAKIP<b>.</b>PEMDA</div><div class="muted" style="font-size:8px">Sistem Akuntabilitas Kinerja Instansi Pemerintah Daerah</div></td>
        <td class="r muted" style="font-size:8px">{{ $meta ?? '' }}<br>Dicetak {{ now()->translatedFormat('d F Y H:i') }}</td>
    </tr></table>
    <div class="accent"></div>
</div>
<div class="ftr"><table width="100%"><tr><td>Dokumen dihasilkan otomatis oleh e-SAKIP Pemda · {{ auth()->user()?->name }}</td><td class="r">Design &amp; Develop by MaiHarta · www.maiharta.com · Hal. <span class="pagenum"></span></td></tr></table></div>
