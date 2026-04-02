<?php

namespace App\Services;

use App\Package;
use App\PackagePlanRate;
use App\PackagePlanRateCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

class DuplicatePackagePlanRateService
{
    private $createdMarkupIds;

    public function __construct()
    {
        $this->createdMarkupIds = collect([]);
    }

    public function execute($packageId, $planRateId)
    {
        $package = Package::find($packageId);

        if (!$package) {
            return ['success' => false, 'message' => 'Package not found'];
        }

        $planRate = PackagePlanRate::query()
            ->with([
                'package_rate_sale_markup',
                'plan_rate_categories.category.translations',
                'plan_rate_categories.sale_rates_fixed',
                'plan_rate_categories.sale_rates.rate_sale_markups',
                'inventory',
                'offers',
            ])
            ->find($planRateId);

        if (!$planRate) {
            return ['success' => false, 'message' => 'Plan rate not found'];
        }

        DB::beginTransaction();

        try {
            $newPlanRate = $planRate->replicate(['package_id']);

            $package->plan_rates()->save($newPlanRate);

            $this->duplicatePlanRateCategories($newPlanRate, $planRate->plan_rate_categories);

            $this->duplicatePlanRateInventory($newPlanRate, $planRate->inventory);

            $this->duplicatePlanRateOffers($newPlanRate, $planRate->offers);

            DB::commit();

            return ['success' => true, 'message' => $newPlanRate];
        } catch(Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }

    }

    private function duplicatePlanRateCategories(PackagePlanRate $planRate, $categories)
    {
        foreach ($categories as $category) {
            /** @var \App\PackagePlanRateCategory $category */
            $newCategory = $category->replicate(['package_plan_rate_id']);

            $planRate->plan_rate_categories()->save($newCategory);

            foreach ($category->rates as $dynamicRate) {
                /** @var \App\PackageDynamicRate $dynamicRate */
                $newDynamicRate = $dynamicRate->replicate(['package_plan_rate_category_id']);

                $newCategory->rates()->save($newDynamicRate);
            }

            foreach ($category->sale_rates_fixed as $fixedSaleRate) {
                /** @var \App\PackageFixedSaleRate $fixedSaleRate */
                $newFixedSaleRate = $fixedSaleRate->replicate(['package_plan_rate_category_id']);

                $newCategory->sale_rates_fixed()->save($newFixedSaleRate);
            }

            $this->duplicatePlanRateCategorySaleRates($newCategory, $category->sale_rates);

            $this->duplicatePlanRateCategoryService($newCategory, $category->services);

            $this->duplicatePlanRateCategoryOptionals($newCategory, $category->optionals);
        }
    }

    private function duplicatePlanRateCategorySaleRates(PackagePlanRateCategory $planRateCategory, $saleRates)
    {
        foreach ($saleRates as $saleRate) {
            /** @var \App\PackageDynamicSaleRate $saleRate */
            $saleMarkup = $saleRate->rate_sale_markups;

            if ($this->createdMarkupIds->has($saleMarkup->id)) {
                $saleMarkupId = $this->createdMarkupIds->get($saleMarkup->id);
            } else {
                $newSaleMarkup = $saleMarkup->replicate(['package_plan_rate_id']);
                $newSaleMarkup->package_plan_rate_id = $planRateCategory->package_plan_rate_id;
                $newSaleMarkup->save();

                $saleMarkupId = $newSaleMarkup->id;
                $this->createdMarkupIds->put($saleMarkup->id, $saleMarkupId);
            }

            $newSaleRate = $saleRate->replicate(['package_plan_rate_category_id', 'package_rate_sale_markup_id']);
            $newSaleRate->package_rate_sale_markup_id = $saleMarkupId;

            $planRateCategory->sale_rates()->save($newSaleRate);
        }
    }

    private function duplicatePlanRateCategoryService(PackagePlanRateCategory $planRateCategory, $services)
    {
        foreach ($services as $service) {
            /** @var \App\PackageService $service */
            $newService = $service->replicate(['package_plan_rate_category_id']);

            $planRateCategory->services()->save($newService);

            foreach ($service->service_rooms as $serviceRoom) {
                /** @var \App\PackageServiceRoom $serviceRoom */
                $newServiceRoom = $serviceRoom->replicate(['package_service_id']);

                $newService->service_rooms()->save($newServiceRoom);
            }

            foreach ($service->service_rates as $serviceRate) {
                /** @var \App\PackageServiceRate $serviceRate */
                $newServiceRate = $serviceRate->replicate(['package_service_id']);

                $newService->service_rates()->save($newServiceRate);
            }
        }
    }

    private function duplicatePlanRateCategoryOptionals(PackagePlanRateCategory $planRateCategory, $optionals)
    {
        foreach ($optionals as $optionalService) {
            /** @var \App\PackageServiceOptional $optionalService */
            $newService = $optionalService->replicate(['package_plan_rate_category_id']);

            $planRateCategory->optionals()->save($newService);

            foreach ($optionalService->service_rooms as $serviceRoom) {
                /** @var \App\PackageServiceOptionalRoom $serviceRoom */
                $newServiceRoom = $serviceRoom->replicate(['package_service_optional_id']);

                $newService->service_rooms()->save($newServiceRoom);
            }
        }
    }

    private function duplicatePlanRateInventory(PackagePlanRate $planRate, $inventories)
    {
        foreach ($inventories as $inventory) {
            /** @var \App\PackageInventory $inventory */
            $newInventory = $inventory->replicate(['package_plan_rate_id']);

            $planRate->inventory()->save($newInventory);
        }
    }

    private function duplicatePlanRateOffers(PackagePlanRate $planRate, $offers)
    {
        foreach ($offers as $offer) {
            /** @var \App\ClientPackageOffer $offer */
            $newOffer = $offer->replicate(['package_plan_rate_id']);

            $planRate->inventory()->save($newOffer);
        }
    }
}
