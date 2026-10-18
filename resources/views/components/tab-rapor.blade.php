@props(['aktif', 'persen'])
{{-- Tab Rapor / Absensi di L2 dan L17 --}}
<div class="mx-4 grid grid-cols-2 gap-1 rounded-2xl bg-white p-1.5">
    <a href="{{ route('ortu.rapor') }}" @class(['flex h-11 items-center justify-center rounded-xl font-bold', 'bg-ungu-600 text-white' => $aktif === 'rapor', 'text-redup' => $aktif !== 'rapor'])>Rapor</a>
    <a href="{{ route('ortu.absensi') }}" @class(['flex h-11 items-center justify-center rounded-xl font-bold', 'bg-ungu-600 text-white' => $aktif === 'absensi', 'text-redup' => $aktif !== 'absensi'])>Absensi {{ $persen }}%</a>
</div>
