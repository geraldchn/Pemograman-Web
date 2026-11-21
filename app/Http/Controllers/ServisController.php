<?php

namespace App\Http\Controllers;

use App\Models\Servis;
use Illuminate\Http\Request;

class ServisController extends Controller
{
    public function index()
    {
        $servisList = Servis::with('penilaian')->latest()->get();
        return view('servis.index', compact('servisList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_servis'   => 'required|string|max:50|unique:servis,kode_servis',
            'nama_pelanggan'=> 'required|string|max:120',
            'jenis_layanan' => 'required|string|max:120',
            'keluhan'       => 'nullable|string',
        ]);

        Servis::create($validated);

        return redirect()->route('servis.index')->with('success', 'Data layanan servis berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, Servis $servis)
    {
        $validated = $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai',
        ]);

        $servis->update(['status' => $validated['status']]);

        return redirect()->route('servis.index')->with('success', 'Status layanan servis berhasil diperbarui.');
    }
}   