{{-- L0 Login --}}
<x-layout title="Masuk · Bandung Spiders" class="bg-white">
    <div class="mx-auto min-h-screen max-w-md bg-white">
        <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 px-6 pb-14 pt-12 text-center text-white">
            <x-court />
            <a href="{{ route('landing') }}" class="absolute left-4 top-4 z-10 flex h-[38px] items-center gap-1 rounded-xl bg-white/10 pl-2 pr-3 text-sm font-semibold text-white hover:bg-white/20">
                <x-icon name="back" size="20" /> Halaman utama
            </a>
            <img src="{{ asset('images/logo-spiders.png') }}" alt="Logo Bandung Spiders Basketball" class="relative mx-auto size-[104px] object-contain drop-shadow-lg">
            <h1 class="relative mt-4 font-display text-[34px] font-extrabold leading-none">Masuk ke akun</h1>
            <p class="relative mt-2 text-sm text-white/75">Untuk orang tua, coach, dan manajemen</p>
        </header>

        <form method="POST" action="{{ route('login') }}" class="px-4 pt-8" x-data="{ lihat: false }">
            @csrf
            @if (session('status'))
                <p class="mb-4 rounded-[14px] bg-hijau-bg px-3.5 py-2.5 text-[13px] font-semibold text-hijau">{{ session('status') }}</p>
            @endif

            <label for="login" class="mb-1.5 block text-[13px] font-semibold text-ungu-700">Email atau No. HP</label>
            <div class="flex h-[50px] items-center gap-2.5 rounded-[14px] bg-white px-3.5 ring-[1.5px] ring-inset ring-garis focus-within:ring-2 focus-within:ring-ungu-600">
                <x-icon name="user" class="shrink-0 text-redup" />
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" inputmode="email"
                    class="min-w-0 flex-1 bg-transparent text-[15px] outline-none placeholder:text-[#9C95AE]" placeholder="nama@email.com atau 08xx">
            </div>
            @error('login')<p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-merah"><x-icon name="warn" size="15" />{{ $message }}</p>@enderror

            <label for="password" class="mb-1.5 mt-4 block text-[13px] font-semibold text-ungu-700">Kata sandi</label>
            <div @class(['flex h-[50px] items-center gap-2.5 rounded-[14px] bg-white px-3.5 ring-inset focus-within:ring-2 focus-within:ring-ungu-600',
                'ring-2 ring-merah' => $errors->has('password'), 'ring-[1.5px] ring-garis' => ! $errors->has('password')])>
                <x-icon name="lock" class="shrink-0 text-redup" />
                <input id="password" name="password" :type="lihat ? 'text' : 'password'" type="password" required autocomplete="current-password"
                    class="min-w-0 flex-1 bg-transparent text-[15px] outline-none">
                <button type="button" @click="lihat = !lihat" class="text-redup" :aria-label="lihat ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'" aria-label="Lihat kata sandi"><x-icon name="eye" /></button>
            </div>
            @error('password')<p class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-merah"><x-icon name="warn" size="15" />{{ $message }}</p>@enderror

            <div class="mt-3 flex items-center justify-between">
                <label class="flex items-center gap-2 text-[13px] text-redup"><input type="checkbox" name="ingat" value="1" class="size-4 accent-ungu-600"> Ingat saya</label>
                <a href="{{ route('password.request') }}" class="text-[15px] font-bold text-ungu-600">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="mt-6 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-ungu-600 text-base font-bold text-white active:scale-[.98]">Masuk</button>

            <p class="mt-5 text-center text-[15px] text-redup">Belum jadi member? <a href="{{ route('daftar') }}" class="font-bold text-oranye-700">Daftar di sini</a></p>
        </form>
    </div>
</x-layout>
