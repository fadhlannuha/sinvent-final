<?php

namespace App\Http\Controllers;

use App\Models\barangmasuk;
use App\Models\barangkeluar;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasukController extends Controller
{
    public function index()
    {
        #$rsetBarangmasuk = barangmasuk::latest()->paginate(10);
        $Barangmasuk = barangmasuk::with('barang')->latest()->paginate(10);
        return view('v_barangmasuk.index', compact('Barangmasuk'));
        //return view('v_barangmasuk.index');
    }

    public function create()
    {
        $barangOptions = Barang::all();
        #dd($barangOptions);

        return view('v_barangmasuk.create', compact('barangOptions'));
    }

    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'tgl_masuk' => 'required',
            'qty_masuk' => 'required|numeric|min:1',
            'barang_id' => 'required',
        ]);

        // Simpan data barang masuk ke database
        Barangmasuk::create([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
            'barang_id' => $request->barang_id,
        ]);


        return redirect()->route('barangmasuk.index')->with('success', 'Data barang masuk berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Mengambil data barang masuk berdasarkan ID
        $rsetBarangmasuk = Barangmasuk::findOrFail($id);
        $barangOptions = Barang::all();

        return view('v_barangmasuk.edit', compact('rsetBarangmasuk', 'barangOptions'));
    }

    public function update(Request $request, $id)
    {
        // Validasi data
        $request->validate([
            'tgl_masuk' => 'required',
            'qty_masuk' => 'required|numeric|min:1',
            'barang_id' => 'required',
        ]);

        // Mengupdate data barang masuk berdasarkan ID
        $rsetBarangmasuk = Barangmasuk::findOrFail($id);
        $rsetBarangmasuk->update([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
            'barang_id' => $request->barang_id,
        ]);

        return redirect()->route('barangmasuk.index')->with('success', 'Data barang masuk berhasil diupdate');
    }

    public function show($id)
    {
        $barangmasuk = barangmasuk::with('barang')->findOrFail($id);
        return view('v_barangmasuk.show', compact('barangmasuk'));
    }
    public function destroy($id)
    {
        // Periksa apakah ada entri barang keluar yang terkait dengan barang masuk yang akan dihapus
        // $barangKeluar = DB::table('barangkeluar')->where('id', $id);
        // if ($barangKeluar) {
        //     // Jika ada barang keluar terkait, kembalikan pesan kesalahan
        //     return redirect()->route('barangmasuk.index')->with(['Gagal' => 'Data tidak dapat dihapus karena terdapat transaksi barang keluar terkait!']);
        // } else {
        //     // Jika tidak ada barang keluar terkait, hapus data barang masuk
        //     Barangmasuk::findOrFail($id)->delete();
        //     return redirect()->route('barangmasuk.index')->with(['success' => 'Data Barang Masuk Berhasil Dihapus!']);
        // }

        $barangmasuk = Barangmasuk::findOrFail($id);

        // Check if there are any related barangkeluar entries
        $barangkeluar = Barangkeluar::where('barang_id', $barangmasuk->barang_id)
            ->where('tgl_keluar', '>=', $barangmasuk->tgl_masuk)
            ->first();

        if ($barangkeluar) {
            return redirect()->route('barangmasuk.index')->with('Gagal', 'Tidak dapat menghapus barang masuk karena ada barang keluar terkait.');
        }

        $barangmasuk->delete();

        return redirect()->route('barangmasuk.index')->with('success', 'Data barang masuk berhasil dihapus');
    }


}
