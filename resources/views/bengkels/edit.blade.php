@extends('layouts.app')

@section('title', 'Edit Profil Bengkel - Techcare')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-note icon-gradient bg-mean-fruit"></i>
            </div>
            <div>
                Edit Profil Bengkel
                <div class="page-title-subheading">Perbarui data profil, jam operasional, kuota harian, atau foto {{ $bengkel->nama }}.</div>
            </div>
        </div>
        <div class="page-title-actions">
            <a href="{{ route('bengkels.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="main-card mb-3 card">
    <div class="card-header">Edit Data: {{ $bengkel->nama }}</div>
    <div class="card-body">
        <form action="{{ route('bengkels.update', $bengkel) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Bengkel <span class="text-danger">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $bengkel->nama) }}"
                           class="form-control @error('nama') is-invalid @enderror" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="telepon" value="{{ old('telepon', $bengkel->telepon) }}"
                           class="form-control @error('telepon') is-invalid @enderror" required>
                    @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat" rows="2"
                              class="form-control @error('alamat') is-invalid @enderror" required>{{ old('alamat', $bengkel->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Link Google Maps</label>
                    <input type="url" name="link_maps" value="{{ old('link_maps', $bengkel->link_maps) }}"
                           class="form-control @error('link_maps') is-invalid @enderror"
                           placeholder="https://maps.google.com/...">
                    @error('link_maps') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ganti Foto Bengkel (Opsional)</label>
                    <input type="file" name="foto" accept="image/*"
                           class="form-control @error('foto') is-invalid @enderror">
                    @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <img src="{{ $bengkel->fotoUrl() }}" alt="Foto saat ini" width="72" height="54"
                             class="rounded border" style="object-fit: cover;">
                        <small class="text-muted">Foto saat ini (biarkan kosong jika tidak ingin mengganti).</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Buka <span class="text-danger">*</span></label>
                    <input type="time" name="jam_buka" value="{{ old('jam_buka', substr($bengkel->jam_buka, 0, 5)) }}"
                           class="form-control @error('jam_buka') is-invalid @enderror" required>
                    @error('jam_buka') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Tutup <span class="text-danger">*</span></label>
                    <input type="time" name="jam_tutup" value="{{ old('jam_tutup', substr($bengkel->jam_tutup, 0, 5)) }}"
                           class="form-control @error('jam_tutup') is-invalid @enderror" required>
                    @error('jam_tutup') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kuota Servis Harian <span class="text-danger">*</span></label>
                    <input type="number" name="kuota_harian" value="{{ old('kuota_harian', $bengkel->kuota_harian) }}"
                           min="1" max="200" class="form-control @error('kuota_harian') is-invalid @enderror" required>
                    @error('kuota_harian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="is_open" name="is_open" value="1"
                               {{ old('is_open', $bengkel->is_open) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_open">
                            Status Bengkel Buka
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('bengkels.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection