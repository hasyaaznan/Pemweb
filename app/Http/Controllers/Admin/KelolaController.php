<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokUsia;
use App\Models\User;
use Illuminate\View\View;

// Kelola data: daftar semua data klub dan isi halaman depan yang bisa ditambah, diubah, dan dihapus Admin
class KelolaController extends Controller
{
    public function __invoke(): View
    {
        $konten = collect(KontenController::JENIS)->map(fn ($j, $jenis) => [
            route('manajemen.konten', $jenis),
            ['linimasa' => 'history', 'turnamen' => 'trophy', 'galeri' => 'image', 'tanya-jawab' => 'help'][$jenis],
            $j['judul'],
            $j['model']::count().' '.$j['satuan'],
        ])->values()->all();

        return view('admin.kelola', ['grup' => [
            'Data klub' => [
                [route('manajemen.jadwal'), 'cal', 'Jadwal latihan', 'Hari, jam, dan tempat per kelompok'],
                [route('manajemen.kelompok'), 'users', 'Kelompok usia dan iuran', KelompokUsia::count().' kelompok'],
                [route('manajemen.coach'), 'user', 'Coach', User::where('peran', 'coach')->where('aktif', true)->count().' coach aktif'],
                [route('manajemen.pengaturan'), 'settings', 'Pengaturan klub', 'Rekening, WhatsApp, alamat GOR, jatuh tempo'],
            ],
            'Halaman depan' => [
                [route('manajemen.pengaturan').'#halaman-depan', 'image', 'Foto sampul dan Tentang klub', 'Foto besar di atas dan cerita singkat klub'],
                ...$konten,
            ],
        ]]);
    }
}
