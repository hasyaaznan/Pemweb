{{-- L9 Input rapor anak: pilih A/B/C, lalu −/+; catatan maks. 300 karakter per kategori --}}
@php
    $awal = $kategori->mapWithKeys(fn ($k) => [$k->id => old("nilai.{$k->id}", $nilai->get($k->id)?->nilai)]);
    $catatanAwal = $kategori->mapWithKeys(fn ($k) => [$k->id => (string) old("catatan.{$k->id}", $nilai->get($k->id)?->catatan)]);
    $bukaAwal = $kategori->first(fn ($k) => ! $awal[$k->id])?->id;
@endphp
<x-coach :title="'Rapor '.$anak->nama" aktif="Rapor" :judul="'Rapor '.strtok($anak->nama, ' ')" :kembali="route('coach.rapor', ['ku' => $anak->kelompok_usia_id])">
    <form method="POST" action="{{ route('coach.rapor.simpan', $anak) }}" class="px-4"
        x-data="{
            nilai: @js($awal), catatan: @js($catatanAwal), buka: @js($bukaAwal),
            terisi() { return Object.values(this.nilai).filter(Boolean).length },
            huruf(id) { return (this.nilai[id] || '').charAt(0) },
            pilihHuruf(id, h) { if (this.huruf(id) !== h) this.nilai[id] = h },
            pilihTanda(id, t) { this.nilai[id] = this.huruf(id) + t },
        }">
        @csrf
        @method('PUT')
        <div class="flex items-center justify-between">
            <span class="rounded-full bg-ungu-100 px-3 py-1.5 text-sm font-bold text-ungu-600">{{ $anak->kelompokUsia->nama }}, {{ $periode->translatedFormat('F Y') }}</span>
            <span class="text-sm text-redup"><b class="text-ungu-600" x-text="terisi() + ' dari {{ $kategori->count() }}'">{{ $awal->filter()->count() }} dari {{ $kategori->count() }}</b> kategori</span>
        </div>

        @if ($errors->any())
            <div class="mt-3 rounded-[14px] bg-merah-bg px-3.5 py-3 text-sm font-semibold text-merah" role="alert">
                @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <div class="mt-3 space-y-2">
            @foreach ($kategori as $i => $k)
                <section class="rounded-[18px] bg-white" :class="buka === {{ $k->id }} && 'ring-2 ring-ungu-600'">
                    <input type="hidden" name="nilai[{{ $k->id }}]" :value="nilai[{{ $k->id }}]" value="{{ $awal[$k->id] }}">
                    <button type="button" class="flex w-full items-center gap-3 px-4 py-2.5 text-left" @click="buka = buka === {{ $k->id }} ? null : {{ $k->id }}" :aria-expanded="buka === {{ $k->id }}">
                        <span class="flex size-[22px] shrink-0 items-center justify-center rounded-full" :class="nilai[{{ $k->id }}] ? 'bg-hijau text-white' : 'bg-garis'"><x-icon name="check" size="14" x-show="nilai[{{ $k->id }}]" /></span>
                        <span class="flex-1 font-display text-[22px] font-extrabold">{{ $i + 1 }}. {{ $k->nama }}</span>
                        <span x-show="buka !== {{ $k->id }}" class="text-sm text-redup" x-text="nilai[{{ $k->id }}] ? nilai[{{ $k->id }}].replace('-', '−') : 'belum dinilai'"></span>
                        <x-icon name="down" size="20" class="text-redup transition-transform" ::class="buka === {{ $k->id }} && 'rotate-180'" />
                    </button>
                    <div x-cloak x-show="buka === {{ $k->id }}" x-transition.opacity.duration.200ms class="px-4 pb-4">
                        <p class="text-[13px] font-semibold text-redup">Nilai</p>
                        <div class="mt-1.5 grid grid-cols-3 gap-2">
                            @foreach (['A', 'B', 'C'] as $h)
                                <button type="button" @click="pilihHuruf({{ $k->id }}, '{{ $h }}')" class="h-[52px] rounded-xl font-display text-[28px] font-extrabold transition-colors"
                                    :class="huruf({{ $k->id }}) === '{{ $h }}' ? 'bg-ungu-600 text-white' : 'bg-ungu-50 text-ungu-700'" :aria-pressed="huruf({{ $k->id }}) === '{{ $h }}'">{{ $h }}</button>
                            @endforeach
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-2" x-show="huruf({{ $k->id }})" x-transition.opacity>
                            @foreach (['-' => '−', '' => '', '+' => '+'] as $t => $tampil)
                                <button type="button" @click="pilihTanda({{ $k->id }}, '{{ $t }}')" class="h-10 rounded-[10px] font-display text-xl font-extrabold"
                                    :class="nilai[{{ $k->id }}] === huruf({{ $k->id }}) + '{{ $t }}' ? 'bg-white text-ungu-600 ring-2 ring-inset ring-ungu-600' : 'bg-ungu-50 text-ungu-700'"
                                    x-text="huruf({{ $k->id }}) + '{{ $tampil }}'"></button>
                            @endforeach
                        </div>
                        <label for="catatan-{{ $k->id }}" class="mt-3 block text-[13px] font-semibold text-redup">Catatan untuk orang tua</label>
                        <textarea id="catatan-{{ $k->id }}" name="catatan[{{ $k->id }}]" rows="3" maxlength="300" x-model="catatan[{{ $k->id }}]"
                            placeholder="Contoh: Dribbling: sudah lancar dengan tangan kanan."
                            class="mt-1.5 w-full rounded-[14px] border-[1.5px] border-garis bg-white px-3 py-2.5 text-sm leading-snug outline-none focus:border-ungu-600">{{ $catatanAwal[$k->id] }}</textarea>
                        <p class="text-right text-xs text-redup"><span x-text="(catatan[{{ $k->id }}] || '').length">{{ mb_strlen($catatanAwal[$k->id]) }}</span>/300</p>
                    </div>
                </section>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-redup">A sangat baik, B baik, C perlu latihan. Orang tua baru bisa membaca rapor setelah kelima kategori dinilai.</p>

        <div class="fixed inset-x-0 bottom-[86px] z-10 mx-auto flex max-w-md gap-2 px-4">
            <button type="submit" name="aksi" value="simpan" class="flex h-[50px] flex-[0.6] items-center justify-center rounded-[14px] bg-white font-bold text-ungu-600 ring-2 ring-inset ring-ungu-600">Simpan</button>
            <button type="submit" name="aksi" value="lanjut" class="flex h-[50px] flex-1 items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">Simpan & lanjut</button>
        </div>
        <div class="h-16"></div>
    </form>
</x-coach>
