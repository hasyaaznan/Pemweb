{{-- L15 Pendaftaran terkirim --}}
@php use App\Http\Controllers\PendaftaranController; @endphp
<x-layout title="Pendaftaran terkirim · Bandung Spiders">
    <main class="mx-auto flex min-h-screen max-w-md flex-col px-4 pb-6 pt-20 text-center lg:min-h-0 lg:pt-24">
        <span class="anim-pop mx-auto flex size-[110px] items-center justify-center rounded-full bg-hijau-bg">
            <span class="flex size-[76px] items-center justify-center rounded-full bg-hijau text-white"><x-icon name="check" size="40" /></span>
        </span>
        <h1 class="anim-muncul mt-6 font-display text-[38px] font-extrabold leading-none">Pendaftaran terkirim</h1>
        <p class="anim-muncul mx-2.5 mt-3 text-[15px] leading-snug text-redup">Data {{ strtok($pendaftaran->nama_anak, ' ') }} sudah kami terima. Admin akan menghubungi lewat WhatsApp {{ $pendaftaran->no_whatsapp }} paling lambat 2 hari kerja.</p>
        <dl class="mt-6 space-y-1.5 rounded-[18px] bg-white p-4 text-left text-sm">
            <div class="flex justify-between gap-3"><dt class="text-redup">No. pendaftaran</dt><dd class="font-bold">{{ PendaftaranController::nomor($pendaftaran) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Kelompok</dt><dd class="font-bold">{{ $pendaftaran->kelompokUsia->nama }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Status</dt><dd class="font-bold">Menunggu verifikasi</dd></div>
        </dl>
        <a href="{{ route('landing') }}" class="mt-auto flex h-[50px] items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900 lg:mt-8">Kembali ke beranda</a>
    </main>
</x-layout>
