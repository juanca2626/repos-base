<?php

namespace Src\Modules\File\Application\EventHandlers;

use Carbon\Carbon;
use Src\Modules\File\Domain\Events\CreatedFileEvent;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileCreateStatementJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileHotelSupplierStellaJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessFileServicesStellaJob;
use Illuminate\Support\Facades\Bus;
use Src\Modules\File\Infrastructure\Jobs\ProcessExecuteStatementJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessFilePassToOpeJob;
use Src\Modules\File\Infrastructure\Jobs\ProcessUpdateCountryFlightJob;
use Src\Modules\File\Presentation\Http\Traits\SqsNotification;
use Illuminate\Support\Facades\DB;

class CreatedFileEventHandler
{
    use SqsNotification;

    public function handle(CreatedFileEvent $event): void
    {
        // $fileItineraries = $event->file->getItineraries();
        $fileItineraries = $event->itinerariesData;
        $equivalences = [];
        $file_itinerary_ids = [];
        $hotels = collect();
        if (count($fileItineraries) > 0) {
            foreach ($fileItineraries as $itinerary) {
                if ($itinerary->entity->value() === 'service') {
                    $totalAdults = $itinerary->totalAdults->value();
                    $totalChildren = $itinerary->totalChildren->value();
                    $totalInfants = $itinerary->totalInfants->value();
                    $totalPassengers = $totalAdults + $totalChildren + $totalInfants;
                    $equivalences[] = [
                        "file_itinerary_id" => $itinerary->id,
                        "code" => $itinerary->serviceCode->value(),
                        "date_in" => Carbon::parse($itinerary->dateIn->value())->format('d/m/Y'),
                        "total_passengers" => $totalPassengers,
                        "total_children" => $itinerary->totalChildren->value(),
                        "start_time" => $itinerary->startTime->value(),
                    ];
                    array_push($file_itinerary_ids, $itinerary->id);
                }

                if ($itinerary->entity->value() === 'hotel') {
                    $hotels->add(
                        $itinerary->serviceCode->value()
                    );
                }
            }

            $hotelCodesData = [
                "service_codes" => $hotels->unique()->values()
            ];

            $equivalencesData = [
                "equivalences" => $equivalences
            ];

            DB::table('job_variables')->insert([
                'module' => 'crear servicios master',
                'key' => 'equivalencesData',
                'value' => json_encode($equivalencesData),
                'created_at' => now()
            ]);

            DB::table('job_variables')->insert([
                'module' => 'crear servicios master',
                'key' => 'fileItineraries',
                'value' => json_encode($fileItineraries),
                'created_at' => now()
            ]);

            Bus::chain([
                new ProcessFileServicesStellaJob($event->id, $equivalencesData, $event->file->fileNumber->value(), $file_itinerary_ids),
                // new ProcessFilePassToOpeJob($event->id),
                new ProcessExecuteStatementJob($event->id, $event->file->executiveId->value())
            ])->dispatch();

            dispatch(new ProcessFileHotelSupplierStellaJob($event->id, $hotelCodesData));
            dispatch(new ProcessUpdateCountryFlightJob($event->id));
        }
    }
}
