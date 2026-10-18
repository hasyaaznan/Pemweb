{{-- L22 Profil Admin --}}
<x-admin title="Profil" aktif="Profil">
    <header class="relative overflow-hidden rounded-b-[34px] bg-ungu-900 lg:rounded-[28px] lg:px-8 px-4 pb-16 pt-6 text-center text-white">
        <x-court />
        <h1 class="relative font-display text-[22px] font-extrabold">Profil</h1>
        <span class="relative mx-auto mt-4 flex size-20 items-center justify-center rounded-full bg-oranye-500 font-display text-4xl font-extrabold text-ungu-900 ring-4 ring-white/20">{{ mb_substr($user->nama, 0, 1) }}</span>
        <p class="relative mt-3 font-display text-[28px] font-extrabold leading-none">{{ $user->nama }}</p>
        <p class="relative mt-1 text-sm text-white/75">Admin Bandung Spiders</p>
    </header>
    <div class="relative -mt-10 space-y-3 px-4 lg:mx-auto lg:max-w-2xl">
        <dl class="divide-y divide-garis rounded-[18px] bg-white px-4 text-[15px] shadow-[0_2px_10px_rgba(34,0,90,.06)]">
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">Akses</dt><dd class="text-right font-bold">Iuran, member, jadwal latihan</dd></div>
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">No. WhatsApp</dt><dd class="font-bold">{{ $user->no_hp }}</dd></div>
            <div class="flex justify-between gap-3 py-3"><dt class="text-redup">Email</dt><dd class="truncate font-bold">{{ $user->email }}</dd></div>
        </dl>
        <a href="{{ route('manajemen.jadwal') }}" class="flex items-center gap-3 rounded-[18px] bg-white px-4 py-3.5 font-bold"><x-icon name="cal" class="text-ungu-600" /> Jadwal latihan <x-icon name="chev" class="ml-auto text-redup" /></a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex h-[50px] w-full items-center justify-center rounded-[14px] bg-white font-bold text-merah ring-[1.5px] ring-inset ring-merah-bg">Keluar</button>
        </form>
    </div>
</x-admin>
