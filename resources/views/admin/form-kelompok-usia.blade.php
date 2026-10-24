{{-- Tambah atau ubah kelompok usia --}}
@php $baru = ! $ku->exists; @endphp
<x-admin :title="$baru ? 'Tambah kelompok' : 'Ubah '.$ku->nama" aktif="Profil" :judul="$baru ? 'Tambah kelompok' : 'Ubah kelompok'" :kembali="route('manajemen.kelompok')">
    <form method="POST" action="{{ $baru ? route('manajemen.kelompok.simpan') : route('manajemen.kelompok.perbarui', $ku) }}" class="px-4 lg:max-w-xl">
        @csrf
        @unless ($baru) @method('PUT') @endunless
        <x-kolom name="nama" label="Nama kelompok" :value="$ku->nama" required maxlength="20" placeholder="KU 6–8" class="mt-0" />
        <div class="grid grid-cols-2 gap-2.5">
            <x-kolom name="usia_min" label="Usia termuda" type="number" :value="$ku->usia_min" required min="3" max="20" />
            <x-kolom name="usia_max" label="Usia tertua" type="number" :value="$ku->usia_max" required min="3" max="20" />
        </div>
        <x-kolom name="iuran_bulanan" label="Iuran per bulan (Rp)" type="number" :value="$ku->iuran_bulanan" required min="0" step="1000"
            bantuan="Tulis angka saja, misalnya 200000. Perubahan berlaku untuk tagihan bulan berikutnya." />
        <x-kolom name="program" label="Isi program" :value="$ku->program" maxlength="100" placeholder="Gerak dasar lewat permainan" bantuan="Tampil di halaman depan di bawah nama kelompok." />
        <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">{{ $baru ? 'Tambah kelompok' : 'Simpan perubahan' }}</button>
    </form>
    @unless ($baru)
        <form method="POST" action="{{ route('manajemen.kelompok.hapus', $ku) }}" class="mt-2.5 px-4 lg:max-w-xl" x-data @submit="if (! confirm('Hapus {{ $ku->nama }}?')) $event.preventDefault()">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-[1.5px] ring-inset ring-merah-bg">Hapus kelompok</button>
        </form>
    @endunless
</x-admin>
