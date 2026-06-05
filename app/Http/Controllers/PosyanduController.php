<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use Illuminate\Http\Request;

class PosyanduController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('posyandus.index', [
            'posyandus' => Posyandu::latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posyandus.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:posyandus,code'],
            'address' => ['required', 'string'],
            'village' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string'],
        ]);

        Posyandu::create($data);

        return redirect()->route('posyandus.index')->with('status', 'Data posyandu berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Posyandu  $posyandu
     * @return \Illuminate\Http\Response
     */
    public function edit(Posyandu $posyandu)
    {
        return view('posyandus.edit', compact('posyandu'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Posyandu  $posyandu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Posyandu $posyandu)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:posyandus,code,' . $posyandu->id],
            'address' => ['required', 'string'],
            'village' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string'],
        ]);

        $posyandu->update($data);

        return redirect()->route('posyandus.index')->with('status', 'Data posyandu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Posyandu  $posyandu
     * @return \Illuminate\Http\Response
     */
    public function destroy(Posyandu $posyandu)
    {
        $posyandu->delete();

        return redirect()->route('posyandus.index')->with('status', 'Data posyandu berhasil dihapus.');
    }
}
