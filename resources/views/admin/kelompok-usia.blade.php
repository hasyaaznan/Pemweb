{{-- Daftar kelompok usia dan iuran --}}
@php use App\Support\Format; @endphp
<x-admin title="Kelompok usia" aktif="Profil" judul="Kelompok usia" :kembali="route('manajemen.kelola')">
    <div class="px-4 lg:max-w-3xl">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-redup">Kelompok calon member dipilih otomatis dari tanggal lahirnya.</p>
            <a href="{{ route('manajemen.kelompok.tambah') }}" class="inline-flex h-9 shrink-0 items-center gap-1 rounded-xl bg-ungu-600 px-3 text-sm font-bold text-white"><x-icon name="plus" size="16" /> Tambah</a>
        </div>
        @error('hapus')<p class="mt-3 rounded-[14px] bg-merah-bg px-3.5 py-3 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
        <div class="mt-3 divide-y divide-garis rounded-[18px] bg-white px-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @forelse ($kelompok as $k)
                <a href="{{ route('manajemen.kelompok.ubah', $k) }}" class="flex items-center gap-3 py-3.5">
                    <span class="w-16 shrink-0 font-display text-2xl font-extrabold text-ungu-600">{{ $k->usia_min }}–{{ $k->usia_max }}</span>
                    <span class="min-w-0 flex-1">
                        <b class="block">{{ $k->nama }} · {{ Format::rupiah($k->iuran_bulanan) }}/bulan</b>
                        <span class="block truncate text-sm text-redup">{{ $k->anak_count }} anak aktif, {{ $k->jadwal_count }} jadwal mingguan</span>
                    </span>
                    <span class="text-sm font-bold text-ungu-600">Ubah</span>
                </a>
            @empty
                <p class="py-3.5 text-sm text-redup">Belum ada kelompok usia.</p>
            @endforelse
        </div>
    </div>
</x-admin>
