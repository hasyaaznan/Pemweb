@props(['title', 'menu', 'aktif', 'judul' => null, 'kembali' => null, 'lonceng' => null])
{{-- Kerangka layar setelah login: app bar + menu bawah 4 item sesuai peran (K2) --}}
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
                @if ($lonceng)
                    <x-lonceng :tautan="$lonceng[0]" :jumlah="$lonceng[1]" />
                @else
                    <span class="size-[38px]"></span>
                @endif
            </header>
        @endif
        @if (session('status'))
            {{-- Pesan singkat setelah menyimpan; hilang sendiri setelah 5 detik --}}
            <p x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 5000)" x-show="tampil" x-transition.opacity.duration.300ms @click="tampil = false"
                class="fixed inset-x-4 top-3 z-30 mx-auto flex max-w-[calc(28rem-2rem)] items-center gap-2 rounded-[14px] bg-hijau px-3.5 py-3 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(22,107,70,.3)]" role="status"><x-icon name="check" size="18" class="shrink-0" />{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </div>
    <nav class="fixed inset-x-3 bottom-3 [view-transition-name:nav-bawah] mx-auto flex h-[66px] max-w-[calc(28rem-1.5rem)] rounded-[22px] bg-white shadow-[0_8px_26px_rgba(34,0,90,.16)]" aria-label="Menu utama">
        @foreach ($menu as [$rute, $ikon, $label])
            <a href="{{ route($rute) }}" @class(['flex flex-1 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold', 'text-ungu-600' => $aktif === $label, 'text-redup' => $aktif !== $label]) @if ($aktif === $label) aria-current="page" @endif>
                <span @class(['flex h-7 w-[34px] items-center justify-center rounded-[10px]', 'bg-ungu-100 [view-transition-name:nav-aktif]' => $aktif === $label])><x-icon :name="$ikon" /></span>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</x-layout>
