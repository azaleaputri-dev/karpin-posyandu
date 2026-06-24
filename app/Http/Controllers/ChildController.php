<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Posyandu;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ChildController extends Controller
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
            'gender' => request('gender'),
            'posyandu_id' => request('posyandu_id'),
        ];

        $query = Child::with('posyandu')->latest();

        if (! $this->isAdmin()) {
            $query->where('posyandu_id', $this->petugasPosyanduId());
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('child_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('mother_name', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if ($this->isAdmin() && ! empty($filters['posyandu_id'])) {
            $query->where('posyandu_id', $filters['posyandu_id']);
        }

        $posyandus = $this->isAdmin()
            ? Posyandu::orderBy('name')->get()
            : Posyandu::where('id', $this->petugasPosyanduId())->get();

        return view('children.index', [
            'children' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'posyandus' => $posyandus,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->ensureChildMutationAllowed();

        $posyandus = $this->isAdmin()
            ? Posyandu::orderBy('name')->get()
            : Posyandu::where('id', $this->petugasPosyanduId())->get();

        return view('children.create', [
            'posyandus' => $posyandus,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function show(Child $child)
    {
        return view('children.show', $this->buildChildDetailPayload($child));
    }

    public function exportPdf(Child $child)
    {
        $payload = $this->buildChildDetailPayload($child);

        $pdf = Pdf::loadView('children.pdf', $payload)->setPaper('a4', 'portrait');

        return $pdf->download('riwayat-anak-' . $child->id . '.pdf');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->ensureChildMutationAllowed();
        $request->merge(['rfid_uid' => $this->normalizeRfidUid($request->input('rfid_uid'))]);

        $data = $request->validate([
            'posyandu_id' => ['required', 'exists:posyandus,id'],
            'nik' => ['nullable', 'string', 'max:32', 'unique:children,nik'],
            'rfid_uid' => ['nullable', 'string', 'max:64', 'unique:children,rfid_uid'],
            'child_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['required', 'date'],
            'mother_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $this->isAdmin()) {
            $data['posyandu_id'] = $this->petugasPosyanduId();
        }

        $data['rfid_uid'] = $this->normalizeRfidUid($data['rfid_uid'] ?? null);
        Child::create($data);

        return redirect()->route('children.index')->with('status', 'Data anak berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function edit(Child $child)
    {
        $this->ensureChildMutationAllowed();
        $this->ensureChildAccessible($child);

        $posyandus = $this->isAdmin()
            ? Posyandu::orderBy('name')->get()
            : Posyandu::where('id', $this->petugasPosyanduId())->get();

        return view('children.edit', [
            'child' => $child,
            'posyandus' => $posyandus,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Child $child)
    {
        $this->ensureChildMutationAllowed();
        $this->ensureChildAccessible($child);
        $request->merge(['rfid_uid' => $this->normalizeRfidUid($request->input('rfid_uid'))]);

        $data = $request->validate([
            'posyandu_id' => ['required', 'exists:posyandus,id'],
            'nik' => ['nullable', 'string', 'max:32', 'unique:children,nik,' . $child->id],
            'rfid_uid' => ['nullable', 'string', 'max:64', 'unique:children,rfid_uid,' . $child->id],
            'child_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['required', 'date'],
            'mother_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $this->isAdmin()) {
            $data['posyandu_id'] = $this->petugasPosyanduId();
        }

        $data['rfid_uid'] = $this->normalizeRfidUid($data['rfid_uid'] ?? null);
        $child->update($data);

        return redirect()->route('children.index')->with('status', 'Data anak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function destroy(Child $child)
    {
        $this->ensureChildMutationAllowed();
        $this->ensureChildAccessible($child);

        $child->delete();

        return redirect()->route('children.index')->with('status', 'Data anak berhasil dihapus.');
    }

    protected function ensureChildAccessible(Child $child)
    {
        if (! $this->isAdmin() && $child->posyandu_id !== $this->petugasPosyanduId()) {
            abort(403);
        }
    }

    protected function ensureChildMutationAllowed()
    {
        abort_unless($this->currentUser() && ($this->isAdmin() || $this->currentUser()->isPetugas()), 403);
    }

    protected function buildChildDetailPayload(Child $child)
    {
        $this->ensureChildAccessible($child);

        $child->load([
            'posyandu',
            'measurements' => function ($query) {
                $query->with('device')->orderBy('measured_at');
            },
        ]);

        $measurements = $child->measurements;
        $latestMeasurement = $measurements->last();
        $firstMeasurement = $measurements->first();
        $nutritionStatus = $this->buildNutritionStatus($child, $latestMeasurement);

        return [
            'child' => $child,
            'measurements' => $measurements->sortByDesc('measured_at')->values(),
            'chartData' => [
                'labels' => $measurements->map(function ($measurement) {
                    return $measurement->measured_at->format('d M Y');
                })->values(),
                'weights' => $measurements->pluck('weight_kg')->map(function ($value) {
                    return (float) $value;
                })->values(),
                'heights' => $measurements->pluck('height_cm')->map(function ($value) {
                    return (float) $value;
                })->values(),
            ],
            'summary' => [
                'total_measurements' => $measurements->count(),
                'latest_weight' => $latestMeasurement ? $latestMeasurement->weight_kg : null,
                'latest_height' => $latestMeasurement ? $latestMeasurement->height_cm : null,
                'weight_gain' => $latestMeasurement && $firstMeasurement ? (float) $latestMeasurement->weight_kg - (float) $firstMeasurement->weight_kg : null,
                'height_gain' => $latestMeasurement && $firstMeasurement ? (float) $latestMeasurement->height_cm - (float) $firstMeasurement->height_cm : null,
            ],
            'nutritionStatus' => $nutritionStatus,
        ];
    }

    protected function buildNutritionStatus(Child $child, $latestMeasurement)
    {
        if (! $latestMeasurement) {
            return [
                'overall' => 'Belum ada data',
                'badge' => 'bg-slate-100 text-slate-700',
                'weight' => null,
                'height' => null,
                'note' => 'Belum ada pengukuran untuk menilai status gizi awal.',
            ];
        }

        $ageMonths = $child->birth_date->diffInMonths($latestMeasurement->measured_at);
        $expectedWeight = max(2.5, 2 + ($ageMonths * 0.25));
        $expectedHeight = max(45, 50 + ($ageMonths * 0.5));

        $weightRatio = (float) $latestMeasurement->weight_kg / $expectedWeight;
        $heightRatio = (float) $latestMeasurement->height_cm / $expectedHeight;

        $weightStatus = $this->classifyRatio($weightRatio, [
            0.85 => 'Berat kurang',
            1.15 => 'Berat baik',
            INF => 'Berat lebih',
        ]);

        $heightStatus = $this->classifyRatio($heightRatio, [
            0.92 => 'Tinggi kurang',
            1.08 => 'Tinggi sesuai',
            INF => 'Tinggi di atas rata-rata',
        ]);

        $overall = 'Perlu perhatian';
        $badge = 'bg-amber-100 text-amber-800';

        if ($weightStatus === 'Berat baik' && $heightStatus === 'Tinggi sesuai') {
            $overall = 'Pertumbuhan sesuai';
            $badge = 'bg-emerald-100 text-emerald-800';
        } elseif ($weightStatus === 'Berat kurang' || $heightStatus === 'Tinggi kurang') {
            $overall = 'Risiko gizi kurang';
            $badge = 'bg-rose-100 text-rose-800';
        } elseif ($weightStatus === 'Berat lebih') {
            $overall = 'Risiko berat lebih';
            $badge = 'bg-cyan-100 text-cyan-800';
        }

        return [
            'overall' => $overall,
            'badge' => $badge,
            'weight' => $weightStatus,
            'height' => $heightStatus,
            'note' => 'Indikator awal berbasis perbandingan sederhana berat per usia dan tinggi per usia. Tetap perlu verifikasi standar antropometri posyandu atau puskesmas.',
        ];
    }

    protected function classifyRatio($ratio, array $thresholds)
    {
        foreach ($thresholds as $threshold => $label) {
            if ($ratio <= $threshold) {
                return $label;
            }
        }

        return '';
    }

    protected function normalizeRfidUid(?string $rfidUid): ?string
    {
        if (! $rfidUid) {
            return null;
        }

        return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rfidUid));
    }
}
