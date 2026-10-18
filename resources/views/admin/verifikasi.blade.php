{{-- L5 Daftar verifikasi iuran --}}
@php
    use App\Support\HariKerja;
    $kotak = [['menunggu', 'Menunggu'], ['disetujui', 'Lunas'], ['ditolak', 'Ditolak']];
@endphp
<x-admin title="Verifikasi iuran" aktif="Verifikasi">
    <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 px-4 pb-16 pt-6 text-white">
        <x-court />
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[13px] text-white/75">Bukti transfer dari orang tua</p>
                <h1 class="mt-1 font-display text-[34px] font-extrabold leading-none">Verifikasi iuran</h1>
            </div>
            <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-14 object-contain">
        </div>
    </header>

    <div class="relative -mt-10 px-4" x-data="{ cari: '' }">
        <nav class="grid grid-cols-3 gap-2" aria-label="Status bukti">
            @foreach ($kotak as [$kunci, $label])
                <a href="{{ route('manajemen.verifikasi', $kunci === 'menunggu' ? [] : ['status' => $kunci]) }}" @class(['rounded-[18px] px-3 py-2.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]', 'tab-aktif bg-oranye-500 text-ungu-900' => $tab === $kunci, 'bg-white' => $tab !== $kunci]) @if ($tab === $kunci) aria-current="page" @endif>
                    <p class="font-display text-[30px] font-extrabold leading-none">{{ $jumlah[$kunci] }}</p>
                    <p class="text-sm">{{ $label }}</p>
                </a>
            @endforeach
        </nav>
        @if ($tab !== 'menunggu')
            <p class="mt-2 text-sm text-redup">Diperiksa sejak 1 {{ now()->translatedFormat('F Y') }}.</p>
        @endif

        <label class="mt-3 flex h-12 items-center gap-2 rounded-[14px] bg-white px-3.5 ring-[1.5px] ring-inset ring-garis focus-within:ring-2 focus-within:ring-ungu-600">
            <x-icon name="search" size="18" class="text-redup" />
            <input type="search" x-model="cari" placeholder="Cari nama anak" aria-label="Cari nama anak" class="min-w-0 flex-1 bg-transparent text-[15px] outline-none">
        </label>

        <div class="mt-2.5 space-y-2">
            @forelse ($bukti as $b)
                @php $anak = $b->tagihan->anak; $telat = $b->status === 'menunggu' && HariKerja::lewatBatas($b->created_at); @endphp
                <a href="{{ route('manajemen.verifikasi.detail', $b) }}" x-show="cari === '' || @js(mb_strtolower($anak->nama)).includes(cari.toLowerCase())"
                    class="flex items-center gap-3 rounded-[18px] bg-white px-3 py-2.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                    <span class="flex size-[42px] shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-lg font-extrabold text-ungu-600">{{ mb_substr($anak->nama, 0, 1) }}</span>
                    <span class="min-w-0 flex-1">
                        <b class="block truncate">{{ $anak->nama }}</b>
                        <span class="block text-sm text-redup">{{ $anak->kelompokUsia->nama }}, {{ $b->tagihan->periode->translatedFormat('F') }}, diunggah {{ $b->created_at->translatedFormat('j M') }}</span>
                        @if ($telat)<span class="block text-sm font-semibold text-merah">Lewat batas 2 hari kerja</span>@endif
                    </span>
                    @if ($b->status !== 'menunggu')<x-status :status="$b->status" admin />@endif
                    <x-icon name="chev" size="20" class="shrink-0 text-redup" />
                </a>
            @empty
                <p class="rounded-[18px] bg-white p-4 text-sm text-redup">{{ $tab === 'menunggu' ? 'Tidak ada bukti yang menunggu. Semua sudah diperiksa.' : 'Belum ada bukti di bulan ini.' }}</p>
            @endforelse
        </div>
    </div>
</x-admin>
