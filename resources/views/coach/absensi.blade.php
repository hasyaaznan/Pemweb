{{-- L8 Input absensi: bawaan semua Hadir, satu ketukan per anak --}}
@php
    $awal = $anak->mapWithKeys(fn ($a) => [$a->id => $tercatat->get($a->id, 'hadir')]);
@endphp
<x-coach :title="'Absensi '.$kelompok->nama" aktif="Absensi" :judul="'Absensi '.$kelompok->nama" :kembali="route('coach.beranda', ['tanggal' => $sesi->tanggal->toDateString()])">
    <form method="POST" action="{{ route('coach.absensi.simpan', [$kelompok, $sesi->tanggal->toDateString()]) }}"
        x-data="{ status: @js($awal), hitung(s) { return Object.values(this.status).filter(v => v === s).length } }" class="px-4">
        @csrf
        @method('PUT')
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="flex items-center gap-1 rounded-full bg-ungu-100 px-3 py-1.5 text-sm font-bold text-ungu-600"><x-icon name="cal" size="15" /> {{ $sesi->tanggal->translatedFormat('D, j M') }}</span>
            <span class="flex-1"></span>
            <span class="rounded-full bg-hijau-bg px-2.5 py-1 text-xs font-bold text-hijau"><b x-text="hitung('hadir')">{{ $awal->filter(fn ($s) => $s === 'hadir')->count() }}</b> hadir</span>
            <span class="rounded-full bg-amber-bg px-2.5 py-1 text-xs font-bold text-amber"><b x-text="hitung('izin')">{{ $awal->filter(fn ($s) => $s === 'izin')->count() }}</b> izin</span>
            <span class="rounded-full bg-merah-bg px-2.5 py-1 text-xs font-bold text-merah"><b x-text="hitung('alpa')">{{ $awal->filter(fn ($s) => $s === 'alpa')->count() }}</b> alpa</span>
        </div>

        @if ($anak->isEmpty())
            <p class="mt-3 rounded-[18px] bg-white p-4 text-sm text-redup">Belum ada anak aktif di kelompok ini.</p>
        @endif

        <ul class="mt-3 space-y-2 lg:grid lg:grid-cols-2 lg:gap-2.5 lg:space-y-0">
            @foreach ($anak as $a)
                <li class="flex items-center gap-3 rounded-[16px] bg-white py-2 pl-2.5 pr-2">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-base font-extrabold text-ungu-600">{{ mb_substr($a->nama, 0, 1) }}</span>
                    <b class="min-w-0 flex-1 truncate text-[15px]">{{ $a->nama }}</b>
                    <fieldset class="flex gap-0.5 rounded-[11px] bg-ungu-50 p-[3px]">
                        <legend class="sr-only">Kehadiran {{ $a->nama }}</legend>
                        @foreach (['hadir' => ['H', 'peer-checked:bg-hijau'], 'izin' => ['I', 'peer-checked:bg-amber'], 'alpa' => ['A', 'peer-checked:bg-merah']] as $nilai => [$huruf, $warna])
                            <label class="cursor-pointer">
                                <input type="radio" class="peer sr-only" name="status[{{ $a->id }}]" value="{{ $nilai }}" x-model="status[{{ $a->id }}]" @checked($awal[$a->id] === $nilai)>
                                <span class="flex h-[34px] w-9 items-center justify-center rounded-[9px] font-display text-base font-extrabold text-redup transition-colors duration-200 peer-focus-visible:ring-2 peer-focus-visible:ring-ungu-600 {{ $warna }} peer-checked:text-white" title="{{ ucfirst($nilai) }}">{{ $huruf }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 text-center text-xs text-redup">H = hadir, I = izin, A = alpa. Semua anak otomatis Hadir; ubah yang tidak datang saja.</p>

        @if ($anak->isNotEmpty())
            <div class="fixed inset-x-0 bottom-[86px] z-10 mx-auto max-w-md px-4 lg:sticky lg:bottom-6 lg:mx-0 lg:mt-5 lg:max-w-sm lg:px-0">
                <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 text-base font-bold text-ungu-900 shadow-[0_6px_18px_rgba(248,146,28,.35)]">Simpan absensi ({{ $anak->count() }} anak)</button>
            </div>
            <div class="h-16 lg:hidden"></div>
        @endif
    </form>
</x-coach>
