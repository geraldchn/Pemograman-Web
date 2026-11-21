<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranPenilaianController extends Controller
{
    public function pembayaranIndex()
    {
        $pembayaranList = Pembayaran::latest()->get();
        $bookingList = DB::table('service_bookings')->orderByDesc('id')->get();

        return view('pembayaran.index', compact('pembayaranList', 'bookingList'));
    }

    public function storePembayaran(Request $request)
    {
        $validated = $request->validate([
            'service_booking_id' => 'required|integer|exists:service_bookings,id',
            'kode_referensi' => 'required|string|max:50|unique:pembayaran,kode_referensi',
            'nama_pelanggan' => 'required|string|max:120',
            'jumlah_tagihan' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string|max:50',
        ]);

        Pembayaran::create([
            'service_booking_id' => $validated['service_booking_id'],
            'kode_referensi' => $validated['kode_referensi'],
            'nama_pelanggan' => $validated['nama_pelanggan'],
            'jumlah_tagihan' => $validated['jumlah_tagihan'],
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'status_pembayaran' => 'Belum Bayar',
        ]);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Data pembayaran layanan servis berhasil disimpan.');
    }

    public function updateStatusPembayaran(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'status_pembayaran' => 'required|in:Belum Bayar,Menunggu Verifikasi,Lunas',
        ]);

        $pembayaran->update([
            'status_pembayaran' => $validated['status_pembayaran'],
            'dibayar_pada' => $validated['status_pembayaran'] === 'Lunas'
                ? ($pembayaran->dibayar_pada ?? now())
                : null,
        ]);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function penilaianIndex()
    {
        $penilaianList = Penilaian::latest()->get();
        $servisList = DB::table('servis')->orderByDesc('id')->get();

        return view('penilaian.index', compact('penilaianList', 'servisList'));
    }

    public function storePenilaian(Request $request)
    {
        $validated = $request->validate([
            'servis_id' => 'required|integer|exists:servis,id',
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
        ]);

        Penilaian::create([
            'servis_id' => $validated['servis_id'],
            'rating' => $validated['rating'],
            'ulasan' => $validated['ulasan'] ?? null,
        ]);

        return redirect()->route('penilaian.index')
            ->with('success', 'Penilaian layanan servis berhasil dikirim.');
    }

    public function updatePenilaian(Request $request, Penilaian $penilaian)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
        ]);

        $penilaian->update([
            'rating' => $validated['rating'],
            'ulasan' => $validated['ulasan'] ?? null,
        ]);

        return redirect()->route('penilaian.index')
            ->with('success', 'Ulasan penilaian berhasil diperbarui.');
    }

    public function destroyPenilaian(Penilaian $penilaian)
    {
        $penilaian->delete();

        return redirect()->route('penilaian.index')
            ->with('success', 'Ulasan penilaian berhasil dihapus.');
    }
}