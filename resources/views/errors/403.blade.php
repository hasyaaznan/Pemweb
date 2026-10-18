<x-layout title="Akses ditolak · Bandung Spiders">
    <main class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center gap-3 px-4 text-center">
        <span class="flex size-16 items-center justify-center rounded-full bg-merah-bg text-merah"><x-icon name="lock" size="28" /></span>
        <h1 class="font-display text-3xl font-extrabold">Akses ditolak</h1>
        <p class="text-redup">Halaman ini bukan untuk akun Anda.</p>
        <a href="{{ url('/beranda') }}" class="mt-2 rounded-[14px] bg-ungu-600 px-5 py-3 font-bold text-white">Kembali ke beranda</a>
    </main>
</x-layout>
