@php
    use App\Support\Esakip;
    $u = auth()->user();
    $menu = collect(config('esakip.menu'))->filter(fn ($m) => Esakip::canMenu($m['key']));
    $isActive = function ($m) {
        if (isset($m['children'])) {
            return request()->routeIs('master.*', 'users.*', 'indikator.*');
        }
        $base = explode('.', $m['route'])[0];
        if ($base === 'annual') {
            return request()->routeIs('annual.*') && request()->route('kind') === $m['params']['kind'];
        }
        return request()->routeIs($base.'*');
    };
    $period = Esakip::activePeriod();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · e-SAKIP Pemda</title>
    @include('partials.head')
    @if (request()->routeIs('dashboard'))<script src="{{ asset('vendor/chart.umd.min.js') }}"></script>@endif
</head>
<body class="app-bg" x-data="{ nav: false }">
<div id="topbar"></div>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div><div class="grain"></div>

<div class="relative z-[2] p-2 sm:p-4 lg:p-6 xl:p-8">
    <div class="shell rounded-[30px] lg:rounded-[38px] min-h-[calc(100vh-4rem)] flex flex-col">
        {{-- header --}}
        <header class="flex items-center gap-3 px-4 sm:px-6 lg:px-8 pt-5 lg:pt-7" data-testid="app-header">
            <button class="icon-btn lg:hidden" @click="nav = true" data-testid="mobile-menu-button"><i data-lucide="menu" class="w-5 h-5"></i></button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0" data-testid="brand-logo">
                <span class="relative w-9 h-9 rounded-xl bg-ink grid place-items-center overflow-hidden">
                    <span class="absolute -right-2 -bottom-2 w-6 h-6 rounded-full bg-brand-600"></span>
                    <span class="relative font-display text-white text-sm font-bold">e</span>
                </span>
                <span class="leading-none">
                    <span class="block font-display text-[17px] font-bold tracking-tight">SAKIP<span class="text-brand-600">.</span></span>
                    <span class="block text-[10px] text-slate-500 font-medium tracking-wide mt-0.5">Pemerintah Daerah</span>
                </span>
            </a>

            <div class="hidden md:flex flex-1 justify-center">
                @hasSection('tabs')
                    @yield('tabs')
                @else
                    <div class="pill-tabs">
                        <span class="pill-tab-active"><i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5"></i>{{ $period?->name ?? 'Periode belum diatur' }}</span>
                        <span class="pill-tab">Tahun {{ Esakip::currentYear() }}</span>
                        <span class="pill-tab hidden xl:inline-flex">{{ $u->opd?->singkatan ?? 'Seluruh OPD' }}</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <div class="relative" x-data="{ o: false }" @click.outside="o = false">
                    <button class="icon-btn relative" @click="o = !o" data-testid="notif-bell-button">
                        <i data-lucide="bell" class="w-[18px] h-[18px]"></i>
                        @if ($unreadCount)
                            <span class="absolute top-2 right-2.5 w-2 h-2 rounded-full bg-brand-600 pulse-dot" data-testid="notif-unread-dot"></span>
                        @endif
                    </button>
                    <div x-show="o" x-cloak x-transition.origin.top.right class="absolute right-0 mt-3 w-80 card p-3 z-50" data-testid="notif-dropdown">
                        <div class="flex items-center justify-between px-2 pb-2">
                            <span class="text-sm font-semibold">Notifikasi</span>
                            <span class="chip">{{ $unreadCount }} baru</span>
                        </div>
                        @forelse ($unreadNotifs as $n)
                            <a href="{{ route('notif.open', $n) }}" class="block rounded-2xl px-3 py-2.5 hover:bg-slate-50">
                                <p class="text-[13px] font-semibold">{{ $n->title }}</p>
                                <p class="text-xs text-slate-500 line-clamp-2">{{ $n->message }}</p>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400 px-3 py-4 text-center">Tidak ada notifikasi baru</p>
                        @endforelse
                        <a href="{{ route('notif.index') }}" class="btn-ghost btn-sm w-full mt-2" data-testid="notif-see-all-link">Lihat semua</a>
                    </div>
                </div>
                <a href="{{ route('cascading') }}" class="icon-btn hidden sm:grid" title="Pohon Kinerja"><i data-lucide="network" class="w-[18px] h-[18px]"></i></a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf
                    <button class="icon-btn" title="Keluar" data-testid="logout-button"><i data-lucide="log-out" class="w-[18px] h-[18px]"></i></button>
                </form>
                <div class="flex items-center gap-3 pl-2" data-testid="user-profile">
                    <span class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white grid place-items-center text-sm font-bold ring-4 ring-white shadow">{{ $u->initials }}</span>
                    <span class="hidden lg:block leading-tight">
                        <span class="block text-sm font-semibold truncate max-w-[180px]" data-testid="user-name">{{ $u->name }}</span>
                        <span class="block text-[11px] text-slate-500" data-testid="user-role">{{ $u->role_label }}</span>
                    </span>
                </div>
            </div>
        </header>

        <div class="flex flex-1 gap-5 lg:gap-7 px-3 sm:px-6 lg:px-8 pb-4 pt-5">
            {{-- sidebar --}}
            <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[70] lg:hidden" @click="nav = false"></div>
            <aside :class="nav ? 'translate-x-0' : '-translate-x-[120%] lg:translate-x-0'" class="fixed lg:relative left-3 top-3 bottom-3 z-[75] lg:z-40 lg:self-start w-64 lg:w-auto bg-white lg:bg-transparent rounded-[26px] p-4 lg:p-0 transition-transform duration-500 lg:transition-none shadow-2xl lg:shadow-none overflow-y-auto lg:overflow-visible" data-testid="sidebar">
                <nav class="flex flex-col gap-2.5 lg:items-center lg:pt-1">
                    @foreach ($menu as $i => $m)
                        @php $active = $isActive($m); @endphp
                        @if (isset($m['children']))
                            <div class="side-link {{ $active ? 'active' : '' }}" x-data="{ s: false }" @mouseenter="s = window.innerWidth >= 1024" @mouseleave="s = false">
                                <button @click="s = !s" class="flex items-center gap-3 lg:gap-0 w-full lg:w-auto press" data-testid="nav-{{ $m['key'] }}">
                                    <span class="w-11 h-11 rounded-full grid place-items-center transition-colors duration-300 {{ $active ? 'bg-ink text-white shadow-lg' : 'bg-white text-slate-500 hover:text-ink border border-slate-200/70' }}"><i data-lucide="{{ $m['icon'] }}" class="w-[18px] h-[18px]"></i></span>
                                    <span class="lg:hidden text-sm font-medium">{{ $m['label'] }}</span>
                                </button>
                                <div x-show="s" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="lg:absolute lg:left-[calc(100%+12px)] lg:top-0 z-[60] mt-2 lg:mt-0 lg:pl-1">
                                    <div class="lg:card lg:p-2 lg:w-56 flex flex-col">
                                        <span class="hidden lg:block px-3 pt-1 pb-2 eyebrow">{{ $m['label'] }}</span>
                                        @foreach ($m['children'] as $c)
                                            <a href="{{ route($c['route'], $c['params']) }}" class="px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-700 transition-colors {{ url()->current() === route($c['route'], $c['params']) ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}" data-testid="nav-master-{{ \Illuminate\Support\Str::slug($c['label']) }}">{{ $c['label'] }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route($m['route'], $m['params'] ?? []) }}" class="side-link {{ $active ? 'active' : '' }} flex items-center gap-3 lg:gap-0 press" data-testid="nav-{{ $m['key'] }}">
                                <span class="w-11 h-11 rounded-full grid place-items-center transition-colors duration-300 {{ $active ? 'bg-ink text-white shadow-lg' : 'bg-white text-slate-500 hover:text-ink border border-slate-200/70' }}"><i data-lucide="{{ $m['icon'] }}" class="w-[18px] h-[18px]"></i></span>
                                <span class="lg:hidden text-sm font-medium">{{ $m['label'] }}</span>
                                <span class="tip hidden lg:block bg-ink text-white text-xs font-medium px-3 py-1.5 rounded-full shadow-xl">{{ $m['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden mt-2">@csrf<button class="btn-ghost btn-sm w-full"><i data-lucide="log-out" class="w-4 h-4"></i>Keluar</button></form>
                </nav>
            </aside>

            {{-- main --}}
            <main class="flex-1 min-w-0 transition-all">
                <div class="flex flex-col xl:flex-row xl:items-end gap-4 mb-6 reveal" style="--i:0">
                    <div class="min-w-0">
                        <p class="text-[15px] text-slate-500 mb-1" data-testid="page-subtitle">@yield('subtitle', 'Kelola dan pantau kinerja daerah')</p>
                        <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-semibold tracking-[-0.03em] leading-[1.05]" data-testid="page-title">@yield('heading')</h1>
                    </div>
                    <div class="xl:ml-auto flex flex-wrap items-center gap-2">
                        @hasSection('actions')
                            @yield('actions')
                        @else
                            <form action="{{ route('indikator.index') }}" class="relative w-full sm:w-80">
                                <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input name="q" class="inp !rounded-full !h-12 pl-11 bg-white/80" placeholder="Cari indikator, sasaran, program..." data-testid="global-search-input">
                            </form>
                        @endif
                    </div>
                </div>

                @yield('content')
            </main>
        </div>

        <footer class="px-6 lg:px-8 pb-5 pt-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400" data-testid="app-footer">
            <span>© {{ date('Y') }} e-SAKIP Pemda · Sistem Akuntabilitas Kinerja Instansi Pemerintah</span>
            <span>Design &amp; Develop by <a href="https://www.maiharta.com" target="_blank" rel="noopener" class="font-semibold text-slate-600 hover:text-brand-600 transition-colors" data-testid="footer-maiharta-link">MaiHarta</a></span>
        </footer>
    </div>
</div>

@include('partials.flash')
@include('partials.confirm')
@stack('scripts')
</body>
</html>
