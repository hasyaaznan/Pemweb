{{-- L6 Detail bukti, L27 Tolak bukti (lembar dari bawah) --}}
@php
    use App\Support\Format;
    $tagihan = $bukti->tagihan;
    $anak = $tagihan->anak;
    $selisih = $bukti->nominal_transfer - $tagihan->nominal;
    $gambar = in_array($bukti->file_tipe, ['jpg', 'png'], true);
    $berkas = route('manajemen.verifikasi.file', $bukti);
@endphp
<x-admin title="Detail bukti" aktif="Verifikasi" judul="Detail bukti" :kembali="route('manajemen.verifikasi')">
    <div class="px-4 pb-24 lg:grid lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-start lg:gap-6 lg:pb-0" x-data="{ tolak: {{ $errors->has('alasan') ? 'true' : 'false' }}, alasan: @js(old('alasan', '')) }">
        <a href="{{ $berkas }}" target="_blank" rel="noopener" class="relative flex h-[300px] items-center justify-center lg:sticky lg:top-8 lg:h-[calc(100vh-10rem)] lg:max-h-[640px] overflow-hidden rounded-[18px] bg-white shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @if ($gambar)
                <img src="{{ $berkas }}" alt="Bukti transfer {{ $anak->nama }}" class="h-full w-full object-contain">
                <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-ungu-600 shadow"><x-icon name="zoom" size="14" /> Perbesar</span>
            @else
                <span class="flex flex-col items-center gap-2 text-ungu-600"><x-icon name="file" size="48" /><b>Buka bukti PDF</b><span class="text-sm text-redup">{{ $bukti->ukuran_kb }} KB</span></span>
            @endif
        </a>

        <div>
        <dl class="mt-2.5 space-y-1 rounded-[18px] bg-white p-3.5 text-sm shadow-[0_2px_10px_rgba(34,0,90,.06)] lg:mt-0 lg:p-5 lg:text-[15px]">
            <div class="flex justify-between gap-3"><dt class="text-redup">Anak</dt><dd class="text-right font-bold"><a href="{{ route('manajemen.member.detail', $anak) }}" class="underline decoration-garis underline-offset-2">{{ $anak->nama }}</a>, {{ $anak->kelompokUsia->nama }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Periode</dt><dd class="font-bold">{{ $tagihan->periode->translatedFormat('F Y') }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Tagihan</dt><dd class="font-bold">{{ Format::rupiah($tagihan->nominal) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Nominal transfer</dt><dd @class(['font-bold', 'text-merah' => $selisih !== 0])>{{ Format::rupiah($bukti->nominal_transfer) }}</dd></div>
            @if ($selisih !== 0)
                <p class="rounded-xl bg-merah-bg px-3 py-2 font-semibold text-merah">{{ $selisih < 0 ? 'Kurang '.Format::rupiah(-$selisih) : 'Lebih '.Format::rupiah($selisih) }} dari tagihan.</p>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-redup">Tanggal transfer</dt><dd class="font-bold">{{ $bukti->tanggal_transfer->translatedFormat('j F Y') }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-redup">Diunggah</dt><dd class="font-bold">{{ $bukti->created_at->translatedFormat('j M, H.i') }}</dd></div>
        </dl>

        @foreach ($sebelumnya as $lama)
            <p class="mt-2 rounded-[14px] bg-netral-bg px-3.5 py-2.5 text-[13px] text-netral">Bukti sebelumnya ({{ $lama->created_at->translatedFormat('j M') }}) {{ $lama->status === 'ditolak' ? 'ditolak: '.$lama->alasan_tolak : $lama->status }}</p>
        @endforeach

        @if ($bukti->status === 'menunggu')
            <div class="fixed inset-x-0 bottom-[90px] mx-auto flex max-w-md gap-2.5 px-4 lg:static lg:mx-0 lg:mt-4 lg:max-w-none lg:px-0">
                <button type="button" @click="tolak = true" class="flex h-[50px] flex-[.6] items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-2 ring-inset ring-merah">Tolak</button>
                <form method="POST" action="{{ route('manajemen.verifikasi.setujui', $bukti) }}" class="flex flex-1">
                    @csrf
                    <button type="submit" class="flex h-[50px] w-full items-center justify-center gap-1.5 rounded-[14px] bg-oranye-500 font-bold text-ungu-900"><x-icon name="check" size="18" /> Setujui</button>
                </form>
            </div>

            {{-- L27: lembar alasan naik dari bawah (280 ms) dengan latar meredup --}}
            <div x-cloak x-show="tolak" class="fixed inset-0 z-40 lg:flex lg:items-center lg:justify-center" role="dialog" aria-modal="true" aria-labelledby="judul-tolak" @keydown.escape.window="tolak = false">
                <div x-show="tolak" x-transition.opacity.duration.280ms class="absolute inset-0 bg-ungu-900/45" @click="tolak = false"></div>
                <form method="POST" action="{{ route('manajemen.verifikasi.tolak', $bukti) }}" x-show="tolak"
                    x-transition:enter="transition duration-[280ms] ease-out" x-transition:enter-start="translate-y-full lg:translate-y-4 lg:opacity-0" x-transition:enter-end="translate-y-0 lg:opacity-100"
                    x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0 lg:opacity-100" x-transition:leave-end="translate-y-full lg:translate-y-4 lg:opacity-0"
                    class="absolute inset-x-0 bottom-0 mx-auto max-w-md rounded-t-[28px] bg-white px-4 pb-5 pt-2.5 lg:relative lg:inset-auto lg:w-full lg:rounded-[28px] lg:p-7">
                    @csrf
                    <span class="mx-auto mb-3 block h-[5px] w-11 rounded-full bg-garis lg:hidden"></span>
                    <h2 id="judul-tolak" class="font-display text-[28px] font-extrabold">Tolak bukti ini?</h2>
                    <p class="mb-3 mt-1 text-sm text-redup">Orang tua melihat alasan ini dan bisa mengunggah ulang.</p>
                    <label for="alasan" class="mb-1.5 block text-[13px] font-semibold text-ungu-700">Alasan (wajib)</label>
                    <textarea id="alasan" name="alasan" rows="3" maxlength="200" x-model="alasan" required
                        placeholder="{{ $selisih < 0 ? 'Nominal transfer kurang '.Format::rupiah(-$selisih).'.' : 'Contoh: foto bukti tidak terbaca.' }}"
                        class="w-full rounded-[14px] bg-white px-3.5 py-3 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600"></textarea>
                    @error('alasan')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
                    <div class="mt-3.5 flex gap-2.5">
                        <button type="button" @click="tolak = false" class="flex h-[50px] flex-[.7] items-center justify-center rounded-[14px] bg-ungu-100 font-bold text-ungu-600">Batal</button>
                        <button type="submit" :disabled="alasan.trim() === ''" class="flex h-[50px] flex-1 items-center justify-center rounded-[14px] bg-merah font-bold text-white disabled:opacity-40">Tolak bukti</button>
                    </div>
                </form>
            </div>
        @else
            <p @class(['mt-2.5 rounded-[14px] px-3.5 py-3 text-sm font-semibold', 'bg-hijau-bg text-hijau' => $bukti->status === 'disetujui', 'bg-merah-bg text-merah' => $bukti->status === 'ditolak'])>
                {{ $bukti->status === 'disetujui' ? 'Disetujui' : 'Ditolak' }} oleh {{ $bukti->diverifikasiOleh?->nama ?? 'Admin' }}, {{ $bukti->diverifikasi_pada?->translatedFormat('j M, H.i') }}@if ($bukti->alasan_tolak). Alasan: {{ $bukti->alasan_tolak }}@endif
            </p>
        @endif
        </div>
    </div>
</x-admin>
