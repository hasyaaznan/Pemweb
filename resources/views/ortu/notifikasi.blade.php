{{-- L18 Notifikasi orang tua --}}
@php
    $gaya = [
        'rapor_terbit' => ['rapor', 'bg-ungu-100 text-ungu-600'],
        'bukti_ditolak' => ['iuran', 'bg-merah-bg text-merah'],
        'iuran_lunas' => ['check', 'bg-hijau-bg text-hijau'],
        'tagihan_terbit' => ['iuran', 'bg-oranye-100 text-oranye-700'],
        'jadwal_berubah' => ['cal', 'bg-amber-bg text-amber'],
    ];
    $waktu = fn ($t) => $t->isToday() ? $t->format('H.i') : ($t->greaterThan(now()->subDays(6)->startOfDay()) ? $t->translatedFormat('D') : $t->translatedFormat('j M'));
@endphp
<x-ortu title="Notifikasi" aktif="Beranda">
    <header class="flex h-16 items-center justify-between px-3.5 lg:mb-2 lg:h-auto lg:max-w-3xl lg:justify-start lg:gap-4 lg:px-4">
        <a href="{{ route('ortu.beranda') }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></a>
        <h1 class="font-display text-[22px] font-extrabold lg:text-4xl">Notifikasi</h1>
        @if ($belumDibaca)
            <form method="POST" action="{{ route('ortu.notifikasi.baca') }}" class="lg:ml-auto">
                @csrf
                <button type="submit" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Tandai semua sudah dibaca" title="Tandai semua sudah dibaca"><x-icon name="check" /></button>
            </form>
        @else
            <span class="size-[38px]"></span>
        @endif
    </header>

    <div class="px-4 lg:max-w-3xl">
        @if ($hariIni->isEmpty() && $sebelumnya->isEmpty())
            <p class="rounded-[18px] bg-white p-4 text-sm text-redup">Belum ada notifikasi. Rapor baru, tagihan, dan perubahan jadwal akan muncul di sini.</p>
        @endif
        @foreach (['Hari ini' => $hariIni, 'Sebelumnya' => $sebelumnya] as $judul => $daftar)
            @if ($daftar->isNotEmpty())
                <h2 class="mb-2 mt-1 text-sm text-redup">{{ $judul }}</h2>
                <ul class="mb-3 space-y-2">
                    @foreach ($daftar as $n)
                        @php [$ikon, $warna] = $gaya[$n->jenis] ?? ['bell', 'bg-ungu-100 text-ungu-600']; $baru = ! $n->dibaca_pada; @endphp
                        <li>
                            <a href="{{ route('ortu.notifikasi.buka', $n) }}" @class(['flex items-start gap-3 rounded-[18px] p-3.5', 'bg-white shadow-[0_2px_10px_rgba(34,0,90,.06)] anim-muncul' => $baru])>
                                <span @class(['flex size-10 shrink-0 items-center justify-center rounded-full', $warna => $baru, 'bg-white text-redup' => ! $baru])><x-icon :name="$ikon" size="20" /></span>
                                <span class="min-w-0 flex-1">
                                    <b @class(['block leading-snug', 'text-ungu-700' => ! $baru])>{{ $n->judul }}</b>
                                    <span class="mt-0.5 block text-[13px] leading-snug text-redup">{{ $n->isi }}</span>
                                    <span class="mt-1 block text-sm text-redup">{{ $waktu($n->created_at) }}</span>
                                </span>
                                @if ($baru)<i class="mt-1.5 size-[9px] shrink-0 rounded-full bg-oranye-500" aria-label="Belum dibaca"></i>@endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </div>
</x-ortu>
