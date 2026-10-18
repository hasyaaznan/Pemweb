{{-- L17 Absensi anak --}}
<x-ortu :title="'Absensi '.$anak->nama" aktif="Rapor" :judul="'Absensi '.strtok($anak->nama, ' ')" :kembali="route('ortu.beranda')">
    <x-tab-rapor aktif="absensi" :persen="$kehadiran['persen']" />

    <div class="mt-4 flex items-center justify-between px-4 text-sm lg:mt-5 lg:max-w-2xl">
        <span class="rounded-full bg-ungu-100 px-3 py-1.5 font-bold text-ungu-600">{{ $periode }}</span>
        <span class="text-redup">{{ $anak->kelompokUsia->nama }}</span>
    </div>

    <div class="mt-3 grid grid-cols-3 gap-2.5 px-4 lg:max-w-2xl">
        @foreach ([['hadir', 'Hadir', 'bg-hijau-bg text-hijau'], ['izin', 'Izin', 'bg-amber-bg text-amber'], ['alpa', 'Alpa', 'bg-merah-bg text-merah']] as [$k, $label, $warna])
            <div class="rounded-2xl p-3 {{ $warna }}">
                <p class="font-display text-[30px] font-extrabold leading-none">{{ $kehadiran[$k] }}</p>
                <p class="mt-1 text-sm">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-5 px-4 font-display text-2xl font-extrabold">Riwayat latihan</h2>
    <ul class="mt-2 space-y-2.5 px-4 lg:grid lg:grid-cols-2 lg:gap-3 lg:space-y-0">
        @forelse ($riwayat as $a)
            <li class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                <div>
                    <p class="font-bold">{{ $a->sesiLatihan->tanggal->translatedFormat('D, j M') }}</p>
                    <p class="text-xs text-redup">{{ \App\Support\Format::jam($a->sesiLatihan->jam_mulai) }}–{{ \App\Support\Format::jam($a->sesiLatihan->jam_selesai) }}, {{ $a->sesiLatihan->lokasi }}</p>
                </div>
                <x-status :status="$a->status" />
            </li>
        @empty
            <li class="rounded-2xl bg-white px-4 py-3 text-sm text-redup">Belum ada absensi dalam 3 bulan terakhir.</li>
        @endforelse
    </ul>
</x-ortu>
