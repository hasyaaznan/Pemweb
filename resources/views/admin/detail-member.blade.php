{{-- Detail member: data anak, kontak orang tua, kehadiran, dan riwayat iuran --}}
@php
    use App\Support\Format;
    $ortu = $anak->orangTua;
    $belum = $anak->tagihan->first(fn ($t) => in_array($t->status, ['belum_bayar', 'ditolak'], true));
    $wa = preg_replace('/^0/', '62', Format::noHp($ortu->no_hp ?? ''));
    $pengingat = $belum
        ? 'Halo '.$ortu->nama.', iuran '.$belum->periode->translatedFormat('F Y').' untuk '.$anak->nama.' sebesar '.Format::rupiah($belum->nominal)
            .' belum kami terima (jatuh tempo '.$belum->jatuh_tempo->translatedFormat('j F').'). Bukti transfer bisa diunggah di website Bandung Spiders. Terima kasih.'
        : null;
@endphp
<x-admin title="{{ $anak->nama }}" aktif="Member" judul="Member" :kembali="route('manajemen.member')">
    <div class="space-y-3 px-4 lg:grid lg:grid-cols-2 lg:items-start lg:gap-6 lg:space-y-0">
        <div class="space-y-3">
        <div class="flex items-center gap-3 rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-oranye-100 font-display text-xl font-extrabold text-oranye-700">{{ mb_substr($anak->nama, 0, 1) }}</span>
            <div class="min-w-0 flex-1">
                <p class="font-display text-[26px] font-extrabold leading-none">{{ $anak->nama }}</p>
                <p class="mt-0.5 text-sm text-redup">{{ $anak->kelompokUsia->nama }}, member sejak {{ $anak->tanggal_masuk->translatedFormat('M Y') }}</p>
            </div>
        </div>

        <dl class="divide-y divide-garis rounded-[18px] bg-white px-4 text-[15px] shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">Orang tua</dt><dd class="text-right font-bold">{{ $ortu->nama }} ({{ $anak->hubungan_orang_tua }})</dd></div>
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">No. WhatsApp</dt><dd class="font-bold">{{ $ortu->no_hp ?? '-' }}</dd></div>
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">Email</dt><dd class="truncate font-bold">{{ $ortu->email }}</dd></div>
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">Kehadiran 3 bulan</dt><dd class="font-bold">{{ $kehadiran['total'] ? $kehadiran['persen'].'% ('.$kehadiran['hadir'].' dari '.$kehadiran['total'].')' : 'Belum ada absensi' }}</dd></div>
        </dl>

        @if ($pengingat && $ortu->no_hp)
            <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode($pengingat) }}" target="_blank" rel="noopener"
                class="flex h-[50px] items-center justify-center gap-2 rounded-[14px] bg-oranye-500 font-bold text-ungu-900"><x-icon name="wa" size="20" /> Kirim pengingat lewat WhatsApp</a>
            <p class="-mt-1 text-[13px] text-redup">Pesan sudah terisi. Anda bisa mengubahnya di WhatsApp sebelum dikirim.</p>
        @elseif ($ortu->no_hp)
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-[18px] bg-white px-4 py-3.5 font-bold"><x-icon name="wa" class="text-ungu-600" /> Hubungi {{ $ortu->nama }} lewat WhatsApp <x-icon name="chev" class="ml-auto text-redup" /></a>
        @endif

        </div>

        <div class="space-y-3">
        <h2 class="pt-2 font-display text-[22px] font-extrabold text-ungu-700 lg:pt-0">Iuran</h2>
        <div class="divide-y divide-garis rounded-[18px] bg-white px-4 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            @forelse ($anak->tagihan as $t)
                <div class="flex items-center justify-between gap-3 py-3">
                    <span><b class="block">{{ $t->periode->translatedFormat('F Y') }}</b><span class="text-sm text-redup">{{ Format::rupiah($t->nominal) }}</span></span>
                    <x-status :status="$t->status" admin />
                </div>
            @empty
                <p class="py-3 text-sm text-redup">Belum ada tagihan.</p>
            @endforelse
        </div>
        </div>
    </div>
</x-admin>
