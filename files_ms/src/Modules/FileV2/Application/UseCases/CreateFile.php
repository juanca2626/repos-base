<?php

namespace Src\Modules\FileV2\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Src\Modules\FileV2\Infrastructure\Persistence\FileRepository;
use Src\Modules\Passengers\Application\UseCases\CreatePassenger;
use Src\Modules\FileV2\Application\Validators\FileValidator;
use Src\Modules\FileV2\Application\Builders\FileDataBuilder;
use Src\Modules\FileV2\Application\Validators\PassengerListValidator;
use Src\Modules\Itineraries\Submodules\Hotels\Application\UseCases\CreateHotelItinerary;

class CreateFile
{
    private $fileRepo;
    private $validator;
    private $createPassenger;
    private $builder;
    private $passengerListValidator;
    private $createHotelItinerary;

    public function __construct(
        FileRepository $fileRepo,
        FileValidator $validator,
        CreatePassenger $createPassenger,
        FileDataBuilder $builder,
        PassengerListValidator $passengerListValidator,
        CreateHotelItinerary $createHotelItinerary
    ) {
        $this->fileRepo = $fileRepo;
        $this->validator = $validator;
        $this->createPassenger = $createPassenger;
        $this->builder = $builder;
        $this->passengerListValidator = $passengerListValidator;
        $this->createHotelItinerary = $createHotelItinerary;
    }

    public function execute(array $data)
    {
        $this->validator->validate($data);

        $this->passengerListValidator->validate(
            $data['reservations_passenger'] ?? []
        );
        
        return DB::transaction(function () use ($data) {

            // FILE
            $fileData = $this->builder->build($data);
            $file = $this->fileRepo->create($fileData);

            // PASSENGERS
            $passengerMapBySequence = [];
            foreach ($data['reservations_passenger'] ?? [] as $p) {
                $passenger = $this->createPassenger->execute($file->id, $p);
                $passengerMapBySequence[$p['sequence_number']] = $passenger->id;
            }

            // CATEGORIES
            // foreach ($data['categories'] ?? [] as $c) {
            //     $this->fileRepo->createCategory($file->id, $c);
            // }

            // HOTELS
            foreach ($data['reservations_hotel'] ?? [] as $hotel) {

                $this->createHotelItinerary->execute(
                    $file->id,
                    $hotel,
                    $passengerMapBySequence
                );
            }
                
            return $file;
        });
    }
}