@props(['tautan', 'jumlah' => 0, 'gelap' => false])
{{-- Ikon lonceng menuju notifikasi (L18); titik oranye jika ada yang belum dibaca --}}
<a href="{{ $tautan }}" @class(['relative flex size-[38px] shrink-0 items-center justify-center rounded-xl', 'bg-white/15 text-white' => $gelap, 'bg-ungu-600/10' => ! $gelap]) aria-label="Notifikasi{{ $jumlah ? ', '.$jumlah.' belum dibaca' : '' }}">
    <x-icon name="bell" size="21" />
    @if ($jumlah)<i class="absolute right-2 top-2 size-[9px] rounded-full bg-oranye-500 ring-2 ring-white"></i>@endif
</a>
