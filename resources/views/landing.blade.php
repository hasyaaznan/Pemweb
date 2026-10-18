{{-- L10 Landing page dan L12 menu. Ponsel: satu kolom seperti mockup. Laptop (lg): hero dua kolom, program 3 kartu, jadwal dan lokasi berdampingan. --}}
@php
    use App\Http\Controllers\Admin\JadwalController;
    use App\Support\Format;
    $singkat = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    $hariLatihan = $hari->map(fn ($h) => $singkat[$h])->implode(' & ');
    $daftar = route('daftar');
    $masuk = auth()->check() ? route('beranda') : route('login');
    $tautan = [['#program', 'Program'], ['#jadwal', 'Jadwal'], ['#lokasi', 'Lokasi & kontak']];
@endphp
<x-layout title="Bandung Spiders Basketball · Latihan basket anak 6–16 tahun di Bandung">
    <div x-data="{ menu: false }" @keydown.escape.window="menu = false" class="bg-white">
        {{-- Hero --}}
        <section class="relative flex min-h-[100svh] flex-col overflow-hidden bg-gradient-to-b from-ungu-600 via-[#3A1088] to-ungu-900 text-white lg:min-h-[88vh]">
            <svg class="garis-gambar pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 360 760" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <g fill="none" stroke="rgba(255,255,255,.16)" stroke-width="2"><path d="M0 330h360"/><circle cx="180" cy="330" r="62"/><rect x="110" y="-40" width="140" height="190" rx="2"/><circle cx="180" cy="150" r="52"/><path d="M40 -10v120a140 140 0 0 0 280 0V-10"/></g>
            </svg>

            <header class="relative mx-auto flex w-full max-w-6xl items-center gap-2 px-4 pt-4 lg:px-8 lg:pt-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-spiders.png') }}" alt="Bandung Spiders Basketball" class="size-12 object-contain lg:size-14">
                    <span class="hidden font-display text-xl font-extrabold uppercase italic leading-none lg:block">Bandung<br>Spiders</span>
                </a>
                <nav class="ml-auto hidden items-center gap-7 text-[15px] font-semibold lg:flex" aria-label="Bagian halaman">
                    @foreach ($tautan as [$href, $label])
                        <a href="{{ $href }}" class="text-white/85 hover:text-oranye-500">{{ $label }}</a>
                    @endforeach
                </nav>
                <a href="{{ $masuk }}" class="ml-auto px-3 py-2 text-[15px] font-semibold lg:ml-6">Masuk</a>
                <a href="{{ $daftar }}" class="hidden h-11 items-center rounded-xl bg-oranye-500 px-5 font-bold text-ungu-900 lg:flex">Daftar sekarang</a>
                <button type="button" @click="menu = true" class="flex size-11 items-center justify-center rounded-xl bg-white/15 lg:hidden" aria-label="Buka menu"><x-icon name="menu" /></button>
            </header>

            <div class="relative mx-auto flex w-full max-w-6xl flex-1 items-end px-4 pb-6 lg:items-center lg:px-8 lg:pb-16">
                <div class="w-full lg:grid lg:grid-cols-[1.15fr_1fr] lg:items-center lg:gap-12">
                    <div>
                        <h1 class="font-display font-extrabold uppercase italic leading-[.86]">
                            <span class="block text-[72px] sm:text-[96px] lg:text-[112px]">Bandung</span>
                            <span class="block text-[94px] sm:text-[124px] lg:text-[148px]">Spiders</span>
                        </h1>
                        <p class="mt-3 max-w-xl text-[15px] leading-snug text-[#E4DAF8] sm:text-lg">Latihan basket untuk anak {{ $usiaMin }}–{{ $usiaMax }} tahun di Bandung. Setiap bulan coach menulis rapor perkembangan anak.</p>
                        <dl class="mt-4 grid max-w-xl grid-cols-3 divide-x divide-white/20 rounded-2xl bg-white/10 py-3">
                            <div class="px-3"><dt class="sr-only">Iuran</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">{{ Format::rupiahSingkat($iuranTermurah) }}</dd><dd class="text-xs text-[#CFC3EE] sm:text-sm">mulai, per bulan</dd></div>
                            <div class="px-3"><dt class="sr-only">Hari</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">{{ $hariLatihan }}</dd><dd class="text-xs text-[#CFC3EE] sm:text-sm">hari latihan</dd></div>
                            <div class="px-3"><dt class="sr-only">Lokasi</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">Bandung</dd><dd class="truncate text-xs text-[#CFC3EE] sm:text-sm">{{ $gor }}</dd></div>
                        </dl>
                        <div class="mt-4 flex max-w-xl gap-3">
                            <a href="{{ $daftar }}" class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] bg-oranye-500 text-base font-bold text-ungu-900 lg:flex-none lg:px-10">Daftar sekarang</a>
                            <a href="#program" class="hidden h-[52px] items-center justify-center rounded-[14px] px-6 font-bold ring-2 ring-inset ring-white/40 lg:flex">Lihat program</a>
                        </div>
                    </div>
                    {{-- Laptop: logo besar di dalam lingkaran lapangan, pengganti foto sampai foto latihan asli tersedia --}}
                    <div class="relative hidden aspect-square items-center justify-center lg:flex" aria-hidden="true">
                        <span class="absolute inset-[6%] rounded-full border-2 border-white/15"></span>
                        <span class="absolute inset-[22%] rounded-full bg-white/[.06]"></span>
                        <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="relative w-[68%] drop-shadow-[0_20px_40px_rgba(0,0,0,.35)]">
                    </div>
                </div>
            </div>
        </section>

        {{-- Program per usia --}}
        <section id="program" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:px-8 lg:py-20">
            <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Program per usia</h2>
            <p class="mt-2 text-sm text-redup lg:text-base">Anak masuk kelompok sesuai usianya saat mendaftar.</p>
            <div class="mt-5 divide-y-[1.5px] divide-garis rounded-[20px] px-3.5 ring-[1.5px] ring-inset ring-garis lg:grid lg:grid-cols-3 lg:gap-5 lg:divide-y-0 lg:px-0 lg:ring-0">
                @foreach ($kelompok as $k)
                    <div class="flex items-center gap-3 py-3.5 transition-colors duration-150 active:bg-ungu-50 lg:flex-col lg:items-start lg:rounded-[20px] lg:p-6 lg:ring-[1.5px] lg:ring-inset lg:ring-garis">
                        <div class="w-[74px] shrink-0 lg:w-auto">
                            <p class="font-display text-[38px] font-extrabold leading-none text-ungu-600 lg:text-6xl">{{ $k->usia_min }}–{{ $k->usia_max }}</p>
                            <p class="text-sm text-redup">tahun</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold lg:text-lg">{{ $k->nama }}</p>
                            <p class="mt-0.5 text-[13px] leading-snug text-redup lg:text-[15px]">{{ $k->program }}</p>
                            <p class="mt-1 text-sm font-semibold text-ungu-600">{{ $k->jadwal->map(fn ($j) => JadwalController::HARI[$j->hari])->unique()->implode(' & ') ?: 'Jadwal menyusul' }}</p>
                        </div>
                        <div class="text-right lg:mt-auto lg:w-full lg:border-t-[1.5px] lg:border-garis lg:pt-4 lg:text-left">
                            <p class="font-display text-xl font-extrabold lg:inline lg:text-3xl">{{ Format::rupiahSingkat($k->iuran_bulanan) }}</p>
                            <p class="text-sm text-redup lg:inline">/bulan</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <h2 class="mt-9 font-display text-2xl font-extrabold text-ungu-900 lg:mt-16 lg:text-4xl">Kenapa di Spiders</h2>
            <ul class="mt-2 space-y-2 lg:mt-5 lg:grid lg:grid-cols-3 lg:gap-5 lg:space-y-0">
                @foreach (['Coach berlisensi Perbasi di setiap kelompok', 'Rapor bulanan untuk fundamental, strategi, mental, IQ basket, dan fisik', 'Kehadiran anak bisa dicek setelah setiap latihan'] as $poin)
                    <li class="flex items-start gap-2.5 text-[15px] leading-snug lg:rounded-[20px] lg:bg-ungu-50 lg:p-6 lg:text-lg">
                        <x-icon name="check" size="20" class="mt-px shrink-0 text-oranye-500 lg:size-7" />{{ $poin }}
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Jadwal dan lokasi --}}
        <section id="jadwal" class="bg-ungu-50/60 scroll-mt-4">
            <div class="mx-auto max-w-6xl px-4 py-10 lg:grid lg:grid-cols-[1.2fr_1fr] lg:gap-10 lg:px-8 lg:py-20">
                <div>
                    <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Jadwal latihan</h2>
                    <p class="mt-2 text-sm text-redup lg:text-base">Jadwal {{ now()->translatedFormat('F Y') }}. Member mendapat notifikasi kalau jadwal berubah.</p>
                    <table class="mt-5 w-full overflow-hidden rounded-[20px] bg-white text-left ring-[1.5px] ring-inset ring-garis">
                        <thead class="bg-ungu-50 text-[13px] font-semibold text-redup">
                            <tr><th class="px-3.5 py-2.5 font-semibold">Kelompok</th>@foreach ($hari as $h)<th class="px-2 py-2.5 font-semibold">{{ JadwalController::HARI[$h] }}</th>@endforeach</tr>
                        </thead>
                        <tbody>
                            @foreach ($kelompok as $k)
                                <tr class="border-t-[1.5px] border-garis">
                                    <th class="px-3.5 py-3 font-display text-xl font-extrabold">{{ $k->nama }}</th>
                                    @foreach ($hari as $h)
                                        @php $j = $k->jadwal->firstWhere('hari', $h); @endphp
                                        <td @class(['px-2 py-3 text-[15px] font-medium', 'text-redup' => ! $j])>{{ $j ? Format::jam($j->jam_mulai).'–'.Format::jam($j->jam_selesai) : '–' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <ul class="mt-4 space-y-3 text-sm leading-snug text-ungu-700 lg:text-[15px]">
                        <li class="flex gap-2.5"><x-icon name="cal" size="18" class="shrink-0 text-ungu-600" />Tidak ada latihan di tanggal merah. Pengganti diumumkan lewat notifikasi.</li>
                        <li class="flex gap-2.5"><x-icon name="clock" size="18" class="shrink-0 text-ungu-600" />Datang 15 menit lebih awal untuk pemanasan bersama.</li>
                        <li class="flex gap-2.5"><x-icon name="ball" size="18" class="shrink-0 text-ungu-600" />Bawa air minum, sepatu basket, dan baju ganti.</li>
                    </ul>
                </div>

                <div id="lokasi" class="relative mt-10 scroll-mt-4 overflow-hidden rounded-[26px] bg-ungu-900 p-5 text-white lg:mt-0 lg:flex lg:flex-col lg:p-7">
                    <x-court :opacity="0.08" />
                    <div class="relative">
                        <h2 class="font-display text-[26px] font-extrabold lg:text-4xl">{{ $gor }}</h2>
                        <p class="mt-1 text-sm leading-snug text-[#CFC3EE] lg:text-base">{{ trim(substr($alamat, strlen($gor)), ', ') }}<br>Admin: {{ Format::tampilHp($waAdmin) }} (WhatsApp)</p>
                    </div>
                    <iframe src="{{ $petaEmbed }}" title="Peta lokasi {{ $gor }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="relative mt-5 hidden h-56 w-full rounded-2xl border-0 lg:block"></iframe>
                    <div class="relative mt-4 grid grid-cols-2 gap-2.5 lg:mt-5">
                        <a href="{{ $tautanMaps }}" target="_blank" rel="noopener" class="flex h-[46px] items-center justify-center gap-1.5 rounded-[14px] bg-white font-bold text-ungu-900"><x-icon name="map" size="18" /> Buka Maps</a>
                        <a href="{{ $tautanWa }}" target="_blank" rel="noopener" class="flex h-[46px] items-center justify-center gap-1.5 rounded-[14px] font-bold ring-2 ring-inset ring-white/40"><x-icon name="wa" size="18" /> Chat WhatsApp</a>
                    </div>
                    <a href="{{ $daftar }}" class="relative mt-2.5 flex h-[52px] items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900 lg:mt-auto">Daftar sekarang</a>
                </div>
            </div>
        </section>

        <footer class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-6 text-sm text-redup sm:flex-row sm:justify-between lg:px-8">
            <p>© {{ now()->year }} Bandung Spiders Basketball</p>
            <p>Sudah member? <a href="{{ $masuk }}" class="font-bold text-ungu-600">Masuk</a></p>
        </footer>

        {{-- L12 Menu (ponsel): turun dari atas menutupi layar, 250 ms --}}
        <div x-cloak x-show="menu" x-transition:enter="transition duration-[250ms] ease-out" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full"
            class="fixed inset-0 z-50 flex flex-col overflow-hidden bg-ungu-900 px-4 pb-7 pt-4 text-white lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
            <x-court :opacity="0.08" />
            <div class="relative flex items-center justify-between">
                <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-12 object-contain">
                <button type="button" @click="menu = false" class="flex size-11 items-center justify-center rounded-xl bg-white/15" aria-label="Tutup menu"><x-icon name="x" /></button>
            </div>
            <nav class="relative mt-6" aria-label="Bagian halaman">
                <a href="#" @click="menu = false" class="block border-b border-white/10 py-3.5 font-display text-[40px] font-extrabold leading-none text-oranye-500">Beranda</a>
                @foreach ($tautan as [$href, $label])
                    <a href="{{ $href }}" @click="menu = false" class="block border-b border-white/10 py-3.5 font-display text-[40px] font-extrabold leading-none">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="relative mt-auto">
                <p class="mb-3 text-[13px] text-[#CFC3EE]">{{ $alamat }}<br>Admin: {{ Format::tampilHp($waAdmin) }} (WhatsApp)</p>
                <a href="{{ $daftar }}" class="flex h-[52px] items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Daftar sekarang</a>
                <p class="mt-3.5 text-center text-sm text-[#CFC3EE]">Sudah member? <a href="{{ $masuk }}" class="font-bold text-white">Masuk</a></p>
            </div>
        </div>
    </div>
</x-layout>
