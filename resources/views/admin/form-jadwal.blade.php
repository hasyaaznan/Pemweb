{{-- Tambah atau ubah jadwal mingguan --}}
@php
    $baru = ! $jadwal->exists;
    $kolom = 'h-[50px] w-full rounded-[14px] bg-white px-3.5 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600';
    $label = 'mb-1.5 mt-4 block text-[13px] font-semibold text-ungu-700';
@endphp
<x-admin :title="$baru ? 'Tambah jadwal' : 'Ubah jadwal'" aktif="Beranda" :judul="$baru ? 'Tambah jadwal' : 'Ubah jadwal'" :kembali="route('manajemen.jadwal')">
    <form method="POST" action="{{ $baru ? route('manajemen.jadwal.simpan') : route('manajemen.jadwal.perbarui', $jadwal) }}" class="px-4">
        @csrf
        @unless ($baru) @method('PUT') @endunless

        <label for="kelompok_usia_id" class="mb-1.5 block text-[13px] font-semibold text-ungu-700">Kelompok usia</label>
        <select id="kelompok_usia_id" name="kelompok_usia_id" required class="{{ $kolom }} pr-11">
            <option value="">Pilih kelompok</option>
            @foreach ($kelompok as $k)
                <option value="{{ $k->id }}" @selected((int) old('kelompok_usia_id', $jadwal->kelompok_usia_id) === $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        @error('kelompok_usia_id')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <label for="hari" class="{{ $label }}">Hari</label>
        <select id="hari" name="hari" required class="{{ $kolom }} pr-11">
            @foreach ($hari as $no => $nama)
                <option value="{{ $no }}" @selected((int) old('hari', $jadwal->hari) === $no)>{{ $nama }}</option>
            @endforeach
        </select>
        @error('hari')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <label for="jam_mulai" class="{{ $label }}">Jam mulai</label>
                <input id="jam_mulai" name="jam_mulai" type="time" required value="{{ old('jam_mulai', $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '') }}" class="{{ $kolom }}">
            </div>
            <div>
                <label for="jam_selesai" class="{{ $label }}">Jam selesai</label>
                <input id="jam_selesai" name="jam_selesai" type="time" required value="{{ old('jam_selesai', $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '') }}" class="{{ $kolom }}">
            </div>
        </div>
        @error('jam_mulai')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
        @error('jam_selesai')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <label for="lokasi" class="{{ $label }}">Tempat</label>
        <input id="lokasi" name="lokasi" type="text" required maxlength="100" value="{{ old('lokasi', $jadwal->lokasi) }}" class="{{ $kolom }}">
        @error('lokasi')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <label for="berlaku_mulai" class="{{ $label }}">Berlaku mulai</label>
        <input id="berlaku_mulai" name="berlaku_mulai" type="date" required value="{{ old('berlaku_mulai', $jadwal->berlaku_mulai?->toDateString()) }}" class="{{ $kolom }}">
        @error('berlaku_mulai')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        @unless ($baru)
            <p class="mt-4 rounded-[14px] bg-ungu-100 px-3.5 py-3 text-[13px] text-ungu-700">Perubahan berlaku untuk semua minggu. Latihan yang sudah diabsen atau sudah diubah per tanggal tidak ikut berubah.</p>
        @endunless

        <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">{{ $baru ? 'Tambah jadwal' : 'Simpan perubahan' }}</button>
    </form>

    @unless ($baru)
        <form method="POST" action="{{ route('manajemen.jadwal.hapus', $jadwal) }}" class="mt-2.5 px-4" x-data @submit="if (! confirm('Hapus jadwal {{ $jadwal->kelompokUsia->nama }} hari {{ $hari[$jadwal->hari] }}? Latihan di hari itu tidak muncul lagi.')) $event.preventDefault()">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-[1.5px] ring-inset ring-merah-bg">Hapus jadwal</button>
        </form>
    @endunless
</x-admin>
