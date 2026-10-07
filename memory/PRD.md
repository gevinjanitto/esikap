# e-SAKIP Pemda (Laravel 12 + MySQL) — cloned from github.com/gevinjanitto/esikap
## Task (2026-06)
- Fix Railway "Railpack could not determine how to build the app" (repo root had no recognizable app; Laravel lives in esakip/).
- Login: logo & "Design & Develop" footer left-aligned with other text.
## Done
- Root Dockerfile (FrankenPHP php8.3, composer --no-dev), root railway.json (DOCKERFILE builder, preDeploy migrate+seed, healthcheck /up), .dockerignore.
- Verified locally: composer build, migrate, seed, optimize, FrankenPHP serve, /up 200, login POST → dashboard 200.
- login.blade.php: brand + footer wrapped in max-w-[560px] mx-auto (all at same x).
## Backlog
- Real .xlsx export; Railway volume for uploads.
