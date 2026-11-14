<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Member') · Spiders API Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bs-primary: #22005A; --bs-primary-rgb: 34, 0, 90; --bs-link-color: #22005A; --bs-link-color-rgb: 34, 0, 90; }
        .btn-primary { --bs-btn-bg: #22005A; --bs-btn-border-color: #22005A; --bs-btn-hover-bg: #3a0f85; --bs-btn-hover-border-color: #3a0f85; --bs-btn-active-bg: #1a0045; }
        .btn-outline-primary { --bs-btn-color: #22005A; --bs-btn-border-color: #22005A; --bs-btn-hover-bg: #22005A; --bs-btn-hover-border-color: #22005A; }
        .aksen { color: #F8921C; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('member.index') }}">Bandung <span class="aksen">Spiders</span> · API Client</a>
        @if(session('pengguna'))
            <form action="{{ route('keluar') }}" method="POST" class="d-flex align-items-center gap-3">
                @csrf
                <span class="text-white-50 small">{{ session('pengguna.nama') }} ({{ session('pengguna.peran') }})</span>
                <button class="btn btn-sm btn-outline-light">Keluar</button>
            </form>
        @endif
    </div>
</nav>
<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
    @endif
    @yield('content')
    <p class="mt-4 small text-muted">Semua data di halaman ini diambil dari API: <code>{{ config('api.server_url') }}</code></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
