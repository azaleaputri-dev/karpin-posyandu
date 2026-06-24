<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\Posyandu;
use App\Models\RfidScan;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filters = [
            'search' => request('search'),
            'source' => request('source'),
            'posyandu_id' => request('posyandu_id'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
        ];

        $query = Measurement::with(['child', 'device'])->latest('measured_at');

        if (! $this->isAdmin()) {
            $query->whereHas('child', function ($builder) {
                $builder->where('posyandu_id', $this->petugasPosyanduId());
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('child', function ($builder) use ($search) {
                $builder->where('child_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if ($this->isAdmin() && ! empty($filters['posyandu_id'])) {
            $query->whereHas('child', function ($builder) use ($filters) {
                $builder->where('posyandu_id', $filters['posyandu_id']);
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('measured_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('measured_at', '<=', $filters['date_to']);
        }

        $posyandus = $this->isAdmin()
            ? Posyandu::orderBy('name')->get()
            : Posyandu::where('id', $this->petugasPosyanduId())->get();

        return view('measurements.index', [
            'measurements' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'posyandus' => $posyandus,
            'initialScanId' => RfidScan::max('id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $children = Child::orderBy('child_name');
        $devices = Device::orderBy('device_name');

        if (! $this->isAdmin()) {
            $children->where('posyandu_id', $this->petugasPosyanduId());
            $devices->where('posyandu_id', $this->petugasPosyanduId());
        }

        return view('measurements.create', [
            'children' => $children->get(),
            'devices' => $devices->get(),
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
            'child_id' => ['required', 'exists:children,id'],
            'device_id' => ['nullable', 'exists:devices,id'],
            'measured_at' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'height_cm' => ['required', 'numeric', 'min:0'],
            'temperature_c' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'in:manual,iot'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $this->isAdmin()) {
            $child = Child::findOrFail($data['child_id']);
            if ($child->posyandu_id !== $this->petugasPosyanduId()) {
                abort(403);
            }

            if (! empty($data['device_id'])) {
                $device = Device::findOrFail($data['device_id']);
                if ($device->posyandu_id !== $this->petugasPosyanduId()) {
                    abort(403);
                }
            }
        }

        Measurement::create($data);

        return redirect()->route('measurements.index')->with('status', 'Data pengukuran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Measurement  $measurement
     * @return \Illuminate\Http\Response
     */
    public function edit(Measurement $measurement)
    {
        $this->ensureMeasurementAccessible($measurement);

        $children = Child::orderBy('child_name');
        $devices = Device::orderBy('device_name');

        if (! $this->isAdmin()) {
            $children->where('posyandu_id', $this->petugasPosyanduId());
            $devices->where('posyandu_id', $this->petugasPosyanduId());
        }

        return view('measurements.edit', [
            'measurement' => $measurement,
            'children' => $children->get(),
            'devices' => $devices->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Measurement  $measurement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Measurement $measurement)
    {
        $this->ensureMeasurementAccessible($measurement);

        $data = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'device_id' => ['nullable', 'exists:devices,id'],
            'measured_at' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'height_cm' => ['required', 'numeric', 'min:0'],
            'temperature_c' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'in:manual,iot'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $this->isAdmin()) {
            $child = Child::findOrFail($data['child_id']);
            if ($child->posyandu_id !== $this->petugasPosyanduId()) {
                abort(403);
            }

            if (! empty($data['device_id'])) {
                $device = Device::findOrFail($data['device_id']);
                if ($device->posyandu_id !== $this->petugasPosyanduId()) {
                    abort(403);
                }
            }
        }

        $measurement->update($data);

        return redirect()->route('measurements.index')->with('status', 'Data pengukuran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Measurement  $measurement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Measurement $measurement)
    {
        $this->ensureMeasurementAccessible($measurement);

        $measurement->delete();

        return redirect()->route('measurements.index')->with('status', 'Data pengukuran berhasil dihapus.');
    }

    protected function ensureMeasurementAccessible(Measurement $measurement)
    {
        $measurement->loadMissing('child');

        if (! $this->isAdmin() && optional($measurement->child)->posyandu_id !== $this->petugasPosyanduId()) {
            abort(403);
        }
    }
}
