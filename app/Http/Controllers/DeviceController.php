<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('devices.index', [
            'devices' => Device::with('posyandu')->latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('devices.create', [
            'posyandus' => Posyandu::orderBy('name')->get(),
        ]);
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
            'posyandu_id' => ['nullable', 'exists:posyandus,id'],
            'device_code' => ['required', 'string', 'max:255', 'unique:devices,device_code'],
            'device_name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:online,offline,maintenance'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $data['api_token'] = Str::random(40);

        Device::create($data);

        return redirect()->route('devices.index')->with('status', 'Perangkat IoT berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device)
    {
        return view('devices.edit', [
            'device' => $device,
            'posyandus' => Posyandu::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'posyandu_id' => ['nullable', 'exists:posyandus,id'],
            'device_code' => ['required', 'string', 'max:255', 'unique:devices,device_code,' . $device->id],
            'device_name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:online,offline,maintenance'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $device->update($data);

        return redirect()->route('devices.index')->with('status', 'Perangkat IoT berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('devices.index')->with('status', 'Perangkat IoT berhasil dihapus.');
    }
}
