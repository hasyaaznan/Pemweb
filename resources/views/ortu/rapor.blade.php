{{-- L2 Detail rapor --}}
<x-ortu :title="'Rapor '.$anak->nama" aktif="Rapor" :judul="'Rapor '.strtok($anak->nama, ' ')" :kembali="route('ortu.beranda')">
    <x-tab-rapor aktif="rapor" :persen="$kehadiran['persen']" />

    @if ($rapor)
        <div class="mt-4 flex items-center justify-between px-4">
            <form method="GET" action="{{ route('ortu.rapor') }}">
                <label class="sr-only" for="periode">Bulan rapor</label>
                <select id="periode" name="periode" onchange="this.form.submit()" class="pilih-kecil rounded-full bg-ungu-100 py-1.5 pl-3 pr-8 text-sm font-bold text-ungu-600">
                    @foreach ($daftar as $r)
                        <option value="{{ $r->periode->toDateString() }}" @selected($r->id === $rapor->id)>{{ $r->periode->translatedFormat('F Y') }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-sm text-redup">Coach {{ $rapor->coach->nama }}</span>
        </div>

        <div class="mt-3 space-y-2.5 px-4" x-data="{ buka: 0 }">
            @foreach ($rapor->nilaiRapor as $i => $n)
                <section class="rounded-[18px] bg-white" :class="buka === {{ $i }} ? 'ring-2 ring-ungu-600' : ''">
                    <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-left" @click="buka = buka === {{ $i }} ? null : {{ $i }}" :aria-expanded="buka === {{ $i }}">
                        <h2 class="flex-1 font-display text-[26px] font-extrabold">{{ $n->kategori }}</h2>
                        <x-nilai :nilai="$n->nilai" :besar="true" />
                        <x-icon name="down" class="text-redup transition-transform" ::class="buka === {{ $i }} && 'rotate-180'" />
                    </button>
                    <div x-cloak x-show="buka === {{ $i }}" x-transition.opacity.duration.200ms class="mx-4 border-t border-garis pb-4 pt-3">
                        <p class="text-sm font-semibold text-ungu-600">Catatan Coach {{ $rapor->coach->nama }}</p>
                        @forelse (preg_split('/\R/', trim((string) $n->catatan)) as $baris)
                            @if ($baris !== '')
                                @php [$kepala, $isi] = str_contains($baris, ':') ? explode(':', $baris, 2) : [null, $baris]; @endphp
                                <p class="mt-1 text-[15px] leading-snug">@if ($kepala)<b>{{ $kepala }}:</b>@endif{{ $isi }}</p>
                            @endif
                        @empty
                        @endforelse
                        @if (blank($n->catatan))<p class="mt-1 text-sm text-redup">Tidak ada catatan untuk kategori ini.</p>@endif
                    </div>
                </section>
            @endforeach
        </div>
        <p class="mt-3 px-4 text-xs text-redup">A sangat baik, B baik, C perlu latihan. Tanda + dan − menunjukkan batas atas dan bawah tiap tingkat.</p>
    @else
        <p class="mx-4 mt-4 rounded-[18px] bg-white p-4 text-sm text-redup">Rapor belum diisi coach.</p>
    @endif
</x-ortu>
