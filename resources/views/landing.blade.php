{{-- L10 Landing page dan L12 menu. Ponsel: satu kolom seperti mockup. Laptop (lg): hero dua kolom, program 3 kolom, jadwal dan lokasi berdampingan.
     Foto sampul, tentang klub, linimasa, coach, turnamen, galeri, dan tanya jawab diisi Admin dari menu Kelola data.
     Bagian yang belum ada isinya tidak ditampilkan. Gerak (GSAP + Lenis) ada di resources/js/landing.js. --}}
@php
    use App\Http\Controllers\Admin\JadwalController;
    use App\Support\Format;
    $singkat = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    $hariLatihan = $hari->map(fn ($h) => $singkat[$h])->implode(' & ');
    $daftar = route('daftar');
    $masuk = auth()->check() ? route('beranda') : route('login');
    $tautan = array_values(array_filter([
        ['#program', 'Program'], ['#tentang', 'Tentang klub'],
        $turnamen->isNotEmpty() ? ['#prestasi', 'Prestasi'] : null,
        $galeri->isNotEmpty() ? ['#galeri', 'Galeri'] : null,
        ['#jadwal', 'Jadwal'], ['#lokasi', 'Kontak'],
    ]));
    $fotoSampul = $sampul ? asset($sampul) : asset('images/logo-spiders.png');
    $dataGaleri = $galeri->map(fn ($g) => [
        'jenis' => $g->jenis,
        'url' => $g->file ? asset($g->file) : null,
        'gambar' => $g->gambar(),
        'youtube' => $g->youtube,
        'keterangan' => $g->keterangan,
    ])->values();
@endphp
<x-layout title="Bandung Spiders Basketball · Latihan basket anak 6–16 tahun di Bandung">
    <div x-data="{ menu: false }" @keydown.escape.window="menu = false" x-effect="window.lenis?.[menu ? 'stop' : 'start']()" class="bg-white" data-landing>
        {{-- Hero: latar ungu polos. Foto sampul tanpa latar berdiri di atas lingkaran oranye, menempel ke tepi bawah.
             Ponsel: teks di atas, foto di bawah (tidak bertumpuk). Laptop: teks kiri, foto kanan. --}}
        <section class="relative flex flex-col overflow-hidden bg-ungu-900 text-white lg:min-h-[min(92vh,860px)]">
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

            <div class="relative mx-auto flex w-full max-w-6xl flex-1 flex-col px-4 lg:flex-row lg:items-stretch lg:gap-8 lg:px-8">
                <div class="relative z-10 pt-8 lg:w-[54%] lg:self-center lg:py-16">
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
                <figure data-hero-foto class="relative mt-8 flex flex-1 items-end justify-center lg:mt-0 lg:w-[46%]">
                    <span data-hero-lingkaran class="absolute left-1/2 top-0 aspect-square w-[86%] max-w-[520px] -translate-x-1/2 rounded-full bg-oranye-500 lg:top-[22%] lg:w-[88%]" aria-hidden="true"></span>
                    <img data-hero-anak src="{{ $fotoSampul }}" alt="Coach Bandung Spiders memberi arahan kepada anak-anak berjersey ungu" fetchpriority="high"
                        class="relative block w-full max-w-[520px] object-contain object-bottom [mask-image:linear-gradient(to_right,transparent,#000_7%,#000_93%,transparent)] lg:max-h-[min(72vh,640px)] lg:max-w-none">
                </figure>
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

            {{-- Kenapa di Spiders: tiga alasan bernomor, dipisah garis tebal seperti daftar coach --}}
            <div class="mt-12 md:mt-20 lg:grid lg:grid-cols-[1fr_2.4fr] lg:gap-12">
                <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Kenapa di Spiders</h2>
                <ol class="mt-5 space-y-6 sm:grid sm:grid-cols-3 sm:gap-6 sm:space-y-0 lg:mt-1">
                    @foreach ([
                        ['Coach di setiap kelompok', 'Setiap kelompok usia dipegang coach berlisensi Perbasi.'],
                        ['Rapor setiap bulan', 'Nilai A+ sampai C- untuk fundamental, strategi, mental, IQ basket, dan fisik, ditambah catatan coach.'],
                        ['Kehadiran tercatat', 'Orang tua bisa mengecek kehadiran anak setelah setiap latihan.'],
                    ] as $i => [$judul, $isi])
                        <li class="border-t-[3px] border-ungu-900 pt-4">
                            <p class="font-display text-5xl font-extrabold leading-none text-oranye-700 lg:text-6xl">0{{ $i + 1 }}</p>
                            <p class="mt-3 font-display text-2xl font-extrabold leading-tight text-ungu-900">{{ $judul }}</p>
                            <p class="mt-1.5 text-[15px] leading-relaxed text-ungu-700">{{ $isi }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Tentang klub: cerita dan linimasa, lalu coach --}}
        <section id="tentang" class="scroll-mt-4 border-t-[1.5px] border-garis">
            {{-- Ponsel: cerita, foto, linimasa. Laptop: cerita dan linimasa di kiri, foto utuh di kanan --}}
            <div class="mx-auto max-w-6xl px-4 py-10 lg:grid lg:grid-cols-2 lg:gap-x-14 lg:px-8 lg:py-20">
                <div class="lg:col-start-1">
                    <p class="text-sm font-bold uppercase tracking-wide text-oranye-700">Tentang klub</p>
                    <h2 class="mt-1 font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">{{ $tentang['judul'] ?? 'Bandung Spiders Basketball' }}</h2>
                    @if ($tentang['cerita'])
                        <p class="mt-3 max-w-prose whitespace-pre-line text-[15px] leading-relaxed text-ungu-700 lg:text-lg">{{ $tentang['cerita'] }}</p>
                    @endif
                </div>
                @if ($tentang['foto'])
                    <img src="{{ asset($tentang['foto']) }}" alt="Tim Bandung Spiders berfoto di {{ $gor }}" loading="lazy" class="mt-6 h-auto w-full rounded-[20px] lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:mt-0 lg:self-start">
                @endif
                @if ($linimasa->isNotEmpty())
                    {{-- Garis dimulai dan berhenti di tengah titik pertama dan terakhir; ujung bawahnya dihitung dari posisi titik terakhir --}}
                    <ol class="relative mt-8 lg:col-start-1 lg:mt-10 lg:self-start" data-linimasa
                        x-data="{ ukur() { const t = $el.querySelector('li:last-child [data-titik]').getBoundingClientRect(); const b = $el.getBoundingClientRect().bottom - (t.top + t.height / 2); $el.querySelectorAll('[data-garis]').forEach((g) => g.style.bottom = b + 'px') } }"
                        x-init="ukur(); new ResizeObserver(() => ukur()).observe($el)">
                        <span data-garis class="absolute bottom-3 left-[7px] top-[18px] w-0.5 bg-garis" aria-hidden="true"></span>
                        <span data-garis data-linimasa-isi class="absolute bottom-3 left-[7px] top-[18px] w-0.5 origin-top bg-oranye-500" aria-hidden="true"></span>
                        @foreach ($linimasa as $t)
                            <li data-linimasa-titik class="relative grid grid-cols-[16px_1fr] gap-x-4 pb-7 last:pb-0">
                                <span data-titik class="lewat mt-2.5 size-4 rounded-full border-[3px] border-oranye-500 bg-white transition-colors duration-300 [&.lewat]:bg-oranye-500" aria-hidden="true"></span>
                                <div>
                                    <p class="font-display text-[32px] font-extrabold leading-none text-ungu-600 lg:text-4xl">{{ $t->tahun }}</p>
                                    <p class="mt-1 text-[15px] leading-snug text-ungu-700 lg:text-base">{{ $t->isi }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            @if ($coach->isNotEmpty())
                <div class="mx-auto max-w-6xl px-4 pb-10 lg:px-8 lg:pb-20">
                    <h3 class="font-display text-2xl font-extrabold text-ungu-900 md:text-4xl">Coach kami</h3>
                    <ul class="mt-3 divide-y-[1.5px] divide-garis md:mt-5 md:grid md:grid-cols-3 md:gap-8 md:divide-y-0">
                        @foreach ($coach as $c)
                            <li class="flex items-center gap-3.5 py-3.5 md:border-t-[3px] md:border-ungu-900 md:pt-5">
                                @if ($c->foto)
                                    <img src="{{ asset($c->foto) }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-2xl font-extrabold text-ungu-600" aria-hidden="true">{{ mb_substr(preg_replace('/^Coach\s+/i', '', $c->nama), 0, 1) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold md:text-lg">{{ $c->nama }}@if ($c->jabatan) <span class="font-medium text-redup">· {{ $c->jabatan }}</span>@endif</p>
                                    @if ($c->kelompokDipegang->isNotEmpty())<p class="text-sm text-ungu-700">{{ $c->kelompokDipegang->sortBy('usia_min')->pluck('nama')->implode(', ') }}</p>@endif
                                    @if ($c->lisensi)<p class="text-sm font-semibold text-ungu-600">{{ $c->lisensi }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        {{-- Turnamen dan prestasi: geser ke samping di ponsel kalau lebih dari satu --}}
        @if ($turnamen->isNotEmpty())
            <section id="prestasi" class="relative scroll-mt-4 overflow-hidden bg-ungu-900 text-white">
                <x-court :opacity="0.06" />
                <div class="relative mx-auto max-w-6xl py-10 lg:px-8 lg:py-20">
                    <div class="px-4 lg:px-0">
                        <h2 class="font-display text-[40px] font-extrabold leading-none lg:text-5xl">Turnamen dan prestasi</h2>
                        <p class="mt-2 text-sm text-[#DCD2F5] lg:text-base">Turnamen yang diikuti tim Spiders.</p>
                    </div>
                    @php $satu = $turnamen->count() === 1; @endphp
                    <ul @class([
                        'mt-6 px-4 lg:px-0',
                        'flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-4 pb-2 [scrollbar-width:none] lg:grid lg:grid-cols-3 lg:gap-6 lg:overflow-visible' => ! $satu,
                    ]) data-prestasi>
                        @foreach ($turnamen as $t)
                            <li @class(['shrink-0 snap-start w-[80%] sm:w-[48%] lg:w-auto' => ! $satu, 'md:grid md:grid-cols-[1.3fr_1fr] md:items-center md:gap-10' => $satu])>
                                @if ($t->foto)
                                    <img src="{{ asset($t->foto) }}" alt="Tim Spiders di {{ $t->nama }}" loading="lazy" class="aspect-[3/2] w-full rounded-[20px] object-cover">
                                @endif
                                <div>
                                    @if ($t->hasil)
                                        <p class="mt-4 flex items-start gap-2 font-display text-[26px] font-extrabold leading-tight text-oranye-500 md:mt-0 lg:text-4xl"><x-icon name="trophy" size="24" class="mt-1 shrink-0" />{{ $t->hasil }}</p>
                                    @elseif ($t->akanDatang())
                                        <p class="mt-4 inline-flex rounded-full bg-oranye-500 px-3 py-1 text-sm font-bold text-ungu-900 md:mt-0">Akan datang</p>
                                    @endif
                                    <p class="mt-2 text-lg font-bold lg:text-xl">{{ $t->nama }}</p>
                                    <p class="mt-1 flex items-center gap-1.5 text-sm text-[#DCD2F5] lg:text-base"><x-icon name="cal" size="16" class="shrink-0" />{{ $t->rentangTanggal() }}</p>
                                    <p class="mt-0.5 flex items-center gap-1.5 text-sm text-[#DCD2F5] lg:text-base"><x-icon name="pin" size="16" class="shrink-0" />{{ $t->lokasi }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        {{-- Galeri foto dan video, dibuka besar di lightbox --}}
        @if ($galeri->isNotEmpty())
        <section id="galeri" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:px-8 lg:py-20"
            x-data="{
                item: @js($dataGaleri),
                buka: null,
                get aktif() { return this.buka === null ? null : this.item[this.buka] },
                lihat(i) { this.buka = i; window.lenis?.stop(); this.$nextTick(() => this.$refs.tutup.focus()) },
                tutup() { this.$refs.video?.pause(); this.buka = null; window.lenis?.start() },
                geser(n) { this.$refs.video?.pause(); this.buka = (this.buka + n + this.item.length) % this.item.length },
            }"
            @keydown.escape.window="buka !== null && tutup()" @keydown.arrow-right.window="buka !== null && geser(1)" @keydown.arrow-left.window="buka !== null && geser(-1)">
            <h2 class="flex flex-wrap items-center gap-x-3 gap-y-1 font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Galeri latihan</h2>
            <p class="mt-2 text-sm text-redup lg:text-base">Foto dan video dari latihan dan pertandingan. Ketuk untuk melihat lebih besar.</p>
            <ul class="mt-5 grid grid-cols-2 gap-2.5 md:grid-cols-4 lg:gap-4">
                @foreach ($dataGaleri as $i => $g)
                    <li @class(['col-span-2 row-span-2' => $i === 0])>
                        <button type="button" @click="lihat({{ $i }})" class="group relative block size-full overflow-hidden rounded-2xl focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-oranye-500" aria-label="Lihat {{ $g['jenis'] }}: {{ $g['keterangan'] }}">
                            <img src="{{ $g['gambar'] }}" alt="" loading="lazy" @class(['size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]', 'aspect-square' => $i > 0, 'aspect-[3/2] md:aspect-auto' => $i === 0])>
                            @if ($g['jenis'] === 'video')
                                <span class="absolute inset-0 flex items-center justify-center bg-black/15">
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
                            <source :src="aktif.url" :type="aktif.url.endsWith('.webm') ? 'video/webm' : 'video/mp4'">
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
        @endif

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
        @if ($tanyaJawab->isNotEmpty())
        <section id="tanya-jawab" class="mx-auto max-w-6xl scroll-mt-4 px-4 py-10 lg:grid lg:grid-cols-[1fr_1.6fr] lg:gap-14 lg:px-8 lg:py-20">
            <div>
                <h2 class="font-display text-[40px] font-extrabold leading-none text-ungu-900 lg:text-5xl">Yang sering ditanyakan</h2>
                <p class="mt-2 text-sm text-redup lg:text-base">Pertanyaan lain bisa langsung ke Admin lewat <a href="{{ $tautanWa }}" target="_blank" rel="noopener" class="font-bold text-ungu-600 underline underline-offset-2">WhatsApp</a>.</p>
            </div>
            <div class="mt-5 divide-y-[1.5px] divide-garis border-y-[1.5px] border-garis lg:mt-0">
                @foreach ($tanyaJawab as $tj)
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-[15px] font-bold lg:text-lg [&::-webkit-details-marker]:hidden">
                            {{ $tj->pertanyaan }}
                            <x-icon name="down" size="20" class="shrink-0 text-ungu-600 transition-transform duration-200 group-open:rotate-180" />
                        </summary>
                        <p class="pb-4 pr-8 text-[15px] leading-relaxed text-ungu-700 lg:text-base">{{ $tj->jawaban }}</p>
                    </details>
                @endforeach
            </div>
        </section>
        @endif

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
