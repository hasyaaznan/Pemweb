@extends('layouts.app')
@section('title', $member ? 'Ubah member' : 'Tambah member')

@section('content')
@php($m = $member ?? [])
<div class="row justify-content-center"><div class="col-md-8">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><h1 class="h5 mb-0">{{ $member ? 'Ubah Member #'.$member['id'] : 'Tambah Member Baru' }}</h1></div>
        <div class="card-body">
            <p class="text-muted small">Form mengirim <code>{{ $member ? 'PUT /member/'.$member['id'] : 'POST /member' }}</code>.</p>
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ $member ? route('member.update', $member['id']) : route('member.store') }}" method="POST">
                @csrf
                @if($member) @method('PUT') @endif
                <h2 class="h6 text-muted">Data anak</h2>
                <div class="row">
                    <div class="col-md-8 mb-3"><label class="form-label" for="nama">Nama anak</label><input id="nama" name="nama" class="form-control" value="{{ old('nama', $m['nama'] ?? '') }}" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label" for="tanggal_lahir">Tanggal lahir</label><input id="tanggal_lahir" type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $m['tanggal_lahir'] ?? '') }}" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label" for="jenis_kelamin">Jenis kelamin</label><select id="jenis_kelamin" name="jenis_kelamin" class="form-select"><option value="L" @selected(old('jenis_kelamin', $m['jenis_kelamin'] ?? '') === 'L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin', $m['jenis_kelamin'] ?? '') === 'P')>Perempuan</option></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label" for="kelompok_usia_id">Kelompok usia</label><select id="kelompok_usia_id" name="kelompok_usia_id" class="form-select">@foreach($kelompok as $k)<option value="{{ $k['id'] }}" @selected((int) old('kelompok_usia_id', $m['kelompok_usia_id'] ?? 0) === $k['id'])>{{ $k['nama'] }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label class="form-label" for="sekolah">Sekolah</label><input id="sekolah" name="sekolah" class="form-control" value="{{ old('sekolah', $m['sekolah'] ?? '') }}"></div>
                    @if($member)
                        <div class="col-md-4 mb-3"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><option value="aktif" @selected(old('status', $m['status']) === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $m['status']) === 'nonaktif')>Nonaktif</option></select></div>
                    @endif
                </div>
                <h2 class="h6 text-muted mt-2">Orang tua</h2>
                <div class="row">
                    <div class="col-md-8 mb-3"><label class="form-label" for="ortu_nama">Nama orang tua</label><input id="ortu_nama" name="orang_tua[nama]" class="form-control" value="{{ old('orang_tua.nama', $m['orang_tua']['nama'] ?? '') }}" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label" for="hubungan">Hubungan</label><select id="hubungan" name="hubungan_orang_tua" class="form-select">@foreach(['Ibu', 'Ayah', 'Wali'] as $h)<option @selected(old('hubungan_orang_tua', $m['hubungan_orang_tua'] ?? '') === $h)>{{ $h }}</option>@endforeach</select></div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="ortu_email">Email</label><input id="ortu_email" type="email" name="orang_tua[email]" class="form-control" value="{{ old('orang_tua.email', $m['orang_tua']['email'] ?? '') }}" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="ortu_hp">No. WhatsApp</label><input id="ortu_hp" name="orang_tua[no_hp]" class="form-control" value="{{ old('orang_tua.no_hp', $m['orang_tua']['no_hp'] ?? '') }}" placeholder="08xxxxxxxxxx" required></div>
                    <div class="col-12 mb-3"><label class="form-label" for="ortu_alamat">Alamat</label><input id="ortu_alamat" name="orang_tua[alamat]" class="form-control" value="{{ old('orang_tua.alamat', $m['orang_tua']['alamat'] ?? '') }}"></div>
                </div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ $member ? route('member.show', $member['id']) : route('member.index') }}" class="btn btn-outline-secondary">Batal</a></div>
            </form>
        </div>
    </div>
</div></div>
@endsection
