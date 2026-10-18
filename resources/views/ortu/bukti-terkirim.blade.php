{{-- L26 Bukti terkirim --}}
@php $batas = $bukti->created_at->copy()->addWeekdays((int) \App\Support\Klub::get('batas_verifikasi_hari_kerja', 2)); @endphp
<x-layout title="Bukti terkirim · Bandung Spiders" class="bg-white">
    <div class="mx-auto flex min-h-screen max-w-md flex-col px-4 pb-6 lg:min-h-0 lg:py-10">
        <header class="flex h-16 items-center justify-end">
            <a href="{{ route('ortu.tagihan') }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Tutup"><x-icon name="x" /></a>
        </header>
        <svg class="mx-auto mt-6 h-24 w-24" viewBox="0 0 96 96" aria-hidden="true">
            <rect x="14" y="4" width="68" height="46" rx="4" fill="none" stroke="#E3DCF0" stroke-width="3"/>
            <rect x="34" y="20" width="28" height="18" fill="none" stroke="#E3DCF0" stroke-width="3"/>
            <circle class="anim-muncul" cx="48" cy="34" r="12" fill="#F8921C"/>
            <ellipse cx="48" cy="48" rx="22" ry="4" fill="none" stroke="#F8921C" stroke-width="3"/>
            <path d="M28 50l6 32M40 51l2 31M56 51l-2 31M68 50l-6 32" stroke="#8E6BD6" stroke-width="2.5" fill="none"/>
        </svg>
        <h1 class="mt-6 text-center font-display text-[40px] font-extrabold leading-none">Bukti terkirim</h1>
        <p class="mt-3 text-center text-[15px] text-redup">Admin memeriksa bukti paling lambat 2 hari kerja. Anda dapat notifikasi kalau statusnya berubah.</p>

        <ol class="mt-6 rounded-[18px] bg-white p-4 shadow-[0_2px_14px_rgba(34,0,90,.08)]">
            <li class="relative flex gap-3 pb-5">
                <span class="absolute left-[13px] top-7 h-full w-0.5 bg-hijau"></span>
                <span class="relative flex size-7 shrink-0 items-center justify-center rounded-full bg-hijau text-white"><x-icon name="check" size="16" /></span>
                <div><p class="font-bold">Diunggah</p><p class="text-sm text-redup">{{ $bukti->created_at->translatedFormat('l, j M, H.i') }}</p></div>
            </li>
            <li class="relative flex gap-3 pb-5">
                <span class="absolute left-[13px] top-7 h-full w-0.5 bg-garis"></span>
                <span class="relative size-7 shrink-0 rounded-full bg-white ring-[3px] ring-inset ring-oranye-500"></span>
                <div><p class="font-bold">Diperiksa admin</p><p class="text-sm text-redup">Paling lambat {{ $batas->translatedFormat('l, j M') }}</p></div>
            </li>
            <li class="flex gap-3">
                <span class="size-7 shrink-0 rounded-full bg-ungu-50 ring-2 ring-inset ring-garis"></span>
                <div><p class="font-bold text-redup">Lunas</p><p class="text-sm text-redup">Setelah disetujui</p></div>
            </li>
        </ol>

        <a href="{{ route('ortu.tagihan') }}" class="mt-auto flex h-[50px] items-center justify-center rounded-[14px] bg-white font-bold text-ungu-600 lg:mt-8 ring-2 ring-inset ring-ungu-600">Kembali ke tagihan</a>
    </div>
</x-layout>
