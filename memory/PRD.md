# e-SAKIP Pemda (Laravel 12 + MySQL) — cloned from github.com/gevinjanitto/esikap
## Task (2026-06)
- Fix Railway "Railpack could not determine how to build the app" (repo root had no recognizable app; Laravel lives in esakip/).
- Login: logo & "Design & Develop" footer left-aligned with other text.
## Done
- Root Dockerfile (FrankenPHP php8.3, composer --no-dev), root railway.json (DOCKERFILE builder, preDeploy migrate+seed, healthcheck /up), .dockerignore.
- Verified locally: composer build, migrate, seed, optimize, FrankenPHP serve, /up 200, login POST → dashboard 200.
- login.blade.php: brand + footer wrapped in max-w-[560px] mx-auto (all at same x).
- Fix 2: GitHub push drops composer.lock & .env.example → Dockerfile no longer COPYs composer.lock; composer install resolves from composer.json. Verified with fresh clone of user's repo.
- Login hero cards: enlarged inner text/badges/avatars/bars + container 620x560; Tailwind recompiled to public/css/app.css.
- Sidebar Master Data flyout: removed 12px hover gap (lg:left-full + lg:pl-4 bridge), 250ms close delay, aside lg:z-50. Tested 100% (iteration_3).
## Backlog
- Real .xlsx export; Railway volume for uploads.
