@extends('layouts.app')
@section('title', 'Daftar member')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h2 mb-1">Daftar Member</h1>
        <p class="text-muted mb-0"><code>GET /member</code>@if($meta) · {{ $meta['total'] }} member @endif</p>
    </div>
    @if(session('pengguna.peran') === 'manajemen')
        <a href="{{ route('member.create') }}" class="btn btn-primary">Tambah Member</a>
    @endif
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-sm-6"><input name="cari" class="form-control" placeholder="Cari nama anak" value="{{ request('cari') }}"></div>
    <div class="col-sm-3"><select name="status" class="form-select"><option value="">Semua status</option><option value="aktif" @selected(request('status') === 'aktif')>Aktif</option><option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option></select></div>
    <div class="col-sm-3"><button class="btn btn-outline-primary w-100">Tampilkan</button></div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark"><tr><th>#</th><th>Nama</th><th>Kelompok</th><th>Orang tua</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($members as $m)
                <tr>
                    <td class="text-muted">{{ $m['id'] }}</td>
                    <td><a href="{{ route('member.show', $m['id']) }}"><strong>{{ $m['nama'] }}</strong></a><br><small class="text-muted">{{ $m['usia'] }} tahun · {{ $m['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</small></td>
                    <td>{{ $m['kelompok_usia']['nama'] ?? '—' }}</td>
                    <td>{{ $m['orang_tua']['nama'] ?? '—' }}<br><small class="text-muted">{{ $m['orang_tua']['no_hp'] ?? '' }}</small></td>
                    <td><span class="badge bg-{{ $m['status'] === 'aktif' ? 'success' : 'secondary' }}">{{ $m['status'] }}</span></td>
                    <td class="text-nowrap">
                        <a href="{{ route('member.show', $m['id']) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                        @if(session('pengguna.peran') === 'manajemen')
                            <a href="{{ route('member.edit', $m['id']) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form action="{{ route('member.destroy', $m['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus {{ $m['nama'] }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada member.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($meta && $meta['last_page'] > 1)
    <nav class="mt-3"><ul class="pagination">
        @for($p = 1; $p <= $meta['last_page']; $p++)
            <li class="page-item @if($p === $meta['current_page']) active @endif"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $p]) }}">{{ $p }}</a></li>
        @endfor
    </ul></nav>
@endif
@endsection
