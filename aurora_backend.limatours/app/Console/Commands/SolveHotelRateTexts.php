<?php

namespace App\Console\Commands;

use App\Language;
use App\RatesPlans;
use App\Translation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolveHotelRateTexts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:solve_hotel_rate_translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soluciona el problema de textos en las tarifas de hoteles';

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
        $languages = Language::where('state', 1)->get(['id']);
        $ratePlans = RatesPlans::with([
            'translations' => function ($query) {
                $query->select(['object_id', 'value', 'language_id', 'created_at']);
            }
        ])->with([
            'translations_no_show' => function ($query) {
                $query->select(['object_id', 'value', 'language_id', 'created_at']);
            }
        ])->with([
            'translations_day_use' => function ($query) {
                $query->select(['object_id', 'value', 'language_id', 'created_at']);
            }
        ])->with([
            'translations_notes' => function ($query) {
                $query->select(['object_id', 'value', 'language_id', 'created_at']);
            }
        ])
//            ->where('id', 441)
            ->get(['id', 'code', 'name', 'hotel_id']);

        $arrayLanguages = $languages->pluck('id');

        $missingRates = collect();
        foreach ($ratePlans as $ratePlan) {
            $language_name = $ratePlan->translations->pluck('language_id');
            $language_day_use = $ratePlan->translations_day_use->pluck('language_id');
            $language_no_show = $ratePlan->translations_no_show->pluck('language_id');
            $language_notes = $ratePlan->translations_notes->pluck('language_id');

            $missingLanguagesNames = $arrayLanguages->diff($language_name);
            $missingLanguagesDayUse = $arrayLanguages->diff($language_day_use);
            $missingLanguagesNoShow = $arrayLanguages->diff($language_no_show);
            $missingLanguagesNotes = $arrayLanguages->diff($language_notes);

            $insertTranslationNames = [];
            $insertTranslationDayUse = [];
            $insertTranslationNoShow = [];
            $insertTranslationNotes = [];
            //Todo Nombres comerciales
            if (count($missingLanguagesNames) > 0) {
                foreach ($missingLanguagesNames as $language) {
                    try {
                        $create_at = (empty($ratePlan->translations->first()->created_at)) ? Carbon::now() : $ratePlan->translations->first()->created_at;
                        if ($ratePlan->translations->first()) {
                            $valueTranslate = $ratePlan->translations->first()->value;
                        } else {
                            $valueTranslate = '';
                        }
                        $insertTranslationNames[] = [
                            'type' => 'rates_plan',
                            'object_id' => $ratePlan->id,
                            'slug' => 'commercial_name',
                            'value' => $valueTranslate,
                            'language_id' => $language,
                            'created_at' => $create_at,
                            'updated_at' => $create_at
                        ];
                    } catch (\Exception $exception) {
                        dd('commercial_name', $ratePlan->translations);
                    }

                }
            }
            //Todo Texto Day Use
            if (count($missingLanguagesDayUse) > 0) {
                foreach ($missingLanguagesDayUse as $language) {
                    try {
                        $create_at = (empty($ratePlan->translations_day_use->first()->created_at)) ? Carbon::now() : $ratePlan->translations_day_use->first()->created_at;
                        if ($ratePlan->translations_day_use->first()) {
                            $valueTranslate = $ratePlan->translations_day_use->first()->value;
                        } else {
                            $valueTranslate = '';
                        }
                        $insertTranslationDayUse[] = [
                            'type' => 'rates_plan',
                            'object_id' => $ratePlan->id,
                            'slug' => 'day_use',
                            'value' => $valueTranslate,
                            'language_id' => $language,
                            'created_at' => $create_at,
                            'updated_at' => $create_at
                        ];
                    } catch (\Exception $exception) {
                        dd('day_use', $ratePlan->translations);
                    }
                }
            }
            //Todo Texto No show
            if (count($missingLanguagesNoShow) > 0) {
                foreach ($missingLanguagesNoShow as $language) {
                    try {
                        $create_at = (empty($ratePlan->translations_no_show->first()->created_at)) ? Carbon::now() : $ratePlan->translations_no_show->first()->created_at;
                        if ($ratePlan->translations_no_show->first()) {
                            $valueTranslate = $ratePlan->translations_no_show->first()->value;
                        } else {
                            $valueTranslate = '';
                        }
                        $insertTranslationNoShow[] = [
                            'type' => 'rates_plan',
                            'object_id' => $ratePlan->id,
                            'slug' => 'no_show',
                            'value' => $valueTranslate,
                            'language_id' => $language,
                            'created_at' => $create_at,
                            'updated_at' => $create_at
                        ];
                    } catch (\Exception $exception) {
                        dd('no_show', $ratePlan->translations);
                    }
                }
            }
            //Todo Texto Notas
            if (count($missingLanguagesNotes) > 0) {
                foreach ($missingLanguagesNotes as $language) {
                    try {
                        $create_at = (empty($ratePlan->translations_notes->first()->created_at)) ? Carbon::now() : $ratePlan->translations_notes->first()->created_at;
                        if ($ratePlan->translations_notes->first()) {
                            $valueTranslate = $ratePlan->translations_notes->first()->value;
                        } else {
                            $valueTranslate = '';
                        }
                        $insertTranslationNotes[] = [
                            'type' => 'rates_plan',
                            'object_id' => $ratePlan->id,
                            'slug' => 'notes',
                            'value' => $valueTranslate,
                            'language_id' => $language,
                            'created_at' => $create_at,
                            'updated_at' => $create_at
                        ];
                    } catch (\Exception $exception) {
                        dd('notes', $ratePlan->translations);
                    }
                }
            }

            $missingRates->add([
                'rate_plan' => [
                    'id' => $ratePlan->id,
                    'name' => $ratePlan->name,
                    'hotel_id' => $ratePlan->hotel_id,
                ],
                'translations' => [
                    'name' => $ratePlan->translations->toArray(),
                    'day_use' => $ratePlan->translations_day_use->toArray(),
                    'no_show' => $ratePlan->translations_no_show->toArray(),
                    'notes' => $ratePlan->translations_notes->toArray(),
                ],
                'missing_languages' => [
                    'names' => $missingLanguagesNames->toArray(),
                    'day_use' => $missingLanguagesDayUse->toArray(),
                    'no_show' => $missingLanguagesNoShow->toArray(),
                    'notes' => $missingLanguagesNotes->toArray(),
                ],
                'inserts_names' => $insertTranslationNames,
                'inserts_day_uses' => $insertTranslationDayUse,
                'inserts_no_shows' => $insertTranslationNoShow,
                'inserts_notes' => $insertTranslationNotes,
            ]);
        }
        $this->output->progressStart($missingRates->count());
        DB::transaction(function () use ($missingRates) {
            foreach ($missingRates as $missingRate) {
                sleep(1);
                if (count($missingRate['inserts_names']) > 0) {
                    DB::table('translations')->insert($missingRate['inserts_names']);
                }
                if (count($missingRate['inserts_day_uses']) > 0) {
                    DB::table('translations')->insert($missingRate['inserts_day_uses']);
                }
                if (count($missingRate['inserts_no_shows']) > 0) {
                    DB::table('translations')->insert($missingRate['inserts_no_shows']);
                }
                if (count($missingRate['inserts_notes']) > 0) {
                    DB::table('translations')->insert($missingRate['inserts_notes']);
                }
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
        });

    }
}
