<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\Posyandu;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = $this->currentUser();

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

        $latestMeasurements = (clone $measurementsQuery)->with(['child', 'device'])
            ->latest('measured_at')
            ->take(5)
            ->get();

        $totalChildren = (clone $childrenQuery)->count();
        $totalMeasurements = (clone $measurementsQuery)->count();
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        $childrenMeasuredThisMonth = (clone $measurementsQuery)
            ->whereBetween('measured_at', [$currentMonthStart, $currentMonthEnd])
            ->distinct()
            ->count('child_id');

        $measurementsThisMonth = (clone $measurementsQuery)
            ->whereBetween('measured_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $latestMeasurementSubquery = DB::table('measurements as m1')
            ->select('m1.child_id', DB::raw('MAX(m1.measured_at) as latest_measured_at'))
            ->groupBy('m1.child_id');

        $latestAveragesQuery = DB::table('children')
            ->joinSub($latestMeasurementSubquery, 'latest_measurements', function ($join) {
                $join->on('latest_measurements.child_id', '=', 'children.id');
            })
            ->join('measurements as lm', function ($join) {
                $join->on('lm.child_id', '=', 'children.id')
                    ->on('lm.measured_at', '=', 'latest_measurements.latest_measured_at');
            });

        if ($user->isPetugas() && $user->posyandu_id) {
            $latestAveragesQuery->where('children.posyandu_id', $user->posyandu_id);
        }

        $latestAverages = $latestAveragesQuery
            ->selectRaw('AVG(lm.weight_kg) as avg_latest_weight, AVG(lm.height_cm) as avg_latest_height')
            ->first();

        $monthlyTrend = [
            'labels' => collect(),
            'measurementCounts' => collect(),
            'measuredChildrenCounts' => collect(),
            'monitoringIndexes' => collect(),
            'avgWeights' => collect(),
            'avgHeights' => collect(),
        ];

        foreach (range(5, 0) as $monthsAgo) {
            $monthStart = now()->subMonths($monthsAgo)->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();
            $measuredChildrenCount = (clone $measurementsQuery)
                ->whereBetween('measured_at', [$monthStart, $monthEnd])
                ->distinct()
                ->count('child_id');
            $measurementCount = (clone $measurementsQuery)
                ->whereBetween('measured_at', [$monthStart, $monthEnd])
                ->count();
            $monthAverages = (clone $measurementsQuery)
                ->whereBetween('measured_at', [$monthStart, $monthEnd])
                ->selectRaw('AVG(weight_kg) as avg_weight, AVG(height_cm) as avg_height')
                ->first();

            $monthlyTrend['labels']->push($monthStart->locale('id')->translatedFormat('M Y'));
            $monthlyTrend['measurementCounts']->push($measurementCount);
            $monthlyTrend['measuredChildrenCounts']->push($measuredChildrenCount);
            $monthlyTrend['monitoringIndexes']->push($totalChildren > 0 ? round(($measuredChildrenCount / $totalChildren) * 100, 1) : 0);
            $monthlyTrend['avgWeights']->push($monthAverages && $monthAverages->avg_weight !== null ? round((float) $monthAverages->avg_weight, 2) : null);
            $monthlyTrend['avgHeights']->push($monthAverages && $monthAverages->avg_height !== null ? round((float) $monthAverages->avg_height, 2) : null);
        }

        $monthlyTrend = collect($monthlyTrend)->map(function ($values) {
            return $values instanceof \Illuminate\Support\Collection ? $values->values() : $values;
        })->all();

        $posyanduAggregate = collect();
        $posyanduChart = [
            'labels' => [],
            'children' => [],
            'measurements' => [],
            'monitoringIndexes' => [],
        ];

        if ($user->isAdmin()) {
            $childrenCountSubquery = DB::table('children')
                ->select('posyandu_id', DB::raw('COUNT(*) as children_count'))
                ->groupBy('posyandu_id');

            $measurementCountSubquery = DB::table('children')
                ->join('measurements', 'measurements.child_id', '=', 'children.id')
                ->select('children.posyandu_id', DB::raw('COUNT(measurements.id) as measurements_count'))
                ->groupBy('children.posyandu_id');

            $monthlyCoverageSubquery = DB::table('children')
                ->leftJoin('measurements', function ($join) use ($currentMonthStart, $currentMonthEnd) {
                    $join->on('measurements.child_id', '=', 'children.id')
                        ->whereBetween('measurements.measured_at', [$currentMonthStart, $currentMonthEnd]);
                })
                ->select(
                    'children.posyandu_id',
                    DB::raw('COUNT(DISTINCT measurements.child_id) as measured_children_count'),
                    DB::raw('COUNT(measurements.id) as month_measurements_count')
                )
                ->groupBy('children.posyandu_id');

            $latestAverageSubquery = DB::table('children')
                ->joinSub($latestMeasurementSubquery, 'latest_measurements', function ($join) {
                    $join->on('latest_measurements.child_id', '=', 'children.id');
                })
                ->join('measurements as lm', function ($join) {
                    $join->on('lm.child_id', '=', 'children.id')
                        ->on('lm.measured_at', '=', 'latest_measurements.latest_measured_at');
                })
                ->select(
                    'children.posyandu_id',
                    DB::raw('AVG(lm.weight_kg) as avg_latest_weight'),
                    DB::raw('AVG(lm.height_cm) as avg_latest_height')
                )
                ->groupBy('children.posyandu_id');

            $posyanduAggregate = Posyandu::query()
                ->leftJoinSub($childrenCountSubquery, 'children_totals', function ($join) {
                    $join->on('children_totals.posyandu_id', '=', 'posyandus.id');
                })
                ->leftJoinSub($measurementCountSubquery, 'measurement_totals', function ($join) {
                    $join->on('measurement_totals.posyandu_id', '=', 'posyandus.id');
                })
                ->leftJoinSub($monthlyCoverageSubquery, 'monthly_coverage', function ($join) {
                    $join->on('monthly_coverage.posyandu_id', '=', 'posyandus.id');
                })
                ->leftJoinSub($latestAverageSubquery, 'latest_averages', function ($join) {
                    $join->on('latest_averages.posyandu_id', '=', 'posyandus.id');
                })
                ->orderBy('posyandus.name')
                ->select([
                    'posyandus.id',
                    'posyandus.name',
                    DB::raw('COALESCE(children_totals.children_count, 0) as children_count'),
                    DB::raw('COALESCE(measurement_totals.measurements_count, 0) as measurements_count'),
                    DB::raw('COALESCE(monthly_coverage.measured_children_count, 0) as measured_children_count'),
                    DB::raw('COALESCE(monthly_coverage.month_measurements_count, 0) as month_measurements_count'),
                    DB::raw('latest_averages.avg_latest_weight as avg_latest_weight'),
                    DB::raw('latest_averages.avg_latest_height as avg_latest_height'),
                ])
                ->get()
                ->map(function ($item) {
                    $childrenCount = (int) $item->children_count;
                    $item->avg_latest_weight = $item->avg_latest_weight !== null ? round((float) $item->avg_latest_weight, 2) : null;
                    $item->avg_latest_height = $item->avg_latest_height !== null ? round((float) $item->avg_latest_height, 2) : null;
                    $item->monitoring_index = $childrenCount > 0
                        ? round(((int) $item->measured_children_count / $childrenCount) * 100, 1)
                        : 0;

                    return $item;
                });

            $posyanduChart = [
                'labels' => $posyanduAggregate->pluck('name')->values(),
                'children' => $posyanduAggregate->pluck('children_count')->map(fn ($value) => (int) $value)->values(),
                'measurements' => $posyanduAggregate->pluck('month_measurements_count')->map(fn ($value) => (int) $value)->values(),
                'monitoringIndexes' => $posyanduAggregate->pluck('monitoring_index')->map(fn ($value) => (float) $value)->values(),
            ];
        }

        return view('dashboard', [
            'stats' => [
                'posyandus' => $posyandusQuery->count(),
                'children' => $totalChildren,
                'measurements' => $totalMeasurements,
                'measurements_this_month' => $measurementsThisMonth,
                'monitoring_index' => $totalChildren > 0 ? round(($childrenMeasuredThisMonth / $totalChildren) * 100, 1) : 0,
            ],
            'latestMeasurements' => $latestMeasurements,
            'developmentSummary' => [
                'children_measured_this_month' => $childrenMeasuredThisMonth,
                'measurements_this_month' => $measurementsThisMonth,
                'avg_latest_weight' => $latestAverages && $latestAverages->avg_latest_weight !== null ? round((float) $latestAverages->avg_latest_weight, 2) : null,
                'avg_latest_height' => $latestAverages && $latestAverages->avg_latest_height !== null ? round((float) $latestAverages->avg_latest_height, 2) : null,
                'monitoring_index' => $totalChildren > 0 ? round(($childrenMeasuredThisMonth / $totalChildren) * 100, 1) : 0,
                'month_label' => $currentMonthStart->locale('id')->translatedFormat('F Y'),
            ],
            'monthlyTrend' => $monthlyTrend,
            'posyanduAggregate' => $posyanduAggregate,
            'posyanduChart' => $posyanduChart,
            'scopeLabel' => $user->isAdmin() ? 'Lintas Posyandu' : 'Posyandu ' . optional($user->posyandu)->name,
        ]);
    }
}
