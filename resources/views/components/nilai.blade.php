@props(['nilai', 'besar' => false])
<span {{ $attributes->class(['inline-flex items-center justify-center rounded-[9px] bg-ungu-50 font-display font-extrabold text-ungu-900 ring-[1.5px] ring-inset ring-garis',
    'h-[46px] min-w-14 px-1.5 text-[28px] rounded-xl' => $besar, 'h-[34px] min-w-[42px] px-1.5 text-[21px]' => ! $besar]) }}>{{ \App\Support\Format::nilai($nilai) }}</span>
