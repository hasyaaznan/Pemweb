{{-- L3 Tagihan iuran --}}
@php use App\Support\Format; use App\Support\Klub; @endphp
<x-ortu title="Tagihan iuran" aktif="Tagihan" judul="Tagihan iuran" :kembali="route('ortu.beranda')">
    <div class="px-4 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] lg:items-start lg:gap-6">
        <section class="relative overflow-hidden rounded-[18px] bg-ungu-900 p-4 text-white lg:sticky lg:top-8 lg:p-6" x-data="{ disalin: false }">
            <x-court :opacity="0.12" />
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-sm text-white/75">Transfer ke {{ Klub::get('rekening_bank') }}</p>
                    <p class="font-display text-[32px] font-extrabold leading-tight tracking-wide">{{ Format::rekening(Klub::get('rekening_nomor', '')) }}</p>
                    <p class="text-sm text-white/75">a.n. {{ Klub::get('rekening_atas_nama') }}</p>
                </div>
                <button type="button" class="rounded-full bg-white/15 px-3 py-1.5 text-sm font-semibold"
                    @click="navigator.clipboard.writeText('{{ Klub::get('rekening_nomor') }}'); disalin = true; setTimeout(() => disalin = false, 2000)"
                    x-text="disalin ? 'Tersalin' : 'Salin'">Salin</button>
            </div>
        </section>

        <div>
        @if (session('info'))
            <p class="mt-3 rounded-xl bg-netral-bg px-3 py-2.5 text-sm text-netral" role="status">{{ session('info') }}</p>
        @endif

        <h2 class="mt-5 font-display text-2xl font-extrabold lg:mt-0">Tagihan{{ auth()->user()->anak()->count() > 1 ? ' '.$anak->nama : '' }}</h2>
        <ul class="mt-2 space-y-2.5">
            @forelse ($tagihan as $t)
                @php $buktiTerakhir = $t->buktiTransfer->first(); @endphp
                <li class="rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-lg font-bold leading-tight">{{ $t->periode->translatedFormat('F Y') }}</p>
                            <p class="text-sm text-redup">
                                @switch($t->status)
                                    @case('belum_bayar') {{ Format::rupiah($t->nominal) }}, {{ Format::jatuhTempo($t->jatuh_tempo) }} @break
                                    @case('menunggu_verifikasi') Bukti dikirim {{ $buktiTerakhir?->created_at?->translatedFormat('j M') }}, diperiksa admin @break
                                    @case('lunas') Diverifikasi admin{{ $buktiTerakhir?->diverifikasi_pada ? ', '.$buktiTerakhir->diverifikasi_pada->translatedFormat('j M') : '' }} @break
                                    @default {{ Format::rupiah($t->nominal) }}
                                @endswitch
                            </p>
                        </div>
                        <x-status :status="$t->status" />
                    </div>
                    @if ($t->status === 'ditolak' && $buktiTerakhir?->alasan_tolak)
                        <p class="mt-2.5 rounded-xl bg-merah-bg px-3 py-2 text-sm font-semibold text-merah">{{ $buktiTerakhir->alasan_tolak }}</p>
                    @endif
                    @if ($t->status === 'belum_bayar')
                        <a href="{{ route('ortu.tagihan.unggah', $t) }}" class="mt-3 flex h-[46px] items-center justify-center gap-2 rounded-[14px] bg-oranye-500 font-bold text-ungu-900"><x-icon name="up" size="20" /> Unggah bukti</a>
                    @elseif ($t->status === 'ditolak')
                        <a href="{{ route('ortu.tagihan.unggah', $t) }}" class="mt-3 flex h-[46px] items-center justify-center gap-2 rounded-[14px] bg-white font-bold text-ungu-600 ring-2 ring-inset ring-ungu-600"><x-icon name="up" size="20" /> Unggah ulang bukti</a>
                    @endif
                </li>
            @empty
                <li class="rounded-[18px] bg-white p-4 text-sm text-redup">Belum ada tagihan.</li>
            @endforelse
        </ul>
        </div>
    </div>
</x-ortu>
