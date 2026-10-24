{{-- Daftar akun coach --}}
<x-admin title="Coach" aktif="Profil" judul="Coach" :kembali="route('manajemen.kelola')">
    <div class="px-4 lg:max-w-3xl">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-redup">Coach masuk dengan email atau No. WhatsApp-nya sendiri.</p>
            <a href="{{ route('manajemen.coach.tambah') }}" class="inline-flex h-9 shrink-0 items-center gap-1 rounded-xl bg-ungu-600 px-3 text-sm font-bold text-white"><x-icon name="plus" size="16" /> Tambah</a>
        </div>
        <div class="mt-3 divide-y divide-garis rounded-[18px] bg-white px-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @forelse ($coach as $c)
                <a href="{{ route('manajemen.coach.ubah', $c) }}" @class(['flex items-center gap-3 py-3.5', 'opacity-60' => ! $c->aktif])>
                    @if ($c->foto)
                        <img src="{{ asset($c->foto) }}" alt="" class="size-12 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-xl font-extrabold text-ungu-600">{{ mb_substr($c->nama, 0, 1) }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <b class="block">{{ $c->nama }}@unless ($c->aktif) <span class="text-sm font-semibold text-merah">· Nonaktif</span>@endunless</b>
                        <span class="block truncate text-sm text-redup">{{ $c->kelompokDipegang->pluck('nama')->implode(', ') ?: 'Belum memegang kelompok' }}</span>
                        @if ($c->tampil_di_landing && $c->aktif)<span class="block text-[13px] font-semibold text-hijau">Tampil di halaman depan</span>@endif
                    </span>
                    <span class="text-sm font-bold text-ungu-600">Ubah</span>
                </a>
            @empty
                <p class="py-3.5 text-sm text-redup">Belum ada coach.</p>
            @endforelse
        </div>
    </div>
</x-admin>
