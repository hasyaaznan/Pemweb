{{-- L13 Data anak, L14 Orang tua, L11 Dokumen: satu formulir, langkah berganti di layar tanpa memuat ulang --}}
@php
    use App\Http\Controllers\PendaftaranController;
    $langkah1 = ['nama_anak', 'tanggal_lahir', 'jenis_kelamin', 'sekolah'];
    $langkah2 = ['nama_orang_tua', 'hubungan', 'no_whatsapp', 'email', 'alamat'];
    $awal = collect($langkah1)->contains(fn ($k) => $errors->has($k)) ? 1 : (collect($langkah2)->contains(fn ($k) => $errors->has($k)) ? 2 : ($errors->any() ? 3 : 1));
    $kolom = 'h-[50px] w-full rounded-[14px] bg-white text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600';
    $label = 'mb-1.5 mt-3.5 block text-[13px] font-semibold text-ungu-700';
    $salah = 'mt-1 text-sm font-semibold text-merah';
@endphp
<x-layout title="Pendaftaran · Bandung Spiders">
    <form method="POST" action="{{ route('daftar.simpan') }}" enctype="multipart/form-data" novalidate
        class="mx-auto flex min-h-screen max-w-md flex-col px-4 pb-28 lg:my-10 lg:min-h-0 lg:max-w-xl lg:rounded-[28px] lg:bg-white lg:px-10 lg:pb-10 lg:shadow-[0_10px_40px_rgba(34,0,90,.10)]"
        x-data="pendaftaran(@js($kelompok), {{ $awal }})" @submit="kirim($event)">
        @csrf
        <header class="flex h-16 items-center justify-between lg:h-20">
            <button type="button" @click="kembali()" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></button>
            <h1 class="font-display text-[22px] font-extrabold">Pendaftaran</h1>
            <a href="{{ route('landing') }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Batal dan kembali ke beranda"><x-icon name="x" /></a>
        </header>

        <ol class="grid grid-cols-3 gap-1.5" aria-label="Langkah pendaftaran">
            @foreach (['Data anak', 'Orang tua', 'Dokumen'] as $i => $nama)
                <li :aria-current="langkah === {{ $i + 1 }} ? 'step' : null">
                    <span class="block h-[5px] overflow-hidden rounded-full bg-garis"><span class="block h-full origin-left rounded-full bg-oranye-500 transition-transform duration-300" :class="langkah >= {{ $i + 1 }} ? 'scale-x-100' : 'scale-x-0'"></span></span>
                    <span class="mt-1.5 block text-[11px] font-semibold" :class="langkah === {{ $i + 1 }} ? 'text-ungu-600' : 'text-redup'">{{ $i + 1 }}. {{ $nama }}</span>
                </li>
            @endforeach
        </ol>

        {{-- Langkah 1: data anak --}}
        <fieldset x-ref="l1" x-show="langkah === 1">
            <legend class="mt-4 font-display text-[30px] font-extrabold leading-tight">Data anak</legend>
            <p class="text-[13px] text-redup">Semua kolom wajib diisi.</p>

            <label for="nama_anak" class="{{ $label }}">Nama lengkap anak</label>
            <div class="relative"><x-icon name="user" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="nama_anak" name="nama_anak" required maxlength="100" value="{{ old('nama_anak') }}" autocomplete="off" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('nama_anak')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <label for="tanggal_lahir" class="{{ $label }}">Tanggal lahir</label>
            <input id="tanggal_lahir" name="tanggal_lahir" type="date" required max="{{ now()->subDay()->toDateString() }}" x-model="lahir" value="{{ old('tanggal_lahir') }}" class="{{ $kolom }} px-3.5">
            <p class="mt-1 text-sm text-redup" x-show="usia !== null" x-text="usia + ' tahun'"></p>
            @error('tanggal_lahir')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <p class="{{ $label }}">Jenis kelamin</p>
            <div class="inline-flex rounded-xl bg-ungu-100 p-1">
                @foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $nilai => $teks)
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="{{ $nilai }}" required @checked(old('jenis_kelamin', 'L') === $nilai) class="peer sr-only">
                        <span class="flex h-10 w-[120px] items-center justify-center rounded-lg text-sm font-bold text-redup peer-checked:bg-ungu-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-ungu-400">{{ $teks }}</span>
                    </label>
                @endforeach
            </div>

            <p class="{{ $label }}">Kelompok usia (otomatis)</p>
            <div class="flex min-h-[50px] items-center justify-between gap-2 rounded-[14px] bg-oranye-100 px-3.5 py-2.5 text-oranye-700">
                <span class="font-display text-[22px] font-extrabold" x-text="ku ? ku.nama : (usia === null ? 'Isi tanggal lahir' : 'Di luar usia 6–16 tahun')">Isi tanggal lahir</span>
                <span class="text-sm" x-show="ku" x-text="ku ? rupiah(ku.iuran_bulanan) + '/bulan' : ''"></span>
            </div>

            <label for="sekolah" class="{{ $label }}">Sekolah</label>
            <div class="relative"><x-icon name="file" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="sekolah" name="sekolah" required maxlength="100" value="{{ old('sekolah') }}" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('sekolah')<p class="{{ $salah }}">{{ $message }}</p>@enderror
        </fieldset>

        {{-- Langkah 2: orang tua --}}
        <fieldset x-ref="l2" x-cloak x-show="langkah === 2">
            <legend class="mt-4 font-display text-[30px] font-extrabold leading-tight">Data orang tua</legend>
            <p class="text-[13px] text-redup">Nomor WhatsApp dipakai Admin untuk konfirmasi.</p>

            <label for="nama_orang_tua" class="{{ $label }}">Nama orang tua / wali</label>
            <div class="relative"><x-icon name="user" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="nama_orang_tua" name="nama_orang_tua" required maxlength="100" value="{{ old('nama_orang_tua') }}" autocomplete="name" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('nama_orang_tua')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <label for="hubungan" class="{{ $label }}">Hubungan dengan anak</label>
            <select id="hubungan" name="hubungan" required class="{{ $kolom }} px-3.5">
                <option value="">Pilih</option>
                @foreach (PendaftaranController::HUBUNGAN as $h)<option @selected(old('hubungan') === $h)>{{ $h }}</option>@endforeach
            </select>
            @error('hubungan')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <label for="no_whatsapp" class="{{ $label }}">No. WhatsApp</label>
            <div class="relative"><x-icon name="wa" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="no_whatsapp" name="no_whatsapp" type="tel" inputmode="numeric" required pattern="[0-9 +\-]{10,18}" placeholder="08xx xxxx xxxx" value="{{ old('no_whatsapp') }}" autocomplete="tel" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('no_whatsapp')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <label for="email" class="{{ $label }}">Email (untuk login)</label>
            <div class="relative"><x-icon name="file" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="email" name="email" type="email" required maxlength="100" value="{{ old('email') }}" autocomplete="email" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('email')<p class="{{ $salah }}">{{ $message }}</p>@enderror

            <label for="alamat" class="{{ $label }}">Alamat</label>
            <div class="relative"><x-icon name="pin" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-redup" />
                <input id="alamat" name="alamat" required maxlength="255" value="{{ old('alamat') }}" autocomplete="street-address" class="{{ $kolom }} pl-10 pr-3.5"></div>
            @error('alamat')<p class="{{ $salah }}">{{ $message }}</p>@enderror
        </fieldset>

        {{-- Langkah 3: dokumen --}}
        <fieldset x-ref="l3" x-cloak x-show="langkah === 3">
            <legend class="mt-4 font-display text-[30px] font-extrabold leading-tight">Unggah dokumen</legend>
            <p class="mb-3.5 text-[13px] text-redup">Foto atau scan yang jelas, format JPG, PNG, atau PDF, maksimal 2 MB.</p>
            @if ($errors->any())
                <p class="mb-3 rounded-[14px] bg-amber-bg px-3.5 py-2.5 text-[13px] font-semibold text-amber">Periksa kembali isian di atas. Dokumen perlu dipilih ulang setelah pengecekan.</p>
            @endif
            @foreach (PendaftaranController::DOKUMEN as $jenis => $nama)
                <label class="mb-2.5 flex cursor-pointer items-center gap-3 rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]"
                    :class="dok.{{ $jenis }}.salah || (dicoba && !dok.{{ $jenis }}.nama) ? 'ring-2 ring-merah' : ''">
                    <span class="flex size-[42px] shrink-0 items-center justify-center rounded-full" :class="dok.{{ $jenis }}.nama && !dok.{{ $jenis }}.salah ? 'bg-hijau-bg text-hijau' : 'bg-ungu-100 text-ungu-600'">
                        <x-icon name="file" size="20" x-show="!dok.{{ $jenis }}.nama || dok.{{ $jenis }}.salah" /><x-icon name="check" size="20" x-cloak x-show="dok.{{ $jenis }}.nama && !dok.{{ $jenis }}.salah" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <b class="block">{{ $nama }}</b>
                        <span class="block truncate text-sm" :class="dok.{{ $jenis }}.salah ? 'font-semibold text-merah' : 'text-redup'"
                            x-text="dok.{{ $jenis }}.salah ? 'File harus JPG, PNG, atau PDF maks. 2 MB' : (dok.{{ $jenis }}.nama ? dok.{{ $jenis }}.nama + ', terpilih' : 'Belum diunggah')">Belum diunggah</span>
                    </span>
                    <span class="text-sm font-bold" :class="dok.{{ $jenis }}.nama ? 'text-ungu-600' : 'inline-flex items-center gap-1 rounded-full bg-ungu-600 px-2.5 py-1 text-white'" x-text="dok.{{ $jenis }}.nama ? 'Ganti' : 'Unggah'">Unggah</span>
                    <input type="file" name="{{ $jenis }}" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="sr-only" @change="pilih('{{ $jenis }}', $event)">
                </label>
                @error($jenis)<p class="-mt-1.5 mb-2.5 text-sm font-semibold text-merah">{{ $nama }}: {{ $message }}</p>@enderror
            @endforeach
            <p x-cloak x-show="kurang.length" class="flex gap-2 rounded-[14px] bg-merah-bg px-3.5 py-3 text-[13px] font-semibold text-merah"><x-icon name="warn" size="18" class="shrink-0" /><span x-text="kurang.join(', ') + ' belum diunggah'"></span></p>
            <p class="mt-3 rounded-[14px] bg-ungu-100 px-3.5 py-3 text-[13px] leading-snug text-ungu-700">Setelah dikirim, Admin memeriksa dokumen dan menghubungi Anda lewat WhatsApp paling lambat 2 hari kerja.</p>
        </fieldset>

        <div class="fixed inset-x-0 bottom-0 mx-auto flex max-w-md gap-2.5 bg-gradient-to-t from-ungu-50 via-ungu-50 px-4 pb-5 pt-6 lg:static lg:mt-6 lg:max-w-none lg:bg-none lg:p-0">
            <button type="button" x-cloak x-show="langkah > 1" @click="kembali()" class="flex h-[50px] flex-[.6] items-center justify-center rounded-[14px] bg-ungu-100 font-bold text-ungu-600">Kembali</button>
            <button type="button" x-show="langkah < 3" @click="lanjut()" class="flex h-[50px] flex-1 items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Lanjut</button>
            <button type="submit" x-cloak x-show="langkah === 3" class="flex h-[50px] flex-1 items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Kirim pendaftaran</button>
        </div>
    </form>

    <script>
        function pendaftaran(kelompok, awal) {
            const nama = @js(PendaftaranController::DOKUMEN);
            return {
                langkah: awal, lahir: @js(old('tanggal_lahir', '')), dicoba: false,
                dok: Object.fromEntries(Object.keys(nama).map(k => [k, { nama: '', salah: false }])),
                get usia() {
                    if (!this.lahir) return null;
                    const l = new Date(this.lahir + 'T00:00'), n = new Date();
                    let u = n.getFullYear() - l.getFullYear();
                    if (n.getMonth() < l.getMonth() || (n.getMonth() === l.getMonth() && n.getDate() < l.getDate())) u--;
                    return u >= 0 ? u : null;
                },
                get ku() { return this.usia === null ? null : kelompok.find(k => this.usia >= k.usia_min && this.usia <= k.usia_max) || null; },
                get kurang() { return this.dicoba ? Object.keys(nama).filter(k => !this.dok[k].nama).map(k => nama[k]) : []; },
                rupiah: n => 'Rp ' + Number(n).toLocaleString('id-ID'),
                periksa() {
                    for (const el of this.$refs['l' + this.langkah].querySelectorAll('input, select')) {
                        if (!el.checkValidity()) { el.reportValidity(); return false; }
                    }
                    if (this.langkah === 1 && !this.ku) { this.$refs.l1.querySelector('#tanggal_lahir').focus(); return false; }
                    return true;
                },
                lanjut() { if (this.periksa()) { this.langkah++; window.scrollTo(0, 0); } },
                kembali() { this.langkah > 1 ? this.langkah-- : (location.href = @js(route('landing'))); },
                pilih(jenis, e) {
                    const f = e.target.files[0];
                    this.dok[jenis] = f ? { nama: f.name, salah: !/\.(jpe?g|png|pdf)$/i.test(f.name) || f.size > 2 * 1024 * 1024 } : { nama: '', salah: false };
                },
                kirim(e) {
                    this.dicoba = true;
                    if (this.kurang.length || Object.values(this.dok).some(d => d.salah)) e.preventDefault();
                },
            };
        }
    </script>
</x-layout>
