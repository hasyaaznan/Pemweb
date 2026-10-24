{{-- Ubah profil dan kata sandi (K2), memakai kerangka sesuai peran --}}
@php
    use App\Http\Controllers\ProfilController;
    $kerangka = match ($user->peran) { 'orang_tua' => 'ortu', 'coach' => 'coach', default => 'admin' };
@endphp
<x-dynamic-component :component="$kerangka" title="Ubah profil" aktif="Profil" judul="Ubah profil" :kembali="route(ProfilController::ruteProfil($user))">
    <div class="px-4 lg:grid lg:max-w-4xl lg:grid-cols-2 lg:items-start lg:gap-8">
        <form method="POST" action="{{ route('profil.perbarui') }}" class="rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @csrf
            @method('PUT')
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Data diri</h2>
            <x-kolom name="nama" label="Nama" :value="$user->nama" required maxlength="100" autocomplete="name" />
            <x-kolom name="no_hp" label="No. WhatsApp" type="tel" :value="$user->no_hp" required inputmode="tel" autocomplete="tel" bantuan="Bisa dipakai untuk masuk, selain email." />
            <x-kolom name="email" label="Email" type="email" :value="$user->email" required maxlength="100" autocomplete="email" />
            @if ($user->isOrangTua())
                <x-kolom name="alamat" label="Alamat" type="textarea" :value="$user->alamat" required maxlength="255" />
            @endif
            <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Simpan profil</button>
        </form>

        <form method="POST" action="{{ route('profil.sandi') }}" class="mt-4 rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)] lg:mt-0">
            @csrf
            @method('PUT')
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Ganti kata sandi</h2>
            <x-kolom name="sandi_lama" label="Kata sandi sekarang" type="password" required autocomplete="current-password" />
            <x-kolom name="password" label="Kata sandi baru" type="password" required minlength="8" autocomplete="new-password" bantuan="Minimal 8 karakter." />
            <x-kolom name="password_confirmation" label="Ulangi kata sandi baru" type="password" required minlength="8" autocomplete="new-password" />
            <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-ungu-600 font-bold text-white">Ganti kata sandi</button>
        </form>
    </div>
</x-dynamic-component>
