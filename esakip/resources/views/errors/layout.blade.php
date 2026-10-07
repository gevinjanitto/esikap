<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('code') · e-SAKIP Pemda</title>@include('partials.head')</head>
<body class="app-bg">
<div class="blob b1"></div><div class="blob b2"></div>
<div class="relative z-[2] min-h-screen grid place-items-center p-6">
    <div class="shell rounded-[36px] p-10 sm:p-14 max-w-lg w-full text-center" data-testid="error-page">
        <p class="font-display text-7xl font-extrabold text-brand-600">@yield('code')</p>
        <p class="text-xl font-semibold mt-4">@yield('title')</p>
        <p class="text-sm text-slate-500 mt-2">{{ $exception->getMessage() ?: View::yieldContent('msg') }}</p>
        <div class="flex justify-center gap-2 mt-8">
            <a href="javascript:history.back()" class="btn-ghost">Kembali</a>
            <a href="{{ url('/dashboard') }}" class="btn-primary">Ke Dashboard</a>
        </div>
        <p class="text-xs text-slate-400 mt-10">Design &amp; Develop by <a href="https://www.maiharta.com" target="_blank" class="font-semibold hover:text-brand-600">MaiHarta</a></p>
    </div>
</div>
</body>
</html>
