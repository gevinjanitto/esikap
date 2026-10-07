# e-SAKIP Pemda

Aplikasi Laravel ada di folder `esakip/`. Lihat `esakip/README.md`.

## Deploy ke Railway
Deploy repo ini apa adanya (Root Directory `/`). Railway memakai `railway.json` + `Dockerfile` di root untuk membangun `esakip/`.
Tambahkan MySQL, lalu set variables: `APP_KEY`, `APP_URL`, `DB_CONNECTION=mysql`, `DB_URL=${{MySQL.MYSQL_URL}}` (+ isi lain dari `esakip/.env.example`). Volume: `/app/storage/app`.
