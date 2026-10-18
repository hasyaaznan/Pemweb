@props(['title', 'aktif', 'judul' => null, 'kembali' => null])
{{-- Kerangka layar orang tua: app bar + menu bawah 4 item (Beranda, Rapor, Tagihan, Profil) --}}
<x-layout :title="$title.' · Bandung Spiders'">
    <div class="mx-auto min-h-screen max-w-md pb-28">
        @if ($judul)
            <header class="flex h-16 items-center justify-between px-3.5">
                @if ($kembali)
                    <a href="{{ $kembali }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></a>
                @else
                    <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-10 object-contain">
                @endif
                <h1 class="font-display text-[22px] font-extrabold">{{ $judul }}</h1>
                <span class="size-[38px]"></span>
            </header>
        @endif
        {{ $slot }}
    </div>
    <nav class="fixed inset-x-3 bottom-3 mx-auto flex h-[66px] max-w-[calc(28rem-1.5rem)] rounded-[22px] bg-white shadow-[0_8px_26px_rgba(34,0,90,.16)]" aria-label="Menu utama">
        @foreach ([['ortu.beranda', 'home', 'Beranda'], ['ortu.rapor', 'rapor', 'Rapor'], ['ortu.tagihan', 'iuran', 'Tagihan'], ['ortu.profil', 'user', 'Profil']] as [$rute, $ikon, $label])
            <a href="{{ route($rute) }}" @class(['flex flex-1 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold', 'text-ungu-600' => $aktif === $label, 'text-redup' => $aktif !== $label]) @if ($aktif === $label) aria-current="page" @endif>
                <span @class(['flex h-7 w-[34px] items-center justify-center rounded-[10px]', 'bg-ungu-100' => $aktif === $label])><x-icon :name="$ikon" /></span>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</x-layout>
