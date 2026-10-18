@props(['title', 'menu', 'aktif', 'peran', 'judul' => null, 'kembali' => null, 'lonceng' => null])
{{-- Kerangka layar setelah login: app bar + menu bawah 4 item sesuai peran (K2).
     Di laptop (lebar 1024px ke atas) menu pindah ke samping kiri dan isi halaman melebar sampai 1024px. --}}
@php
    // Halaman notifikasi memakai menu bawah Beranda; di menu samping yang aktif adalah Notifikasi
    $notifikasiAktif = $lonceng && url()->current() === $lonceng[0];
@endphp
<x-layout :title="$title.' · Bandung Spiders'">
    <aside class="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col bg-ungu-900 px-4 pb-5 pt-6 text-white [view-transition-name:menu-samping] lg:flex">
        <div class="flex items-center gap-3 px-2">
            <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-12 object-contain">
            <div>
                <p class="font-display text-xl font-extrabold uppercase italic leading-none">Bandung<br>Spiders</p>
                <p class="mt-1 text-[13px] text-white/60">{{ $peran }}</p>
            </div>
        </div>
        <nav class="mt-8 space-y-1" aria-label="Menu utama">
            @foreach ($menu as [$rute, $ikon, $label])
                @php $ini = $aktif === $label && ! $notifikasiAktif; @endphp
                <a href="{{ route($rute) }}" @class(['flex h-12 items-center gap-3 rounded-[14px] px-3 font-semibold transition-colors', 'bg-white/10 text-white [view-transition-name:samping-aktif]' => $ini, 'text-white/70 hover:bg-white/5 hover:text-white' => ! $ini]) @if ($ini) aria-current="page" @endif>
                    <x-icon :name="$ikon" :class="$ini ? 'text-oranye-500' : ''" />{{ $label }}
                </a>
            @endforeach
            @if ($lonceng)
                <a href="{{ $lonceng[0] }}" @class(['flex h-12 items-center gap-3 rounded-[14px] px-3 font-semibold transition-colors', 'bg-white/10 text-white [view-transition-name:samping-aktif]' => $notifikasiAktif, 'text-white/70 hover:bg-white/5 hover:text-white' => ! $notifikasiAktif]) @if ($notifikasiAktif) aria-current="page" @endif>
                    <x-icon name="bell" :class="$notifikasiAktif ? 'text-oranye-500' : ''" />Notifikasi
                    @if ($lonceng[1])<span class="ml-auto rounded-full bg-oranye-500 px-2 py-0.5 text-xs font-bold text-ungu-900">{{ $lonceng[1] }}</span>@endif
                </a>
            @endif
        </nav>
        <div class="mt-auto border-t border-white/10 px-2 pt-4">
            <p class="truncate font-bold">{{ auth()->user()->nama }}</p>
            <p class="truncate text-[13px] text-white/60">{{ auth()->user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-sm font-semibold text-white/70 hover:text-white"><x-icon name="logout" size="18" /> Keluar</button>
            </form>
        </div>
    </aside>

    <div class="mx-auto min-h-screen max-w-md pb-28 lg:ml-64 lg:max-w-none lg:pb-12">
        <div class="lg:mx-auto lg:max-w-5xl lg:px-6 lg:pt-8">
            @if ($judul)
                <header class="flex h-16 items-center justify-between px-3.5 lg:mb-2 lg:h-auto lg:justify-start lg:gap-4 lg:px-4">
                    @if ($kembali)
                        <a href="{{ $kembali }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></a>
                    @else
                        <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-10 object-contain lg:hidden">
                    @endif
                    <h1 class="font-display text-[22px] font-extrabold lg:text-4xl">{{ $judul }}</h1>
                    @if ($lonceng)
                        <span class="lg:hidden"><x-lonceng :tautan="$lonceng[0]" :jumlah="$lonceng[1]" /></span>
                    @else
                        <span class="size-[38px] lg:hidden"></span>
                    @endif
                </header>
            @endif
            @if (session('status'))
                {{-- Pesan singkat setelah menyimpan; hilang sendiri setelah 5 detik --}}
                <p x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 5000)" x-show="tampil" x-transition.opacity.duration.300ms @click="tampil = false"
                    class="fixed inset-x-4 top-3 z-30 mx-auto flex max-w-[calc(28rem-2rem)] items-center gap-2 rounded-[14px] bg-hijau px-3.5 py-3 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(22,107,70,.3)] lg:left-64 lg:top-5" role="status"><x-icon name="check" size="18" class="shrink-0" />{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
    <nav class="fixed inset-x-3 bottom-3 [view-transition-name:nav-bawah] mx-auto flex h-[66px] max-w-[calc(28rem-1.5rem)] rounded-[22px] bg-white shadow-[0_8px_26px_rgba(34,0,90,.16)] lg:hidden" aria-label="Menu utama">
        @foreach ($menu as [$rute, $ikon, $label])
            <a href="{{ route($rute) }}" @class(['flex flex-1 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold', 'text-ungu-600' => $aktif === $label, 'text-redup' => $aktif !== $label]) @if ($aktif === $label) aria-current="page" @endif>
                <span @class(['flex h-7 w-[34px] items-center justify-center rounded-[10px]', 'bg-ungu-100 [view-transition-name:nav-aktif]' => $aktif === $label])><x-icon :name="$ikon" /></span>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</x-layout>
