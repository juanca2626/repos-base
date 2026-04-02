<?php

namespace App\Services;

use App\Package;
use App\PackageDestination;
use App\PackagePlanRate;
use App\PackageService;

class UpdatePackageDestinationsService
{
    public function execute($packageId)
    {
        $packagePlanRates = PackagePlanRate::with([
            'plan_rate_categories' => function ($query) {
                $query->select('id', 'package_plan_rate_id')->pluck('package_plan_rate_id');
            }
        ])->where('package_id', $packageId)->get();

        if (!$packagePlanRates) {
            return;
        }

        $categoryIds = $packagePlanRates->map(function($packagePlanRate) {
            return $packagePlanRate->plan_rate_categories->map(function($planRateCategory) {
                return $planRateCategory->id;
            });
        })->flatten();

        $packageServices = $this->getPackageServices($categoryIds);

        [$stateIds, $stateNames] = $this->getStateNamesAndIds($packageServices);

        PackageDestination::where('package_id', $packageId)->delete();

        foreach ($stateIds as $stateId) {
            PackageDestination::create([
                'package_id' => $packageId,
                'state_id' => $stateId,
            ]);
        }

        $destination = implode(',', $stateNames);

        Package::where('id', $packageId)->update([
            'destinations' => $destination,
        ]);
    }

    private function getPackageServices($categoryIds)
    {
        return PackageService::with([
            'hotel' => function ($query) {
                $query->select('id', 'state_id');
                $query->with([
                    'state' => function ($query) {
                        $query->select('id', 'iso');
                        $query->with([
                            'translations' => function ($query) {
                                $query->select('object_id', 'value');
                                $query->where('type', 'state');
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
            },
            'service' => function ($query) {
                $query->select('id');
                $query->with(['serviceDestination' => function($query) {
                    $query->select('id', 'service_id', 'state_id');
                    $query->with([
                        'state' => function ($query) {
                            $query->select('id', 'iso');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                }]);
            }
        ])->select('object_id', 'date_in', 'type')
            ->whereIn('package_plan_rate_category_id', $categoryIds)
            ->where(function($query) {
                $query
                    ->where('type', 'hotel')
                    ->orWhere('type', 'service');
            })
            ->orderBy('date_in', 'asc')->get();
    }

    private function getStateNamesAndIds($packageServices)
    {
        $stateIds = [];
        $stateNames = [];

        foreach ($packageServices as $packageService) {
            if ($packageService->type === 'hotel') {
                $stateNames[] = $packageService->hotel->state->translations[0]->value;
                $stateIds[] = $packageService->hotel->state_id;
                continue;
            }

            foreach ($packageService->service->serviceDestination as $serviceDestination) {
                $stateNames[] = $serviceDestination->state->translations[0]->value;
                $stateIds[] = $serviceDestination->state_id;
            }
        }

        return [
            array_values(array_unique($stateIds)),
            array_values(array_unique($stateNames))
        ];
    }
}
