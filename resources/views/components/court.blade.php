@props(['opacity' => 0.14])
{{-- Garis lapangan basket sebagai latar blok ungu, sama dengan mockup --}}
<svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 360 220" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <g fill="none" stroke="rgba(255,255,255,{{ $opacity }})" stroke-width="2"><circle cx="300" cy="110" r="46"/><circle cx="300" cy="110" r="128"/><rect x="230" y="62" width="130" height="96"/><path d="M0 110h360"/></g>
</svg>
