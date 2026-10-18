@props(['title', 'aktif', 'judul' => null, 'kembali' => null])
{{-- Kerangka layar Admin: Beranda, Verifikasi, Member, Profil --}}
<x-aplikasi :title="$title" peran="Admin" :aktif="$aktif" :judul="$judul" :kembali="$kembali"
    :menu="[['manajemen.beranda', 'home', 'Beranda'], ['manajemen.verifikasi', 'iuran', 'Verifikasi'], ['manajemen.member', 'users', 'Member'], ['manajemen.profil', 'user', 'Profil']]">{{ $slot }}</x-aplikasi>
