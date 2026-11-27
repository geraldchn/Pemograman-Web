@extends('layouts.app')

@section('title', $bengkel->nama . ' - Detail Bengkel')

@section('content')
@php
    $sisaKuota = $bengkel->sisaKuotaHariIni();
    $kuotaTerpakai = $bengkel->kuotaTerpakaiHariIni();
    $persenKuota = $bengkel->kuota_harian > 0 ? min(100, round(($kuotaTerpakai / $bengkel->kuota_harian) * 100)) : 0;
    $bisaBooking = $bengkel->is_open && $sisaKuota > 0;
@endphp

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-shopbag icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                {{ $bengkel->nama }}
                <div class="page-title-subheading">Informasi profil bengkel, jam operasional, lokasi, dan ketersediaan kuota harian.</div>
            </div>
        </div>
        <div class="page-title-actions">
            @if ($bengkel->user_id === auth()->id())
                <a href="{{ route('bengkels.edit', $bengkel) }}" class="btn btn-warning me-1">
                    <i class="fa fa-edit me-1"></i> Edit Profil
                </a>
                <a href="{{ route('bengkels.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Daftar Bengkel Saya
                </a>
            @else
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Kartu Profil Utama Bengkel (Modul 1) --}}
<div class="main-card mb-3 card overflow-hidden">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="{{ $bengkel->fotoUrl() }}" alt="{{ $bengkel->nama }}"
                 class="w-100 h-100" style="min-height: 240px; max-height: 320px; object-fit: cover;">
        </div>
        <div class="col-md-8">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h3 class="fw-bold mb-1">{{ $bengkel->nama }}</h3>
                        <p class="text-muted mb-0">
                            <i class="fa fa-map-marker-alt text-danger me-1"></i> {{ $bengkel->alamat }}
                        </p>
                    </div>
                    <span class="badge fs-6 px-3 py-2 {{ $bengkel->is_open ? 'bg-success' : 'bg-danger' }}">
                        <i class="fa {{ $bengkel->is_open ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                        {{ $bengkel->is_open ? 'Buka' : 'Tutup' }}
                    </span>
                </div>

                <div class="row g-3 my-2">
                    <div class="col-sm-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Jam Operasional</small>
                            <span class="fw-bold fs-6">
                                <i class="fa fa-clock text-primary me-1"></i>
                                {{ substr($bengkel->jam_buka, 0, 5) }} - {{ substr($bengkel->jam_tutup, 0, 5) }} WIB
                            </span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Telepon / WhatsApp</small>
                            <span class="fw-bold fs-6">
                                <i class="fa fa-phone text-success me-1"></i> {{ $bengkel->telepon }}
                            </span>
                        </div>
                    </div>

                    <div class="col-sm-12 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Kuota Servis Hari Ini</small>
                            <span class="fw-bold fs-6 {{ $sisaKuota > 0 ? 'text-success' : 'text-danger' }}">
                                Tersisa {{ $sisaKuota }} dari {{ $bengkel->kuota_harian }} slot
                            </span>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar {{ $sisaKuota > 0 ? 'bg-success' : 'bg-danger' }}"
                                     style="width: {{ $persenKuota }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    @if ($bengkel->link_maps)
                        <a href="{{ $bengkel->link_maps }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fa fa-map-marked-alt me-1"></i> Buka Lokasi di Google Maps
                        </a>
                    @endif

                    @if ($bisaBooking)
                        <a href="{{ route('bookings.create', ['bengkel_id' => $bengkel->id]) }}" class="btn btn-primary">
                            <i class="fa fa-calendar-plus me-1"></i> Booking Servis di Bengkel Ini
                        </a>
                    @else
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fa fa-ban me-1"></i>
                            {{ !$bengkel->is_open ? 'Bengkel Sedang Tutup' : 'Kuota Hari Ini Penuh' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Slot Integrasi Modul 2 (Katalog Layanan - Christopher) --}}
<div class="main-card mb-3 card">
    <div class="card-header">
        <i class="fa fa-tools me-2"></i> Katalog Layanan Servis
    </div>
    <div class="card-body">
        {{-- Bagian ini siap diisi @include daftar layanan milik Christopher (Modul 2) berdasarkan $bengkel->id --}}
        <p class="text-muted mb-0">
            Daftar layanan servis dan estimasi harga untuk <strong>{{ $bengkel->nama }}</strong> akan ditampilkan di sini.
        </p>
    </div>
</div>
@endsection