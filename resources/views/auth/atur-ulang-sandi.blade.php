{{-- Halaman dari tautan email lupa kata sandi --}}
<x-layout title="Kata sandi baru · Bandung Spiders">
    <form method="POST" action="{{ route('password.update') }}" class="mx-auto flex min-h-screen max-w-md flex-col px-4 py-8">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <h1 class="font-display text-[30px] font-extrabold leading-none">Buat kata sandi baru</h1>
        <p class="mt-2 text-sm text-redup">Untuk akun {{ $email }}. Minimal 8 karakter.</p>

        @foreach ([['password', 'Kata sandi baru', 'new-password'], ['password_confirmation', 'Ulangi kata sandi baru', 'new-password']] as [$nama, $label, $auto])
            <label for="{{ $nama }}" class="mb-1.5 mt-5 block text-[13px] font-semibold text-ungu-700">{{ $label }}</label>
            <input id="{{ $nama }}" name="{{ $nama }}" type="password" required minlength="8" autocomplete="{{ $auto }}"
                class="h-[50px] w-full rounded-[14px] bg-white px-3.5 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600">
        @endforeach
        @error('password')<p class="mt-1.5 text-xs font-semibold text-merah">{{ $message }}</p>@enderror

        <button class="mt-8 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-ungu-600 text-base font-bold text-white">Simpan kata sandi</button>
    </form>
</x-layout>
