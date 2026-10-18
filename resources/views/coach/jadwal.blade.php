{{-- L24 Jadwal coach: satu minggu, latihan hari terpilih, dan perubahan jadwal minggu ini --}}
@php
    use App\Support\Format;
    $hariLatihan = $sesi->map(fn ($s) => $s->tanggal->toDateString())->unique();
    $hariIni = $sesi->filter(fn ($s) => $s->tanggal->isSameDay($dipilih))->values();
    $perubahan = $sesi->where('status', '!=', 'terjadwal');
@endphp
<x-coach title="Jadwal" aktif="Jadwal" judul="Jadwal">
    <div class="px-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('coach.jadwal', ['tanggal' => $senin->copy()->subWeek()->toDateString()]) }}" class="flex size-9 items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Minggu sebelumnya"><x-icon name="back" size="18" /></a>
            <h2 class="font-display text-2xl font-extrabold">{{ $senin->translatedFormat('F Y') }}</h2>
            <a href="{{ route('coach.jadwal', ['tanggal' => $senin->copy()->addWeek()->toDateString()]) }}" class="flex size-9 rotate-180 items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Minggu berikutnya"><x-icon name="back" size="18" /></a>
        </div>

        <nav class="mt-3 grid grid-cols-7 gap-1 rounded-[18px] bg-white p-2 shadow-[0_2px_10px_rgba(34,0,90,.06)]" aria-label="Hari dalam minggu">
            @foreach (range(0, 6) as $i)
                @php $h = $senin->copy()->addDays($i); $aktif = $h->isSameDay($dipilih); $ada = $hariLatihan->contains($h->toDateString()); @endphp
                <a href="{{ route('coach.jadwal', ['tanggal' => $h->toDateString()]) }}" @class(['flex flex-col items-center rounded-xl py-1.5', 'tab-aktif bg-oranye-500 text-ungu-900' => $aktif, 'text-redup' => ! $aktif]) @if ($aktif) aria-current="date" @endif>
                    <span class="text-[11px] font-semibold">{{ $h->translatedFormat('D') }}</span>
                    <span class="font-display text-xl font-extrabold leading-tight">{{ $h->day }}</span>
                    <i @class(['mt-0.5 size-[5px] rounded-full', 'bg-ungu-900' => $ada && $aktif, 'bg-ungu-600' => $ada && ! $aktif, 'bg-transparent' => ! $ada])></i>
                </a>
            @endforeach
        </nav>

        <h3 class="mb-2 mt-5 font-display text-xl font-extrabold text-ungu-700">{{ $dipilih->translatedFormat('l, j F') }}</h3>
        @forelse ($hariIni as $i => $s)
            <div class="mb-2 flex gap-2.5">
                <div class="w-12 shrink-0 pt-3 text-right">
                    <b class="font-display text-lg font-extrabold">{{ Format::jam($s->jam_mulai) }}</b>
                    <p class="text-xs text-redup">{{ Format::jam($s->jam_selesai) }}</p>
                </div>
                <div @class(['flex-1 rounded-[18px] p-3.5', 'bg-ungu-600 text-white' => $i === 0 && $s->status !== 'batal', 'bg-white' => $i !== 0 || $s->status === 'batal', 'opacity-60' => $s->status === 'batal'])>
                    <p class="font-display text-[22px] font-extrabold leading-none">{{ $s->kelompokUsia->nama }}@if ($s->status === 'batal') <span class="text-base">(batal)</span>@endif</p>
                    <p class="mt-1 text-sm opacity-80">{{ $s->jumlah_anak }} anak</p>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <span class="inline-flex min-w-0 items-center gap-1 rounded-full bg-black/5 px-2 py-0.5 text-xs font-semibold"><x-icon name="pin" size="12" class="shrink-0" /> <span class="truncate">{{ $s->lokasi }}</span></span>
                        @unless ($s->tanggal->isBefore(today()))
                            <a href="{{ route('coach.sesi', [$s->kelompokUsia, $s->tanggal->toDateString()]) }}" class="shrink-0 text-sm font-bold underline underline-offset-2">Ubah</a>
                        @endunless
                    </div>
                </div>
            </div>
        @empty
            <p class="rounded-[18px] bg-white p-4 text-sm text-redup">Tidak ada latihan di hari ini.</p>
        @endforelse

        @foreach ($perubahan as $s)
            <p class="mt-2 flex gap-2 rounded-[14px] bg-amber-bg px-3.5 py-3 text-[13px] font-semibold text-amber">
                <x-icon name="warn" size="18" class="shrink-0" />
                <span>{{ $s->tanggal->translatedFormat('l j M') }}: {{ $s->kelompokUsia->nama }} {{ $s->status === 'batal' ? 'batal' : 'mulai '.Format::jam($s->jam_mulai) }}@if ($s->alasan_perubahan) ({{ $s->alasan_perubahan }})@endif</span>
            </p>
        @endforeach
    </div>
</x-coach>
