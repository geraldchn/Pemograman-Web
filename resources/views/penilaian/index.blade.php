@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Penilaian Layanan Servis</h3>

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
        <div class="card-header fw-bold">Form Penilaian Layanan Servis</div>
        <div class="card-body">
            <form action="{{ route('penilaian.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Data Servis</label>
                    <select name="servis_id" class="form-select" required>
                        <option value="">-- Pilih Data Servis --</option>
                        @foreach ($servisList as $servis)
                            <option value="{{ $servis->id }}" {{ old('servis_id') == $servis->id ? 'selected' : '' }}>
                                Servis #{{ $servis->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating Layanan (1 - 5)</label>
                    <select name="rating" class="form-select" required>
                        <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>5 - Sangat Memuaskan</option>
                        <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>4 - Memuaskan</option>
                        <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>3 - Cukup</option>
                        <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>2 - Kurang</option>
                        <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>1 - Sangat Kurang</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ulasan / Catatan Layanan</label>
                    <textarea name="ulasan" rows="4" class="form-control" placeholder="Tuliskan penilaian terhadap layanan servis...">{{ old('ulasan') }}</textarea>
                </div>

                <button type="submit" class="btn btn-success">Kirim Penilaian</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-bold">Daftar Penilaian Layanan Servis</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Servis</th>
                        <th>Rating</th>
                        <th>Ulasan</th>
                        <th>Waktu</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                   @forelse ($penilaianList as $nilai)
    <tr>
        <td>#{{ $nilai->servis_id }}</td>
        <td>{{ $nilai->rating }} / 5</td>
        <td>{{ $nilai->ulasan ?? '-' }}</td>
        <td>{{ $nilai->created_at?->format('d-m-Y H:i') }}</td>
        <td>
            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-sm btn-warning"
                        onclick="toggleEditPenilaian({{ $nilai->id }})">
                    Edit
                </button>

                <form action="{{ route('penilaian.destroy', $nilai->id) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus ulasan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </div>
        </td>
    </tr>

    <tr id="edit-row-{{ $nilai->id }}" style="display: none;">
        <td colspan="5" class="bg-light">
            <form action="{{ route('penilaian.update', $nilai->id) }}" method="POST" class="p-2">
                @csrf
                @method('PUT')

                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Rating (1 - 5)</label>
                        <select name="rating" class="form-select form-select-sm" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ (int) $nilai->rating === $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label mb-1">Ulasan</label>
                        <input type="text"
                               name="ulasan"
                               class="form-control form-control-sm"
                               value="{{ old('ulasan', $nilai->ulasan) }}"
                               placeholder="Ubah ulasan...">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                        <button type="button"
                                class="btn btn-sm btn-secondary"
                                onclick="toggleEditPenilaian({{ $nilai->id }})">
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">Belum ada penilaian layanan servis.</td>
    </tr>
@endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<script>
function toggleEditPenilaian(id) {
    const row = document.getElementById('edit-row-' + id);
    if (!row) return;
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>