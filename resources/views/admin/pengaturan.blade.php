{{-- Pengaturan klub dan teks utama halaman depan --}}
@php use App\Http\Controllers\Admin\PengaturanController as P; @endphp
<x-admin title="Pengaturan klub" aktif="Profil" judul="Pengaturan klub" :kembali="route('manajemen.kelola')">
    <form method="POST" enctype="multipart/form-data" action="{{ route('manajemen.pengaturan.simpan') }}" class="px-4 lg:grid lg:grid-cols-2 lg:items-start lg:gap-8">
        @csrf
        @method('PUT')
        <section class="rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Rekening dan kontak</h2>
            <p class="mt-1 text-sm text-redup">Rekening tampil di halaman tagihan orang tua. WhatsApp dan alamat tampil di halaman depan.</p>
            @foreach (P::KLUB as $kunci => [$label])
                <x-kolom :name="$kunci" :label="$label" :value="$nilai[$kunci] ?? null" required
                    :type="in_array($kunci, ['tanggal_jatuh_tempo', 'batas_verifikasi_hari_kerja']) ? 'number' : ($kunci === 'whatsapp_admin' ? 'tel' : ($kunci === 'alamat_gor' ? 'textarea' : 'text'))"
                    :bantuan="['whatsapp_admin' => 'Nomor yang dihubungi calon member dan orang tua.', 'alamat_gor' => 'Kata sebelum koma pertama dipakai sebagai nama tempat, misalnya GOR Rabbani.', 'tanggal_jatuh_tempo' => 'Berlaku untuk tagihan yang dibuat berikutnya.'][$kunci] ?? null" />
            @endforeach
        </section>
        <section id="halaman-depan" class="mt-4 scroll-mt-24 rounded-[18px] bg-white p-4 pt-1 shadow-[0_2px_10px_rgba(34,0,90,.06)] lg:mt-0">
            <h2 class="mt-3 font-display text-[22px] font-extrabold text-ungu-700">Halaman depan</h2>
            @foreach (P::FOTO as $kunci => $label)
                @if (! empty($nilai[$kunci]))
                    <img src="{{ asset($nilai[$kunci]) }}" alt="{{ $label }} sekarang" class="mt-4 aspect-[16/9] w-full rounded-[14px] object-cover">
                @endif
                <x-kolom :name="$kunci" :label="(empty($nilai[$kunci]) ? '' : 'Ganti ').lcfirst($label)" type="file" accept="image/jpeg,image/png,image/webp"
                    bantuan="JPG, PNG, atau WebP, maksimal 4 MB. Pakai foto mendatar dengan lebar minimal 1600 px." :class="empty($nilai[$kunci]) ? '' : 'mt-2'" />
            @endforeach
            <x-kolom name="tentang_judul" label="Judul bagian Tentang klub" :value="$nilai['tentang_judul'] ?? null" required maxlength="80" />
            <x-kolom name="tentang_cerita" label="Cerita singkat klub" type="textarea" :value="$nilai['tentang_cerita'] ?? null" required maxlength="600" bantuan="Maksimal 600 huruf. Tulis apa adanya: sejak kapan, latihan di mana, untuk siapa." />
            <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Simpan pengaturan</button>
        </section>
    </form>
</x-admin>
