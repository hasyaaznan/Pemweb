<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L18 Notifikasi orang tua: rapor terbit, iuran lunas/ditolak, tagihan baru, jadwal berubah (K13)
class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasi = $request->user()->notifikasi()->latest('created_at')->latest('id')->limit(50)->get();

        return view('ortu.notifikasi', [
            'hariIni' => $notifikasi->filter(fn ($n) => $n->created_at->isToday()),
            'sebelumnya' => $notifikasi->reject(fn ($n) => $n->created_at->isToday()),
            'belumDibaca' => $notifikasi->whereNull('dibaca_pada')->count(),
        ]);
    }

    // Membuka notifikasi menandainya sudah dibaca, lalu menuju halaman terkait
    public function buka(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403, 'Akses ditolak');
        $notifikasi->dibaca_pada ??= now();
        $notifikasi->save();

        return redirect($notifikasi->tautan && str_starts_with($notifikasi->tautan, '/') ? $notifikasi->tautan : route('ortu.notifikasi'));
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->notifikasi()->whereNull('dibaca_pada')->update(['dibaca_pada' => now()]);

        return redirect()->route('ortu.notifikasi');
    }
}
