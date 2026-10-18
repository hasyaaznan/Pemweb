{{-- L1 Beranda orang tua --}}
@php
    use App\Support\Format;
    $sapaan = ['Ibu' => 'Bu', 'Ayah' => 'Pak'][$anak->hubungan_orang_tua] ?? null;
    $namaDepan = strtok(auth()->user()->nama, ' ');
    $coach = $anak->kelompokUsia->coach->first();
@endphp
<x-ortu title="Beranda" aktif="Beranda">
    <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 px-4 pb-20 pt-6 text-white">
        <x-court />
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[15px] text-white/75">{{ now()->translatedFormat('l, j F Y') }}</p>
                <h1 class="mt-1 font-display text-[36px] font-extrabold leading-none">Halo, {{ $sapaan ? "$sapaan $namaDepan" : $namaDepan }}</h1>
            </div>
            <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-16 object-contain">
        </div>
    </header>

    <div class="relative -mt-14 space-y-3 px-4">
        <section class="flex items-center gap-3 rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]" x-data="{ buka: false }">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-oranye-100 font-display text-xl font-extrabold text-oranye-700">{{ mb_substr($anak->nama, 0, 1) }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-lg font-bold leading-tight">{{ $anak->nama }}</p>
                <p class="text-sm text-redup">{{ $anak->kelompokUsia->nama }}@if ($coach), Coach {{ $coach->nama }}@endif</p>
            </div>
            @if ($semuaAnak->count() > 1)
                <div class="relative">
                    <button type="button" @click="buka = !buka" class="flex items-center gap-1 rounded-full bg-ungu-100 px-3 py-2 text-sm font-semibold text-ungu-600"><x-icon name="down" size="16" /> Ganti anak</button>
                    <div x-cloak x-show="buka" x-transition @click.outside="buka = false" class="absolute right-0 z-10 mt-2 w-48 rounded-xl bg-white p-1 shadow-lg">
                        @foreach ($semuaAnak as $a)
                            <a href="{{ route('ortu.beranda', ['anak' => $a->id]) }}" @class(['block rounded-lg px-3 py-2 text-sm', 'bg-ungu-50 font-bold' => $a->id === $anak->id])>{{ $a->nama }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @if ($rapor)
                <div class="flex items-baseline justify-between border-b border-garis pb-2">
                    <h2 class="font-display text-2xl font-extrabold">Rapor {{ $rapor->periode->translatedFormat('F') }}</h2>
                    <span class="text-sm text-redup">Terbit {{ $rapor->terbit_pada?->translatedFormat('j M') }}</span>
                </div>
                <ul class="divide-y divide-garis">
                    @foreach ($rapor->nilaiRapor->sortBy('kategoriRapor.urutan') as $n)
                        <li class="flex items-center justify-between py-2"><span class="text-[15px]">{{ $n->kategoriRapor->nama }}</span><x-nilai :nilai="$n->nilai" /></li>
                    @endforeach
                </ul>
                <div class="mt-2 flex items-center justify-between text-xs">
                    <span class="text-redup">A sangat baik, B baik, C perlu latihan</span>
                    <a href="{{ route('ortu.rapor') }}" class="flex items-center gap-0.5 text-sm font-bold text-ungu-600">Catatan coach <x-icon name="chev" size="16" /></a>
                </div>
            @else
                <h2 class="font-display text-2xl font-extrabold">Rapor</h2>
                <p class="mt-1 text-sm text-redup">Rapor belum diisi coach. Rapor bulan ini terbit paling lambat awal bulan depan.</p>
            @endif
        </section>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('ortu.absensi') }}" class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                <p class="font-display text-[34px] font-extrabold leading-none">{{ $kehadiran['persen'] }}%</p>
                <p class="mt-1 text-sm leading-tight">Hadir {{ $kehadiran['hadir'] }} dari {{ $kehadiran['total'] }} latihan</p>
                <p class="text-xs text-redup">{{ \App\Support\Kehadiran::awalTigaBulan()->translatedFormat('M') }}–{{ now()->translatedFormat('M') }}</p>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-ungu-100"><div class="anim-bar h-full rounded-full bg-ungu-600" style="width: {{ $kehadiran['persen'] }}%"></div></div>
            </a>

            @if ($tagihan)
                <div @class(['flex flex-col rounded-[18px] p-3.5', 'bg-oranye-500 text-ungu-900' => $tagihan->status !== 'lunas', 'bg-hijau-bg text-hijau' => $tagihan->status === 'lunas'])>
                    <p class="text-sm font-semibold">Iuran {{ $tagihan->periode->translatedFormat('F') }}</p>
                    <p class="font-display text-[26px] font-extrabold leading-tight">{{ Format::rupiah($tagihan->nominal) }}</p>
                    <p class="text-xs">
                        @switch($tagihan->status)
                            @case('belum_bayar') {{ ucfirst(Format::jatuhTempo($tagihan->jatuh_tempo)) }} @break
                            @case('ditolak') Perlu unggah ulang @break
                            @case('menunggu_verifikasi') Menunggu verifikasi admin @break
                            @default Lunas
                        @endswitch
                    </p>
                    @if (in_array($tagihan->status, ['belum_bayar', 'ditolak']))
                        <a href="{{ route('ortu.tagihan.unggah', $tagihan) }}" class="mt-auto flex h-10 items-center justify-center gap-1.5 rounded-xl bg-ungu-900 pt-0 text-sm font-bold text-white"><x-icon name="up" size="18" /> Unggah bukti</a>
                    @else
                        <a href="{{ route('ortu.tagihan') }}" class="mt-auto pt-2 text-sm font-bold underline">Lihat tagihan</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-ortu>
