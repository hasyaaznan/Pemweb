{{-- L4 Unggah bukti transfer --}}
@php use App\Support\Format; @endphp
<x-layout title="Unggah bukti · Bandung Spiders">
    <form method="POST" action="{{ route('ortu.tagihan.simpan', $tagihan) }}" enctype="multipart/form-data"
        class="mx-auto flex min-h-screen max-w-md flex-col px-4 pb-6 lg:my-10 lg:min-h-0 lg:max-w-xl lg:rounded-[28px] lg:bg-white lg:px-10 lg:pb-10 lg:pt-4 lg:shadow-[0_10px_40px_rgba(34,0,90,.10)]"
        x-data="unggahBukti({{ (int) old('nominal_transfer', $tagihan->nominal) }})" @submit="if (salah) $event.preventDefault()">
        @csrf
        <header class="flex h-16 items-center justify-between">
            <a href="{{ route('ortu.tagihan') }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></a>
            <h1 class="font-display text-[22px] font-extrabold">Unggah bukti</h1>
            <span class="size-[38px]"></span>
        </header>

        <div class="flex items-center gap-3 rounded-[18px] bg-white p-3.5 shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <span class="flex size-12 items-center justify-center rounded-full bg-oranye-100 text-oranye-700"><x-icon name="iuran" /></span>
            <div class="min-w-0 flex-1">
                <p class="font-bold leading-tight">Iuran {{ $tagihan->periode->translatedFormat('F Y') }}</p>
                <p class="truncate text-sm text-redup">{{ $tagihan->anak->nama }}, {{ $tagihan->anak->kelompokUsia->nama }}</p>
            </div>
            <p class="font-display text-[22px] font-extrabold">{{ Format::rupiah($tagihan->nominal) }}</p>
        </div>

        <p class="mb-1.5 mt-5 text-[13px] font-semibold text-ungu-700">Bukti transfer</p>
        <label class="flex cursor-pointer items-center gap-3 rounded-[18px] bg-white p-3.5" :class="salah ? 'ring-2 ring-merah' : (nama ? 'ring-2 ring-ungu-600' : 'ring-[1.5px] ring-garis border-dashed')">
            <span class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-ungu-50 text-ungu-600"><x-icon name="file" size="26" /></span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-bold" x-text="nama || 'Pilih foto atau PDF bukti transfer'">Pilih foto atau PDF bukti transfer</span>
                <span class="block text-sm text-redup" x-show="nama" x-text="info"></span>
                <span x-cloak x-show="nama && !salah" class="mt-0.5 flex items-center gap-1 text-sm font-semibold text-hijau"><x-icon name="check" size="16" /> Format dan ukuran sesuai</span>
            </span>
            <span class="text-sm font-bold text-ungu-600" x-text="nama ? 'Ganti' : 'Pilih'">Pilih</span>
            <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" required class="sr-only" @change="pilih($event)">
        </label>
        <p class="mt-1.5 text-sm" :class="salah ? 'font-semibold text-merah' : 'text-redup'"
            x-text="salah ? 'File harus JPG, PNG, atau PDF maksimal 2 MB.' : 'JPG, PNG, atau PDF, maksimal 2 MB.'">JPG, PNG, atau PDF, maksimal 2 MB.</p>
        @error('bukti')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <label for="tanggal_transfer" class="mb-1.5 mt-4 block text-[13px] font-semibold text-ungu-700">Tanggal transfer</label>
        <input id="tanggal_transfer" name="tanggal_transfer" type="date" required max="{{ now()->toDateString() }}" value="{{ old('tanggal_transfer', now()->toDateString()) }}"
            class="h-[50px] w-full rounded-[14px] bg-white px-3.5 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600">
        @error('tanggal_transfer')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <label for="nominal" class="mb-1.5 mt-4 block text-[13px] font-semibold text-ungu-700">Nominal yang ditransfer</label>
        <div class="flex h-[50px] items-center gap-1.5 rounded-[14px] bg-white px-3.5 ring-[1.5px] ring-inset ring-garis focus-within:ring-2 focus-within:ring-ungu-600">
            <span class="text-[15px]">Rp</span>
            <input id="nominal" type="text" inputmode="numeric" required x-model="tampilan" @input="ubahNominal" class="min-w-0 flex-1 bg-transparent text-[15px] outline-none">
            <input type="hidden" name="nominal_transfer" :value="nominal">
        </div>
        @error('nominal_transfer')<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror

        <button class="mt-auto flex h-[50px] lg:mt-8 w-full items-center justify-center rounded-[14px] bg-oranye-500 text-base font-bold text-ungu-900 disabled:opacity-50" :disabled="salah">Kirim bukti</button>
    </form>

    <script>
        function unggahBukti(awal) {
            const rupiah = n => n ? Number(n).toLocaleString('id-ID') : '';
            return {
                nama: '', info: '', salah: false, nominal: awal, tampilan: rupiah(awal),
                pilih(e) {
                    const f = e.target.files[0];
                    if (!f) { this.nama = ''; this.salah = false; return; }
                    const ext = f.name.split('.').pop().toLowerCase();
                    this.nama = f.name;
                    this.info = (f.size / 1048576).toFixed(1).replace('.', ',') + ' MB, ' + ext.toUpperCase();
                    this.salah = !['jpg', 'jpeg', 'png', 'pdf'].includes(ext) || f.size > 2 * 1024 * 1024;
                },
                ubahNominal() {
                    this.nominal = this.tampilan.replace(/\D/g, '');
                    this.tampilan = rupiah(this.nominal);
                },
            };
        }
    </script>
</x-layout>
