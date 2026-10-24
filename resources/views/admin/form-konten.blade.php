{{-- Tambah atau ubah satu isi halaman depan. Kolom formulir diambil dari KontenController::JENIS --}}
@php $baru = ! $item->exists; @endphp
<x-admin :title="($baru ? 'Tambah ' : 'Ubah ').$j['satuan']" aktif="Profil" :judul="($baru ? 'Tambah ' : 'Ubah ').$j['satuan']" :kembali="route('manajemen.konten', $jenis)">
    <form method="POST" enctype="multipart/form-data" action="{{ $baru ? route('manajemen.konten.simpan', $jenis) : route('manajemen.konten.perbarui', [$jenis, $item->id]) }}"
        class="px-4 lg:max-w-xl" x-data="{ jenis: @js(old('jenis', $item->jenis ?? 'foto')) }">
        @csrf
        @unless ($baru) @method('PUT') @endunless
        @foreach ($j['kolom'] as $nama => [$label, $tipe, $aturan])
            @php
                $wajib = in_array('required', $aturan, true);
                $nilai = $item->getAttribute($nama);
                if ($nilai instanceof \Carbon\CarbonInterface) $nilai = $nilai->format('Y-m-d');
                if ($nama === 'youtube' && $nilai) $nilai = 'https://youtu.be/'.$nilai;
                // Galeri: kolom link YouTube dan sampul hanya untuk video
                $tampil = match ($nama) { 'youtube', 'poster' => "jenis === 'video'", default => null };
            @endphp
            <div @if ($tampil) x-show="{{ $tampil }}" x-cloak @endif>
                @if (in_array($tipe, ['foto', 'berkas'], true))
                    @if ($nilai)
                        @if (str_ends_with($nilai, '.mp4') || str_ends_with($nilai, '.webm'))
                            <video src="{{ asset($nilai) }}" controls preload="metadata" class="mt-4 aspect-video w-full rounded-[14px] bg-ungu-900"></video>
                        @else
                            <img src="{{ asset($nilai) }}" alt="{{ $label }} sekarang" class="mt-4 max-h-56 w-full rounded-[14px] object-cover">
                        @endif
                    @endif
                    <x-kolom :name="$nama" :label="($nilai ? 'Ganti ' : '').lcfirst($label)" type="file" :class="$nilai ? 'mt-2' : ''"
                        :accept="$tipe === 'foto' ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,video/mp4,video/webm'"
                        :bantuan="$tipe === 'foto' ? 'JPG, PNG, atau WebP, maksimal 4 MB.' : 'Foto JPG/PNG/WebP atau video MP4/WebM, maksimal 20 MB. Video yang lebih besar unggah ke YouTube lalu isi linknya.'" />
                @elseif ($tipe === 'select')
                    <x-kolom :name="$nama" :label="$label" type="select" :value="$nilai" :pilihan="['foto' => 'Foto', 'video' => 'Video']" required x-model="jenis" />
                @else
                    <x-kolom :name="$nama" :label="$label" :type="$tipe" :value="$nilai" :required="$wajib"
                        :bantuan="['youtube' => 'Boleh salah satu: file video di atas atau link YouTube.', 'tahun' => '4 angka, misalnya 2023.', 'hasil' => 'Kosongkan kalau turnamen belum selesai.'][$nama] ?? null" />
                @endif
            </div>
        @endforeach
        <button type="submit" class="mt-5 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-oranye-500 font-bold text-ungu-900">{{ $baru ? 'Tambah '.$j['satuan'] : 'Simpan perubahan' }}</button>
    </form>
    @unless ($baru)
        <form method="POST" action="{{ route('manajemen.konten.hapus', [$jenis, $item->id]) }}" class="mt-2.5 px-4 lg:max-w-xl" x-data @submit="if (! confirm('Hapus {{ $j['satuan'] }} ini dari halaman depan?')) $event.preventDefault()">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-[1.5px] ring-inset ring-merah-bg">Hapus {{ $j['satuan'] }}</button>
        </form>
    @endunless
</x-admin>
