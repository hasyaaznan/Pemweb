{{-- Jadwal latihan (Admin): pola mingguan per kelompok usia dan latihan 4 minggu ke depan --}}
@php
    use App\Http\Controllers\Admin\JadwalController;
    use App\Support\Format;
@endphp
<x-admin title="Jadwal latihan" aktif="Beranda" judul="Jadwal latihan" :kembali="route('manajemen.beranda')">
    <div class="px-4 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)] lg:items-start lg:gap-8">
        <div>
        <div class="flex items-center justify-between">
            <h2 class="font-display text-[22px] font-extrabold text-ungu-700">Jadwal mingguan</h2>
            <a href="{{ route('manajemen.jadwal.tambah') }}" class="inline-flex h-9 items-center gap-1 rounded-xl bg-ungu-600 px-3 text-sm font-bold text-white"><x-icon name="plus" size="16" /> Tambah</a>
        </div>
        <p class="mt-0.5 text-sm text-redup">Berlaku setiap minggu. Untuk satu tanggal saja, ubah di daftar latihan di bawah.</p>

        <div class="mt-2.5 space-y-2.5">
            @foreach ($kelompok as $k)
                <section class="rounded-[18px] bg-white px-3.5 pb-1 pt-3 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                    <h3 class="font-display text-xl font-extrabold leading-none">{{ $k->nama }}</h3>
                    <ul class="divide-y divide-garis">
                        @forelse ($k->jadwal as $j)
                            <li>
                                <a href="{{ route('manajemen.jadwal.ubah', $j) }}" class="flex items-center gap-3 py-2.5">
                                    <span class="min-w-0 flex-1">
                                        <b class="block">{{ JadwalController::HARI[$j->hari] }}, {{ Format::jam($j->jam_mulai) }}–{{ Format::jam($j->jam_selesai) }}</b>
                                        <span class="block truncate text-sm text-redup">{{ $j->lokasi }}@if ($j->berlaku_mulai->isFuture()), mulai {{ $j->berlaku_mulai->translatedFormat('j M') }}@endif</span>
                                    </span>
                                    <span class="text-sm font-bold text-ungu-600">Ubah</span>
                                </a>
                            </li>
                        @empty
                            <li class="py-2.5 text-sm text-redup">Belum ada jadwal. <a href="{{ route('manajemen.jadwal.tambah', ['ku' => $k->id]) }}" class="font-bold text-ungu-600">Tambah</a></li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>

        </div>

        <div>
        <h2 class="mb-0.5 mt-6 font-display text-[22px] font-extrabold text-ungu-700 lg:mt-0">4 minggu ke depan</h2>
        <p class="mb-2.5 text-sm text-redup">Ketuk latihan untuk mengubah jam, tempat, atau membatalkannya. Orang tua di kelompok itu akan diberi tahu.</p>
        <div class="space-y-1.5">
            @forelse ($sesi->groupBy(fn ($s) => $s->tanggal->toDateString()) as $tanggal => $hari)
                <h3 class="pt-2 text-[13px] font-bold text-redup">{{ $hari->first()->tanggal->translatedFormat('l, j F') }}</h3>
                @foreach ($hari as $s)
                    <a href="{{ route('manajemen.sesi', [$s->kelompokUsia, $tanggal]) }}" @class(['flex items-center gap-3 rounded-[14px] bg-white px-3.5 py-2.5', 'opacity-70' => $s->status === 'batal'])>
                        <span class="w-[86px] shrink-0 font-display text-lg font-extrabold leading-tight">{{ Format::jam($s->jam_mulai) }}–{{ Format::jam($s->jam_selesai) }}</span>
                        <span class="min-w-0 flex-1">
                            <b class="block">{{ $s->kelompokUsia->nama }}</b>
                            @if ($s->status !== 'terjadwal')
                                <span class="block truncate text-[13px] font-semibold text-amber">{{ $s->status === 'batal' ? 'Batal' : 'Diubah' }}: {{ $s->alasan_perubahan }}</span>
                            @endif
                        </span>
                        <x-icon name="chev" size="18" class="shrink-0 text-redup" />
                    </a>
                @endforeach
            @empty
                <p class="rounded-[18px] bg-white p-4 text-sm text-redup">Tidak ada latihan dalam 4 minggu ke depan.</p>
            @endforelse
        </div>
        </div>
    </div>
</x-admin>
