<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\ClientRatePlan;
use App\RatePlanAssociation;
use App\RatesPlans;
use App\Client;
use App\LogClientRatePlan;

class SyncRatesClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:rates-clients
                            {--apply : Apply missing restrictions}
                            {--verbose-list : Show the detailed list of every client missing (danger: can be long)}
                            {--debug-id= : Show deep details for a specific rate plan ID}
                            {--show-invalid : Show restrictions that should NOT exist (clients in whitelist)}
                            {--cleanup-invalid : Remove invalid restrictions (clients now in whitelist)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync rate plan restrictions for current year and next year';

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
            $this->debugRatePlan((int)$debugId, $currentYear, $activeClients, $apply, $cleanupInvalid);
            return;
        }

        $ratePlans = RatesPlans::where('status', 1)
            ->whereHas('rate_plans_room', function ($query) {
                $query->where('channel_id', 1);
            })
            ->get();

        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║   HOTEL RATE RESTRICTIONS SYNC (Channel: 1)                  ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");
        $this->info("Active Clients: " . $activeClients->count());
        $this->info("Active Rate Plans: " . $ratePlans->count());
        $this->info("Processing Years: $currentYear, $nextYear");
        $this->line("");

        $yearStats = [];

        foreach ($years as $year) {
            $this->info("Processing year: <fg=cyan>$year</>");

            $missingTotal = 0;
            $invalidTotal = 0;
            $bar = $this->output->createProgressBar($ratePlans->count());
            $bar->start();

            $summary = [];
            $invalidSummary = [];

            foreach ($ratePlans as $ratePlan) {
                // Check if rate plan has associations (white-list)
                $associations = RatePlanAssociation::where('rate_plan_id', $ratePlan->id)->get();

                if ($associations->count() == 0) {
                    $bar->advance();
                    continue;
                }

                // Calculate Whitelist based on Hierarchy: Client > Country > Market
                // Requirement: Only use Market associations if Client and Country associations are empty.
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
                $existingRestrictions = ClientRatePlan::where('rate_plan_id', $ratePlan->id)
                    ->where('period', $year)
                    ->pluck('client_id')
                    ->toArray();

                // Clients that SHOULD be restricted (Active, not in whitelist, not already restricted)
                $missingClients = $activeClients->whereNotIn('id', $whiteListIds)
                    ->whereNotIn('id', $existingRestrictions);

                if ($missingClients->count() > 0) {
                    $missingTotal += $missingClients->count();
                    $summary[] = [
                        'id' => $ratePlan->id,
                        'name' => $ratePlan->name,
                        'missing' => $missingClients->count()
                    ];

                    if ($verbose) {
                        foreach ($missingClients as $client) {
                            $this->line("Rate Plan: <info>{$ratePlan->id}</info> | Client: <comment>{$client->id}</comment> (<comment>{$client->name}</comment>) - MISSING");
                        }
                    }

                    if ($apply) {
                        foreach ($missingClients as $client) {
                            ClientRatePlan::insert([
                                'period' => $year,
                                'client_id' => $client->id,
                                'rate_plan_id' => $ratePlan->id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                            ]);

                            // Log the insertion
                            LogClientRatePlan::logInsertion(
                                'hotel',
                                $ratePlan->id,
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
                    $invalidClients = ClientRatePlan::where('rate_plan_id', $ratePlan->id)
                        ->where('period', $year)
                        ->whereIn('client_id', $whiteListIds)
                        ->get();

                    if ($invalidClients->count() > 0) {
                        $invalidTotal += $invalidClients->count();
                        $invalidSummary[] = [
                            'id' => $ratePlan->id,
                            'name' => $ratePlan->name,
                            'invalid' => $invalidClients->count()
                        ];

                        if ($verbose) {
                            foreach ($invalidClients as $restriction) {
                                $client = $activeClients->firstWhere('id', $restriction->client_id);
                                if ($client) {
                                    $this->line("Rate Plan: <info>{$ratePlan->id}</info> | Client: <comment>{$client->id}</comment> (<comment>{$client->name}</comment>) - INVALID (now in whitelist)");
                                }
                            }
                        }

                        if ($cleanupInvalid) {
                            // Log deletions before removing
                            foreach ($invalidClients as $restriction) {
                                LogClientRatePlan::logDeletion(
                                    'hotel',
                                    $ratePlan->id,
                                    $restriction->client_id,
                                    $year,
                                    'command',
                                    'Client now in whitelist'
                                );
                            }

                            // Delete after logging
                            ClientRatePlan::where('rate_plan_id', $ratePlan->id)
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
                $this->table(['Rate Plan ID', 'Name', 'Missing Restrictions'], $stats['summary']);
            }
            $this->info("Total missing restrictions: {$stats['missing']}");

            if ($apply && $stats['missing'] > 0) {
                $this->info("✓ Restrictions applied successfully.");
            }

            if ($showInvalid || $cleanupInvalid) {
                if (count($stats['invalidSummary']) > 0) {
                    $this->line("");
                    $this->line("<fg=yellow>INVALID RESTRICTIONS (clients blocked but now in whitelist):</>");
                    $this->table(['Rate Plan ID', 'Name', 'Invalid Restrictions'], $stats['invalidSummary']);
                }
                $this->warn("Total invalid restrictions: {$stats['invalid']}");

                if ($cleanupInvalid && $stats['invalid'] > 0) {
                    $this->info("✓ Invalid restrictions removed successfully.");
                }
            }
        }
    }

    protected function debugRatePlan($id, $currentYear, $activeClients, $apply = false, $cleanupInvalid = false)
    {
        $ratePlan = RatesPlans::find($id);
        if (!$ratePlan) {
            $this->error("Rate Plan $id not found.");
            return;
        }

        $this->info("╔═══════════════════════════════════════════════════════════════╗");
        $this->info("║ DEBUG DETAILS FOR RATE PLAN: [$id] " . str_pad($ratePlan->name, 35) . " ║");
        $this->info("╚═══════════════════════════════════════════════════════════════╝");

        $associations = RatePlanAssociation::where('rate_plan_id', $id)->get();
        $this->info("Total Associations: " . $associations->count());
        foreach ($associations as $assoc) {
            $this->line(" - Entity: <comment>{$assoc->entity}</comment> | Object ID: <comment>{$assoc->object_id}</comment> | Except: <comment>{$assoc->except}</comment>");
        }

        $this->line("");
        $this->info("Evaluating Hierarchy (Client > Country > Market)...");
        $this->info("Market rules are ignored if Client or Country rules exist.");

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

            $existingRestrictions = ClientRatePlan::where('rate_plan_id', $id)
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
                        ClientRatePlan::insert([
                            'period' => $year,
                            'client_id' => $client->id,
                            'rate_plan_id' => $id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ]);

                        LogClientRatePlan::logInsertion(
                            'hotel',
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
            $invalidRestrictions = ClientRatePlan::where('rate_plan_id', $id)
                ->where('period', $year)
                ->whereIn('client_id', $whiteListIds)
                ->get();

            if ($invalidRestrictions->count() > 0) {
                $this->error("INVALID RESTRICTIONS (Blocked but SHOULD BE ALLOWED): " . $invalidRestrictions->count());

                if ($cleanupInvalid) {
                    $this->info("Cleaning up invalid restrictions for $year...");
                    foreach ($invalidRestrictions as $restriction) {
                        LogClientRatePlan::logDeletion(
                            'hotel',
                            $id,
                            $restriction->client_id,
                            $year,
                            'command_debug',
                            'Client now in whitelist (Hierarchy)'
                        );
                    }

                    ClientRatePlan::where('rate_plan_id', $id)
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
