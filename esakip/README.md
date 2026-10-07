# e-SAKIP Pemda

Sistem Akuntabilitas Kinerja Instansi Pemerintah Daerah — Laravel 12 + MySQL, Blade + Tailwind (pre-built) + Alpine.js + Chart.js.

Modul: Master Data, RPJMD, Cascading, Renstra OPD, Renja Tahunan, Perjanjian Kinerja, Rencana Aksi, Monitoring & Realisasi (+bukti dukung), Evaluasi & Tindak Lanjut, Dokumen (versi), Persetujuan (workflow Draft → Diajukan → Disetujui → Ditetapkan), Pelaporan (Excel/PDF), Audit Trail, Notifikasi. RBAC 8 peran dengan scope OPD.

## Menjalankan lokal
```bash
composer install
cp .env.example .env   # set DB_* lokal, APP_DEBUG=true, APP_FORCE_HTTPS=false, SESSION_SECURE_COOKIE=false
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
Akun demo (password `password123`): `superadmin@esakip.go.id`, `admin@`, `bappeda@`, `sakip@`, `operator.dinkes@`, `kepala.dinkes@`, `operator.disdik@`, `kepala.disdik@`, `operator.pupr@`, `kepala.pupr@`, `inspektorat@`, `bupati@esakip.go.id`.

## Deploy ke Railway
1. Di Railway: **New Project → Deploy from GitHub repo** (biarkan Root Directory kosong / `/`). `Dockerfile` + `railway.json` di root repo otomatis membangun folder `esakip/` (PHP 8.3 + FrankenPHP).
2. Tambahkan database: **+ New → Database → MySQL**.
3. Di service aplikasi → **Variables**, salin isi `.env.example`, lalu:
   - `APP_KEY` → hasil `php artisan key:generate --show`
   - `APP_URL` → domain Railway Anda (Settings → Networking → Generate Domain)
   - `DB_URL` → `${{MySQL.MYSQL_URL}}` (reference variable)
4. **Volume** (agar file unggahan tidak hilang saat redeploy): Settings → Volumes → mount path `/app/storage/app`.
5. Deploy. `railway.json` (root) menjalankan `migrate --force` dan `db:seed` (idempotent) sebagai pre-deploy; container menjalankan `php artisan optimize` lalu FrankenPHP di `$PORT`. Healthcheck: `/up`.

## Mengubah tampilan (Tailwind)
CSS sudah dikompilasi ke `public/css/app.css` sehingga server produksi tidak membutuhkan Node. Jika mengubah kelas Tailwind di Blade:
```bash
npx tailwindcss@3 -i resources/css/app.css -o public/css/app.css --minify
```

---
Design & Develop by [MaiHarta](https://www.maiharta.com)
