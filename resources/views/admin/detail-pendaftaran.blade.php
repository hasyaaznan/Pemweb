{{-- Detail pendaftar: data anak, orang tua, 3 dokumen, lalu Terima atau Tolak --}}
@php
    use App\Http\Controllers\PendaftaranController;
    use App\Support\Format;
    $wa = preg_replace('/^0/', '62', $p->no_whatsapp);
    $sapa = 'Halo '.$p->nama_orang_tua.', ';
    $pesan = match ($p->status) {
        'diterima' => $sapa.'pendaftaran '.$p->nama_anak.' di Bandung Spiders sudah kami terima ('.$p->kelompokUsia->nama.'). Kata sandi untuk masuk ke website bisa dibuat lewat tautan yang kami kirim ke '.$p->email.'. Jika email tidak masuk, buka '.route('password.request').' lalu isi nomor WhatsApp ini. Sampai jumpa di latihan.',
        'ditolak' => $sapa.'pendaftaran '.$p->nama_anak.' di Bandung Spiders belum bisa kami terima. Alasannya: '.$p->alasan_tolak.' Silakan daftar ulang di '.route('daftar').' setelah diperbaiki.',
        default => $sapa.'kami dari Bandung Spiders, terkait pendaftaran '.$p->nama_anak.' ('.PendaftaranController::nomor($p).').',
    };
    $baris = 'flex justify-between gap-3 py-2.5';
@endphp
<x-admin :title="$p->nama_anak" aktif="Member" judul="Pendaftar" :kembali="route('manajemen.pendaftaran')">
    <div class="space-y-3 px-4 pb-24" x-data="{ tolak: {{ $errors->has('alasan') ? 'true' : 'false' }}, alasan: @js(old('alasan', '')) }">
        <div class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <p class="text-[13px] text-redup">{{ PendaftaranController::nomor($p) }}, dikirim {{ $p->created_at->translatedFormat('j M Y, H.i') }}</p>
            <p class="mt-0.5 font-display text-[26px] font-extrabold leading-none">{{ $p->nama_anak }}</p>
            <p class="mt-1 text-sm text-redup">{{ $p->kelompokUsia->nama }}, {{ $p->tanggal_lahir->age }} tahun, {{ Format::rupiah($p->kelompokUsia->iuran_bulanan) }}/bulan</p>
        </div>

        <dl class="divide-y divide-garis rounded-[18px] bg-white px-4 text-[15px] shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <div class="{{ $baris }}"><dt class="text-redup">Tanggal lahir</dt><dd class="font-bold">{{ $p->tanggal_lahir->translatedFormat('j F Y') }}</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">Jenis kelamin</dt><dd class="font-bold">{{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">Sekolah</dt><dd class="text-right font-bold">{{ $p->sekolah }}</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">Orang tua</dt><dd class="text-right font-bold">{{ $p->nama_orang_tua }} ({{ $p->hubungan }})</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">No. WhatsApp</dt><dd class="font-bold">{{ Format::tampilHp($p->no_whatsapp) }}</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">Email</dt><dd class="truncate font-bold">{{ $p->email }}</dd></div>
            <div class="{{ $baris }}"><dt class="text-redup">Alamat</dt><dd class="text-right font-bold">{{ $p->alamat }}</dd></div>
        </dl>

        <h2 class="pt-1 font-display text-[22px] font-extrabold text-ungu-700">Dokumen</h2>
        <div class="grid grid-cols-3 gap-2">
            @foreach (PendaftaranController::DOKUMEN as $jenis => $nama)
                @php $d = $p->dokumenPendaftaran->firstWhere('jenis', $jenis); @endphp
                @if ($d)
                    <a href="{{ route('manajemen.pendaftaran.dokumen', $d) }}" target="_blank" rel="noopener" class="overflow-hidden rounded-[14px] bg-white shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                        @if ($d->file_tipe === 'pdf')
                            <span class="flex aspect-[3/4] items-center justify-center bg-ungu-50 text-ungu-600"><x-icon name="file" size="34" /></span>
                        @else
                            <img src="{{ route('manajemen.pendaftaran.dokumen', $d) }}" alt="{{ $nama }} {{ $p->nama_anak }}" class="aspect-[3/4] w-full bg-ungu-50 object-cover">
                        @endif
                        <span class="block px-2 py-1.5 text-xs font-bold">{{ $nama }}</span>
                    </a>
                @else
                    <span class="flex aspect-[3/4] items-center justify-center rounded-[14px] bg-merah-bg p-2 text-center text-xs font-bold text-merah">{{ $nama }} tidak ada</span>
                @endif
            @endforeach
        </div>
        <p class="text-[13px] text-redup">Ketuk dokumen untuk membukanya dalam ukuran penuh.</p>

        @error('terima')<p class="rounded-[14px] bg-merah-bg px-3.5 py-3 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        @if ($p->status !== 'menunggu_verifikasi')
            <p @class(['rounded-[14px] px-3.5 py-3 text-sm font-semibold', 'bg-hijau-bg text-hijau' => $p->status === 'diterima', 'bg-merah-bg text-merah' => $p->status === 'ditolak'])>
                {{ $p->status === 'diterima' ? 'Diterima' : 'Ditolak' }} oleh {{ $p->diverifikasiOleh?->nama ?? 'Admin' }}, {{ $p->diverifikasi_pada?->translatedFormat('j M, H.i') }}@if ($p->alasan_tolak). Alasan: {{ $p->alasan_tolak }}@endif
            </p>
            @if ($p->anak)
                <a href="{{ route('manajemen.member.detail', $p->anak) }}" class="flex items-center gap-3 rounded-[18px] bg-white px-4 py-3.5 font-bold">Lihat data member <x-icon name="chev" class="ml-auto text-redup" /></a>
            @endif
        @endif

        <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode($pesan) }}" target="_blank" rel="noopener"
            @class(['flex h-[50px] items-center justify-center gap-2 rounded-[14px] font-bold', 'bg-oranye-500 text-ungu-900' => $p->status !== 'menunggu_verifikasi', 'bg-white text-ungu-600 ring-[1.5px] ring-inset ring-garis' => $p->status === 'menunggu_verifikasi'])>
            <x-icon name="wa" size="20" /> {{ $p->status === 'menunggu_verifikasi' ? 'Hubungi lewat WhatsApp' : 'Kabari lewat WhatsApp' }}
        </a>

        @if ($p->status === 'menunggu_verifikasi')
            <div class="fixed inset-x-0 bottom-[90px] mx-auto flex max-w-md gap-2.5 px-4">
                <button type="button" @click="tolak = true" class="flex h-[50px] flex-[.6] items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-2 ring-inset ring-merah">Tolak</button>
                <form method="POST" action="{{ route('manajemen.pendaftaran.terima', $p) }}" class="flex flex-1">
                    @csrf
                    <button type="submit" class="flex h-[50px] w-full items-center justify-center gap-1.5 rounded-[14px] bg-oranye-500 font-bold text-ungu-900"><x-icon name="check" size="18" /> Terima</button>
                </form>
            </div>

            <div x-cloak x-show="tolak" class="fixed inset-0 z-40" role="dialog" aria-modal="true" aria-labelledby="judul-tolak" @keydown.escape.window="tolak = false">
                <div x-show="tolak" x-transition.opacity.duration.280ms class="absolute inset-0 bg-ungu-900/45" @click="tolak = false"></div>
                <form method="POST" action="{{ route('manajemen.pendaftaran.tolak', $p) }}" x-show="tolak"
                    x-transition:enter="transition duration-[280ms] ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                    x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                    class="absolute inset-x-0 bottom-0 mx-auto max-w-md rounded-t-[28px] bg-white px-4 pb-5 pt-2.5">
                    @csrf
                    <span class="mx-auto mb-3 block h-[5px] w-11 rounded-full bg-garis"></span>
                    <h2 id="judul-tolak" class="font-display text-[28px] font-extrabold">Tolak pendaftaran ini?</h2>
                    <p class="mb-3 mt-1 text-sm text-redup">Alasan ini masuk ke pesan WhatsApp untuk pendaftar.</p>
                    <label for="alasan" class="mb-1.5 block text-[13px] font-semibold text-ungu-700">Alasan (wajib)</label>
                    <textarea id="alasan" name="alasan" rows="3" maxlength="200" x-model="alasan" required placeholder="Contoh: foto Kartu Keluarga tidak terbaca."
                        class="w-full rounded-[14px] bg-white px-3.5 py-3 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600"></textarea>
                    @error('alasan')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
                    <div class="mt-3.5 flex gap-2.5">
                        <button type="button" @click="tolak = false" class="flex h-[50px] flex-[.7] items-center justify-center rounded-[14px] bg-ungu-100 font-bold text-ungu-600">Batal</button>
                        <button type="submit" :disabled="alasan.trim() === ''" class="flex h-[50px] flex-1 items-center justify-center rounded-[14px] bg-merah font-bold text-white disabled:opacity-40">Tolak pendaftaran</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-admin>
