@props(['title'])
{{-- Kerangka Masuk, Lupa sandi, dan Atur ulang sandi. Di laptop: panel ungu dengan logo di kiri, formulir di kanan. --}}
<x-layout :title="$title" {{ $attributes }}>
    <div class="lg:grid lg:min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <aside class="relative hidden overflow-hidden bg-gradient-to-b from-ungu-600 via-[#3A1088] to-ungu-900 p-12 text-white lg:flex lg:flex-col">
            <x-court :opacity="0.1" />
            <a href="{{ route('landing') }}" class="relative inline-flex h-[38px] w-fit items-center gap-1 rounded-xl bg-white/10 pl-2 pr-3 text-sm font-semibold hover:bg-white/20">
                <x-icon name="back" size="20" /> Halaman utama
            </a>
            <div class="relative my-auto">
                <img src="{{ asset('images/logo-spiders.png') }}" alt="Logo Bandung Spiders Basketball" class="size-44 object-contain drop-shadow-[0_20px_40px_rgba(0,0,0,.35)]">
                <p class="mt-6 font-display text-7xl font-extrabold uppercase italic leading-[.88]">Bandung<br>Spiders</p>
                <p class="mt-4 max-w-sm text-lg text-[#E4DAF8]">Satu akun untuk orang tua, coach, dan Admin. Rapor, absensi, dan iuran ada di sini.</p>
            </div>
        </aside>
        <div class="lg:flex lg:items-center lg:justify-center lg:bg-white lg:px-12">
            <div class="w-full">{{ $slot }}</div>
        </div>
    </div>
</x-layout>
