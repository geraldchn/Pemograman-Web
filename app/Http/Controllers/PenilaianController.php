<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use App\Models\Servis;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function store(Request $request, Servis $servis)
    {
        if ($servis->status !== 'selesai') {
            return back()->with('error', 'Penilaian hanya bisa diberikan setelah layanan selesai.');
        }

        if ($servis->penilaian) {
            return back()->with('error', 'Layanan servis ini sudah dinilai.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
        ]);

        Penilaian::create([
            'servis_id' => $servis->id,
            'rating'    => $validated['rating'],
            'ulasan'    => $validated['ulasan'] ?? null,
        ]);

        return redirect()->route('servis.index')->with('success', 'Terima kasih atas penilaian layanan servis Anda.');
    }
}