@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Pengelolaan Pembayaran Layanan Servis</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header fw-bold">Input Pembayaran Servis</div>
        <div class="card-body">
            <form action="{{ route('pembayaran.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Booking Servis</label>
                        <select name="service_booking_id" class="form-select" required>
                            <option value="">-- Pilih Booking Servis --</option>
                            @foreach ($bookingList as $booking)
                                <option value="{{ $booking->id }}" {{ old('service_booking_id') == $booking->id ? 'selected' : '' }}>
                                    #{{ $booking->id }} - {{ \Illuminate\Support\Str::limit($booking->issue, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kode Referensi</label>
                        <input type="text" name="kode_referensi" class="form-control" value="{{ old('kode_referensi') }}" placeholder="Contoh: PAY-001" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" class="form-control" value="{{ old('nama_pelanggan') }}" placeholder="Nama pelanggan" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Tagihan (Rp)</label>
                        <input type="number" step="0.01" min="0" name="jumlah_tagihan" class="form-control" value="{{ old('jumlah_tagihan') }}" placeholder="150000" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select" required>
                            <option value="Tunai" {{ old('metode_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="Transfer" {{ old('metode_pembayaran') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="E-Wallet" {{ old('metode_pembayaran') == 'E-Wallet' ? 'selected' : '' }}>E-Wallet</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-bold">Daftar Pembayaran Servis</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Booking</th>
                        <th>Pelanggan</th>
                        <th>Tagihan</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembayaranList as $item)
                        <tr>
                            <td>{{ $item->kode_referensi }}</td>
                            <td>#{{ $item->service_booking_id }}</td>
                            <td>{{ $item->nama_pelanggan }}</td>
                            <td>Rp {{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</td>
                            <td>{{ $item->metode_pembayaran }}</td>
                            <td>{{ $item->status_pembayaran }}</td>
                            <td>
                                <form action="{{ route('pembayaran.status', $item->id) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_pembayaran" class="form-select form-select-sm">
                                        <option value="Belum Bayar" {{ $item->status_pembayaran === 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                        <option value="Menunggu Verifikasi" {{ $item->status_pembayaran === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                        <option value="Lunas" {{ $item->status_pembayaran === 'Lunas' ? 'selected' : '' }}>Lunas</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection