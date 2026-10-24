@props(['name', 'label', 'type' => 'text', 'value' => null, 'pilihan' => null, 'bantuan' => null, 'required' => false])
{{-- Satu kolom isian dengan label dan pesan galat, dipakai di semua formulir Admin dan profil.
     type: text, email, tel, number, date, password, textarea, select (pilihan = [nilai => label]), file --}}
@php
    $kelas = 'w-full rounded-[14px] bg-white px-3.5 text-[15px] outline-none ring-[1.5px] ring-inset ring-garis focus:ring-2 focus:ring-ungu-600';
    $isi = $type === 'password' || $type === 'file' ? null : old($name, $value);
    $galat = $errors->has($name) ? 'ring-merah' : '';
@endphp
<div {{ $attributes->only('class')->class(str_contains((string) $attributes->get('class'), 'mt-') ? '' : 'mt-4') }}>
    <label for="{{ $name }}" class="mb-1.5 block text-[13px] font-semibold text-ungu-700">{{ $label }}@unless ($required)<span class="font-normal text-redup"> (boleh kosong)</span>@endunless</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" @required($required) {{ $attributes->only(['maxlength', 'placeholder']) }} class="{{ $kelas }} {{ $galat }} py-3 leading-snug">{{ $isi }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->only(['x-model']) }} class="{{ $kelas }} {{ $galat }} h-[50px]">
            @foreach ($pilihan as $nilai => $teks)
                <option value="{{ $nilai }}" @selected((string) $isi === (string) $nilai)>{{ $teks }}</option>
            @endforeach
        </select>
    @elseif ($type === 'file')
        <input id="{{ $name }}" name="{{ $name }}" type="file" @required($required) {{ $attributes->only(['accept']) }}
            class="block w-full rounded-[14px] bg-white p-2 text-sm ring-[1.5px] ring-inset ring-garis file:mr-3 file:h-10 file:rounded-[10px] file:border-0 file:bg-ungu-100 file:px-3 file:font-bold file:text-ungu-700 {{ $galat }}">
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $isi }}" @required($required)
            {{ $attributes->only(['maxlength', 'min', 'max', 'step', 'placeholder', 'autocomplete', 'inputmode', 'minlength']) }} class="{{ $kelas }} {{ $galat }} h-[50px]">
    @endif
    @if ($bantuan)<p class="mt-1 text-[13px] text-redup">{{ $bantuan }}</p>@endif
    @error($name)<p class="mt-1 text-sm font-semibold text-merah">{{ $message }}</p>@enderror
</div>
