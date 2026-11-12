@extends('layouts.app')

@section('title', 'Riwayat Booking - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-date icon-gradient bg-mean-fruit"></i></div>
                <div>
                    Riwayat Booking Servis
                    <div class="page-title-subheading">Daftar pemesanan servis laptop Anda beserta status pengerjaannya.</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('bookings.create') }}" class="tc-btn tc-btn-accent">
                    <i class="fa fa-plus"></i> Buat Booking
                </a>
            </div>
        </div>
    </div>

    @php
        $dummyBookings = [
            [
                'kode' => 'TC-20261112-001',
                'bengkel' => 'Batam Laptop Center',
                'layanan' => 'Ganti Thermal Paste & Deep Cleaning',
                'tanggal' => '12 Nov 2026',
                'metode' => 'Walk-in',
                'status' => 'received',
                'status_label' => 'Received',
                'badge_class' => 'tc-badge tc-badge-accent',
                'total' => 'Rp 150.000',
            ],
            [
                'kode' => 'TC-20261110-004',
                'bengkel' => 'Nagoya Tech Repair',
                'layanan' => 'Ganti LCD Laptop 14 Inch',
                'tanggal' => '10 Nov 2026',
                'metode' => 'Delivery',
                'status' => 'repairing',
                'status_label' => 'Repairing',
                'badge_class' => 'badge bg-warning text-dark',
                'total' => 'Rp 950.000',
            ],
            [
                'kode' => 'TC-20261105-002',
                'bengkel' => 'Klinik Laptop Kepri',
                'layanan' => 'Install Ulang Windows 11 + Office',
                'tanggal' => '05 Nov 2026',
                'metode' => 'On-site',
                'status' => 'completed',
                'status_label' => 'Completed',
                'badge_class' => 'tc-badge tc-badge-success',
                'total' => 'Rp 120.000',
            ],
            [
                'kode' => 'TC-20261101-009',
                'bengkel' => 'Batam Laptop Center',
                'layanan' => 'Upgrade RAM DDR4 8GB',
                'tanggal' => '01 Nov 2026',
                'metode' => 'Walk-in',
                'status' => 'cancelled',
                'status_label' => 'Cancelled',
                'badge_class' => 'badge bg-danger',
                'total' => 'Rp 450.000',
            ],
        ];
    @endphp

    <div class="card mb-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Bengkel</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Total Biaya</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dummyBookings as $booking)
                            <tr>
                                <td class="fw-semibold">{{ $booking['kode'] }}</td>
                                <td>{{ $booking['bengkel'] }}</td>
                                <td>{{ $booking['layanan'] }}</td>
                                <td>{{ $booking['tanggal'] }}</td>
                                <td>{{ $booking['metode'] }}</td>
                                <td>
                                    <span class="{{ $booking['badge_class'] }}">
                                        {{ $booking['status_label'] }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $booking['total'] }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('bookings.show') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection