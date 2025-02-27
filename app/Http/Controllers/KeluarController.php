<?php

namespace App\Http\Controllers;
use App\Models\barangkeluar;
use App\Models\barangmasuk;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeluarController extends Controller
{
    public function index()
    {
        $Barangkeluar = barangkeluar::with('barang')->latest()->paginate(10);
        return view('v_barangkeluar.index', compact('Barangkeluar'));
    }

    public function create()
    {
        $barangOptions = Barang::all();       
        return view('v_barangkeluar.create', compact('barangOptions'));
    }

    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'tgl_keluar' => 'required|date',
            'qty_keluar' => 'required|integer|min:1',
            'barang_id' => 'required|exists:barang,id',
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        // Cek apakah stok mencukupi
        if ($barang->stok < $request->qty_keluar) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah Barang yang keluar melebihi stok yang ada.']);
        }

        // Cek apakah tanggal keluar lebih awal dari tanggal barang masuk terakhir
        $barangMasukTerakhir = DB::table('barangmasuk')
            ->where('barang_id', $request->barang_id)
            ->orderBy('tgl_masuk', 'desc')
            ->first();

        if ($barangMasukTerakhir && $request->tgl_keluar < $barangMasukTerakhir->tgl_masuk) {
            return redirect()->back()->withInput()->withErrors(['tgl_keluar' => 'Tanggal keluar tidak bisa lebih awal dari tanggal Barang Masuk terakhir.']);
        }

        // Simpan data barang keluar ke database
        barangkeluar::create([
            'tgl_keluar' => $request->tgl_keluar,
            'qty_keluar' => $request->qty_keluar,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangkeluar.index')->with('success', 'Data Barang Keluar berhasil disimpan');
    }

    public function show($id)
    {
        $barangkeluar = barangkeluar::with('barang')->findOrFail($id);
        return view('v_barangkeluar.show', compact('barangkeluar'));
    }

    public function edit($id)
    {
        $barangkeluar = Barangkeluar::findOrFail($id);
        $barangOptions = Barang::all();
        
        return view('v_barangkeluar.edit', compact('barangkeluar', 'barangOptions'));
    }

    public function update(Request $request, $id)
    {
        // Validasi data
        $request->validate([
            'tgl_keluar' => 'required|date',
            'qty_keluar' => 'required|integer|min:1',
            'barang_id' => 'required|exists:barang,id',
        ]);

        // Temukan data barang keluar berdasarkan ID
        $barangkeluar = Barangkeluar::findOrFail($id);

        // Temukan barang terkait untuk validasi stok
        $barang = Barang::findOrFail($request->barang_id);

        // Cek apakah stok mencukupi setelah update
        $stokSetelahUpdate = $barang->stok + $barangkeluar->qty_keluar - $request->qty_keluar;
        if ($stokSetelahUpdate < 0) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah Barang yang keluar melebihi stok yang ada.']);
        }

        // Cek apakah tanggal keluar lebih awal dari tanggal barang masuk terakhir
        $barangMasukTerakhir = DB::table('barangmasuk')
            ->where('barang_id', $request->barang_id)
            ->orderBy('tgl_masuk', 'desc')
            ->first();

        if ($barangMasukTerakhir && $request->tgl_keluar < $barangMasukTerakhir->tgl_masuk) {
            return redirect()->back()->withInput()->withErrors(['tgl_keluar' => 'Tanggal keluar tidak bisa lebih awal dari tanggal Barang Masuk terakhir.']);
        }

        // Perbarui data barang keluar
        $barangkeluar->update([
            'tgl_keluar' => $request->tgl_keluar,
            'qty_keluar' => $request->qty_keluar,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangkeluar.index')->with('success', 'Data Barang Keluar berhasil diupdate');
    }

    public function destroy($id)
    {
        // Hapus data barang keluar berdasarkan ID
        Barangkeluar::findOrFail($id)->delete();
        return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Dihapus!']);
    }
}
