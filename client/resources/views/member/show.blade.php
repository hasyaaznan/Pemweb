@extends('layouts.app')
@section('title', $member['nama'])

@section('content')
<div class="row justify-content-center"><div class="col-md-8">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h1 class="h5 mb-0">{{ $member['nama'] }}</h1>
            <span class="badge bg-{{ $member['status'] === 'aktif' ? 'success' : 'secondary' }}">{{ $member['status'] }}</span>
        </div>
        <div class="card-body">
            <p class="text-muted small"><code>GET /member/{{ $member['id'] }}</code></p>
            <dl class="row mb-0">
                <dt class="col-sm-4">Tanggal lahir</dt><dd class="col-sm-8">{{ $member['tanggal_lahir'] }} ({{ $member['usia'] }} tahun)</dd>
                <dt class="col-sm-4">Jenis kelamin</dt><dd class="col-sm-8">{{ $member['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                <dt class="col-sm-4">Sekolah</dt><dd class="col-sm-8">{{ $member['sekolah'] ?? '—' }}</dd>
                <dt class="col-sm-4">Kelompok usia</dt><dd class="col-sm-8">{{ $member['kelompok_usia']['nama'] ?? '—' }}</dd>
                <dt class="col-sm-4">Tanggal masuk</dt><dd class="col-sm-8">{{ $member['tanggal_masuk'] }}</dd>
                <dt class="col-sm-4">Orang tua</dt><dd class="col-sm-8">{{ $member['orang_tua']['nama'] }} ({{ $member['hubungan_orang_tua'] }})<br><small>{{ $member['orang_tua']['email'] }} · {{ $member['orang_tua']['no_hp'] }}</small>@if($member['orang_tua']['alamat'])<br><small>{{ $member['orang_tua']['alamat'] }}</small>@endif</dd>
            </dl>
        </div>
        @if(session('pengguna.peran') === 'manajemen')
            <div class="card-footer d-flex flex-wrap gap-2">
                <a href="{{ route('member.edit', $member['id']) }}" class="btn btn-primary">Ubah (PUT)</a>
                <form action="{{ route('member.status', $member['id']) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $member['status'] === 'aktif' ? 'nonaktif' : 'aktif' }}">
                    <button class="btn btn-outline-primary">{{ $member['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} (PATCH)</button>
                </form>
                <form action="{{ route('member.destroy', $member['id']) }}" method="POST" onsubmit="return confirm('Hapus {{ $member['nama'] }}?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">Hapus (DELETE)</button>
                </form>
                <a href="{{ route('member.index') }}" class="btn btn-link ms-auto">Kembali</a>
            </div>
        @endif
    </div>
</div></div>
@endsection
