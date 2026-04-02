<?php

namespace App\Services;

use App\Jobs\CopyPlanRatesForDuplicatedPackage;
use App\Package;
use App\PackagePlanRate;
use App\PackagePlanRateCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

class DuplicatePackageService
{
    public function execute($packageId)
    {
        $package = Package::query()
            ->with([
                'package_destinations',
                'tag',
                'country',
                'physical_intensity',
                'permissions',
                'translations',
                'itineraries',
                'schedules',
                'extension_recommended',
                'fixed_outputs',
                'galleries',
                'rated',
                'client_package_setting',
                'children',
                'highlights',
                'tax',
                'inclusions',
            ])
            ->find($packageId);

        if (!$package) {
            // TODO: deberia ser traducción
            return ['success' => false, 'message' => 'Package not found'];
        }

        DB::beginTransaction();

        try {
            $newPackage = $package->replicate(['code']);
            $newPackage->status = 0;
            $newPackage->save();

            $newPackage->duplicationInfo()->create([
                'duplicated_package_id' => $package->id,
                'processed_plan_rate_ids' => [],
            ]);

            $this->duplicatePackageDestinations($newPackage, $package->package_destinations);

            $this->duplicatePackagePermissions($newPackage, $package->permissions);

            $this->duplicatePackageTranslations($newPackage, $package->translations);

            $this->duplicatePackageItineraries($newPackage, $package->itineraries);

            $this->duplicatePackageRecommendedExtensions($newPackage, $package->extension_recommended);

            $this->duplicateFixedOutputs($newPackage, $package->fixed_outputs);

            $this->duplicateGalleries($newPackage, $package->galleries);

            /** INFO: Tal vez no se debería duplicar */
            $this->duplicateRated($newPackage, $package->rated);

            $this->duplicateClientSetting($newPackage, $package->client_package_setting);

            $this->duplicateChildren($newPackage, $package->children);

            $this->duplicateHighlights($newPackage, $package->highlights);

            $this->duplicateTaxes($newPackage, $package->tax);

            $this->duplicateInclusions($newPackage, $package->inclusions);

            DB::commit();

            CopyPlanRatesForDuplicatedPackage::dispatch($newPackage->duplicationInfo);

            return ['success' => true, 'data' => $newPackage];
        } catch(Throwable $e) {
            DB::rollBack();

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function duplicatePackageDestinations(Package $package, $packageDestinations)
    {
        foreach ($packageDestinations as $packageDestination) {
            /** @var \App\PackageDestination $packageDestination */
            $newPackageDestination = $packageDestination->replicate(['package_id']);
            $package->package_destinations()->save($newPackageDestination);
        }
    }

    private function duplicatePackagePermissions(Package $package, $packagePermissions)
    {
        foreach ($packagePermissions as $packagePermission) {
            /** @var \App\PackagePermission $packagePermission */
            $newPackagePermission = $packagePermission->replicate(['package_id']);
            $package->permissions()->save($newPackagePermission);
        }
    }

    private function duplicatePackageTranslations(Package $package, $packageTranslations)
    {
        foreach ($packageTranslations as $packageTranslation) {
            /** @var \App\PackageTranslation $packageTranslation */
            $newPackageTranslation = $packageTranslation->replicate(['package_id']);
            $newPackageTranslation->name = "{$newPackageTranslation->name} - (COPIA)";
            $package->translations()->save($newPackageTranslation);
        }
    }

    private function duplicatePackageItineraries(Package $package, $packageItineraries)
    {
        foreach ($packageItineraries as $packageItinerary) {
            /** @var \App\PackageItinerary $packageItinerary */
            $newPackageItinerary = $packageItinerary->replicate(['package_id']);
            $package->itineraries()->save($newPackageItinerary);
        }
    }

    private function duplicatePackageRecommendedExtensions(Package $package, $packageExtensions)
    {
        foreach ($packageExtensions as $packageExtension) {
            /** @var \App\PackageExtension $packageExtension */
            $newPackageExtension = $packageExtension->replicate(['package_id']);
            $package->extension_recommended()->save($newPackageExtension);
        }
    }

    private function duplicateFixedOutputs(Package $package, $fixedOutputs)
    {
        foreach ($fixedOutputs as $fixedOutput) {
            /** @var \App\FixedOutput $fixedOutput */
            $newFixedOutput = $fixedOutput->replicate(['package_id']);
            $package->fixed_outputs()->save($newFixedOutput);
        }
    }

    private function duplicateGalleries(Package $package, $galleries)
    {
        foreach ($galleries as $gallery) {
            /** @var \App\Galery $gallery */
            $newGallery = $gallery->replicate(['package_id']);
            $package->galleries()->save($newGallery);
        }
    }

    private function duplicateRated(Package $package, $clientRatings)
    {
        foreach ($clientRatings as $rating) {
            /** @var \App\ClientPackageRated $rating */
            $newRating = $rating->replicate(['package_id']);
            $package->rated()->save($newRating);
        }
    }

    private function duplicateClientSetting(Package $package, $clientSettings)
    {
        foreach ($clientSettings as $setting) {
            /** @var \App\ClientPackageSetting $setting */
            $newSetting = $setting->replicate(['package_id']);
            $package->client_package_setting()->save($newSetting);
        }
    }

    private function duplicateChildren(Package $package, $children)
    {
        foreach ($children as $child) {
            /** @var \App\PackageChild $child */
            $newChild = $child->replicate(['package_id']);

            $package->children()->save($newChild);
        }
    }

    private function duplicateHighlights(Package $package, $highlights)
    {
        foreach ($highlights as $highlight) {
            /** @var \App\PackageHighlight $highlight */
            $newHighlight = $highlight->replicate(['package_id']);

            $package->highlights()->save($newHighlight);
        }
    }

    private function duplicateTaxes(Package $package, $taxes)
    {
        foreach ($taxes as $tax) {
            /** @var \App\PackaPackageTaxgeHighlight $tax */
            $newTax = $tax->replicate(['package_id']);

            $package->tax()->save($newTax);
        }
    }

    private function duplicateInclusions(Package $package, $inclusions)
    {
        foreach ($inclusions as $inclusion) {
            /** @var \App\PackageInclusion $inclusion */
            $newInclusion = $inclusion->replicate(['package_id']);

            $package->inclusions()->save($newInclusion);
        }
    }
}
