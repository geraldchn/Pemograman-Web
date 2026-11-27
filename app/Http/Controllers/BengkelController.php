<?php

namespace App\Http\Controllers;

use App\Models\Bengkel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BengkelController extends Controller
{
    /**
     * Halaman 1: Kelola Daftar Bengkel (khusus milik Admin Bengkel yang login).
     */
    public function index(Request $request): View
    {
        $query = Bengkel::where('user_id', Auth::id())->latest();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->status === 'buka') {
            $query->where('is_open', true);
        } elseif ($request->status === 'tutup') {
            $query->where('is_open', false);
        }

        $bengkels = $query->get();

        return view('bengkels.index', compact('bengkels'));
    }

    /**
     * Halaman 2: Form Pendaftaran Bengkel Baru.
     */
    public function create(): View
    {
        return view('bengkels.create');
    }

    /**
     * Simpan data bengkel baru (langsung tayang ke publik).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:25'],
            'link_maps' => ['nullable', 'url', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i', 'after:jam_buka'],
            'kuota_harian' => ['required', 'integer', 'min:1', 'max:200'],
            'is_open' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('bengkels', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['is_open'] = $request->boolean('is_open', true);

        Bengkel::create($validated);

        return redirect()
            ->route('bengkels.index')
            ->with('success', 'Bengkel baru berhasil didaftarkan dan langsung tayang.');
    }

    /**
     * Halaman 4: Detail Profil Bengkel (untuk Customer & Preview Admin).
     */
    public function show(Bengkel $bengkel): View
    {
        return view('bengkels.show', compact('bengkel'));
    }

    /**
     * Halaman 3: Form Edit Profil Bengkel.
     */
    public function edit(Bengkel $bengkel): View
    {
        abort_if($bengkel->user_id !== Auth::id(), 403, 'Anda tidak berhak mengedit bengkel ini.');

        return view('bengkels.edit', compact('bengkel'));
    }

    /**
     * Update data profil dan foto bengkel.
     */
    public function update(Request $request, Bengkel $bengkel): RedirectResponse
    {
        abort_if($bengkel->user_id !== Auth::id(), 403, 'Anda tidak berhak mengubah bengkel ini.');

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'telepon' => ['required', 'string', 'max:25'],
            'link_maps' => ['nullable', 'url', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i', 'after:jam_buka'],
            'kuota_harian' => ['required', 'integer', 'min:1', 'max:200'],
            'is_open' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('foto')) {
            if ($bengkel->foto && Storage::disk('public')->exists($bengkel->foto)) {
                Storage::disk('public')->delete($bengkel->foto);
            }
            $validated['foto'] = $request->file('foto')->store('bengkels', 'public');
        }

        $validated['is_open'] = $request->boolean('is_open');

        $bengkel->update($validated);

        return redirect()
            ->route('bengkels.index')
            ->with('success', 'Profil bengkel berhasil diperbarui.');
    }

    /**
     * Toggle status Buka / Tutup bengkel secara cepat.
     */
    public function toggleStatus(Bengkel $bengkel): RedirectResponse
    {
        abort_if($bengkel->user_id !== Auth::id(), 403, 'Anda tidak berhak mengubah status bengkel ini.');

        $bengkel->update([
            'is_open' => ! $bengkel->is_open,
        ]);

        $statusText = $bengkel->is_open ? 'dibuka' : 'ditutup';

        return back()->with('success', "Status bengkel {$bengkel->nama} berhasil {$statusText}.");
    }

    /**
     * Hapus bengkel (hanya jika belum memiliki riwayat booking).
     */
    public function destroy(Bengkel $bengkel): RedirectResponse
    {
        abort_if($bengkel->user_id !== Auth::id(), 403, 'Anda tidak berhak menghapus bengkel ini.');

        if ($bengkel->hasBookings()) {
            return back()->with('error', 'Bengkel tidak dapat dihapus karena sudah memiliki riwayat booking.');
        }

        if ($bengkel->foto && Storage::disk('public')->exists($bengkel->foto)) {
            Storage::disk('public')->delete($bengkel->foto);
        }

        $bengkel->delete();

        return redirect()
            ->route('bengkels.index')
            ->with('success', 'Data bengkel berhasil dihapus.');
    }
}