{{-- Ubah atau batalkan latihan pada satu tanggal (Admin dan coach) --}}
@php
    use App\Support\Format;
    $admin = $peran === 'manajemen';
    $rute = $admin ? 'manajemen.sesi.simpan' : 'coach.sesi.simpan';
    $kembali = $admin ? route('manajemen.jadwal') : route('coach.jadwal', ['tanggal' => $sesi->tanggal->toDateString()]);
    $ku = $sesi->kelompokUsia;
    $kolom = 'h-[50px] w-full rounded-[14px] bg-white px-3.5 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600';
    $label = 'mb-1.5 mt-4 block text-[13px] font-semibold text-ungu-700';
    $aksiAwal = old('aksi', 'ubah');
@endphp
<x-dynamic-component :component="$admin ? 'admin' : 'coach'" title="Ubah latihan" :aktif="$admin ? 'Beranda' : 'Jadwal'" judul="Ubah latihan" :kembali="$kembali">
    <div class="px-4" x-data="{ aksi: @js($aksiAwal) }">
        <div class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <p class="font-display text-[26px] font-extrabold leading-none">{{ $ku->nama }}</p>
            <p class="mt-1 text-[15px]">{{ $sesi->tanggal->translatedFormat('l, j F Y') }}</p>
            <p class="mt-0.5 text-sm text-redup">{{ Format::jam($sesi->jam_mulai) }}–{{ Format::jam($sesi->jam_selesai) }}, {{ $sesi->lokasi }}</p>
            @if ($sesi->status !== 'terjadwal')
                <p class="mt-2 rounded-xl bg-amber-bg px-3 py-2 text-[13px] font-semibold text-amber">{{ $sesi->status === 'batal' ? 'Sudah dibatalkan' : 'Sudah diubah' }}: {{ $sesi->alasan_perubahan }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route($rute, [$ku, $sesi->tanggal->toDateString()]) }}">
            @csrf
            @method('PUT')

            <fieldset class="mt-4 grid grid-cols-2 gap-2">
                <legend class="mb-1.5 text-[13px] font-semibold text-ungu-700">Yang ingin dilakukan</legend>
                <label class="cursor-pointer">
                    <input type="radio" name="aksi" value="ubah" x-model="aksi" class="peer sr-only">
                    <span class="flex h-12 items-center justify-center rounded-[14px] bg-white text-[15px] font-bold text-ungu-600 ring-[1.5px] ring-inset ring-garis peer-checked:bg-ungu-600 peer-checked:text-white peer-checked:ring-ungu-600 peer-focus-visible:ring-2">Ubah jam/tempat</span>
                </label>
                <label @class(['cursor-pointer', 'opacity-50' => $adaAbsensi])>
                    <input type="radio" name="aksi" value="batal" x-model="aksi" class="peer sr-only" @disabled($adaAbsensi)>
                    <span class="flex h-12 items-center justify-center rounded-[14px] bg-white text-[15px] font-bold text-merah ring-[1.5px] ring-inset ring-garis peer-checked:bg-merah peer-checked:text-white peer-checked:ring-merah peer-focus-visible:ring-2">Batalkan latihan</span>
                </label>
            </fieldset>
            @if ($adaAbsensi)<p class="mt-1.5 text-sm text-redup">Absensi latihan ini sudah diisi, jadi latihan tidak bisa dibatalkan.</p>@endif
            @error('aksi')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

            <div x-show="aksi === 'ubah'">
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label for="jam_mulai" class="{{ $label }}">Jam mulai</label>
                        <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', substr($sesi->jam_mulai, 0, 5)) }}" class="{{ $kolom }}" :required="aksi === 'ubah'">
                    </div>
                    <div>
                        <label for="jam_selesai" class="{{ $label }}">Jam selesai</label>
                        <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', substr($sesi->jam_selesai, 0, 5)) }}" class="{{ $kolom }}" :required="aksi === 'ubah'">
                    </div>
                </div>
                @error('jam_mulai')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
                @error('jam_selesai')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

                <label for="lokasi" class="{{ $label }}">Tempat</label>
                <input id="lokasi" name="lokasi" type="text" maxlength="100" value="{{ old('lokasi', $sesi->lokasi) }}" class="{{ $kolom }}" :required="aksi === 'ubah'">
                @error('lokasi')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
            </div>

            <label for="alasan" class="{{ $label }}">Alasan (wajib, dibaca orang tua)</label>
            <input id="alasan" name="alasan" type="text" required maxlength="150" value="{{ old('alasan') }}" placeholder="Contoh: GOR dipakai acara pagi" class="{{ $kolom }}">
            @error('alasan')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

            <button type="submit" :class="aksi === 'batal' ? 'bg-merah text-white' : 'bg-oranye-500 text-ungu-900'" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] font-bold"
                x-text="aksi === 'batal' ? 'Batalkan dan beri tahu orang tua' : 'Simpan dan beri tahu orang tua'">Simpan dan beri tahu orang tua</button>
        </form>

        @if ($sesi->status !== 'terjadwal')
            <form method="POST" action="{{ route($rute, [$ku, $sesi->tanggal->toDateString()]) }}" class="mt-2.5">
                @csrf
                @method('PUT')
                <input type="hidden" name="aksi" value="kembalikan">
                <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-white font-bold text-ungu-600 ring-[1.5px] ring-inset ring-garis">Kembalikan ke jadwal mingguan</button>
            </form>
        @endif
    </div>
</x-dynamic-component>
