@extends('layouts.app')

@section('title', 'Kelola Profil Bengkel - Techcare')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Kelola Profil Bengkel
                <div class="page-title-subheading">Daftar bengkel yang Anda kelola, atur status buka/tutup, dan kuota harian.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('bengkels.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Daftarkan Bengkel Baru
            </a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="main-card mb-3 card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>Daftar Bengkel Milik Anda ({{ $bengkels->count() }})</span>
        <form method="GET" action="{{ route('bengkels.index') }}" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama bengkel...">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="buka" {{ request('status') === 'buka' ? 'selected' : '' }}>Buka</option>
                <option value="tutup" {{ request('status') === 'tutup' ? 'selected' : '' }}>Tutup</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Cari</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
            <thead>
                <tr>
                    <th class="ps-4">Bengkel</th>
                    <th>Kontak & Lokasi</th>
                    <th class="text-center">Jam Operasional</th>
                    <th class="text-center">Kuota Harian</th>
                    <th class="text-center">Status Buka/Tutup</th>
                    <th class="text-center pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bengkels as $bengkel)
                    @php
                        $punyaBooking = $bengkel->hasBookings();
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $bengkel->fotoUrl() }}" alt="{{ $bengkel->nama }}"
                                     class="rounded" width="56" height="44" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold">{{ $bengkel->nama }}</div>
                                    <small class="text-muted">{{ Str::limit($bengkel->alamat, 45) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><i class="fa fa-phone text-muted me-1"></i> {{ $bengkel->telepon }}</div>
                            @if ($bengkel->link_maps)
                                <a href="{{ $bengkel->link_maps }}" target="_blank" class="small text-decoration-none">
                                    <i class="fa fa-map-marker-alt text-danger me-1"></i> Lihat Google Maps
                                </a>
                            @else
                                <span class="small text-muted">Link Maps belum diisi</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">
                                {{ substr($bengkel->jam_buka, 0, 5) }} - {{ substr($bengkel->jam_tutup, 0, 5) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold">{{ $bengkel->sisaKuotaHariIni() }}</span> / {{ $bengkel->kuota_harian }} slot
                        </td>
                        <td class="text-center">
                            <form action="{{ route('bengkels.toggle-status', $bengkel) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $bengkel->is_open ? 'btn-success' : 'btn-secondary' }}"
                                        title="Klik untuk mengubah status buka/tutup">
                                    <i class="fa {{ $bengkel->is_open ? 'fa-door-open' : 'fa-door-closed' }} me-1"></i>
                                    {{ $bengkel->is_open ? 'Buka' : 'Tutup' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('bengkels.show', $bengkel) }}" class="btn btn-outline-info" title="Lihat Tampilan Customer">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('bengkels.edit', $bengkel) }}" class="btn btn-outline-warning" title="Edit Profil">
                                    <i class="fa fa-edit"></i>
                                </a>
                                @if ($punyaBooking)
                                    <button type="button" class="btn btn-outline-secondary" disabled
                                            title="Tidak dapat dihapus karena sudah memiliki booking">
                                        <i class="fa fa-lock"></i>
                                    </button>
                                @else
                                    <form action="{{ route('bengkels.destroy', $bengkel) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus bengkel {{ $bengkel->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Bengkel">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa fa-store-slash fa-2x mb-2 d-block"></i>
                            Belum ada bengkel terdaftar. Klik <strong>Daftarkan Bengkel Baru</strong> untuk memulai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection