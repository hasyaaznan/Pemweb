{{-- L23 Daftar rapor anak bulan berjalan --}}
@php
    use App\Support\Format;
    $total = $selesai->count() + $belum->count();
    $persen = $total ? round(100 * $selesai->count() / $total) : 0;
@endphp
<x-coach :title="'Rapor '.$periode->translatedFormat('F')" aktif="Rapor" :judul="'Rapor '.$periode->translatedFormat('F')">
    <div class="px-4">
        @if ($kelompok->count() > 1)
            <nav class="mb-3 flex gap-1.5" aria-label="Kelompok usia">
                @foreach ($kelompok as $k)
                    <a href="{{ route('coach.rapor', ['ku' => $k->id]) }}" @class(['rounded-full px-3 py-1.5 text-sm font-bold', 'bg-ungu-600 text-white' => $k->id === $ku->id, 'bg-ungu-100 text-ungu-600' => $k->id !== $ku->id]) @if ($k->id === $ku->id) aria-current="page" @endif>{{ $k->nama }}</a>
                @endforeach
            </nav>
        @endif

        <section class="rounded-[18px] bg-white p-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <div class="flex items-baseline justify-between">
                <h2 class="font-display text-[22px] font-extrabold text-ungu-700">{{ $selesai->count() }} dari {{ $total }} selesai</h2>
                <span class="text-sm text-redup">Batas {{ $periode->copy()->endOfMonth()->translatedFormat('j M') }}</span>
            </div>
            <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-ungu-100"><div class="anim-bar h-full rounded-full bg-oranye-500" style="width: {{ $persen }}%"></div></div>
        </section>

        @if ($belum->isNotEmpty())
            <h3 class="mb-2 mt-4 text-sm text-redup">Belum selesai</h3>
            <ul class="space-y-2">
                @foreach ($belum as $i => $a)
                    @php $isi = $a->rapor_ini?->nilai_rapor_count ?? 0; @endphp
                    <li>
                        <a href="{{ route('coach.rapor.isi', $a) }}" @class(['flex items-center gap-3 rounded-[16px] bg-white px-3 py-2.5', 'ring-2 ring-inset ring-ungu-600' => $i === 0])>
                            <span class="flex size-[38px] shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-lg font-extrabold text-ungu-600">{{ mb_substr($a->nama, 0, 1) }}</span>
                            <span class="min-w-0 flex-1"><b class="block truncate text-[15px]">{{ $a->nama }}</b><span class="text-sm text-redup">{{ $isi ? "$isi dari 5 kategori" : 'Belum mulai' }}</span></span>
                            <span @class(['flex items-center gap-0.5 rounded-full px-3 py-1.5 text-sm font-bold', 'bg-oranye-500 text-ungu-900' => $i === 0, 'bg-ungu-100 text-ungu-600' => $i !== 0])>Isi <x-icon name="chev" size="14" /></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($selesai->isNotEmpty())
            <h3 class="mb-2 mt-4 text-sm text-redup">Selesai, sudah bisa dibaca orang tua</h3>
            <ul class="space-y-2">
                @foreach ($selesai as $a)
                    <li>
                        <a href="{{ route('coach.rapor.isi', $a) }}" class="flex items-center gap-3 rounded-[16px] bg-white px-3 py-2.5">
                            <span class="flex size-[38px] shrink-0 items-center justify-center rounded-full bg-hijau-bg text-hijau"><x-icon name="check" size="18" /></span>
                            <span class="min-w-0 flex-1"><b class="block truncate text-[15px]">{{ $a->nama }}</b><span class="text-sm text-redup">{{ $a->rapor_ini->nilaiRapor->sortBy('kategoriRapor.urutan')->map(fn ($n) => Format::nilai($n->nilai))->implode(', ') }}</span></span>
                            <x-icon name="chev" size="18" class="text-redup" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($total === 0)
            <p class="mt-3 rounded-[18px] bg-white p-4 text-sm text-redup">Belum ada anak aktif di {{ $ku->nama }}.</p>
        @endif
    </div>
</x-coach>
