{{-- Kelola data klub: pintu masuk ke semua data yang bisa ditambah, diubah, dan dihapus Admin --}}
<x-admin title="Kelola data" aktif="Profil" judul="Kelola data" :kembali="route('manajemen.profil')">
    <div class="space-y-5 px-4 lg:grid lg:grid-cols-2 lg:gap-6 lg:space-y-0">
        @foreach ($grup as $judul => $isi)
            <section>
                <h2 class="mb-2 font-display text-[22px] font-extrabold text-ungu-700">{{ $judul }}</h2>
                <div class="divide-y divide-garis rounded-[18px] bg-white px-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                    @foreach ($isi as [$url, $ikon, $nama, $ket])
                        <a href="{{ $url }}" class="flex items-center gap-3 py-3.5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-ungu-100 text-ungu-600"><x-icon :name="$ikon" size="20" /></span>
                            <span class="min-w-0 flex-1"><b class="block">{{ $nama }}</b><span class="block text-sm text-redup">{{ $ket }}</span></span>
                            <x-icon name="chev" size="18" class="shrink-0 text-redup" />
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-admin>
