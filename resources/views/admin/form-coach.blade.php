{{-- Tambah atau ubah coach --}}
@php
    $baru = ! $coach->exists;
    $dipegang = collect(old('kelompok', $coach->exists ? $coach->kelompokDipegang->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $centang = 'size-5 rounded accent-ungu-600';
@endphp
<x-admin :title="$baru ? 'Tambah coach' : 'Ubah '.$coach->nama" aktif="Profil" :judul="$baru ? 'Tambah coach' : 'Ubah coach'" :kembali="route('manajemen.coach')">
    <form method="POST" enctype="multipart/form-data" action="{{ $baru ? route('manajemen.coach.simpan') : route('manajemen.coach.perbarui', $coach) }}" class="px-4 lg:grid lg:grid-cols-2 lg:items-start lg:gap-8">
        @csrf
        @unless ($baru) @method('PUT') @endunless
        <section class="rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Akun</h2>
            <x-kolom name="nama" label="Nama" :value="$coach->nama" required maxlength="100" />
            <x-kolom name="no_hp" label="No. WhatsApp" type="tel" :value="$coach->no_hp" required inputmode="tel" />
            <x-kolom name="email" label="Email" type="email" :value="$coach->email" required maxlength="100"
                :bantuan="$baru ? 'Tautan untuk membuat kata sandi dikirim ke email ini.' : null" />
            <fieldset class="mt-4">
                <legend class="mb-1.5 text-[13px] font-semibold text-ungu-700">Kelompok yang dipegang</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($kelompok as $k)
                        <label class="flex h-11 items-center gap-2 rounded-xl bg-ungu-50 px-3 font-semibold has-[:checked]:bg-ungu-100 has-[:checked]:text-ungu-700">
                            <input type="checkbox" name="kelompok[]" value="{{ $k->id }}" @checked($dipegang->contains($k->id)) class="{{ $centang }}"> {{ $k->nama }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-[13px] text-redup">Coach hanya bisa mengisi absensi dan rapor anak di kelompok ini.</p>
            </fieldset>
            @unless ($baru)
                <label class="mt-4 flex items-start gap-3 rounded-[14px] bg-ungu-50 p-3.5">
                    <input type="hidden" name="aktif" value="0">
                    <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $coach->aktif)) class="{{ $centang }} mt-0.5">
                    <span><b class="block">Akun aktif</b><span class="text-sm text-redup">Hilangkan centang kalau coach berhenti. Ia tidak bisa masuk lagi, tetapi rapor yang ia tulis tetap ada.</span></span>
                </label>
            @endunless
        </section>
        <section class="mt-4 rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)] lg:mt-0">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Di halaman depan</h2>
            <label class="mt-3 flex items-start gap-3">
                <input type="hidden" name="tampil_di_landing" value="0">
                <input type="checkbox" name="tampil_di_landing" value="1" @checked(old('tampil_di_landing', $coach->tampil_di_landing)) class="{{ $centang }} mt-0.5">
                <span><b class="block">Tampilkan di bagian Coach kami</b><span class="text-sm text-redup">Nama, jabatan, lisensi, foto, dan kelompok yang dipegang.</span></span>
            </label>
            <x-kolom name="jabatan" label="Jabatan" :value="$coach->jabatan" maxlength="60" placeholder="Kepala pelatih" />
            <x-kolom name="lisensi" label="Lisensi atau sertifikat" :value="$coach->lisensi" maxlength="100" placeholder="Lisensi pelatih Perbasi C" />
            @if ($coach->foto)
                <div class="mt-4 flex items-center gap-3">
                    <img src="{{ asset($coach->foto) }}" alt="Foto {{ $coach->nama }}" class="size-16 rounded-full object-cover">
                    <label class="flex items-center gap-2 text-sm font-semibold text-merah"><input type="checkbox" name="hapus_foto" value="1" class="{{ $centang }}"> Hapus foto</label>
                </div>
            @endif
            <x-kolom name="foto" :label="$coach->foto ? 'Ganti foto' : 'Foto'" type="file" accept="image/jpeg,image/png,image/webp" bantuan="JPG, PNG, atau WebP, maksimal 2 MB. Foto wajah dengan latar polos paling bagus." />
            <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">{{ $baru ? 'Tambah coach' : 'Simpan perubahan' }}</button>
        </section>
    </form>
</x-admin>
