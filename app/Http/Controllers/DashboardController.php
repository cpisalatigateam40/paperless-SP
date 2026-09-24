<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Product;
use App\Models\ItemStorageRmCleanliness;
use App\Models\ItemProcessAreaCleanliness;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');

        if ($isSuperadmin) {
            $plants = Area::orderBy('name')->get();
            $plant  = $request->input('plant', $plants->first()->uuid ?? null);
        } else {
            $plants = Area::where('uuid', $user->area_uuid)->get();
            $plant  = $user->area_uuid;
        }

        $date = $request->input('date', now()->toDateString());
        $jam  = $request->input('jam');

        $storageQuery = ItemStorageRmCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
            $q->whereDate('date', $date)
              ->where('area_uuid', $plant);
        });

        $processQuery = ItemProcessAreaCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
            $q->whereDate('date', $date)
              ->where('area_uuid', $plant);
        });

        if ($jam !== null && $jam !== '') {
            $storageQuery->whereHas('detail', function ($q) use ($jam) {
                $q->whereRaw('HOUR(inspection_hour) = ?', [$jam]);
            });
            $processQuery->whereHas('detail', function ($q) use ($jam) {
                $q->whereRaw('HOUR(inspection_hour) = ?', [$jam]);
            });
        }

        $storageVerifications = $storageQuery->pluck('verification');
        $processVerifications = $processQuery->pluck('verification');

        $allVerifications = $storageVerifications->merge($processVerifications);

        $totalPoints = $allVerifications->count();
        $okPoints = $allVerifications->filter(fn($v) => (int) $v === 1)->count();
        $percentage = $totalPoints > 0 ? round(($okPoints / $totalPoints) * 100, 1) : 0;

        $hourOptions = collect(range(0, 23))->map(fn($h) => [
            'value' => $h,
            'label' => sprintf('%02d:00', $h),
        ]);

        $products = Product::selectRaw('MIN(uuid) as uuid, product_name')
            ->groupBy('product_name')
            ->get();
        $product  = $request->input('product');

        $isCtOk = function ($settingCt, $actualCt) {
            if ($settingCt === null || $actualCt === null || $settingCt === '' || $actualCt === '') {
                return null;
            }

            $actual = (float) $actualCt;

            if (str_contains($settingCt, '-')) {
                [$min, $max] = array_map('trim', explode('-', $settingCt, 2));
                return $actual >= (float) $min && $actual <= (float) $max;
            }

            return $actual == (float) $settingCt;
        };

        $mixingLatest = \App\Models\DetailProcessProd::whereHas('report', function ($q) use ($plant, $date) {
            $q->where('area_uuid', $plant)
              ->whereDate('date', $date);
        })
        ->when($product, function ($q) use ($product) {
            $q->where('product_uuid', $product);
        })
        ->with(['product', 'sensoric', 'items'])
        ->latest('created_at')
        ->first();

        $mixingIsCurrent = $mixingLatest && $mixingLatest->created_at->gt(now()->subHours(8));
        

        $currentProductionCode = $mixingLatest->production_code ?? null;
        $currentProductUuid = $mixingLatest->product_uuid ?? null;

        $stuffingLatest = $currentProductionCode
        ? \App\Models\DetailWeightStuffer::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->where('production_code', $currentProductionCode)
            ->where('product_uuid', $currentProductUuid)
            ->with('product')
            ->latest('created_at')
            ->first()
        : null;

        $stuffingIsCurrent = $stuffingLatest && $stuffingLatest->created_at->gt(now()->subHours(8));


        $cookingLatest = $currentProductionCode
        ? \App\Models\DetailSmokeHouse::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->where('production_code', $currentProductionCode)
            ->where('product_uuid', $currentProductUuid)
            ->with(['product', 'steps', 'sensories'])
            ->latest('created_at')
            ->first()
        : null;

        $cookingIsCurrent = $cookingLatest && $cookingLatest->created_at->gt(now()->subHours(8));

        $pasteurLatest = $currentProductionCode
            ? \App\Models\DetailPasteur::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)->whereDate('date', $date);
                })
                ->where('product_code', $currentProductionCode)
                ->where('product_uuid', $currentProductUuid)
                ->with('product')
                ->latest('created_at')
                ->first()
            : null;

        $waterbathLatest = $currentProductionCode
            ? \App\Models\DetailWaterbath::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)->whereDate('date', $date);
                })
                ->where('batch_code', $currentProductionCode)
                ->where('product_uuid', $currentProductUuid)
                ->with('product')
                ->latest('created_at')
                ->first()
            : null;

        // Bandingkan mana yang lebih baru antara 2 sumber, ambil satu yang paling baru
        if ($pasteurLatest && $waterbathLatest) {
            $pasteurizingLatest = $pasteurLatest->created_at->gt($waterbathLatest->created_at)
                ? $pasteurLatest
                : $waterbathLatest;
        } else {
            $pasteurizingLatest = $pasteurLatest ?? $waterbathLatest;
        }

        $pasteurizingIsCurrent = $pasteurizingLatest && $pasteurizingLatest->created_at->gt(now()->subHours(8));

        // Kode produksi beda nama kolom tergantung sumbernya
        $pasteurizingCode = $pasteurizingLatest instanceof \App\Models\DetailPasteur
            ? $pasteurizingLatest->product_code
            : ($pasteurizingLatest->batch_code ?? null);

        $pasteurizingTemperatureOk = $pasteurizingLatest ? true : null;

        $packingLatest = $currentProductionCode
        ? \App\Models\DetailPackagingVerif::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)->whereDate('date', $date);
            })
            ->where('production_code', $currentProductionCode)
            ->where('product_uuid', $currentProductUuid)
            ->with(['product', 'checklist'])
            ->latest('created_at')
            ->first()
        : null;

        $packingIsCurrent = $packingLatest && $packingLatest->created_at->gt(now()->subHours(8));

        $packingWeightOk = null;
        $packingLengthOk = null;

        if ($packingLatest && $packingLatest->checklist) {
            $checklist = $packingLatest->checklist;

            $packingWeightOk = $isCtOk($checklist->standard_weight_pcs, $checklist->avg_weight_pcs);
            $packingLengthOk = $isCtOk($checklist->standard_long_pcs, $checklist->avg_long_pcs);
        }

        $cartoningLatest = $currentProductionCode
        ? \App\Models\DetailFreezPackaging::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)->whereDate('date', $date);
            })
            ->where('production_code', $currentProductionCode)
            ->where('product_uuid', $currentProductUuid)
            ->with(['product', 'kartoning'])
            ->latest('created_at')
            ->first()
        : null;

        $cartoningIsCurrent = $cartoningLatest && $cartoningLatest->created_at->gt(now()->subHours(8));

        $cartoningCartonOk = null;
        $cartoningLabelOk = null;

        if ($cartoningLatest && $cartoningLatest->kartoning) {
            $kartoning = $cartoningLatest->kartoning;

            $cartoningCartonOk = $kartoning->carton_condition !== null
                ? $kartoning->carton_condition === '✓'
                : null;

            $cartoningLabelOk = $kartoning->label_condition !== null
                ? $kartoning->label_condition === 'OK'
                : null;
        }

        $mixingPrevious = \App\Models\DetailProcessProd::whereHas('report', function ($q) use ($plant) {
            $q->where('area_uuid', $plant);
        })
        ->with('product')
        ->latest('created_at')
        ->skip(1)
        ->take(1)
        ->first();

        $stuffingPrevious = \App\Models\DetailWeightStuffer::whereHas('report', function ($q) use ($plant) {
            $q->where('area_uuid', $plant);
        })
        ->with('product')
        ->latest('created_at')
        ->skip(1)
        ->take(1)
        ->first();

        $cookingPrevious = \App\Models\DetailSmokeHouse::whereHas('report', function ($q) use ($plant) {
            $q->where('area_uuid', $plant);
        })
        ->with('product')
        ->latest('created_at')
        ->skip(1)
        ->take(1)
        ->first();

        $pasteurCandidates = \App\Models\DetailPasteur::whereHas('report', function ($q) use ($plant) {
                $q->where('area_uuid', $plant);
            })
            ->with('product')
            ->latest('created_at')
            ->take(2)
            ->get();

        $waterbathCandidates = \App\Models\DetailWaterbath::whereHas('report', function ($q) use ($plant) {
                $q->where('area_uuid', $plant);
            })
            ->with('product')
            ->latest('created_at')
            ->take(2)
            ->get();

        $pasteurizingAll = $pasteurCandidates->merge($waterbathCandidates)
            ->sortByDesc('created_at')
            ->values();

        $pasteurizingPrevious = $pasteurizingAll->get(1); // index 1 = urutan kedua

        $pasteurizingPreviousCode = $pasteurizingPrevious instanceof \App\Models\DetailPasteur
            ? $pasteurizingPrevious?->product_code
            : $pasteurizingPrevious?->batch_code;

        $packingPrevious = \App\Models\DetailPackagingVerif::whereHas('report', function ($q) use ($plant) {
            $q->where('area_uuid', $plant);
        })
        ->with('product')
        ->latest('created_at')
        ->skip(1)
        ->take(1)
        ->first();

        $cartoningPrevious = \App\Models\DetailFreezPackaging::whereHas('report', function ($q) use ($plant) {
            $q->where('area_uuid', $plant);
        })
        ->with('product')
        ->latest('created_at')
        ->skip(1)
        ->take(1)
        ->first();


        $incomingRm = \App\Models\DetailRmArrival::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->get();

        $incomingRmTotal = $incomingRm->count();
        $incomingRmOk = $incomingRm->where('status', 'OK')->count();
        $incomingRmNotOk = $incomingRm->where('status', 'Tidak OK')->count();
        $incomingRmNgPercent = $incomingRmTotal > 0
            ? round(($incomingRmNotOk / $incomingRmTotal) * 100, 1)
            : 0;

        $sensoryData = \App\Models\DetailRmArrival::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->get();

        $sensoryOk = 0;
        $sensoryNotOk = 0;

        foreach ($sensoryData as $d) {
            foreach ([$d->sensory_appearance, $d->sensory_aroma, $d->sensory_color] as $value) {
                if ($value === '✓') {
                    $sensoryOk++;
                } elseif ($value === 'x') {
                    $sensoryNotOk++;
                }
            }
        }

        $sensoryTotal = $sensoryOk + $sensoryNotOk;

        $sensoryNgPercent = $sensoryTotal > 0
            ? round(($sensoryNotOk / $sensoryTotal) * 100, 3)
            : 0;

        $processes = [
            'Showering', 'Warming',
            'Drying I', 'Drying II', 'Drying III', 'Drying IV', 'Drying V',
            'Smoking', 'Cooking I', 'Cooking II',
            'Evacuation', 'Showering & Cooling Down',
        ];

        $machines = ['Fessmann', 'Maurer', 'Bastra', 'Vemag'];

        

        $coreTempSteps = \App\Models\DetailSmokeHouseStep::whereHas('detail.report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->when($product, function ($q) use ($product) {
                $q->whereHas('detail', function ($q2) use ($product) {
                    $q2->where('product_uuid', $product);
                });
            })
            ->with('detail')
            ->get();

        $coreTempByMachineStep = [];

        foreach ($machines as $machineName) {
            foreach ($processes as $processName) {
                $filtered = $coreTempSteps->filter(function ($step) use ($machineName, $processName) {
                    return $step->detail
                        && $step->detail->machine_name === $machineName
                        && $step->process_name === $processName;
                });

                $ok = 0;
                $notOk = 0;

                foreach ($filtered as $step) {
                    $result = $isCtOk($step->setting_ct, $step->actual_ct);
                    if ($result === true) {
                        $ok++;
                    } elseif ($result === false) {
                        $notOk++;
                    }
                }

                $total = $ok + $notOk;
                $ngPercent = $total > 0 ? round(($notOk / $total) * 100, 1) : 0;

                $coreTempByMachineStep[$machineName][$processName] = [
                    'total' => $total,
                    'ok' => $ok,
                    'not_ok' => $notOk,
                    'ng_percent' => $ngPercent,
                ];
            }
        }




        $mixingBatches = \App\Models\DetailProcessProd::whereHas('report', function ($q) use ($plant, $date) {
        $q->where('area_uuid', $plant)
            ->whereDate('date', $date);
        })
        ->when($product, function ($q) use ($product) {
            $q->where('product_uuid', $product);
        })
        ->with('product')
        ->latest('created_at')
        ->take(3)
        ->get();

        $mixingToday = \App\Models\DetailProcessProd::whereHas('report', function ($q) use ($plant, $date) {
                $q->where('area_uuid', $plant)
                ->whereDate('date', $date);
            })
            ->when($product, function ($q) use ($product) {
                $q->where('product_uuid', $product);
            })
            ->get();

        $totalBatchesToday = $mixingToday->count();

        

        $mixingLastCheck = $mixingBatches->first()?->created_at;

        $stuffingStatuses = $mixingBatches->map(function ($batch) use ($plant, $date, $product) {
            $match = \App\Models\DetailWeightStuffer::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('production_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            return (object) [
                'product_name' => $batch->product->product_name ?? '-',
                'production_code' => $batch->production_code,
                'is_running' => (bool) $match,
                'created_at' => $match?->created_at,
            ];
        });

        $stuffingLastCheck = $stuffingStatuses
            ->filter(fn ($s) => $s->is_running)
            ->pluck('created_at')
            ->max();

        $cookingStatuses = $mixingBatches->map(function ($batch) use ($plant, $date, $product) {
            $match = \App\Models\DetailSmokeHouse::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('production_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            return (object) [
                'product_name' => $batch->product->product_name ?? '-',
                'production_code' => $batch->production_code,
                'is_running' => (bool) $match,
                'created_at' => $match?->created_at,
            ];
        });

        $cookingLastCheck = $cookingStatuses
            ->filter(fn ($s) => $s->is_running)
            ->pluck('created_at')
            ->max();

        $pasteurizingStatuses = $mixingBatches->map(function ($batch) use ($plant, $date, $product) {
            $matchPasteur = \App\Models\DetailPasteur::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('product_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            $matchWaterbath = \App\Models\DetailWaterbath::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('batch_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            // Ambil yang paling baru di antara 2 sumber, kalau ada keduanya
            $match = collect([$matchPasteur, $matchWaterbath])
                ->filter()
                ->sortByDesc('created_at')
                ->first();

            return (object) [
                'product_name' => $batch->product->product_name ?? '-',
                'production_code' => $batch->production_code,
                'is_running' => (bool) $match,
                'created_at' => $match?->created_at,
            ];
        });

        $pasteurizingLastCheck = $pasteurizingStatuses
            ->filter(fn ($s) => $s->is_running)
            ->pluck('created_at')
            ->max();

        $packingStatuses = $mixingBatches->map(function ($batch) use ($plant, $date, $product) {
            $match = \App\Models\DetailPackagingVerif::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('production_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            return (object) [
                'product_name' => $batch->product->product_name ?? '-',
                'production_code' => $batch->production_code,
                'is_running' => (bool) $match,
                'created_at' => $match?->created_at,
            ];
        });

        $packingLastCheck = $packingStatuses
            ->filter(fn ($s) => $s->is_running)
            ->pluck('created_at')
            ->max();

        $cartoningStatuses = $mixingBatches->map(function ($batch) use ($plant, $date, $product) {
            $match = \App\Models\DetailFreezPackaging::whereHas('report', function ($q) use ($plant, $date) {
                    $q->where('area_uuid', $plant)
                    ->whereDate('date', $date);
                })
                ->where('production_code', $batch->production_code)
                ->where('product_uuid', $batch->product_uuid)
                ->when($product, function ($q) use ($product) {
                    $q->where('product_uuid', $product);
                })
                ->latest('created_at')
                ->first();

            return (object) [
                'product_name' => $batch->product->product_name ?? '-',
                'production_code' => $batch->production_code,
                'is_running' => (bool) $match,
                'created_at' => $match?->created_at,
            ];
        });

        $cartoningLastCheck = $cartoningStatuses
            ->filter(fn ($s) => $s->is_running)
            ->pluck('created_at')
            ->max();

        $batchTracking = $mixingBatches->map(function ($batch, $index) use (
            $stuffingStatuses, $cookingStatuses, $pasteurizingStatuses, $packingStatuses, $cartoningStatuses
        ) {
            $stuffing = $stuffingStatuses[$index]->is_running ?? false;
            $cooking = $cookingStatuses[$index]->is_running ?? false;
            $pasteurizing = $pasteurizingStatuses[$index]->is_running ?? false;
            $packing = $packingStatuses[$index]->is_running ?? false;
            $cartoning = $cartoningStatuses[$index]->is_running ?? false;

            $hasData = [true, $stuffing, $cooking, $pasteurizing, $packing, $cartoning];
            $times = [
                $batch->created_at,
                $stuffingStatuses[$index]->created_at ?? null,
                $cookingStatuses[$index]->created_at ?? null,
                $pasteurizingStatuses[$index]->created_at ?? null,
                $packingStatuses[$index]->created_at ?? null,
                $cartoningStatuses[$index]->created_at ?? null,
            ];
            $lastIndex = count($hasData) - 1;

            $statuses = [];
            foreach ($hasData as $i => $current) {
                if (!$current) {
                    $statuses[$i] = 'pending';
                    continue;
                }

                if ($i === $lastIndex) {
                    $statuses[$i] = 'completed';
                    continue;
                }

                $next = $hasData[$i + 1] ?? false;
                $statuses[$i] = $next ? 'completed' : 'running';
            }

            return (object) [
                'batch_no' => $batch->production_code,
                'product_name' => $batch->product->product_name ?? '-',
                'meat_preparation' => $statuses[0],
                'meat_preparation_time' => $times[0],
                'stuffing' => $statuses[1],
                'stuffing_time' => $times[1],
                'cooking' => $statuses[2],
                'cooking_time' => $times[2],
                'pasteurization' => $statuses[3],
                'pasteurization_time' => $times[3],
                'packing' => $statuses[4],
                'packing_time' => $times[4],
                'cartoning' => $statuses[5],
                'cartoning_time' => $times[5],
            ];
        });

        $totalProductsRunning = $batchTracking
            ->filter(function ($row) {
                return in_array('running', [
                    $row->meat_preparation,
                    $row->stuffing,
                    $row->cooking,
                    $row->pasteurization,
                    $row->packing,
                    $row->cartoning,
                ]);
            })
            ->pluck('product_name')
            ->unique()
            ->count();

        $temperatureSchedule = [7, 9, 11, 13, 15];

        $roomStandards = [
            'Chillroom' => ['label' => '≤ 5', 'compare' => fn($t) => $t <= 5],
            'Seasoning' => ['label' => '≤ 20', 'compare' => fn($t) => $t <= 20],
            'Meat Preparation' => ['label' => '≤ 20', 'compare' => fn($t) => $t <= 20],
            'Cooking Sausage' => ['label' => '≤ 15', 'compare' => fn($t) => $t <= 15],
            'Cooking Meatball' => ['label' => '≤ 15', 'compare' => fn($t) => $t <= 15],
            'Pasteurization' => ['label' => '≥ 90', 'compare' => fn($t) => $t >= 90],
            'Packing' => ['label' => '≤ 12', 'compare' => fn($t) => $t <= 12],
            'Cartoning' => ['label' => '≤ 12', 'compare' => fn($t) => $t <= 12],
        ];

        // Peta nama tampilan -> nama room asli di database
        $roomDbMapping = [
            'Chillroom' => ['source' => 'storage', 'db_name' => 'Chillroom'],
            'Seasoning' => ['source' => 'storage', 'db_name' => 'Seasoning'],
            'Meat Preparation' => ['source' => 'process', 'db_name' => 'MP'],
            'Cooking Sausage' => ['source' => 'process', 'db_name' => 'Cooking'],
            'Cooking Meatball' => ['source' => 'process', 'db_name' => 'Cooking Bakso'],
            'Pasteurization' => ['source' => 'process', 'db_name' => 'Pasteurisasi'],
            'Packing' => ['source' => 'process', 'db_name' => 'Packing'],
            'Cartoning' => ['source' => 'process', 'db_name' => 'Cartoning'],
        ];

        $areaConditions = [];

        foreach ($roomDbMapping as $displayName => $mapping) {
            if ($mapping['source'] === 'storage') {
                $latestItem = \App\Models\ItemStorageRmCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
                        $q->where('area_uuid', $plant)
                        ->whereDate('date', $date);
                    })
                    ->whereHas('detail.report', function ($q) use ($mapping) {
                        $q->where('room_name', $mapping['db_name']);
                    })
                    ->where('item', 'Suhu ruang (℃) / RH (%)')
                    ->with('detail')
                    ->get()
                    ->sortByDesc(fn ($i) => $i->detail->created_at)
                    ->first();

                $temp = $latestItem ? (float) preg_replace('/[^0-9.]/', '', $latestItem->condition) : null;
                $lastCheckTime = $latestItem?->detail?->inspection_hour;

                $isConditionOk = null;
                if ($latestItem) {
                    $conditionItem = \App\Models\ItemStorageRmCleanliness::where('detail_uuid', $latestItem->detail_uuid)
                        ->where('item', 'Kebersihan Ruangan')
                        ->first();
                    $isConditionOk = $conditionItem ? (int) $conditionItem->verification === 1 : null;
                }
            } else {
                $latestItem = \App\Models\ItemProcessAreaCleanliness::whereHas('detail.report', function ($q) use ($plant, $date) {
                        $q->where('area_uuid', $plant)
                        ->whereDate('date', $date);
                    })
                    ->whereHas('detail.report', function ($q) use ($mapping) {
                        $q->where('section_name', $mapping['db_name']);
                    })
                    ->where('item', 'Suhu ruang (℃)')
                    ->with('detail')
                    ->get()
                    ->sortByDesc(fn ($i) => $i->detail->created_at)
                    ->first();

                $temp = $latestItem?->temperature_actual;
                $lastCheckTime = $latestItem?->detail?->inspection_hour;

                $isConditionOk = null;
                if ($latestItem) {
                    $conditionItem = \App\Models\ItemProcessAreaCleanliness::where('detail_uuid', $latestItem->detail_uuid)
                        ->where('item', 'Kondisi Kebersihan Ruangan')
                        ->first();
                    $isConditionOk = $conditionItem ? (int) $conditionItem->verification === 1 : null;
                }
            }

            $standard = $roomStandards[$displayName];
            $isTempOk = $temp !== null ? $standard['compare']($temp) : null;

            $lastCheckHour = $lastCheckTime ? (int) \Carbon\Carbon::parse($lastCheckTime)->format('H') : null;
            $nextCheckHour = null;
            if ($lastCheckHour !== null) {
                foreach ($temperatureSchedule as $hour) {
                    if ($hour > $lastCheckHour) {
                        $nextCheckHour = $hour;
                        break;
                    }
                }
            } else {
                $nextCheckHour = $temperatureSchedule[0];
            }

            $areaConditions[] = (object) [
                'area' => $displayName,
                'temperature' => $temp,
                'standard_label' => $standard['label'],
                'is_temp_ok' => $isTempOk,
                'is_condition_ok' => $isConditionOk,
                'last_check' => $lastCheckTime ? \Carbon\Carbon::parse($lastCheckTime)->format('H:i') : null,
                'next_check' => $nextCheckHour !== null ? sprintf('%02d:00', $nextCheckHour) : '-',
            ];
        }

        // Kolom checklist final (gabungan item dari kedua form, tidak termasuk suhu karena sudah ada di tabel sebelumnya)
        $checklistColumns = [
            'Kondisi dan penempatan barang',
            'Pelabelan',
            'Kebersihan Ruangan',
            'Kebersihan Peralatan',
            'Kebersihan Karyawan',
        ];

        $checklistData = [];
        $checklistOkCount = 0;
        $checklistNgCount = 0;

        foreach ($roomDbMapping as $displayName => $mapping) {
            if ($mapping['source'] === 'storage') {
                $latestDetail = \App\Models\DetailStorageRmCleanliness::whereHas('report', function ($q) use ($plant, $date, $mapping) {
                        $q->where('area_uuid', $plant)
                        ->whereDate('date', $date)
                        ->where('room_name', $mapping['db_name']);
                    })
                    ->orderByDesc('created_at')
                    ->first();

                $items = $latestDetail
                    ? \App\Models\ItemStorageRmCleanliness::where('detail_uuid', $latestDetail->uuid)->get()
                    : collect();

                $itemMap = [
                    'Kondisi dan penempatan barang' => 'Kondisi dan penempatan barang',
                    'Pelabelan' => 'Pelabelan',
                    'Kebersihan Ruangan' => 'Kebersihan Ruangan',
                ];
            } else {
                $latestDetail = \App\Models\DetailProcessAreaCleanliness::whereHas('report', function ($q) use ($plant, $date, $mapping) {
                        $q->where('area_uuid', $plant)
                        ->whereDate('date', $date)
                        ->where('section_name', $mapping['db_name']);
                    })
                    ->orderByDesc('created_at')
                    ->first();

                $items = $latestDetail
                    ? \App\Models\ItemProcessAreaCleanliness::where('detail_uuid', $latestDetail->uuid)->get()
                    : collect();

                $itemMap = [
                    'Kebersihan Ruangan' => 'Kondisi Kebersihan Ruangan',
                    'Kebersihan Peralatan' => 'Kondisi Kebersihan Peralatan',
                    'Kebersihan Karyawan' => 'Kondisi Kebersihan Karyawan',
                ];
            }

            $rowResult = [];
            $roomHasNg = false;
            $roomHasAnyItem = false;

            foreach ($checklistColumns as $column) {
                if (!isset($itemMap[$column])) {
                    $rowResult[$column] = null; // item ini tidak berlaku untuk sumber ini
                    continue;
                }

                $item = $items->firstWhere('item', $itemMap[$column]);

                if (!$item) {
                    $rowResult[$column] = null;
                    continue;
                }

                $isOk = (int) $item->verification === 1;
                $rowResult[$column] = $isOk;
                $roomHasAnyItem = true;

                if (!$isOk) {
                    $roomHasNg = true;
                }
            }

            if ($roomHasAnyItem) {
                $roomHasNg ? $checklistNgCount++ : $checklistOkCount++;
            }

            $checklistData[] = (object) [
                'area' => $displayName,
                'items' => $rowResult,
                'has_ng' => $roomHasNg,
            ];
        }

        $checklistTotalAreas = count($roomDbMapping);


        $trendDate = \Carbon\Carbon::parse($date);
        $isToday = $trendDate->isToday();

        $trendFrom = $trendDate->copy()->startOfDay()->timestamp * 1000;

        $trendTo = $isToday
            ? now()->timestamp * 1000
            : $trendDate->copy()->endOfDay()->timestamp * 1000;

        $trendRoomConfig = [];

        foreach ($roomDbMapping as $displayName => $mapping) {
            $standard = $roomStandards[$displayName];

            if ($mapping['source'] === 'storage') {
                $readings = \App\Models\ItemStorageRmCleanliness::whereHas('detail.report', function ($q) use ($plant, $mapping, $date) {
                        $q->where('area_uuid', $plant)
                        ->where('room_name', $mapping['db_name'])
                        ->where('date', $date);
                    })
                    ->where('item', 'Suhu ruang (℃) / RH (%)')
                    ->with('detail.report')
                    ->get()
                    ->map(fn ($item) => (float) preg_replace('/[^0-9.]/', '', $item->condition));
            } else {
                $readings = \App\Models\ItemProcessAreaCleanliness::whereHas('detail.report', function ($q) use ($plant, $mapping, $date) {
                        $q->where('area_uuid', $plant)
                        ->where('section_name', $mapping['db_name'])
                        ->where('date', $date);
                    })
                    ->where('item', 'Suhu ruang (℃)')
                    ->with('detail.report')
                    ->get()
                    ->pluck('temperature_actual')
                    ->map(fn ($v) => (float) $v);
            }

            $avg = $readings->avg();
            $min = $readings->min();
            $max = $readings->max();

            $trendRoomConfig[$displayName] = [
                'source' => $mapping['source'],
                'db_room' => $mapping['db_name'],
                'standard_value' => (float) preg_replace('/[^0-9.]/', '', $standard['label']),
                'standard_label' => $standard['label'],
                'average' => $avg ? round($avg, 1) : null,
                'min' => $min !== null ? round($min, 1) : null,
                'max' => $max !== null ? round($max, 1) : null,
                'within_standard' => $readings->isNotEmpty() ? $readings->every(fn ($t) => $standard['compare']($t)) : null,
            ];
        }

        // QC Verification status untuk card SPV
        $mixingForeignOk = $mixingLatest && $mixingLatest->sensoric
            ? $mixingLatest->sensoric->foreign_object === 'Tidak Terdeteksi'
            : null;

        $mixingWeighingOk = $mixingLatest && $mixingLatest->items->isNotEmpty()
            ? $mixingLatest->items->every(fn($item) => $item->sensory !== 'Tidak OK')
            : null;
        $mixingTemperatureOk = $mixingLatest ? true : null;

        

        $stuffingWeighingOk = $stuffingLatest ? $stuffingLatest->weight_status === 'OK' : null;
        $stuffingLengthOk = $stuffingLatest ? $stuffingLatest->long_status === 'OK' : null;
        $stuffingDiameterOk = $stuffingLatest ? true : null;

        $cookingCoreTempOk = null;
        if ($cookingLatest && $cookingLatest->steps->isNotEmpty()) {
            $cookingCoreTempOk = $cookingLatest->steps->every(function ($step) use ($isCtOk) {
                return $isCtOk($step->setting_ct, $step->actual_ct) !== false;
            });
        }

        $cookingSensoryOk = null;
        if ($cookingLatest && $cookingLatest->sensories) {
            $cookingSensoryOk = collect(['appearance', 'color', 'aroma', 'taste', 'texture'])
                ->every(fn($field) => $cookingLatest->sensories->{$field} !== 'Fail');
        }


        $mixingStageStatus = $mixingLatest
        ? ($stuffingLatest ? 'completed' : 'running')
        : 'waiting';
        // setelah $cookingLatest dihitung
        $stuffingStageStatus = $stuffingLatest
            ? ($cookingLatest ? 'completed' : 'running')
            : 'waiting';

        // setelah $pasteurizingLatest dihitung
        $cookingStageStatus = $cookingLatest
            ? ($pasteurizingLatest ? 'completed' : 'running')
            : 'waiting';

        // setelah $packingLatest dihitung
        $pasteurizingStageStatus = $pasteurizingLatest
            ? ($packingLatest ? 'completed' : 'running')
            : 'waiting';

        // setelah $cartoningLatest dihitung
        $packingStageStatus = $packingLatest
            ? ($cartoningLatest ? 'completed' : 'running')
            : 'waiting';

        // cartoning: tidak ada next stage, jadi completed kalau sudah ada data
        $cartoningStageStatus = $cartoningLatest ? 'completed' : 'waiting';

        return view('dashboard', compact(
            'percentage', 'okPoints', 'totalPoints',
            'plants', 'hourOptions',
            'plant', 'date', 'jam',
            'isSuperadmin', 'products', 'product', 'mixingLatest', 'mixingIsCurrent', 'stuffingLatest', 'stuffingIsCurrent', 'cookingLatest', 'cookingIsCurrent', 'pasteurizingLatest', 'pasteurizingIsCurrent', 'pasteurizingCode', 'packingLatest', 'packingIsCurrent', 'cartoningLatest', 'cartoningIsCurrent', 'mixingPrevious', 'stuffingPrevious', 'cookingPrevious', 'pasteurizingPrevious', 'pasteurizingPreviousCode', 'packingPrevious', 'cartoningPrevious', 'incomingRmTotal', 'incomingRmOk', 'incomingRmNotOk', 'incomingRmNgPercent', 'sensoryTotal', 'sensoryOk', 'sensoryNotOk', 'sensoryNgPercent', 'coreTempByMachineStep', 'processes', 'machines', 'mixingBatches', 'mixingLastCheck', 'stuffingStatuses', 'stuffingLastCheck', 'cookingStatuses', 'cookingLastCheck', 'pasteurizingStatuses', 'pasteurizingLastCheck', 'packingStatuses', 'packingLastCheck', 'cartoningStatuses', 'cartoningLastCheck', 'batchTracking', 'areaConditions', 'checklistColumns', 'checklistData', 'checklistOkCount', 'checklistNgCount', 'checklistTotalAreas', 'trendRoomConfig', 'trendFrom', 'trendTo', 'mixingForeignOk', 'mixingWeighingOk', 'mixingTemperatureOk', 'stuffingWeighingOk', 'stuffingLengthOk', 'stuffingDiameterOk', 'cookingCoreTempOk', 'cookingSensoryOk', 'mixingStageStatus', 'currentProductionCode', 'currentProductUuid', 'stuffingStageStatus', 'cookingStageStatus', 'pasteurizingStageStatus', 'pasteurizingTemperatureOk', 'packingStageStatus', 'packingWeightOk', 'packingLengthOk', 'cartoningStageStatus', 'cartoningCartonOk', 'cartoningLabelOk', 'totalBatchesToday', 'totalProductsRunning'
        ));
    }
}