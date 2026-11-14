@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="row justify-content-center"><div class="col-md-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><h1 class="h5 mb-0">Masuk</h1></div>
        <div class="card-body">
            <p class="text-muted small">Form ini mengirim <code>POST /masuk</code> ke API dan menyimpan token di session.</p>
            <form action="{{ route('masuk') }}" method="POST">
                @csrf
                <div class="mb-3"><label for="login" class="form-label">Email atau No. HP</label><input id="login" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login', 'admin@spiders.test') }}" required>@error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label for="password" class="form-label">Kata sandi</label><input id="password" type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</div></div>
@endsection
