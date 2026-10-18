{{-- L20 Beranda Admin --}}
@php
    use App\Support\Format;
    use Illuminate\Support\Carbon;
    $perubahan = $sesiMingguIni->where('status', '!=', 'terjadwal');
    $tugas = [
        [$buktiMenunggu, 'Bukti iuran menunggu', 'Periksa paling lambat '.\App\Support\Klub::get('batas_verifikasi_hari_kerja', '2').' hari kerja', 'text-oranye-700', route('manajemen.verifikasi')],
        [$belumBayar, 'Belum bayar '.$bulanIni->translatedFormat('F'),
            $jatuhTempo && Carbon::parse($jatuhTempo)->isPast() ? 'Lewat jatuh tempo '.Carbon::parse($jatuhTempo)->translatedFormat('j M').', kirim pengingat' : ($jatuhTempo ? 'Jatuh tempo '.Carbon::parse($jatuhTempo)->translatedFormat('j M') : 'Tagihan bulan ini belum dibuat'),
            'text-merah', route('manajemen.member', ['iuran' => 'belum'])],
        [$pendaftarBaru, 'Pendaftar baru', 'Cek dokumen, lalu hubungi lewat WhatsApp', 'text-ungu-600', route('manajemen.pendaftaran')],
    ];
@endphp
<x-admin title="Beranda" aktif="Beranda">
    <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 lg:rounded-[28px] lg:px-8 px-4 pb-16 pt-6 text-white">
        <x-court />
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-[13px] text-white/75">{{ now()->translatedFormat('l, j F Y') }}</p>
                <h1 class="mt-1 font-display text-[34px] font-extrabold leading-none">Halo, {{ strtok(auth()->user()->nama, ' ') }}</h1>
            </div>
            <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-14 object-contain">
        </div>
    </header>

    <div class="relative -mt-10 px-4 lg:px-8">
        <div class="grid grid-cols-2 gap-2.5 lg:gap-4">
            <a href="{{ route('manajemen.member') }}" class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                <p class="font-display text-[34px] font-extrabold leading-none text-ungu-700">{{ $memberAktif }}</p>
                <p class="mt-1 text-sm text-redup">Member aktif</p>
            </a>
            <div class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                <p class="font-display text-[34px] font-extrabold leading-none text-ungu-700">{{ $persenLunas === null ? '-' : $persenLunas.'%' }}</p>
                <p class="mt-1 text-sm text-redup">Iuran {{ $bulanLalu->translatedFormat('F') }} lunas</p>
            </div>
        </div>

        @if ($belumDitagih > 0)
            {{-- Muncul jika tagihan otomatis tanggal 1 belum berjalan, misalnya cron server belum dipasang --}}
            <form method="POST" action="{{ route('manajemen.tagihan.buat') }}" class="mt-3 rounded-[18px] bg-amber-bg p-3.5 text-amber">
                @csrf
                <p class="flex gap-2 text-sm font-semibold"><x-icon name="warn" size="18" class="shrink-0" /> {{ $belumDitagih }} anak aktif belum punya tagihan {{ $bulanIni->translatedFormat('F') }}.</p>
                <button type="submit" class="mt-2.5 flex h-10 w-full items-center justify-center rounded-xl bg-ungu-600 text-[15px] font-bold text-white">Buat tagihan {{ $bulanIni->translatedFormat('F') }}</button>
            </form>
        @endif

        <div class="lg:mt-3 lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
        <div>
        <h2 class="mb-2 mt-5 font-display text-[22px] font-extrabold text-ungu-700">Perlu tindakan</h2>
        <div class="divide-y divide-garis rounded-[18px] bg-white px-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @foreach ($tugas as [$angka, $judul, $ket, $warna, $tautan])
                <a href="{{ $tautan }}" class="flex items-center gap-3 py-3">
                    <span class="w-10 font-display text-[34px] font-extrabold leading-none {{ $angka ? $warna : 'text-perak-400' }}">{{ $angka }}</span>
                    <span class="min-w-0 flex-1">
                        <b class="block">{{ $judul }}</b>
                        <span class="block text-sm text-redup">{{ $ket }}</span>
                    </span>
                    <x-icon name="chev" size="20" class="text-redup" />
                </a>
            @endforeach
        </div>

        </div>

        <div>
        <h2 class="mb-2 mt-5 font-display text-[22px] font-extrabold text-ungu-700">Jadwal latihan</h2>
        <a href="{{ route('manajemen.jadwal') }}" class="flex items-center gap-3 rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-ungu-100 text-ungu-600"><x-icon name="cal" /></span>
            <span class="min-w-0 flex-1">
                <b class="block">{{ $sesiMingguIni->where('status', '!=', 'batal')->count() }} latihan dalam 7 hari ke depan</b>
                <span class="block text-sm text-redup">{{ $perubahan->isEmpty() ? 'Semua sesuai jadwal mingguan' : $perubahan->count().' latihan diubah atau batal' }}</span>
            </span>
            <x-icon name="chev" size="20" class="text-redup" />
        </a>
        @foreach ($perubahan as $s)
            <p class="mt-2 flex gap-2 rounded-[14px] bg-amber-bg px-3.5 py-3 text-[13px] font-semibold text-amber">
                <x-icon name="warn" size="18" class="shrink-0" />
                <span>{{ $s->tanggal->translatedFormat('l j M') }}: {{ $s->kelompokUsia->nama }} {{ $s->status === 'batal' ? 'batal' : 'mulai '.Format::jam($s->jam_mulai) }}@if ($s->alasan_perubahan) ({{ $s->alasan_perubahan }})@endif</span>
            </p>
        @endforeach
        </div>
        </div>
    </div>
</x-admin>
