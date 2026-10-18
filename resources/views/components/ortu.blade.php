@props(['title', 'aktif', 'judul' => null, 'kembali' => null])
{{-- Kerangka layar orang tua: Beranda, Rapor, Tagihan, Profil --}}
@php $belumDibaca = auth()->user()->notifikasi()->whereNull('dibaca_pada')->count(); @endphp
<x-aplikasi :title="$title" :aktif="$aktif" :judul="$judul" :kembali="$kembali" :lonceng="[route('ortu.notifikasi'), $belumDibaca]"
    :menu="[['ortu.beranda', 'home', 'Beranda'], ['ortu.rapor', 'rapor', 'Rapor'], ['ortu.tagihan', 'iuran', 'Tagihan'], ['ortu.profil', 'user', 'Profil']]">{{ $slot }}</x-aplikasi>
