@props(['status', 'admin' => false])
@php
    // Status tagihan seperti dilihat orang tua; "ditolak" ditulis "Perlu unggah ulang" (keputusan copy 17 Okt 2026).
    // Admin tetap melihat "Ditolak".
    [$label, $warna] = [
        'belum_bayar' => ['Belum bayar', 'text-netral bg-netral-bg'],
        'menunggu_verifikasi' => ['Menunggu verifikasi', 'text-amber bg-amber-bg'],
        'lunas' => ['Lunas', 'text-hijau bg-hijau-bg'],
        'ditolak' => [$admin ? 'Ditolak' : 'Perlu unggah ulang', 'text-merah bg-merah-bg'],
        'menunggu' => ['Menunggu', 'text-amber bg-amber-bg'],
        'disetujui' => ['Disetujui', 'text-hijau bg-hijau-bg'],
        'hadir' => ['Hadir', 'text-hijau bg-hijau-bg'],
        'izin' => ['Izin', 'text-amber bg-amber-bg'],
        'alpa' => ['Alpa', 'text-merah bg-merah-bg'],
    ][$status];
@endphp
<span {{ $attributes->class("inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold $warna") }}><i class="size-[7px] rounded-full bg-current"></i>{{ $label }}</span>
