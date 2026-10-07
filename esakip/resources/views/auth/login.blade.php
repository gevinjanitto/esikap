<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · e-SAKIP Pemda</title>
    @include('partials.head')
</head>
@php
    $demo = [
        ['superadmin', 'Super Admin'], ['admin', 'Admin Pemda'], ['bappeda', 'Bappeda'], ['sakip', 'Tim SAKIP'],
        ['operator.dinkes', 'Operator OPD'], ['kepala.dinkes', 'Kepala OPD'], ['inspektorat', 'Evaluator'], ['bupati', 'Pimpinan'],
    ];
@endphp
<body class="app-bg" x-data="{ login: @js(old('login', '')), password: '', show: false }">
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div><div class="grain"></div>

<div class="relative z-[2] min-h-screen flex p-3 sm:p-6 lg:p-10">
    <div class="login-card w-full bg-white rounded-[30px] lg:rounded-[40px] p-3 lg:p-4 shadow-[0_40px_120px_-40px_rgba(15,23,42,.45)] grid lg:grid-cols-[1.15fr_1fr] gap-3 lg:gap-4 min-h-[calc(100vh-1.5rem)] sm:min-h-[calc(100vh-3rem)] lg:min-h-[calc(100vh-5rem)]">

        {{-- hero --}}
        <div class="hero-panel relative bg-ink rounded-[24px] lg:rounded-[30px] overflow-hidden min-h-[320px] hidden sm:block" data-testid="login-hero-panel">
            <div class="absolute inset-0 opacity-[.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px"></div>
            <div class="absolute -left-20 -bottom-24 w-[420px] h-[420px] rounded-full bg-brand-600/30 blur-[90px]"></div>
            <img src="{{ asset('img/spring.png') }}" alt="" class="absolute left-[6%] top-[5%] w-40 lg:w-52 float-b drop-shadow-2xl" style="--r:-18deg">
            <img src="{{ asset('img/torus.png') }}" alt="" class="absolute right-[4%] bottom-[4%] w-44 lg:w-60 float-c drop-shadow-2xl" style="--r:12deg">

            <div class="absolute inset-0 flex items-center justify-center p-8">
                <div class="relative w-full max-w-[520px] h-[480px] hidden lg:block">
                    {{-- main card --}}
                    <div class="absolute left-[6%] top-[14%] w-[66%] bg-white rounded-[26px] p-3 shadow-2xl float-a" style="--r:-3deg">
                        <div class="rounded-[20px] bg-gradient-to-br from-brand-500 via-brand-600 to-brand-900 h-40 p-4 relative overflow-hidden">
                            <span class="absolute -right-6 -top-6 w-28 h-28 rounded-full border-[14px] border-white/15"></span>
                            <span class="badge bg-white/90 text-ink ring-white">Triwulan II · 2026</span>
                            <p class="font-display text-white text-4xl font-bold mt-6 num">92,4<span class="text-xl">%</span></p>
                            <p class="text-white/80 text-xs">Rata-rata capaian kinerja</p>
                        </div>
                        <p class="font-display text-[15px] font-bold mt-3 px-1">CAPAIAN KINERJA OPD</p>
                        <div class="flex items-center gap-4 text-[11px] text-slate-500 px-1 mt-1.5 pb-1">
                            <span class="inline-flex items-center gap-1"><i data-lucide="target" class="w-3 h-3"></i>26 Indikator</span>
                            <span class="inline-flex items-center gap-1"><i data-lucide="building-2" class="w-3 h-3"></i>9 OPD</span>
                        </div>
                    </div>
                    {{-- revenue-like card --}}
                    <div class="absolute right-0 top-0 w-[52%] bg-white rounded-[20px] p-4 shadow-2xl float-b" style="--r:2deg">
                        <p class="text-xs font-semibold">Predikat SAKIP</p>
                        <p class="text-[10px] text-slate-400">Evaluasi 2025</p>
                        <div class="flex items-end justify-between mt-2">
                            <p class="font-display text-3xl font-bold">BB</p>
                            <span class="badge bg-ink text-emerald-300 ring-ink">+2,1 poin</span>
                        </div>
                        <div class="bar sm mt-3"><div class="bar-fill moving bg-brand-600" style="width:72%"></div></div>
                    </div>
                    {{-- students-like card --}}
                    <div class="absolute left-0 bottom-[4%] w-[56%] bg-brand-600 rounded-[20px] p-4 shadow-2xl text-white float-c" style="--r:-2deg">
                        <p class="text-sm font-semibold">OPD Terhubung</p>
                        <p class="text-[10px] text-white/75">Cascading RPJMD → Kegiatan</p>
                        <div class="flex items-center mt-3">
                            @foreach (['DK', 'DP', 'PU', 'DS', 'KI'] as $k => $i)
                                <span class="w-8 h-8 rounded-full ring-2 ring-brand-600 grid place-items-center text-[10px] font-bold {{ ['bg-white text-ink', 'bg-amber-300 text-ink', 'bg-sky-300 text-ink', 'bg-emerald-300 text-ink', 'bg-ink text-white'][$k] }} {{ $k ? '-ml-2' : '' }}">{{ $i }}</span>
                            @endforeach
                            <span class="w-9 h-9 -ml-2 rounded-full bg-white text-ink grid place-items-center text-[11px] font-bold">9+</span>
                        </div>
                    </div>
                    {{-- small status --}}
                    <div class="absolute right-[2%] bottom-[16%] w-[44%] bg-white rounded-[20px] p-4 shadow-2xl float-a">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold">Renstra OPD</p>
                            <span class="badge bg-red-600 text-white ring-red-600">Ditetapkan</span>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <div class="h-1.5 rounded-full bg-slate-100"><div class="h-full w-[88%] rounded-full bg-ink"></div></div>
                            <div class="h-1.5 rounded-full bg-slate-100"><div class="h-full w-[64%] rounded-full bg-brand-600"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute left-8 bottom-8 right-8 text-white/80 text-xs flex items-center gap-2 lg:hidden">
                <i data-lucide="sparkles" class="w-4 h-4 text-brand-400"></i>Kinerja terukur, akuntabel dan transparan.
            </div>
        </div>

        {{-- form --}}
        <div class="flex flex-col px-5 sm:px-10 lg:px-14 py-8 lg:py-12">
            <div class="flex items-center gap-2.5 stagger max-w-[560px] w-full mx-auto" data-testid="login-brand">
                <span class="relative w-10 h-10 rounded-xl bg-ink grid place-items-center overflow-hidden">
                    <span class="absolute -right-2 -bottom-2 w-7 h-7 rounded-full bg-brand-600"></span>
                    <span class="relative font-display text-white font-bold">e</span>
                </span>
                <span class="font-display text-xl font-extrabold tracking-tight">e-SAKIP<span class="text-brand-600">.</span>PEMDA</span>
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-[560px] w-full mx-auto py-10">
                <p class="text-brand-600 font-medium mb-3 stagger"><span>Masuk ke akun Anda</span></p>
                <h1 class="font-display font-extrabold text-[38px] sm:text-[52px] leading-[.98] tracking-[-0.04em] mb-10" data-testid="login-heading">
                    @foreach (['SELAMAT', 'DATANG', 'DI', 'e-SAKIP'] as $k => $w)
                        <span class="inline-block overflow-hidden align-bottom"><span class="word {{ $w === 'e-SAKIP' ? 'text-brand-600' : '' }}" style="animation-delay: {{ 0.35 + $k * 0.09 }}s">{{ $w }}</span></span>
                    @endforeach
                </h1>

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5 stagger" data-testid="login-form">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-2" for="login">Email atau Username</label>
                        <input id="login" name="login" x-model="login" autocomplete="username" required class="inp !h-14 !rounded-2xl !text-base" placeholder="nama@esakip.go.id" data-testid="login-email-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" for="password">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" name="password" x-model="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required class="inp !h-14 !rounded-2xl !text-base pr-12" placeholder="••••••••" data-testid="login-password-input">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ink" data-testid="login-toggle-password"><i data-lucide="eye" class="w-5 h-5"></i></button>
                        </div>
                    </div>
                    @error('login')
                        <p class="text-sm text-brand-600 bg-brand-50 rounded-2xl px-4 py-3 flex items-center gap-2" data-testid="login-error"><i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $message }}</p>
                    @enderror
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-slate-500"><input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-200" data-testid="login-remember-checkbox">Ingat saya</label>
                        <span class="text-slate-400 text-xs">Lupa sandi? Hubungi Admin Pemda</span>
                    </div>
                    <button class="btn-primary w-full !h-14 !text-base !rounded-2xl" data-testid="login-submit-button">Masuk <i data-lucide="arrow-right" class="w-5 h-5"></i></button>
                </form>

                <div class="mt-8 reveal" style="--i:8" x-data="{ o: false }">
                    <button type="button" @click="o = !o" class="text-xs font-semibold text-slate-500 inline-flex items-center gap-1.5 hover:text-ink" data-testid="login-demo-toggle">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>Akun demo per peran (sandi: password123)
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="o && 'rotate-180'"></i>
                    </button>
                    <div x-show="o" x-cloak x-transition class="flex flex-wrap gap-2 mt-3">
                        @foreach ($demo as [$un, $lbl])
                            <button type="button" @click="login = '{{ $un }}@esakip.go.id'; password = 'password123'" class="chip hover:bg-brand-50 hover:text-brand-700 transition-colors press" data-testid="demo-account-{{ \Illuminate\Support\Str::slug($un) }}">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <p class="text-sm text-slate-400 max-w-[560px] w-full mx-auto" data-testid="login-footer">
                Design &amp; Develop by <a href="https://www.maiharta.com" target="_blank" rel="noopener" class="text-brand-600 font-semibold hover:underline" data-testid="login-footer-maiharta-link">MaiHarta</a>
            </p>
        </div>
    </div>
</div>
@include('partials.flash')
</body>
</html>
