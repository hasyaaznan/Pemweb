@props(['title', 'aktif', 'judul' => null, 'kembali' => null])
{{-- Kerangka layar coach: Absensi, Rapor, Jadwal, Profil --}}
<x-aplikasi :title="$title" :aktif="$aktif" :judul="$judul" :kembali="$kembali"
    :menu="[['coach.beranda', 'check', 'Absensi'], ['coach.rapor', 'rapor', 'Rapor'], ['coach.jadwal', 'cal', 'Jadwal'], ['coach.profil', 'user', 'Profil']]">{{ $slot }}</x-aplikasi>
