@props(['gelap' => false])
{{-- Penanda isi landing yang masih contoh (config/landing.php 'contoh' => true) --}}
<span @class([
    'inline-flex items-center rounded-md border border-dashed px-2 py-0.5 font-sans text-xs font-semibold normal-case not-italic tracking-normal',
    'border-oranye-700/50 bg-oranye-100 text-oranye-700' => ! $gelap,
    'border-white/40 bg-white/10 text-[#DCD2F5]' => $gelap,
])>Isi contoh</span>
