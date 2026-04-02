<?php

namespace Src\Modules\File\Domain\Model;


use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Genre;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Phone;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\TypePassenger;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CityIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CountryIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DateBirth;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DietaryRestrictions;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Email;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\MedicalRestrictions;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Name;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DoctypeIso;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentTypeId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Frequent;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Notes;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\OrderNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\SequenceNumber;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\SuggestedRoomType;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Surnames;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\Accommodation;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\CostByPassenger;
use Src\Modules\File\Domain\ValueObjects\FileHotelRoom\TypeRoomDescription;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\DocumentUrl;
use Src\Shared\Domain\Entity;
use Src\Modules\File\Presentation\Http\Traits\PhoneNumberFormat;
use Carbon\Carbon;

class FilePassenger extends Entity
{
    use PhoneNumberFormat;

    public $phoneCode='';
    public $phoneNumber='';
    
    public function __construct(
        public readonly ?int $id,
        public readonly ?FileId $fileId,
        public readonly SequenceNumber $sequenceNumber,
        public readonly OrderNumber $orderNumber,
        public readonly Frequent $frequent,
        public readonly DocumentTypeId $documentTypeId,
        public readonly DoctypeIso $doctypeIso,
        public readonly DocumentNumber $documentNumber,
        public readonly Name $name,
        public readonly Surnames $surnames,
        public readonly DateBirth $dateBirth,
        public readonly TypePassenger $typePassenger,
        public readonly SuggestedRoomType $suggestedRoomType,
        public readonly Genre $genre,
        public readonly Email $email,
        public readonly Phone $phone,
        public readonly CountryIso $countryIso,
        public readonly CityIso $cityIso,
        public readonly DietaryRestrictions $dietaryRestrictions,
        public readonly MedicalRestrictions $medicalRestrictions,
        public readonly Notes $notes,
        public readonly Accommodation $accommodation,
        public readonly CostByPassenger $costByPassenger, 
        public readonly DocumentUrl $documentUrl

        
    ) {

        $phone_parse = $this->phoneNumberFormat($phone->value(), $countryIso->value());
        $this->phoneCode = $phone_parse['phone_code'];
        
        if($this->phoneCode)
        {
            $this->phoneNumber = $phone_parse['phone_number'];
        }
    }
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->fileId->value(),
            'sequence_number' => $this->sequenceNumber->value(),
            'order_number' => $this->orderNumber->value(),
            'frequent' => $this->frequent->value(),
            'document_type_id' => $this->documentTypeId->value(),
            'doctype_iso' => $this->doctypeIso->value(),
            'document_number' => $this->documentNumber->value(),
            'name' => $this->name->value(),
            'surnames' => $this->surnames->value(),
            'date_birth' => $this->dateBirth->value(),
            'type' => strtoupper($this->typePassenger->value()),
            'suggested_room_type' => $this->suggestedRoomType->value(), 
            'genre' => $this->genre->value(),
            'email' => $this->email->value(),
            'phone' => $this->phone->value(),
            'country_iso' => $this->countryIso->value(),
            'city_iso' => $this->cityIso->value(),
            'dietary_restrictions' => $this->dietaryRestrictions->value(),
            'medical_restrictions' => $this->medicalRestrictions->value(),
            'notes' => $this->notes->value(),
            'accommodation' => $this->accommodation->value(),
            'cost_by_passenger' => $this->costByPassenger->value(),
            'document_url' => $this->documentUrl->value()
        ];
    }

    public function getRoomTypeDescription(): string
    {        
        return (new TypeRoomDescription($this->suggestedRoomType->value()))->toString();
    }

    public function getPhoneNumber(): string
    {        
        return $this->phoneNumber;
    }

    public function getPhoneCode(): string
    {        
        return $this->phoneCode;
    }

    public function getDateBirth(): string
    {      
        if($this->dateBirth->value()){  
            return Carbon::parse($this->dateBirth->value())->format('d/m/Y');
        }

        return '';
    }
 
    public function getAccommodation(): array
    {        
        return $this->accommodation->value() ? json_decode($this->accommodation->value()) : [];
    }

}
