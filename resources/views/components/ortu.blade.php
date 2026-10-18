@props(['title', 'aktif', 'judul' => null, 'kembali' => null])
{{-- Kerangka layar orang tua: Beranda, Rapor, Tagihan, Profil --}}
<x-aplikasi :title="$title" :aktif="$aktif" :judul="$judul" :kembali="$kembali"
    :menu="[['ortu.beranda', 'home', 'Beranda'], ['ortu.rapor', 'rapor', 'Rapor'], ['ortu.tagihan', 'iuran', 'Tagihan'], ['ortu.profil', 'user', 'Profil']]">{{ $slot }}</x-aplikasi>
