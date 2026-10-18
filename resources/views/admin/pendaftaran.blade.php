{{-- Pendaftar baru: yang menunggu di atas (paling lama dulu), lalu 10 yang terakhir diperiksa --}}
@php
    use App\Http\Controllers\PendaftaranController;
    use App\Support\HariKerja;
@endphp
<x-admin title="Pendaftar baru" aktif="Member" judul="Pendaftar baru" :kembali="route('manajemen.member')">
    <div class="space-y-2 px-4">
        @forelse ($menunggu as $p)
            <a href="{{ route('manajemen.pendaftaran.detail', $p) }}" class="flex items-center gap-3 rounded-[18px] bg-white px-3 py-2.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                <span class="flex size-[42px] shrink-0 items-center justify-center rounded-full bg-ungu-100 font-display text-lg font-extrabold text-ungu-600">{{ mb_substr($p->nama_anak, 0, 1) }}</span>
                <span class="min-w-0 flex-1">
                    <b class="block truncate">{{ $p->nama_anak }}</b>
                    <span class="block text-sm text-redup">{{ $p->kelompokUsia->nama }}, dikirim {{ $p->created_at->translatedFormat('j M') }}</span>
                    @if (HariKerja::lewatBatas($p->created_at))<span class="block text-sm font-semibold text-merah">Lewat batas 2 hari kerja</span>@endif
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-bg px-2.5 py-1 text-xs font-bold text-amber"><i class="size-[7px] rounded-full bg-current"></i>Baru</span>
            </a>
        @empty
            <p class="rounded-[18px] bg-white p-4 text-sm text-redup">Tidak ada pendaftar yang menunggu.</p>
        @endforelse

        @if ($selesai->isNotEmpty())
            <h2 class="pt-4 font-display text-[22px] font-extrabold text-ungu-700">Sudah diperiksa</h2>
            @foreach ($selesai as $p)
                <a href="{{ route('manajemen.pendaftaran.detail', $p) }}" class="flex items-center gap-3 rounded-[14px] bg-white px-3.5 py-2.5">
                    <span class="min-w-0 flex-1"><b class="block truncate">{{ $p->nama_anak }}</b><span class="block text-sm text-redup">{{ PendaftaranController::nomor($p) }}, {{ $p->kelompokUsia->nama }}</span></span>
                    <span @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold', 'bg-hijau-bg text-hijau' => $p->status === 'diterima', 'bg-merah-bg text-merah' => $p->status === 'ditolak'])><i class="size-[7px] rounded-full bg-current"></i>{{ $p->status === 'diterima' ? 'Diterima' : 'Ditolak' }}</span>
                </a>
            @endforeach
        @endif
    </div>
</x-admin>
