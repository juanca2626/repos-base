<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FilePassenger;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Genre;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Phone;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\TypePassenger;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CityIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CostByPassenger;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CountryIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DateBirth;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DietaryRestrictions;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DoctypeIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentTypeId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentUrl;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Email;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Frequent;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\MedicalRestrictions;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Name;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Notes;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\OrderNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\SequenceNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\SuggestedRoomType;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Surnames;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;

class FilePassengerMapper
{

    public static function fromRequestToArray(
        array $passengers
    ): array {

        $passengerEntities = [];
        foreach($passengers as $passenger) {
            $passenger['id'] = null;
            array_push($passengerEntities, $passenger);
        }
        return $passengerEntities;
    }

    public static function fromArraySearch(array $passengers): FilePassengers
    {
        return  new FilePassengers(array_map(function ($passenger) {
            return self::fromArray($passenger);
        }, $passengers));
    }

    public static function fromArray(array $passenger): FilePassenger
    {
        $filePassengerEloquentModel = new FilePassengerEloquentModel($passenger);
        $filePassengerEloquentModel->id = $passenger['id'] ?? null;
        return self::fromEloquent($filePassengerEloquentModel);
    }


    public static function fromEloquent(FilePassengerEloquentModel $filePassengerEloquentModel): FilePassenger
    {
        return new FilePassenger(
            id: $filePassengerEloquentModel->id,
            fileId: new FileId($filePassengerEloquentModel->file_id),
            sequenceNumber: new SequenceNumber($filePassengerEloquentModel->sequence_number),
            orderNumber: new OrderNumber($filePassengerEloquentModel->order_number),
            frequent: new Frequent($filePassengerEloquentModel->frequent),
            documentTypeId: new DocumentTypeId($filePassengerEloquentModel->document_type_id),
            doctypeIso: new DoctypeIso($filePassengerEloquentModel->doctype_iso),
            documentNumber: new DocumentNumber($filePassengerEloquentModel->document_number),
            name: new Name($filePassengerEloquentModel->name),
            surnames: new Surnames($filePassengerEloquentModel->surnames),
            dateBirth: new DateBirth($filePassengerEloquentModel->date_birth),
            typePassenger: new TypePassenger(strtoupper($filePassengerEloquentModel->type)),
            suggestedRoomType: new SuggestedRoomType($filePassengerEloquentModel->suggested_room_type),
            genre: new Genre($filePassengerEloquentModel->genre),
            email: new Email($filePassengerEloquentModel->email),
            phone: new Phone($filePassengerEloquentModel->phone),
            countryIso: new CountryIso($filePassengerEloquentModel->country_iso),
            cityIso: new CityIso($filePassengerEloquentModel->city_iso),
            dietaryRestrictions: new DietaryRestrictions($filePassengerEloquentModel->dietary_restrictions),
            medicalRestrictions: new MedicalRestrictions($filePassengerEloquentModel->medical_restrictions),
            notes: new Notes($filePassengerEloquentModel->notes),
            accommodation: new Accommodation($filePassengerEloquentModel->accommodation),
            costByPassenger: new CostByPassenger($filePassengerEloquentModel->cost_by_passenger),
            documentUrl: new DocumentUrl($filePassengerEloquentModel->document_url)
        );
    }

    public static function toEloquent(FilePassenger $filePassenger): FilePassengerEloquentModel
    {
        $passengerEloquent = null;

        if($filePassenger->id)
        {
            $passengerEloquent = FilePassengerEloquentModel::query();
            // $passengerEloquent = $passengerEloquent->where('file_id', '=', $filePassenger->id);
            $passengerEloquent = $passengerEloquent->where('id', '=', $filePassenger->id);
            $passengerEloquent = $passengerEloquent->first();
        }

        // if($filePassenger->fileId->value() > 0)
        // {
        //     $passengerEloquent = $passengerEloquent
        //         ->where('file_id', '=', $filePassenger->fileId->value());
        // }

        // if($filePassenger->sequenceNumber->value() > 0)
        // {
        //     $passengerEloquent = $passengerEloquent
        //         ->where('sequence_number', '=', $filePassenger->sequenceNumber->value());
        // }
 

        if(!$passengerEloquent)
        {
            $passengerEloquent = new FilePassengerEloquentModel();
        }

        $passengerEloquent->sequence_number = $filePassenger->sequenceNumber->value();
        $passengerEloquent->order_number = $filePassenger->orderNumber->value();
        $passengerEloquent->frequent = $filePassenger->frequent->value();
        $passengerEloquent->document_type_id = $filePassenger->documentTypeId->value();
        $passengerEloquent->doctype_iso = $filePassenger->doctypeIso->value();
        $passengerEloquent->document_number = $filePassenger->documentNumber->value();
        $passengerEloquent->name = $filePassenger->name->value();
        $passengerEloquent->surnames = $filePassenger->surnames->value();
        $passengerEloquent->date_birth = $filePassenger->dateBirth->value();
        $passengerEloquent->type = $filePassenger->typePassenger->value();
        $passengerEloquent->suggested_room_type = $filePassenger->suggestedRoomType->value();
        $passengerEloquent->genre = $filePassenger->genre->value();
        $passengerEloquent->email = $filePassenger->email->value();
        $passengerEloquent->phone = $filePassenger->phone->value();
        $passengerEloquent->country_iso = $filePassenger->countryIso->value();
        $passengerEloquent->city_iso = $filePassenger->cityIso->value();
        $passengerEloquent->dietary_restrictions = $filePassenger->dietaryRestrictions->value();
        $passengerEloquent->medical_restrictions = $filePassenger->medicalRestrictions->value();
        $passengerEloquent->notes = $filePassenger->notes->value();
        $passengerEloquent->accommodation = $filePassenger->accommodation->value();
        $passengerEloquent->cost_by_passenger = $filePassenger->costByPassenger->value();
        $passengerEloquent->document_url = $filePassenger->documentUrl->value();
        return $passengerEloquent;
    }
}
