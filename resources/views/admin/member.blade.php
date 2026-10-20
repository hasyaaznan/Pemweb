{{-- L21 Daftar member --}}
<x-admin title="Member" aktif="Member" judul="Member">
    <div class="px-4" x-data="{ cari: '' }">
        <nav class="flex gap-1.5 overflow-x-auto pb-1" aria-label="Saring member">
            @php
                $chip = fn ($aktif) => 'shrink-0 rounded-full px-3 py-1.5 text-[13px] font-bold '.($aktif ? 'tab-aktif bg-ungu-600 text-white' : 'bg-white text-ungu-600');
            @endphp
            <a href="{{ route('manajemen.member') }}" class="{{ $chip(! $ku && ! $belumBayar) }}">Semua {{ $jumlahSemua }}</a>
            @foreach ($kelompok as $k)
                <a href="{{ route('manajemen.member', ['ku' => $k->id]) }}" class="{{ $chip($ku?->id === $k->id) }}">{{ $k->nama }}</a>
            @endforeach
            <a href="{{ route('manajemen.member', ['iuran' => 'belum']) }}" class="{{ $chip($belumBayar) }}">Belum bayar</a>
        </nav>

        <label class="mt-2.5 flex h-12 lg:mt-4 lg:max-w-md items-center gap-2 rounded-[14px] bg-white px-3.5 ring-[1.5px] ring-inset ring-garis focus-within:ring-2 focus-within:ring-ungu-600">
            <x-icon name="search" size="18" class="text-redup" />
            <input type="search" x-model="cari" placeholder="Cari nama anak" aria-label="Cari nama anak" class="min-w-0 flex-1 bg-transparent text-[15px] outline-none">
        </label>

        @if ($pendaftarBaru > 0 && ! $belumBayar)
            <a href="{{ route('manajemen.pendaftaran') }}" class="mt-2.5 flex items-center gap-3 rounded-[18px] bg-ungu-50 p-3.5 ring-[1.5px] ring-inset ring-ungu-400 lg:max-w-md">
                <span class="flex size-[42px] shrink-0 items-center justify-center rounded-full bg-ungu-600 text-white"><x-icon name="users" size="20" /></span>
                <span class="min-w-0 flex-1"><b class="block">{{ $pendaftarBaru }} pendaftar baru</b><span class="block text-sm text-redup">Menunggu verifikasi dokumen</span></span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-bg px-2.5 py-1 text-xs font-bold text-amber"><i class="size-[7px] rounded-full bg-current"></i>Baru</span>
            </a>
        @endif

        @if ($belumBayar)
            <p class="mt-2.5 text-sm text-redup">Anak yang tagihan {{ now()->translatedFormat('F') }}-nya belum lunas dan belum ada bukti yang menunggu. Buka nama anak untuk mengirim pengingat lewat WhatsApp.</p>
        @endif

        <div class="mt-2.5 space-y-[7px] lg:grid lg:grid-cols-2 lg:gap-2.5 lg:space-y-0">
            @forelse ($anak as $a)
                @php $tagihan = $a->tagihan->first(); @endphp
                <a href="{{ route('manajemen.member.detail', $a) }}" x-show="cari === '' || @js(mb_strtolower($a->nama)).includes(cari.toLowerCase())"
                    class="flex items-center gap-3 rounded-[18px] bg-white px-3 py-2.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                    <span class="flex size-[38px] shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-[17px] font-extrabold text-ungu-600">{{ mb_substr($a->nama, 0, 1) }}</span>
                    <span class="min-w-0 flex-1"><b class="block truncate">{{ $a->nama }}</b><span class="block text-sm text-redup">{{ $a->kelompokUsia->nama }}</span></span>
                    @if ($tagihan)
                        <x-status :status="$tagihan->status" admin />
                    @else
                        <span class="text-xs text-redup">Belum ada tagihan</span>
                    @endif
                </a>
            @empty
                <p class="rounded-[18px] bg-white p-4 text-sm text-redup">{{ $belumBayar ? 'Semua tagihan bulan ini sudah dibayar atau sedang diperiksa.' : 'Belum ada member di kelompok ini.' }}</p>
            @endforelse
        </div>
    </div>
</x-admin>
