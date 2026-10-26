{{-- Daftar isi halaman depan: linimasa, turnamen, galeri, atau tanya jawab --}}
<x-admin :title="$j['judul']" aktif="Profil" :judul="$j['judul']" :kembali="route('manajemen.kelola')">
    <div class="px-4 lg:max-w-3xl">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-redup">{{ $j['keterangan'] }}</p>
            <a href="{{ route('manajemen.konten.tambah', $jenis) }}" class="inline-flex h-9 shrink-0 items-center gap-1 rounded-xl bg-ungu-600 px-3 text-sm font-bold text-white"><x-icon name="plus" size="16" /> Tambah</a>
        </div>
        <div class="mt-3 divide-y divide-garis rounded-[18px] bg-white px-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @forelse ($daftar as $item)
                @php
                    $gambar = match ($jenis) {
                        'galeri' => $item->gambar(),
                        'turnamen' => $item->foto ? asset($item->foto) : null,
                        default => null,
                    };
                    $kedua = match ($jenis) {
                        'turnamen' => $item->rentangTanggal().($item->hasil ? ' · '.$item->hasil : ''),
                        'galeri' => ucfirst($item->jenis).($item->youtube ? ' YouTube' : '').' · urutan '.$item->urutan,
                        default => $item->{$j['kedua']},
                    };
                @endphp
                <a href="{{ route('manajemen.konten.ubah', [$jenis, $item->id]) }}" class="flex items-center gap-3 py-3">
                    @if ($gambar)
                        <img src="{{ $gambar }}" alt="" class="size-14 shrink-0 rounded-xl object-cover">
                    @elseif ($jenis === 'linimasa')
                        <span class="w-14 shrink-0 font-display text-2xl font-extrabold text-ungu-600">{{ $item->tahun }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <b class="line-clamp-2 block">{{ $jenis === 'linimasa' ? $item->isi : $item->{$j['utama']} }}</b>
                        @unless ($jenis === 'linimasa')<span class="block truncate text-sm text-redup">{{ $kedua }}</span>@endunless
                    </span>
                    <span class="shrink-0 text-sm font-bold text-ungu-600">Ubah</span>
                </a>
            @empty
                <p class="py-3.5 text-sm text-redup">Belum ada {{ $j['satuan'] }}. Bagian ini tidak tampil di halaman depan sampai ada isinya.</p>
            @endforelse
        </div>
        <a href="{{ route('landing') }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-ungu-600"><x-icon name="eye" size="16" /> Lihat halaman depan</a>
    </div>
</x-admin>
