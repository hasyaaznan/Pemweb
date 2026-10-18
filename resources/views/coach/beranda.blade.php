{{-- L7 Pilih kelompok & tanggal --}}
@php
    use App\Support\Format;
    use Illuminate\Support\Carbon;
    $tgl = $dipilih ? Carbon::parse($dipilih) : null;
@endphp
<x-coach title="Absensi" aktif="Absensi">
    <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 lg:rounded-[28px] lg:px-8 px-4 pb-16 pt-6 text-white">
        <x-court />
        <div class="relative flex items-start justify-between">
            <div>
                <h1 class="font-display text-[34px] font-extrabold leading-none">Halo, Coach {{ strtok(auth()->user()->nama, ' ') }}</h1>
                <p class="mt-1.5 text-[15px] text-white/75">Jadwal latihan Anda</p>
            </div>
            <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-14 object-contain">
        </div>
    </header>

    <div class="relative -mt-10 px-4 lg:px-8">
        @if ($hariLatihan->isEmpty())
            <p class="rounded-[18px] bg-white p-4 text-sm text-redup shadow-[0_2px_10px_rgba(34,0,90,.06)]">Tidak ada latihan untuk kelompok Anda dalam 7 hari terakhir dan 7 hari ke depan.</p>
        @else
            <nav class="flex gap-1 overflow-x-auto rounded-[18px] bg-white p-2 shadow-[0_2px_10px_rgba(34,0,90,.06)]" aria-label="Hari latihan">
                @foreach ($hariLatihan as $h)
                    @php $c = Carbon::parse($h); @endphp
                    <a href="{{ route('coach.beranda', ['tanggal' => $h]) }}" @class(['flex min-w-14 flex-1 flex-col items-center rounded-[14px] py-1.5 transition-colors duration-200', 'tab-aktif bg-oranye-500 text-ungu-900' => $h === $dipilih, 'text-redup' => $h !== $dipilih]) @if ($h === $dipilih) aria-current="date" @endif>
                        <span class="text-xs font-semibold">{{ $c->translatedFormat('D') }}</span>
                        <span class="font-display text-[26px] font-extrabold leading-none">{{ $c->day }}</span>
                    </a>
                @endforeach
            </nav>

            <h2 class="mt-5 font-display text-2xl font-extrabold text-ungu-700">{{ $tgl->translatedFormat('l, j F') }}</h2>
            <div class="mt-2 space-y-2.5 lg:grid lg:grid-cols-2 lg:gap-4 lg:space-y-0">
                @foreach ($sesi as $i => $s)
                    @php
                        $gelap = $i === 0;
                        $sudah = $s->rekap->sum() > 0;
                        $nanti = $s->tanggal->isAfter(today());
                    @endphp
                    <section @class(['relative overflow-hidden rounded-[18px] p-4', 'bg-ungu-600 text-white' => $gelap, 'bg-white shadow-[0_2px_10px_rgba(34,0,90,.06)]' => ! $gelap])>
                        @if ($gelap)<x-court :opacity="0.12" />@endif
                        <div class="relative">
                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="font-display text-[26px] font-extrabold leading-none">{{ $s->kelompokUsia->nama }}</h3>
                                <span class="text-right text-sm opacity-80">
                                    @if ($s->status === 'batal') Batal
                                    @elseif ($sudah) Sudah diabsen, {{ $s->rekap->get('hadir', 0) }} hadir
                                    @elseif ($nanti) Belum dimulai
                                    @else Belum diabsen
                                    @endif
                                </span>
                            </div>
                            <p class="mt-1 text-sm opacity-85">{{ $s->jumlah_anak }} anak, {{ Format::jam($s->jam_mulai) }}–{{ Format::jam($s->jam_selesai) }}, {{ $s->lokasi }}</p>
                            @if ($s->status !== 'terjadwal' && $s->alasan_perubahan)
                                <p @class(['mt-2 rounded-xl px-3 py-2 text-[13px] font-semibold', 'bg-white/15' => $gelap, 'bg-amber-bg text-amber' => ! $gelap])>{{ $s->status === 'batal' ? 'Latihan dibatalkan' : 'Jadwal diubah' }}: {{ $s->alasan_perubahan }}</p>
                            @endif
                            <div class="mt-3 flex gap-2">
                                @if ($s->status !== 'batal' && ! $nanti)
                                    <a href="{{ route('coach.absensi', [$s->kelompokUsia, $s->tanggal->toDateString()]) }}" @class(['flex h-10 flex-1 items-center justify-center rounded-xl text-[15px] font-bold', 'bg-oranye-500 text-ungu-900' => $gelap, 'bg-ungu-600 text-white' => ! $gelap])>{{ $sudah ? 'Ubah absensi' : 'Isi absensi' }}</a>
                                @else
                                    <span @class(['flex h-10 flex-1 items-center justify-center rounded-xl text-sm font-semibold opacity-70', 'bg-white/10' => $gelap, 'bg-ungu-50' => ! $gelap])>{{ $nanti ? 'Absensi dibuka saat latihan' : 'Tidak ada absensi' }}</span>
                                @endif
                                <a href="{{ route('coach.rapor', ['ku' => $s->kelompok_usia_id]) }}" @class(['flex h-10 flex-1 items-center justify-center rounded-xl text-[15px] font-bold ring-[1.5px] ring-inset', 'ring-white/50 text-white' => $gelap, 'ring-ungu-600 text-ungu-600' => ! $gelap])>Isi rapor</a>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-coach>
