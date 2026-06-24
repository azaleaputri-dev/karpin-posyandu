<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class MobileDataController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $childrenQuery = Child::query();
        $measurementsQuery = Measurement::query();
        $posyandusQuery = Posyandu::query();

        if ($user->isPetugas() && $user->posyandu_id) {
            $childrenQuery->where('posyandu_id', $user->posyandu_id);
            $measurementsQuery->whereHas('child', function ($query) use ($user) {
                $query->where('posyandu_id', $user->posyandu_id);
            });
            $posyandusQuery->where('id', $user->posyandu_id);
        }

        $latestMeasurements = (clone $measurementsQuery)
            ->with(['child', 'device'])
            ->latest('measured_at')
            ->limit(5)
            ->get()
            ->map(function ($measurement) {
                return [
                    'id' => $measurement->id,
                    'child_name' => optional($measurement->child)->child_name,
                    'measured_at' => optional($measurement->measured_at)->format('d M Y H:i'),
                    'weight_kg' => (float) $measurement->weight_kg,
                    'height_cm' => (float) $measurement->height_cm,
                    'source' => $measurement->source,
                    'device_name' => optional($measurement->device)->device_name,
                ];
            })
            ->values();

        return response()->json([
            'stats' => [
                'posyandus' => (clone $posyandusQuery)->count(),
                'children' => (clone $childrenQuery)->count(),
                'measurements' => (clone $measurementsQuery)->count(),
                'measurements_this_month' => (clone $measurementsQuery)
                    ->whereMonth('measured_at', now()->month)
                    ->whereYear('measured_at', now()->year)
                    ->count(),
            ],
            'latest_measurements' => $latestMeasurements,
        ]);
    }

    public function children(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'gender' => $request->query('gender'),
            'posyandu_id' => $request->query('posyandu_id'),
        ];

        $query = Child::with('posyandu')->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('posyandu_id', $request->user()->posyandu_id);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('child_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('rfid_uid', 'like', '%' . $this->normalizeRfidUid($search) . '%')
                    ->orWhere('mother_name', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if ($request->user()->isAdmin() && ! empty($filters['posyandu_id'])) {
            $query->where('posyandu_id', $filters['posyandu_id']);
        }

        $children = $query->paginate(10)->withQueryString();

        return response()->json([
            'data' => collect($children->items())->map(function ($child) {
                return [
                    'id' => $child->id,
                    'child_name' => $child->child_name,
                    'nik' => $child->nik,
                    'rfid_uid' => $child->rfid_uid,
                    'gender' => $child->gender,
                    'birth_date' => optional($child->birth_date)->format('Y-m-d'),
                    'mother_name' => $child->mother_name,
                    'father_name' => $child->father_name,
                    'guardian_phone' => $child->guardian_phone,
                    'blood_type' => $child->blood_type,
                    'posyandu' => $child->posyandu ? [
                        'id' => $child->posyandu->id,
                        'name' => $child->posyandu->name,
                        'code' => $child->posyandu->code,
                    ] : null,
                ];
            })->values(),
            'meta' => [
                'current_page' => $children->currentPage(),
                'last_page' => $children->lastPage(),
                'per_page' => $children->perPage(),
                'total' => $children->total(),
            ],
        ]);
    }

    public function childDetail(Request $request, Child $child)
    {
        if (! $request->user()->isAdmin() && $child->posyandu_id !== $request->user()->posyandu_id) {
            abort(403);
        }

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

        return response()->json([
            'child' => [
                'id' => $child->id,
                'child_name' => $child->child_name,
                'nik' => $child->nik,
                'rfid_uid' => $child->rfid_uid,
                'gender' => $child->gender,
                'birth_date' => optional($child->birth_date)->format('Y-m-d'),
                'birth_age_years' => optional($child->birth_date)->age,
                'mother_name' => $child->mother_name,
                'father_name' => $child->father_name,
                'guardian_phone' => $child->guardian_phone,
                'blood_type' => $child->blood_type,
                'notes' => $child->notes,
                'posyandu' => $child->posyandu ? [
                    'id' => $child->posyandu->id,
                    'name' => $child->posyandu->name,
                    'code' => $child->posyandu->code,
                ] : null,
            ],
            'summary' => [
                'total_measurements' => $measurements->count(),
                'latest_weight' => $latestMeasurement ? (float) $latestMeasurement->weight_kg : null,
                'latest_height' => $latestMeasurement ? (float) $latestMeasurement->height_cm : null,
                'weight_gain' => $latestMeasurement && $firstMeasurement ? round((float) $latestMeasurement->weight_kg - (float) $firstMeasurement->weight_kg, 2) : null,
                'height_gain' => $latestMeasurement && $firstMeasurement ? round((float) $latestMeasurement->height_cm - (float) $firstMeasurement->height_cm, 2) : null,
            ],
            'nutrition_status' => $nutritionStatus,
            'measurements' => $measurements->sortByDesc('measured_at')->values()->map(function ($measurement) {
                return [
                    'id' => $measurement->id,
                    'measured_at' => optional($measurement->measured_at)->format('d M Y H:i'),
                    'measured_at_raw' => optional($measurement->measured_at)->toISOString(),
                    'weight_kg' => (float) $measurement->weight_kg,
                    'height_cm' => (float) $measurement->height_cm,
                    'temperature_c' => $measurement->temperature_c !== null ? (float) $measurement->temperature_c : null,
                    'source' => $measurement->source,
                    'notes' => $measurement->notes,
                    'device_id' => $measurement->device_id,
                    'device_name' => optional($measurement->device)->device_name,
                ];
            })->values(),
            'chart_data' => [
                'labels' => $measurements->map(function ($measurement) {
                    return $measurement->measured_at->format('d M');
                })->values(),
                'weights' => $measurements->pluck('weight_kg')->map(function ($value) {
                    return (float) $value;
                })->values(),
                'heights' => $measurements->pluck('height_cm')->map(function ($value) {
                    return (float) $value;
                })->values(),
            ],
            'devices' => Device::query()
                ->when($child->posyandu_id, function ($query) use ($child) {
                    $query->where('posyandu_id', $child->posyandu_id);
                })
                ->orderBy('device_name')
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'device_name' => $device->device_name,
                        'device_code' => $device->device_code,
                        'status' => $device->status,
                    ];
                })
                ->values(),
        ]);
    }

    public function storeChild(Request $request)
    {
        if ($request->user()->isAdmin()) {
            abort(403, 'Admin tidak dapat menambahkan data anak dari alur ini.');
        }

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

        $data['posyandu_id'] = $request->user()->posyandu_id;

        $data['rfid_uid'] = $this->normalizeRfidUid($data['rfid_uid'] ?? null);
        $child = Child::create($data);
        $child->load('posyandu');

        return response()->json([
            'message' => 'Data anak berhasil ditambahkan.',
            'child' => [
                'id' => $child->id,
                'child_name' => $child->child_name,
                'nik' => $child->nik,
                'rfid_uid' => $child->rfid_uid,
                'gender' => $child->gender,
                'birth_date' => optional($child->birth_date)->format('Y-m-d'),
                'mother_name' => $child->mother_name,
                'posyandu' => $child->posyandu ? [
                    'id' => $child->posyandu->id,
                    'name' => $child->posyandu->name,
                ] : null,
            ],
        ], 201);
    }

    public function updateChild(Request $request, Child $child)
    {
        if ($request->user()->isAdmin()) {
            abort(403, 'Admin tidak dapat mengubah data anak dari alur ini.');
        }

        if ($child->posyandu_id !== $request->user()->posyandu_id) {
            abort(403);
        }

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

        $data['posyandu_id'] = $request->user()->posyandu_id;

        $data['rfid_uid'] = $this->normalizeRfidUid($data['rfid_uid'] ?? null);
        $child->update($data);
        $child->load('posyandu');

        return response()->json([
            'message' => 'Data anak berhasil diperbarui.',
            'child' => [
                'id' => $child->id,
                'child_name' => $child->child_name,
                'nik' => $child->nik,
                'rfid_uid' => $child->rfid_uid,
                'gender' => $child->gender,
                'birth_date' => optional($child->birth_date)->format('Y-m-d'),
                'mother_name' => $child->mother_name,
                'posyandu' => $child->posyandu ? [
                    'id' => $child->posyandu->id,
                    'name' => $child->posyandu->name,
                ] : null,
            ],
        ]);
    }

    public function destroyChild(Request $request, Child $child)
    {
        if ($request->user()->isAdmin()) {
            abort(403, 'Admin tidak dapat menghapus data anak dari alur ini.');
        }

        if ($child->posyandu_id !== $request->user()->posyandu_id) {
            abort(403);
        }

        $child->delete();

        return response()->json([
            'message' => 'Data anak berhasil dihapus.',
        ]);
    }

    public function measurements(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'source' => $request->query('source'),
            'posyandu_id' => $request->query('posyandu_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $query = Measurement::with(['child.posyandu', 'device'])->latest('measured_at');

        if (! $request->user()->isAdmin()) {
            $query->whereHas('child', function ($builder) use ($request) {
                $builder->where('posyandu_id', $request->user()->posyandu_id);
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

        if ($request->user()->isAdmin() && ! empty($filters['posyandu_id'])) {
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

        $measurements = $query->paginate(10)->withQueryString();

        return response()->json([
            'data' => collect($measurements->items())->map(function ($measurement) {
                return [
                    'id' => $measurement->id,
                    'measured_at' => optional($measurement->measured_at)->format('Y-m-d H:i:s'),
                    'measured_at_raw' => optional($measurement->measured_at)->toISOString(),
                    'weight_kg' => (float) $measurement->weight_kg,
                    'height_cm' => (float) $measurement->height_cm,
                    'temperature_c' => $measurement->temperature_c !== null ? (float) $measurement->temperature_c : null,
                    'source' => $measurement->source,
                    'notes' => $measurement->notes,
                    'child' => $measurement->child ? [
                        'id' => $measurement->child->id,
                        'child_name' => $measurement->child->child_name,
                        'nik' => $measurement->child->nik,
                        'posyandu_name' => optional($measurement->child->posyandu)->name,
                    ] : null,
                    'device' => $measurement->device ? [
                        'id' => $measurement->device->id,
                        'device_name' => $measurement->device->device_name,
                    ] : null,
                ];
            })->values(),
            'meta' => [
                'current_page' => $measurements->currentPage(),
                'last_page' => $measurements->lastPage(),
                'per_page' => $measurements->perPage(),
                'total' => $measurements->total(),
            ],
        ]);
    }

    public function storeMeasurement(Request $request)
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

        $child = Child::findOrFail($data['child_id']);
        $user = $request->user();

        if (! $user->isAdmin()) {
            if ($child->posyandu_id !== $user->posyandu_id) {
                abort(403);
            }

            $data['source'] = 'manual';
        }

        if (! empty($data['device_id'])) {
            $device = Device::findOrFail($data['device_id']);

            if (! $user->isAdmin() && $device->posyandu_id !== $user->posyandu_id) {
                abort(403);
            }

            if ($child->posyandu_id && $device->posyandu_id && $child->posyandu_id !== $device->posyandu_id) {
                abort(422, 'Perangkat tidak sesuai dengan posyandu anak.');
            }
        }

        $measurement = Measurement::create($data);
        $measurement->load(['child', 'device']);

        return response()->json([
            'message' => 'Data pengukuran berhasil ditambahkan.',
            'measurement' => [
                'id' => $measurement->id,
                'child_id' => $measurement->child_id,
                'measured_at' => optional($measurement->measured_at)->format('d M Y H:i'),
                'weight_kg' => (float) $measurement->weight_kg,
                'height_cm' => (float) $measurement->height_cm,
                'temperature_c' => $measurement->temperature_c !== null ? (float) $measurement->temperature_c : null,
                'source' => $measurement->source,
                'device_name' => optional($measurement->device)->device_name,
            ],
        ], 201);
    }

    public function updateMeasurement(Request $request, Measurement $measurement)
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

        $measurement->loadMissing('child');
        $user = $request->user();

        if (! $user->isAdmin() && optional($measurement->child)->posyandu_id !== $user->posyandu_id) {
            abort(403);
        }

        $child = Child::findOrFail($data['child_id']);

        if (! $user->isAdmin()) {
            if ($child->posyandu_id !== $user->posyandu_id) {
                abort(403);
            }

            $data['source'] = 'manual';
        }

        if (! empty($data['device_id'])) {
            $device = Device::findOrFail($data['device_id']);

            if (! $user->isAdmin() && $device->posyandu_id !== $user->posyandu_id) {
                abort(403);
            }

            if ($child->posyandu_id && $device->posyandu_id && $child->posyandu_id !== $device->posyandu_id) {
                abort(422, 'Perangkat tidak sesuai dengan posyandu anak.');
            }
        }

        $measurement->update($data);
        $measurement->load(['child', 'device']);

        return response()->json([
            'message' => 'Data pengukuran berhasil diperbarui.',
            'measurement' => [
                'id' => $measurement->id,
                'child_id' => $measurement->child_id,
                'measured_at' => optional($measurement->measured_at)->format('d M Y H:i'),
                'weight_kg' => (float) $measurement->weight_kg,
                'height_cm' => (float) $measurement->height_cm,
                'temperature_c' => $measurement->temperature_c !== null ? (float) $measurement->temperature_c : null,
                'source' => $measurement->source,
                'device_name' => optional($measurement->device)->device_name,
            ],
        ]);
    }

    public function destroyMeasurement(Request $request, Measurement $measurement)
    {
        $measurement->loadMissing('child');
        $user = $request->user();

        if (! $user->isAdmin() && optional($measurement->child)->posyandu_id !== $user->posyandu_id) {
            abort(403);
        }

        $measurement->delete();

        return response()->json([
            'message' => 'Data pengukuran berhasil dihapus.',
        ]);
    }

    protected function buildNutritionStatus(Child $child, $latestMeasurement)
    {
        if (! $latestMeasurement) {
            return [
                'overall' => 'Belum ada data',
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

        if ($weightStatus === 'Berat baik' && $heightStatus === 'Tinggi sesuai') {
            $overall = 'Pertumbuhan sesuai';
        } elseif ($weightStatus === 'Berat kurang' || $heightStatus === 'Tinggi kurang') {
            $overall = 'Risiko gizi kurang';
        } elseif ($weightStatus === 'Berat lebih') {
            $overall = 'Risiko berat lebih';
        }

        return [
            'overall' => $overall,
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
