{{-- L10 Landing page dan L12 menu. Ponsel: satu kolom seperti mockup. Laptop (lg): hero dua kolom, program 3 kolom, jadwal dan lokasi berdampingan.
     Sejarah, coach, prestasi, galeri, dan tanya jawab diisi dari config/landing.php. Gerak (GSAP + Lenis) ada di resources/js/landing.js. --}}
@php
    use App\Http\Controllers\Admin\JadwalController;
    use App\Support\Format;
    $singkat = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    $hariLatihan = $hari->map(fn ($h) => $singkat[$h])->implode(' & ');
    $daftar = route('daftar');
    $masuk = auth()->check() ? route('beranda') : route('login');
    $tautan = [['#program', 'Program'], ['#tentang', 'Tentang klub'], ['#prestasi', 'Prestasi'], ['#galeri', 'Galeri'], ['#jadwal', 'Jadwal'], ['#lokasi', 'Kontak']];
    $contoh = $konten['contoh'] ?? false;
    $galeri = collect($konten['galeri'])->map(fn ($g) => $g + [
        'url' => isset($g['src']) ? asset($g['src']) : null,
        'webm' => isset($g['webm']) ? asset($g['webm']) : null,
        'gambar' => asset($g['poster'] ?? $g['src']),
    ])->values();
@endphp
<x-layout title="Bandung Spiders Basketball · Latihan basket anak 6–16 tahun di Bandung">
    <div x-data="{ menu: false }" @keydown.escape.window="menu = false" x-effect="window.lenis?.[menu ? 'stop' : 'start']()" class="bg-white" data-landing>
        {{-- Hero: latar ungu polos, foto latihan sebagai fokus --}}
        <section class="relative flex min-h-[100svh] flex-col overflow-hidden bg-ungu-900 text-white md:min-h-[min(100svh,760px)] lg:min-h-[min(92vh,860px)]">
            {{-- Ponsel: foto di bagian atas, memudar ke ungu --}}
            <img src="{{ asset('images/contoh/hero-coach.webp') }}" alt="" class="absolute inset-x-0 top-0 h-[58%] w-full object-cover object-[50%_62%] opacity-60 grayscale [mask-image:linear-gradient(#000_45%,transparent)] lg:hidden" fetchpriority="high">
            <svg class="garis-gambar pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 360 760" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <g fill="none" stroke="rgba(255,255,255,.14)" stroke-width="2"><path d="M0 330h360"/><circle cx="180" cy="330" r="62"/><rect x="110" y="-40" width="140" height="190" rx="2"/><circle cx="180" cy="150" r="52"/><path d="M40 -10v120a140 140 0 0 0 280 0V-10"/></g>
            </svg>

            <header class="relative z-10 mx-auto flex w-full max-w-6xl items-center gap-2 px-4 pt-4 lg:px-8 lg:pt-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-spiders.png') }}" alt="Bandung Spiders Basketball" class="size-12 object-contain lg:size-14">
                    <span class="hidden font-display text-xl font-extrabold uppercase italic leading-none xl:block">Bandung<br>Spiders</span>
                </a>
                <nav class="ml-auto hidden items-center gap-6 text-[15px] font-semibold lg:flex" aria-label="Bagian halaman">
                    @foreach ($tautan as [$href, $label])
                        <a href="{{ $href }}" class="text-white/85 transition-colors hover:text-oranye-500">{{ $label }}</a>
                    @endforeach
                </nav>
                <a href="{{ $masuk }}" class="ml-auto px-3 py-2 text-[15px] font-semibold lg:ml-4">Masuk</a>
                <a href="{{ $daftar }}" class="hidden h-11 items-center rounded-xl bg-oranye-500 px-5 font-bold text-ungu-900 lg:flex">Daftar sekarang</a>
                <button type="button" @click="menu = true" class="flex size-11 items-center justify-center rounded-xl bg-white/15 lg:hidden" aria-label="Buka menu"><x-icon name="menu" /></button>
            </header>

            <div class="relative mx-auto flex w-full max-w-6xl flex-1 items-end px-4 pb-6 lg:items-center lg:px-8 lg:pb-16">
                <div class="w-full lg:grid lg:grid-cols-[1.15fr_1fr] lg:items-center lg:gap-12">
                    <div>
                        <h1 class="font-display font-extrabold uppercase italic leading-[.86]">
                            <span class="block overflow-hidden pr-3"><span data-hero-baris class="block text-[72px] sm:text-[96px] lg:text-[112px]">Bandung</span></span>
                            <span class="block overflow-hidden pr-3"><span data-hero-baris class="block text-[94px] sm:text-[124px] lg:text-[148px]">Spiders</span></span>
                        </h1>
                        <div data-hero-isi>
                            <p class="mt-3 max-w-xl text-[15px] leading-snug text-[#E4DAF8] sm:text-lg">Latihan basket untuk anak {{ $usiaMin }}–{{ $usiaMax }} tahun di Bandung. Setiap bulan coach menulis rapor perkembangan anak.</p>
                            <dl class="mt-4 grid max-w-xl grid-cols-3 divide-x divide-white/20 rounded-2xl bg-white/10 py-3">
                                <div class="px-3"><dt class="sr-only">Iuran</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">{{ Format::rupiahSingkat($iuranTermurah) }}</dd><dd class="text-xs text-[#DCD2F5] sm:text-sm">mulai, per bulan</dd></div>
                                <div class="px-3"><dt class="sr-only">Hari</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">{{ $hariLatihan }}</dd><dd class="text-xs text-[#DCD2F5] sm:text-sm">hari latihan</dd></div>
                                <div class="px-3"><dt class="sr-only">Lokasi</dt><dd class="font-display text-xl font-extrabold sm:text-2xl">Bandung</dd><dd class="truncate text-xs text-[#DCD2F5] sm:text-sm">{{ $gor }}</dd></div>
                            </dl>
                            <div class="mt-4 flex max-w-xl gap-3">
                                <a href="{{ $daftar }}" class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] bg-oranye-500 text-base font-bold text-ungu-900 lg:flex-none lg:px-10">Daftar sekarang</a>
                                <a href="#program" class="hidden h-[52px] items-center justify-center rounded-[14px] px-6 font-bold ring-2 ring-inset ring-white/40 lg:flex">Lihat program</a>
                            </div>
                        </div>
                    </div>
                    {{-- Laptop: foto latihan dalam bingkai tinggi --}}
                    <figure data-hero-foto class="relative hidden aspect-[4/5] max-h-[640px] overflow-hidden rounded-[28px] lg:block">
                        <img src="{{ asset('images/contoh/hero-coach.webp') }}" alt="Coach bertos dengan anak-anak setelah latihan" class="size-full object-cover object-[50%_70%] grayscale">
                        <span class="absolute inset-0 bg-ungu-900/35 mix-blend-multiply"></span>
                        <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="absolute bottom-5 right-5 size-16 drop-shadow-lg">
                    </figure>
                </div>
            </div>
        </section>

        {{-- Program per usia --}}
        <section id="program" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:px-8 lg:py-20">
            <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Program per usia</h2>
            <p class="mt-2 text-sm text-redup lg:text-base">Anak masuk kelompok sesuai usianya saat mendaftar.</p>
            <div class="mt-5 divide-y-[1.5px] divide-garis rounded-[20px] px-3.5 ring-[1.5px] ring-inset ring-garis md:grid md:grid-cols-3 md:gap-5 md:divide-y-0 md:px-0 md:ring-0">
                @foreach ($kelompok as $k)
                    <div class="flex items-center gap-3 py-3.5 transition-colors duration-150 active:bg-ungu-50 md:flex-col md:items-start md:rounded-[20px] md:p-6 md:ring-[1.5px] md:ring-inset md:ring-garis">
                        <div class="w-[74px] shrink-0 md:w-auto">
                            <p class="font-display text-[38px] font-extrabold leading-none text-ungu-600 md:text-6xl">{{ $k->usia_min }}–{{ $k->usia_max }}</p>
                            <p class="text-sm text-redup">tahun</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold md:text-lg">{{ $k->nama }}</p>
                            <p class="mt-0.5 text-[13px] leading-snug text-redup md:text-[15px]">{{ $k->program }}</p>
                            <p class="mt-1 text-sm font-semibold text-ungu-600">{{ $k->jadwal->map(fn ($j) => JadwalController::HARI[$j->hari])->unique()->implode(' & ') ?: 'Jadwal menyusul' }}</p>
                        </div>
                        <div class="text-right md:mt-auto md:w-full md:border-t-[1.5px] md:border-garis md:pt-4 md:text-left">
                            <p class="font-display text-xl font-extrabold md:inline md:text-3xl">{{ Format::rupiahSingkat($k->iuran_bulanan) }}</p>
                            <p class="text-sm text-redup md:inline">/bulan</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <h2 class="mt-9 font-display text-2xl font-extrabold text-ungu-900 md:mt-16 md:text-4xl">Kenapa di Spiders</h2>
            <ul class="mt-2 space-y-2 md:mt-5 md:grid md:grid-cols-3 md:gap-5 md:space-y-0">
                @foreach (['Coach berlisensi Perbasi di setiap kelompok', 'Rapor bulanan untuk fundamental, strategi, mental, IQ basket, dan fisik', 'Kehadiran anak bisa dicek setelah setiap latihan'] as $poin)
                    <li class="flex items-start gap-2.5 text-[15px] leading-snug md:rounded-[20px] md:bg-ungu-50 md:p-6 md:text-lg">
                        <x-icon name="check" size="20" class="mt-px shrink-0 text-oranye-500 md:size-7" />{{ $poin }}
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Tentang klub: sejarah dengan linimasa, lalu coach --}}
        <section id="tentang" class="scroll-mt-4 border-t-[1.5px] border-garis">
            <div class="mx-auto max-w-6xl px-4 py-10 lg:grid lg:grid-cols-[1fr_1fr] lg:gap-14 lg:px-8 lg:py-20">
                <div>
                    <p class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-oranye-700">Sejarah klub @if ($contoh)<x-isi-contoh />@endif</p>
                    <h2 class="mt-1 font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">{{ $konten['sejarah']['judul'] }}</h2>
                    <p class="mt-3 max-w-prose text-[15px] leading-relaxed text-ungu-700 lg:text-lg">{{ $konten['sejarah']['cerita'] }}</p>
                    <img src="{{ asset($konten['sejarah']['foto']) }}" alt="Coach dan anak-anak berkumpul sebelum latihan" loading="lazy" class="mt-6 aspect-[3/2] w-full rounded-[20px] object-cover grayscale">
                </div>
                <ol class="relative mt-8 lg:mt-14 lg:self-start" data-linimasa>
                    <span class="absolute bottom-3 left-[7px] top-3 w-0.5 bg-garis" aria-hidden="true"></span>
                    <span data-linimasa-isi class="absolute bottom-3 left-[7px] top-3 w-0.5 origin-top bg-oranye-500" aria-hidden="true"></span>
                    @foreach ($konten['sejarah']['linimasa'] as $t)
                        <li data-linimasa-titik class="relative grid grid-cols-[16px_1fr] gap-x-4 pb-7 last:pb-0">
                            <span class="lewat mt-2.5 size-4 rounded-full border-[3px] border-oranye-500 bg-white transition-colors duration-300 [&.lewat]:bg-oranye-500" aria-hidden="true"></span>
                            <div>
                                <p class="font-display text-[32px] font-extrabold leading-none text-ungu-600 lg:text-4xl">{{ $t['tahun'] }}</p>
                                <p class="mt-1 text-[15px] leading-snug text-ungu-700 lg:text-base">{{ $t['isi'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mx-auto max-w-6xl px-4 pb-10 lg:px-8 lg:pb-20">
                <h3 class="flex items-center gap-2 font-display text-2xl font-extrabold text-ungu-900 md:text-4xl">Coach kami @if ($contoh)<x-isi-contoh />@endif</h3>
                <ul class="mt-3 divide-y-[1.5px] divide-garis md:mt-5 md:grid md:grid-cols-3 md:gap-8 md:divide-y-0">
                    @foreach ($konten['coach'] as $c)
                        <li class="flex items-center gap-3.5 py-3.5 md:border-t-[3px] md:border-ungu-900 md:pt-5">
                            <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-2xl font-extrabold text-ungu-600" aria-hidden="true">{{ mb_substr(str_replace('Coach ', '', $c['nama']), 0, 1) }}</span>
                            <div class="min-w-0">
                                <p class="font-bold md:text-lg">{{ $c['nama'] }} <span class="font-medium text-redup">· {{ $c['peran'] }}</span></p>
                                <p class="text-sm text-ungu-700">{{ $c['kelompok'] }}</p>
                                <p class="text-sm font-semibold text-ungu-600">{{ $c['lisensi'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Siswa berprestasi: geser ke samping di ponsel, 4 kolom di laptop --}}
        <section id="prestasi" class="relative scroll-mt-4 overflow-hidden bg-ungu-900 text-white">
            <x-court :opacity="0.06" />
            <div class="relative mx-auto max-w-6xl py-10 lg:px-8 lg:py-20">
                <div class="px-4 lg:px-0">
                    <h2 class="flex flex-wrap items-center gap-x-3 gap-y-1 font-display text-[40px] font-extrabold leading-none lg:text-5xl">Siswa berprestasi @if ($contoh)<x-isi-contoh gelap />@endif</h2>
                    <p class="mt-2 text-sm text-[#DCD2F5] lg:text-base">Anak Spiders yang tampil di turnamen dan seleksi tim kota.</p>
                </div>
                <ul class="mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-4 px-4 pb-2 [scrollbar-width:none] lg:grid lg:grid-cols-4 lg:gap-6 lg:overflow-visible lg:px-0" data-prestasi>
                    @foreach ($konten['prestasi'] as $p)
                        <li class="w-[72%] shrink-0 snap-start sm:w-[44%] lg:w-auto">
                            <img src="{{ asset($p['foto']) }}" alt="{{ $p['nama'] }}" loading="lazy" class="aspect-[4/5] w-full rounded-[20px] object-cover">
                            <p class="mt-3 flex items-start gap-2 font-display text-[22px] font-extrabold leading-tight text-oranye-500"><x-icon name="trophy" size="20" class="mt-1 shrink-0" />{{ $p['prestasi'] }}</p>
                            <p class="mt-1 text-sm text-[#DCD2F5]"><span class="font-bold text-white">{{ $p['nama'] }}</span> · {{ $p['kelompok'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Galeri foto dan video, dibuka besar di lightbox --}}
        <section id="galeri" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:px-8 lg:py-20"
            x-data="{
                item: @js($galeri),
                buka: null,
                get aktif() { return this.buka === null ? null : this.item[this.buka] },
                lihat(i) { this.buka = i; window.lenis?.stop(); this.$nextTick(() => this.$refs.tutup.focus()) },
                tutup() { this.$refs.video?.pause(); this.buka = null; window.lenis?.start() },
                geser(n) { this.$refs.video?.pause(); this.buka = (this.buka + n + this.item.length) % this.item.length },
            }"
            @keydown.escape.window="buka !== null && tutup()" @keydown.arrow-right.window="buka !== null && geser(1)" @keydown.arrow-left.window="buka !== null && geser(-1)">
            <h2 class="flex flex-wrap items-center gap-x-3 gap-y-1 font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Galeri latihan @if ($contoh)<x-isi-contoh />@endif</h2>
            <p class="mt-2 text-sm text-redup lg:text-base">Foto dan video dari latihan dan pertandingan. Ketuk untuk melihat lebih besar.</p>
            <ul class="mt-5 grid grid-cols-2 gap-2.5 md:grid-cols-4 lg:gap-4">
                @foreach ($galeri as $i => $g)
                    <li @class(['col-span-2 row-span-2' => $i === 0])>
                        <button type="button" @click="lihat({{ $i }})" class="group relative block size-full overflow-hidden rounded-2xl focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-oranye-500" aria-label="Lihat {{ $g['jenis'] }}: {{ $g['keterangan'] }}">
                            <img src="{{ $g['gambar'] }}" alt="" loading="lazy" @class(['size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]', 'aspect-square' => $i > 0, 'aspect-[3/2] md:aspect-auto' => $i === 0])>
                            @if ($g['jenis'] === 'video')
                                <span class="absolute inset-0 flex items-center justify-center bg-ungu-900/30">
                                    <span class="flex size-16 items-center justify-center rounded-full bg-oranye-500 text-ungu-900 transition-transform duration-200 group-hover:scale-110 lg:size-20"><x-icon name="play" size="30" class="ml-1 fill-current" /></span>
                                </span>
                                <span class="absolute bottom-3 left-3 rounded-lg bg-ungu-900/80 px-2.5 py-1 text-[13px] font-semibold text-white">{{ $g['keterangan'] }}</span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>

            <div x-cloak x-show="buka !== null" x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex flex-col bg-[#0E0026]/95 p-4 text-white lg:p-8" role="dialog" aria-modal="true" aria-label="Galeri">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-[#DCD2F5]" x-text="aktif && (buka + 1) + ' dari ' + item.length"></p>
                    <button type="button" x-ref="tutup" @click="tutup()" class="flex size-11 items-center justify-center rounded-xl bg-white/15" aria-label="Tutup galeri"><x-icon name="x" /></button>
                </div>
                <div class="flex min-h-0 flex-1 items-center justify-center gap-3 py-4" @click.self="tutup()">
                    <button type="button" @click="geser(-1)" class="hidden size-12 shrink-0 items-center justify-center rounded-full bg-white/15 md:flex" aria-label="Sebelumnya"><x-icon name="back" /></button>
                    <template x-if="aktif && aktif.jenis === 'foto'">
                        <img :src="aktif.url" :alt="aktif.keterangan" class="max-h-full max-w-full rounded-xl object-contain">
                    </template>
                    <template x-if="aktif && aktif.jenis === 'video' && aktif.youtube">
                        <iframe :src="'https://www.youtube-nocookie.com/embed/' + aktif.youtube + '?autoplay=1'" :title="aktif.keterangan" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen class="aspect-video w-full max-w-4xl rounded-xl"></iframe>
                    </template>
                    <template x-if="aktif && aktif.jenis === 'video' && ! aktif.youtube">
                        <video x-ref="video" :poster="aktif.gambar" controls autoplay playsinline class="max-h-full max-w-full rounded-xl">
                            <source :src="aktif.webm || aktif.url" :type="aktif.webm ? 'video/webm' : 'video/mp4'">
                            <source :src="aktif.url" type="video/mp4">
                        </video>
                    </template>
                    <button type="button" @click="geser(1)" class="hidden size-12 shrink-0 items-center justify-center rounded-full bg-white/15 md:flex" aria-label="Berikutnya"><x-icon name="chev" /></button>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="geser(-1)" class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/15 md:hidden" aria-label="Sebelumnya"><x-icon name="back" /></button>
                    <p class="flex-1 text-center text-[15px]" x-text="aktif?.keterangan"></p>
                    <button type="button" @click="geser(1)" class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/15 md:hidden" aria-label="Berikutnya"><x-icon name="chev" /></button>
                </div>
            </div>
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
                        <p class="mt-1 text-sm leading-snug text-[#DCD2F5] lg:text-base">{{ trim(substr($alamat, strlen($gor)), ', ') }}<br>Admin: {{ Format::tampilHp($waAdmin) }} (WhatsApp)</p>
                    </div>
                    <iframe src="{{ $petaEmbed }}" title="Peta lokasi {{ $gor }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="relative mt-5 hidden h-56 w-full rounded-2xl border-0 lg:block"></iframe>
                    <div class="relative mt-5 space-y-3 lg:mt-auto lg:pt-6">
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ $tautanMaps }}" target="_blank" rel="noopener" class="flex h-[46px] items-center justify-center gap-1.5 rounded-[14px] bg-white font-bold text-ungu-900"><x-icon name="map" size="18" /> Buka Maps</a>
                            <a href="{{ $tautanWa }}" target="_blank" rel="noopener" class="flex h-[46px] items-center justify-center gap-1.5 rounded-[14px] font-bold ring-2 ring-inset ring-white/40"><x-icon name="wa" size="18" /> Chat WhatsApp</a>
                        </div>
                        <a href="{{ $daftar }}" class="flex h-[52px] items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Daftar sekarang</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tanya jawab orang tua --}}
        <section id="tanya-jawab" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:grid lg:grid-cols-[1fr_1.6fr] lg:gap-14 lg:px-8 lg:py-20">
            <div>
                <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Yang sering ditanyakan</h2>
                <p class="mt-2 text-sm text-redup lg:text-base">Pertanyaan lain bisa langsung ke Admin lewat <a href="{{ $tautanWa }}" target="_blank" rel="noopener" class="font-bold text-ungu-600 underline underline-offset-2">WhatsApp</a>.</p>
            </div>
            <div class="mt-5 divide-y-[1.5px] divide-garis border-y-[1.5px] border-garis lg:mt-0">
                @foreach ($konten['tanya_jawab'] as $tj)
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-[15px] font-bold lg:text-lg [&::-webkit-details-marker]:hidden">
                            {{ $tj['t'] }}
                            <x-icon name="down" size="20" class="shrink-0 text-ungu-600 transition-transform duration-200 group-open:rotate-180" />
                        </summary>
                        <p class="pb-4 pr-8 text-[15px] leading-relaxed text-ungu-700 lg:text-base">{{ $tj['j'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <footer class="border-t-[1.5px] border-garis">
            <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-6 text-sm text-redup sm:flex-row sm:justify-between lg:px-8">
                <p>© {{ now()->year }} Bandung Spiders Basketball</p>
                <p>Sudah member? <a href="{{ $masuk }}" class="font-bold text-ungu-600">Masuk</a></p>
            </div>
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
            <nav class="relative mt-4 min-h-0 overflow-y-auto" aria-label="Bagian halaman">
                <a href="#" @click="menu = false" class="block border-b border-white/10 py-3 font-display text-[34px] font-extrabold leading-none text-oranye-500">Beranda</a>
                @foreach ($tautan as [$href, $label])
                    <a href="{{ $href }}" @click="menu = false" class="block border-b border-white/10 py-3 font-display text-[34px] font-extrabold leading-none">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="relative mt-auto pt-4">
                <p class="mb-3 text-[13px] text-[#DCD2F5]">{{ $alamat }}<br>Admin: {{ Format::tampilHp($waAdmin) }} (WhatsApp)</p>
                <a href="{{ $daftar }}" class="flex h-[52px] items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Daftar sekarang</a>
                <p class="mt-3.5 text-center text-sm text-[#DCD2F5]">Sudah member? <a href="{{ $masuk }}" class="font-bold text-white">Masuk</a></p>
            </div>
        </div>
    </div>
</x-layout>
