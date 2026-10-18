{{-- Sementara: beranda coach dan manajemen dibuat di tahap berikutnya --}}
<x-layout title="Beranda · Bandung Spiders">
    <main class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center gap-3 px-4 text-center">
        <img src="{{ asset('images/logo-spiders.png') }}" alt="" class="size-24">
        <h1 class="font-display text-3xl font-extrabold">Halo, {{ $peran === 'coach' ? 'Coach ' : '' }}{{ auth()->user()->nama }}</h1>
        <p class="text-redup">Halaman {{ $peran }} sedang dibuat.</p>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="mt-2 rounded-[14px] px-5 py-3 font-bold text-ungu-600 ring-2 ring-inset ring-ungu-600">Keluar</button></form>
    </main>
</x-layout>
