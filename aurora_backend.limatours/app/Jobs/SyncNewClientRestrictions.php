<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Client;
use App\RatesPlans;
use App\ServiceRate;
use App\RatePlanAssociation;
use App\ServiceRateAssociation;
use App\ClientRatePlan;
use App\ServiceClientRatePlan;
use App\LogClientRatePlan;

class SyncNewClientRestrictions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clientId;

    /**
     * Create a new job instance.
     *
     * @param int $clientId
     * @return void
     */
    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = Client::find($this->clientId);
        if (!$client || $client->status != 1) {
            return;
        }

        $years = [
            Carbon::now()->format('Y'),
            Carbon::now()->addYear()->format('Y')
        ];

        // Process Hotel Restrictions
        $this->processHotelRestrictions($client, $years);

        // Process Service Restrictions
        $this->processServiceRestrictions($client, $years);
    }

    /**
     * Process Hotel Rate Plan Restrictions
     */
    protected function processHotelRestrictions($client, $years)
    {
        $ratePlans = RatesPlans::where('status', 1)
            ->whereHas('rate_plans_room', function ($query) {
                $query->where('channel_id', 1);
            })->get(['id']);

        foreach ($ratePlans as $plan) {
            // Check whitelist associations
            $associations = RatePlanAssociation::where('rate_plan_id', $plan->id)->get();

            // If no associations, everyone sees it (no restriction needed)
            if ($associations->count() == 0) continue;

            // Check if client is allowed (whitelisted)
            $isAllowed = $this->checkAllowed($client, $associations, 'App\RatePlanAssociation');

            // If NOT allowed, apply restriction
            if (!$isAllowed) {
                foreach ($years as $year) {
                    $restriction = ClientRatePlan::firstOrCreate([
                        'period' => $year,
                        'client_id' => $client->id,
                        'rate_plan_id' => $plan->id
                    ], [
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    // Log insertion if it was recently created
                    if ($restriction->wasRecentlyCreated) {
                        LogClientRatePlan::logInsertion(
                            'hotel',
                            $plan->id,
                            $client->id,
                            $year,
                            'observer',
                            'New client created (Auto Sync)'
                        );
                    }
                }
            }
        }
    }

    /**
     * Process Service Rate Restrictions
     */
    protected function processServiceRestrictions($client, $years)
    {
        $serviceRates = ServiceRate::where('status', 1)
            ->whereHas('service', function ($query) {
                $query->where('status', 1);
            })->get(['id']);

        foreach ($serviceRates as $rate) {
            // Check whitelist associations
            $associations = ServiceRateAssociation::where('service_rate_id', $rate->id)->get();

            // If no associations, everyone sees it
            if ($associations->count() == 0) continue;

            // Check if client is allowed
            $isAllowed = $this->checkAllowed($client, $associations, 'App\ServiceRateAssociation');

            // If NOT allowed, apply restriction
            if (!$isAllowed) {
                foreach ($years as $year) {
                    $restriction = ServiceClientRatePlan::firstOrCreate([
                        'period' => $year,
                        'client_id' => $client->id,
                        'service_rate_id' => $rate->id
                    ], [
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    // Log insertion if it was recently created
                    if ($restriction->wasRecentlyCreated) {
                        LogClientRatePlan::logInsertion(
                            'service',
                            $rate->id,
                            $client->id,
                            $year,
                            'observer',
                            'New client created (Auto Sync)'
                        );
                    }
                }
            }
        }
    }

    /**
     * Generic Whitelist Check Logic
     */
    protected function checkAllowed($client, $associations, $modelClass)
    {
        // 1. Markets
        $marketAssoc = $associations->where('entity', 'Market');
        if ($marketAssoc->count() > 0) {
            // Check if client's market is in the allowed list
            if (!$marketAssoc->contains('object_id', $client->market_id)) {
                return false;
            }
        }

        // 2. Countries
        $countryAssoc = $associations->where('entity', 'Country');
        if ($countryAssoc->count() > 0) {
            $countryIds = $countryAssoc->pluck('object_id');
            $isExcept = $countryAssoc->first()->except; // 0 = Include, 1 = Exclude

            if ($isExcept) {
                // If exclude mode, client country MUST NOT be in list
                if ($countryIds->contains($client->country_id)) return false;
            } else {
                // If include mode, client country MUST be in list
                if (!$countryIds->contains($client->country_id)) return false;
            }
        }

        // 3. Clients
        $clientAssoc = $associations->where('entity', 'Client');
        if ($clientAssoc->count() > 0) {
            $clientIds = $clientAssoc->pluck('object_id');
            $isExcept = $clientAssoc->first()->except;

            if ($isExcept) {
                // If exclude mode, client MUST NOT be in list
                if ($clientIds->contains($client->id)) return false;
            } else {
                // If include mode, client MUST be in list
                if (!$clientIds->contains($client->id)) return false;
            }
        }

        // If passed all checks, it's allowed
        return true;
    }
}
