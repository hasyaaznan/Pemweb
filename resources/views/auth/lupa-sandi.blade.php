{{-- L16 Lupa kata sandi --}}
<x-auth title="Lupa kata sandi · Bandung Spiders">
    <div class="mx-auto flex min-h-screen max-w-md flex-col px-4 lg:min-h-0 lg:px-0">
        <header class="flex h-16 items-center justify-between">
            <a href="{{ route('login') }}" class="flex size-[38px] items-center justify-center rounded-xl bg-ungu-600/10" aria-label="Kembali"><x-icon name="back" /></a>
            <h1 class="font-display text-[22px] font-extrabold">Lupa kata sandi</h1>
            <span class="size-[38px]"></span>
        </header>
        <form method="POST" action="{{ route('password.email') }}" class="flex flex-1 flex-col pb-6 lg:pb-0">
            @csrf
            <span class="mt-2 flex size-16 items-center justify-center rounded-full bg-ungu-100 text-ungu-600"><x-icon name="lock" size="26" /></span>
            <h2 class="mt-4 font-display text-[30px] font-extrabold leading-none">Atur ulang kata sandi</h2>
            <p class="mt-2 text-sm text-redup">Masukkan email atau nomor HP yang terdaftar. Kami kirim tautan atur ulang yang berlaku 30 menit.</p>

            <label for="login" class="mb-1.5 mt-5 block text-[13px] font-semibold text-ungu-700">Email / No. HP</label>
            <div class="flex h-[50px] items-center gap-2.5 rounded-[14px] bg-white px-3.5 ring-[1.5px] ring-inset ring-garis focus-within:ring-2 focus-within:ring-ungu-600">
                <x-icon name="user" class="shrink-0 text-redup" />
                <input id="login" name="login" value="{{ old('login') }}" required autofocus class="min-w-0 flex-1 bg-transparent text-[15px] outline-none">
            </div>
            @error('login')<p class="mt-1.5 text-xs font-semibold text-merah">{{ $message }}</p>@enderror

            @if (session('terkirim'))
                <p class="anim-muncul mt-5 flex gap-2.5 rounded-[14px] bg-hijau-bg px-3.5 py-3 text-[13px] font-semibold text-hijau">
                    <x-icon name="check" class="shrink-0" />
                    Kalau email atau nomor itu terdaftar, tautan sudah kami kirim ke email akun tersebut. Cek juga folder spam.
                </p>
            @endif

            <button class="mt-auto flex h-[50px] lg:mt-8 w-full items-center justify-center rounded-[14px] bg-oranye-500 text-base font-bold text-ungu-900">Kirim tautan</button>
        </form>
    </div>
</x-auth>
