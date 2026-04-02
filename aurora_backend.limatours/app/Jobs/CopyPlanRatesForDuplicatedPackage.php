<?php

namespace App\Jobs;

use App\PackageDuplicationInfo;
use App\Services\DuplicatePackagePlanRateService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CopyPlanRatesForDuplicatedPackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PackageDuplicationInfo $packageDuplicationInfo
     */
    protected $packageDuplicationInfo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PackageDuplicationInfo $packageDuplicationInfo)
    {
        $this->packageDuplicationInfo = $packageDuplicationInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DuplicatePackagePlanRateService $duplicatePackagePlanRateService)
    {
        if (!$this->packageDuplicationInfo->isProcessing()) {
            return;
        }

        $processedPlanRateIds = $this->packageDuplicationInfo->processed_plan_rate_ids;

        $planRate = $this->packageDuplicationInfo->duplicatedPackage
            ->plan_rates()
            ->whereNotIn('id', $processedPlanRateIds)
            ->first();

        if (!$planRate) {
            $this->markInfoAsProcessed();
            return;
        }

        $response = $duplicatePackagePlanRateService->execute(
            $this->packageDuplicationInfo->package_id,
            $planRate->id,
        );

        if (!$response['success']) {
            throw new Exception($response['message']);
        }

        array_push($processedPlanRateIds, $planRate->id);

        $this->packageDuplicationInfo->processed_plan_rate_ids = $processedPlanRateIds;
        $this->packageDuplicationInfo->save();

        CopyPlanRatesForDuplicatedPackage::dispatch($this->packageDuplicationInfo);
    }

    public function markInfoAsProcessed()
    {
        $this->packageDuplicationInfo->duplication_status = PackageDuplicationInfo::DUPLICATION_STATUS_PROCESSED;
        $this->packageDuplicationInfo->save();
    }
}
