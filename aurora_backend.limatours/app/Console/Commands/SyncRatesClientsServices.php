<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\ServiceClientRatePlan;
use App\ServiceRateAssociation;
use App\ServiceRate;
use App\Client;
use App\LogClientRatePlan;

class SyncRatesClientsServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:rates-clients-services
                            {--apply : Apply missing restrictions}
                            {--verbose-list : Show the detailed list of every client missing}
                            {--debug-id= : Show deep details for a specific service rate ID}
                            {--show-invalid : Show restrictions that should NOT exist (clients in whitelist)}
                            {--cleanup-invalid : Remove invalid restrictions (clients now in whitelist)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync service rate restrictions for current year and next year';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apply = $this->option('apply');
        $verbose = $this->option('verbose-list');
        $debugId = $this->option('debug-id');
        $showInvalid = $this->option('show-invalid');
        $cleanupInvalid = $this->option('cleanup-invalid');

        $currentYear = (int)Carbon::now()->format('Y');
        $nextYear = $currentYear + 1;
        $years = [$currentYear, $nextYear];

        $activeClients = Client::where('status', 1)
            ->whereHas('markets', function ($query) {
                $query->where('status', 1);
            })
            ->get(['id', 'name', 'market_id', 'country_id']);

        if ($debugId) {
            $this->debugServiceRate((int)$debugId, $currentYear, $activeClients, $apply, $cleanupInvalid);
            return;
        }

        $serviceRates = ServiceRate::where('status', 1)
            ->whereHas('service', function ($query) {
                $query->where('status', 1);
            })
            ->get();

        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║        SERVICE RATE RESTRICTIONS SYNC                         ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->info("Active Clients: " . $activeClients->count());
        $this->info("Active Service Rates: " . $serviceRates->count());
        $this->info("Processing Years: $currentYear, $nextYear");
        $this->line("");

        $yearStats = [];

        foreach ($years as $year) {
            $this->info("Processing year: <fg=cyan>$year</>");

            $missingTotal = 0;
            $invalidTotal = 0;
            $bar = $this->output->createProgressBar($serviceRates->count());
            $bar->start();

            $summary = [];
            $invalidSummary = [];

            foreach ($serviceRates as $rate) {
            // Check if rate has associations (whitelist)
            $associations = ServiceRateAssociation::where('service_rate_id', $rate->id)->get();

            if ($associations->count() == 0) {
                $bar->advance();
                continue;
            }

            $whiteListIds = [];
            foreach ($activeClients as $client) {
                // 1. Client Level
                $clientRules = $associations->where('entity', 'Client');
                if ($clientRules->count() > 0) {
                    $include = $clientRules->where('except', 0)->pluck('object_id')->toArray();
                    $exclude = $clientRules->where('except', 1)->pluck('object_id')->toArray();
                    
                    if (count($include) > 0) {
                        if (in_array($client->id, $include)) {
                            $whiteListIds[] = $client->id;
                            continue;
                        } else {
                            // Client priority inclusion exists, and this client is not in it.
                            continue; 
                        }
                    }
                    if (in_array($client->id, $exclude)) {
                        continue; 
                    }
                }

                // 2. Country Level
                $countryRules = $associations->where('entity', 'Country');
                if ($countryRules->count() > 0) {
                    $include = $countryRules->where('except', 0)->pluck('object_id')->toArray();
                    $exclude = $countryRules->where('except', 1)->pluck('object_id')->toArray();

                    if (count($include) > 0) {
                        if (in_array($client->country_id, $include)) {
                            $whiteListIds[] = $client->id;
                            continue;
                        } else {
                            // Country priority inclusion exists, and this client is not in it.
                            continue;
                        }
                    }
                    if (in_array($client->country_id, $exclude)) {
                        continue;
                    }
                }

                // 3. Market Level
                $marketRules = $associations->where('entity', 'Market');
                if ($marketRules->count() > 0) {
                    $include = $marketRules->pluck('object_id')->toArray();
                    if (in_array($client->market_id, $include)) {
                        $whiteListIds[] = $client->id;
                    }
                } else {
                    // If no market rules, and passed previous levels, it's allowed
                    $whiteListIds[] = $client->id;
                }
            }

            // Current restrictions in DB
            $existingRestrictions = ServiceClientRatePlan::where('service_rate_id', $rate->id)
                ->where('period', $year)
                ->pluck('client_id')
                ->toArray();

            // Clients that SHOULD be restricted
            $missingClients = $activeClients->whereNotIn('id', $whiteListIds)
                ->whereNotIn('id', $existingRestrictions);

            if ($missingClients->count() > 0) {
                $missingTotal += $missingClients->count();
                $summary[] = [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'missing' => $missingClients->count()
                ];

                if ($verbose) {
                    foreach ($missingClients as $client) {
                        $this->line("Service Rate: <info>{$rate->id}</info> | Client: <comment>{$client->id}</comment> - MISSING");
                    }
                }

                    if ($apply) {
                        foreach ($missingClients as $client) {
                            ServiceClientRatePlan::insert([
                                'period' => $year,
                                'client_id' => $client->id,
                                'service_rate_id' => $rate->id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ]);

                            // Log the insertion
                            LogClientRatePlan::logInsertion(
                                'service',
                                $rate->id,
                                $client->id,
                                $year,
                                'command',
                                'Client not in whitelist'
                            );
                        }
                    }
            }

            // Check for INVALID restrictions (clients that ARE restricted but SHOULD NOT be)
            if ($showInvalid || $cleanupInvalid) {
                $invalidClients = ServiceClientRatePlan::where('service_rate_id', $rate->id)
                    ->where('period', $year)
                    ->whereIn('client_id', $whiteListIds)
                    ->get();

                if ($invalidClients->count() > 0) {
                    $invalidTotal += $invalidClients->count();
                    $invalidSummary[] = [
                        'id' => $rate->id,
                        'name' => $rate->name,
                        'invalid' => $invalidClients->count()
                    ];

                    if ($verbose) {
                        foreach ($invalidClients as $restriction) {
                            $client = $activeClients->firstWhere('id', $restriction->client_id);
                            if ($client) {
                                $this->line("Service Rate: <info>{$rate->id}</info> | Client: <comment>{$client->id}</comment> (<comment>{$client->name}</comment>) - INVALID (now in whitelist)");
                            }
                        }
                    }

                    if ($cleanupInvalid) {
                        // Log deletions before removing
                        foreach ($invalidClients as $restriction) {
                            LogClientRatePlan::logDeletion(
                                'service',
                                $rate->id,
                                $restriction->client_id,
                                $year,
                                'command',
                                'Client now in whitelist'
                            );
                        }

                        // Delete after logging
                        ServiceClientRatePlan::where('service_rate_id', $rate->id)
                            ->where('period', $year)
                            ->whereIn('client_id', $whiteListIds)
                            ->delete();
                    }
                }
            }

                $bar->advance();
            }

            $bar->finish();
            $this->line("");

            // Store stats for this year
            $yearStats[$year] = [
                'missing' => $missingTotal,
                'invalid' => $invalidTotal,
                'summary' => $summary,
                'invalidSummary' => $invalidSummary
            ];

            $this->line("");
        }

        // Display summary for all years
        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║                    SUMMARY BY YEAR                            ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");

        foreach ($years as $year) {
            $stats = $yearStats[$year];
            $this->line("");
            $this->info("━━━ YEAR <fg=cyan>$year</> ━━━");

            if (count($stats['summary']) > 0) {
                $this->table(['Service Rate ID', 'Name', 'Missing Restrictions'], $stats['summary']);
            }
            $this->info("Total missing service restrictions: {$stats['missing']}");

            if ($apply && $stats['missing'] > 0) {
                $this->info("✓ Restrictions applied successfully.");
            }

            if ($showInvalid || $cleanupInvalid) {
                if (count($stats['invalidSummary']) > 0) {
                    $this->line("");
                    $this->line("<fg=yellow>INVALID SERVICE RESTRICTIONS (clients blocked but now in whitelist):</>");
                    $this->table(['Service Rate ID', 'Name', 'Invalid Restrictions'], $stats['invalidSummary']);
                }
                $this->warn("Total invalid service restrictions: {$stats['invalid']}");
                if ($cleanupInvalid && $stats['invalid'] > 0) {
                    $this->info("✓ Invalid restrictions removed successfully.");
                }
            }
        }
    }

    protected function debugServiceRate($id, $currentYear, $activeClients, $apply = false, $cleanupInvalid = false)
    {
        $rate = ServiceRate::find($id);
        if (!$rate) {
            $this->error("Service Rate $id not found.");
            return;
        }

        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║ DEBUG DETAILS FOR SERVICE RATE: [$id] " . str_pad($rate->name, 35) . " ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");

        $associations = ServiceRateAssociation::where('service_rate_id', $id)->get();
        $this->info("Total Associations: " . $associations->count());
        foreach ($associations as $assoc) {
            $this->line(" - Entity: <comment>{$assoc->entity}</comment> | Object ID: <comment>{$assoc->object_id}</comment> | Except: <comment>{$assoc->except}</comment>");
        }

        $this->line("");
        $whiteListIds = [];
        foreach ($activeClients as $client) {
            $allowedAt = null;
            $ruleApplied = null;

            // 1. Client Level
            $clientRules = $associations->where('entity', 'Client');
            if ($clientRules->count() > 0) {
                $include = $clientRules->where('except', 0)->pluck('object_id')->toArray();
                $exclude = $clientRules->where('except', 1)->pluck('object_id')->toArray();
                
                if (count($include) > 0) {
                    if (in_array($client->id, $include)) {
                        $whiteListIds[] = $client->id;
                        $allowedAt = 'Client';
                        $ruleApplied = 'Include';
                    } else {
                        $ruleApplied = 'Blocked (Client Priority Include exists)';
                    }
                } elseif (in_array($client->id, $exclude)) {
                    $ruleApplied = 'Exclude';
                }
            }

            // 2. Country Level
            if (!$ruleApplied) {
                $countryRules = $associations->where('entity', 'Country');
                if ($countryRules->count() > 0) {
                    $include = $countryRules->where('except', 0)->pluck('object_id')->toArray();
                    $exclude = $countryRules->where('except', 1)->pluck('object_id')->toArray();

                    if (count($include) > 0) {
                        if (in_array($client->country_id, $include)) {
                            $whiteListIds[] = $client->id;
                            $allowedAt = 'Country';
                            $ruleApplied = 'Include';
                        } else {
                            $ruleApplied = 'Blocked (Country Priority Include exists)';
                        }
                    } elseif (in_array($client->country_id, $exclude)) {
                        $ruleApplied = 'Exclude';
                    }
                }
            }

            // 3. Market Level
            if (!$ruleApplied) {
                $marketRules = $associations->where('entity', 'Market');
                if ($marketRules->count() > 0) {
                    $include = $marketRules->pluck('object_id')->toArray();
                    if (in_array($client->market_id, $include)) {
                        $whiteListIds[] = $client->id;
                        $allowedAt = 'Market';
                        $ruleApplied = 'Include';
                    } else {
                        $ruleApplied = 'Exclude (Not in Market)';
                    }
                } else {
                    $whiteListIds[] = $client->id;
                    $allowedAt = 'Default';
                    $ruleApplied = 'Allow (No Market Rules)';
                }
            }

            // Special logging for IDs mentioned by user or critical issues
            if ($client->id == 15766 || $client->id == 14139 || (count($whiteListIds) < 20 && $allowedAt)) {
                $status = $allowedAt ? "<info>ALLOWED</info> by $allowedAt ($ruleApplied)" : "<fg=red>BLOCKED</> by $ruleApplied";
                $this->line("   - Client <comment>{$client->id}</comment> ({$client->name}): $status");
            }
        }

        $this->info("Total clients in Whitelist: " . count($whiteListIds));

        $years = [$currentYear, $currentYear + 1];

        foreach ($years as $year) {
            $this->line("");
            $this->info("━━━ YEAR <fg=cyan>$year</> ━━━");

            $existingRestrictions = ServiceClientRatePlan::where('service_rate_id', $id)
                ->where('period', $year)
                ->pluck('client_id')
                ->toArray();
            $this->info("Existing Restrictions In DB: " . count($existingRestrictions));

            // Missing
            $missingClients = $activeClients->whereNotIn('id', $whiteListIds)
                ->whereNotIn('id', $existingRestrictions);

            if ($missingClients->count() > 0) {
                $this->warn("MISSING RESTRICTIONS (Should be blocked but aren't): " . $missingClients->count());

                if ($apply) {
                    $this->info("Applying missing restrictions for $year...");
                    foreach ($missingClients as $client) {
                        ServiceClientRatePlan::insert([
                            'period' => $year,
                            'client_id' => $client->id,
                            'service_rate_id' => $id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);

                        LogClientRatePlan::logInsertion(
                            'service',
                            $id,
                            $client->id,
                            $year,
                            'command_debug',
                            'Client not in whitelist (Hierarchy)'
                        );
                    }
                    $this->info("✓ Applied " . $missingClients->count() . " restrictions.");
                } elseif ($missingClients->count() < 20) {
                    foreach ($missingClients as $client) {
                        $this->line("   - MISSING: <info>{$client->id}</info> - {$client->name}");
                    }
                }
            } else {
                $this->info("✓ No missing restrictions found.");
            }

            // Invalid
            $invalidRestrictions = ServiceClientRatePlan::where('service_rate_id', $id)
                ->where('period', $year)
                ->whereIn('client_id', $whiteListIds)
                ->get();

            if ($invalidRestrictions->count() > 0) {
                $this->error("INVALID RESTRICTIONS (Blocked but SHOULD BE ALLOWED): " . $invalidRestrictions->count());

                if ($cleanupInvalid) {
                    $this->info("Cleaning up invalid restrictions for $year...");
                    foreach ($invalidRestrictions as $restriction) {
                        LogClientRatePlan::logDeletion(
                            'service',
                            $id,
                            $restriction->client_id,
                            $year,
                            'command_debug',
                            'Client now in whitelist (Hierarchy)'
                        );
                    }

                    ServiceClientRatePlan::where('service_rate_id', $id)
                        ->where('period', $year)
                        ->whereIn('client_id', $whiteListIds)
                        ->delete();

                    $this->info("✓ Removed " . $invalidRestrictions->count() . " invalid restrictions.");
                } else {
                    foreach ($invalidRestrictions as $rest) {
                        $c = $activeClients->firstWhere('id', $rest->client_id);
                        $this->line("   - INVALID: <fg=red>{$rest->client_id}</> - " . ($c ? $c->name : 'N/A'));
                    }
                }
            } else {
                $this->info("✓ No invalid restrictions found.");
            }
        }
    }
}
