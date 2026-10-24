{{-- Ubah data member: data anak, status, dan kontak orang tua --}}
@php
    use App\Http\Controllers\PendaftaranController;
    $ortu = $anak->orangTua;
@endphp
<x-admin title="Ubah {{ $anak->nama }}" aktif="Member" judul="Ubah data member" :kembali="route('manajemen.member.detail', $anak)">
    <form method="POST" action="{{ route('manajemen.member.perbarui', $anak) }}" class="px-4 lg:grid lg:grid-cols-2 lg:items-start lg:gap-8">
        @csrf
        @method('PUT')
        <section class="rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Data anak</h2>
            <x-kolom name="nama" label="Nama anak" :value="$anak->nama" required maxlength="100" />
            <x-kolom name="tanggal_lahir" label="Tanggal lahir" type="date" :value="$anak->tanggal_lahir->toDateString()" required />
            <x-kolom name="jenis_kelamin" label="Jenis kelamin" type="select" :pilihan="['L' => 'Laki-laki', 'P' => 'Perempuan']" :value="$anak->jenis_kelamin" required />
            <x-kolom name="sekolah" label="Sekolah" :value="$anak->sekolah" maxlength="100" />
            <x-kolom name="kelompok_usia_id" label="Kelompok usia" type="select" :pilihan="$kelompok->pluck('nama', 'id')->all()" :value="$anak->kelompok_usia_id" required
                bantuan="Pindah kelompok berlaku untuk absensi, rapor, dan tagihan berikutnya." />
            <x-kolom name="status" label="Status" type="select" :pilihan="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif (berhenti latihan)']" :value="$anak->status" required
                bantuan="Anak nonaktif tidak mendapat tagihan baru dan tidak muncul di absensi. Riwayatnya tetap tersimpan." />
        </section>
        <section class="mt-4 rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)] lg:mt-0">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Orang tua</h2>
            <x-kolom name="ortu_nama" label="Nama orang tua" :value="$ortu->nama" required maxlength="100" />
            <x-kolom name="hubungan_orang_tua" label="Hubungan dengan anak" type="select" :pilihan="array_combine(PendaftaranController::HUBUNGAN, PendaftaranController::HUBUNGAN)" :value="$anak->hubungan_orang_tua" required />
            <x-kolom name="ortu_no_hp" label="No. WhatsApp" type="tel" :value="$ortu->no_hp" required inputmode="tel" />
            <x-kolom name="ortu_email" label="Email" type="email" :value="$ortu->email" required maxlength="100" bantuan="Dipakai orang tua untuk masuk ke website." />
            <x-kolom name="ortu_alamat" label="Alamat" type="textarea" :value="$ortu->alamat" maxlength="255" />
            <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Simpan perubahan</button>
        </section>
    </form>
</x-admin>
