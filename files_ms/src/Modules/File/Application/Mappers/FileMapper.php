<?php

namespace Src\Modules\File\Application\Mappers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\ValueObjects\File\Adults;
use Src\Modules\File\Domain\ValueObjects\File\Applicant;
use Src\Modules\File\Domain\ValueObjects\File\BudgetNumber;
use Src\Modules\File\Domain\ValueObjects\File\Children;
use Src\Modules\File\Domain\ValueObjects\File\ClientId;
use Src\Modules\File\Domain\ValueObjects\File\ClientCode;
use Src\Modules\File\Domain\ValueObjects\File\ClientCreditLine;
use Src\Modules\File\Domain\ValueObjects\File\ClientHaveCredit;
use Src\Modules\File\Domain\ValueObjects\File\ClientName;
use Src\Modules\File\Domain\ValueObjects\File\Currency;
use Src\Modules\File\Domain\ValueObjects\File\DateIn;
use Src\Modules\File\Domain\ValueObjects\File\DateOut;
use Src\Modules\File\Domain\ValueObjects\File\Description;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCode;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeProcess;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeSale;
use Src\Modules\File\Domain\ValueObjects\File\FileCategories; 
use Src\Modules\File\Domain\ValueObjects\File\FileCodeAgency;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Modules\File\Domain\ValueObjects\File\FileNumber;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Modules\File\Domain\ValueObjects\File\FileVips;
use Src\Modules\File\Domain\ValueObjects\File\Group;
use Src\Modules\File\Domain\ValueObjects\File\HaveInvoice;
use Src\Modules\File\Domain\ValueObjects\File\HaveQuote;
use Src\Modules\File\Domain\ValueObjects\File\HaveTicket;
use Src\Modules\File\Domain\ValueObjects\File\HaveVoucher;
use Src\Modules\File\Domain\ValueObjects\File\Infants;
use Src\Modules\File\Domain\ValueObjects\File\Lang;
use Src\Modules\File\Domain\ValueObjects\File\MarkupClient;
use Src\Modules\File\Domain\ValueObjects\File\Observation;
use Src\Modules\File\Domain\ValueObjects\File\OrderNumber;
use Src\Modules\File\Domain\ValueObjects\File\Promotion;
use Src\Modules\File\Domain\ValueObjects\File\ReservationId;
use Src\Modules\File\Domain\ValueObjects\File\ReservationNumber;
use Src\Modules\File\Domain\ValueObjects\File\RevisionStages;
use Src\Modules\File\Domain\ValueObjects\File\SaleType;
use Src\Modules\File\Domain\ValueObjects\File\SectorCode;
use Src\Modules\File\Domain\ValueObjects\File\SerieReserveId;
use Src\Modules\File\Domain\ValueObjects\File\Status;
use Src\Modules\File\Domain\ValueObjects\File\Tariff;
use Src\Modules\File\Domain\ValueObjects\File\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\File\TotalPax;
use Src\Modules\File\Domain\ValueObjects\File\UseInvoice;
use Src\Modules\File\Domain\ValueObjects\File\FileItinearyServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\File\GenerateStatement;
use Src\Modules\File\Domain\ValueObjects\File\PassengerChanges;
use Src\Modules\File\Domain\ValueObjects\File\FileReasonStatementId;
use Src\Modules\File\Domain\ValueObjects\File\FileStatusReasons;
use Src\Modules\File\Domain\ValueObjects\File\OpeAssignStages;
use Src\Modules\File\Domain\ValueObjects\File\StatusReason;
use Src\Modules\File\Domain\ValueObjects\File\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationDbl;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationSgl;
use Src\Modules\File\Domain\ValueObjects\File\SuggestedAccommodationTpl;
use Src\Modules\File\Domain\ValueObjects\File\TypeClassId;
use Src\Modules\File\Domain\ValueObjects\File\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\File\Origin;
use Src\Modules\File\Domain\ValueObjects\File\Statement;
use Src\Modules\File\Domain\ValueObjects\File\StatusReasonId;
use Src\Modules\File\Domain\ValueObjects\File\StelaProcessing;
use Src\Modules\File\Domain\ValueObjects\File\StelaProcessingError;
use Src\Modules\File\Domain\ValueObjects\File\ViewProtectedRate;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;

class FileMapper
{
    public static function fromRequestCreateBasic(Request $request, ?int $fileId = null){
        
        $markupClient = 0;         
        $serieReserveId = 0;
        $clientId = (int)$request->client_id;
        $clientCode = (string)$request->client_code;
        $clientName = $request->client_name;
        $clientHaveCredit = isset($request->have_credit) ? $request->have_credit : 0;
        $clientCreditLine = isset($request->credit_line) ? $request->credit_line : 0; 
        $reservationId = 0;
        $fileNumber = 0;
        $orderNumber = null;
        $dateIn = Carbon::parse($request->date_init)->format('Y-m-d');
        $dateOut = Carbon::parse($request->date_init)->format('Y-m-d');
        $adults = (int)$request->input('adults');
        $children = (int)$request->input('children', 0);
        $infants = (int)$request->input('infants', 0);
        $description = $request->description;
        $fileCodeAgency = null;
        $typeClassId = $request->input('type_class_id', null);
        $passengerChanges = 0;
        $totalPax = $adults + $children + $infants;
        $suggestedAccommodationSgl = (int)$request->input('accommodation_sgl', 0);
        $suggestedAccommodationDbl = (int)$request->input('accommodation_dbl', 0);
        $suggestedAccommodationTpl = (int)$request->input('accommodation_tpl', 0);
        $generateStatement = (bool)$request->input('generate_statement', false);
        $protectedRate = 0;
        $viewProtectedRate = false;
        $reasonStatementId = $request->input('reason_statement_id', null);
        $categories = $request->input('categories', null);
        $categories = (isset($categories) && is_array($categories)) ? FileCategoryMapper::fromRequestToArray($categories) : [];

        $passengers = [];
        $sequence_number = 0;
        for($i=0; $i<$adults; $i++){
           $sequence_number++;
           array_push($passengers, self::create_passenger($sequence_number, 'ADL', 'Adult', ($i+1))) ;
        }

        for($i=0; $i<$children; $i++){
            $sequence_number++;
            array_push($passengers, self::create_passenger($sequence_number, 'CHD', 'Child', ($i+1))) ;
        }
 
        if($suggestedAccommodationSgl == 0 && $suggestedAccommodationDbl == 0 &&  $suggestedAccommodationTpl == 0 ){
            throw new \DomainException('Select an accommodation');
        }


        return new File(
            id: new FileId(null),
            serieReserveId: new SerieReserveId($serieReserveId),
            clientId: new ClientId($clientId),
            clientCode: new ClientCode($clientCode),
            clientName: new ClientName($clientName),
            clientHaveCredit: new ClientHaveCredit($clientHaveCredit),
            clientCreditLine: new ClientCreditLine($clientCreditLine),    
            reservationId: new ReservationId($reservationId),
            orderNumber: new OrderNumber($orderNumber),
            fileNumber: new FileNumber($fileNumber),
            reservationNumber: new ReservationNumber(null),
            budgetNumber: new BudgetNumber(null),
            sectorCode: new SectorCode(null),
            group: new Group(null),
            saleType: new SaleType(null),
            tariff: new Tariff(null),
            currency: new Currency('USD'),
            revisionStages: new RevisionStages(null),
            opeAssignStages: new OpeAssignStages(0),             
            executiveId: new ExecutiveId(2857),
            executiveCode: new ExecutiveCode('SNB'),
            executiveCodeSale: new ExecutiveCodeSale('SNB'),
            executiveCodeProcess: new ExecutiveCodeProcess('SNB'),
            applicant: new Applicant(null),
            fileCodeAgency: new FileCodeAgency($fileCodeAgency),
            description: new Description($description),
            lang: new Lang('EN'),
            dateIn: new DateIn($dateIn),
            dateOut: new DateOut($dateOut),
            adults: new Adults($adults),
            children: new Children($children),
            infants: new Infants($infants),
            useInvoice: new UseInvoice(false),
            observation: new Observation(null),
            totalPax: new TotalPax($totalPax),
            haveQuote: new HaveQuote(false),
            haveVoucher: new HaveVoucher(false),
            haveTicket: new HaveTicket(false),
            haveInvoice: new HaveInvoice(false),
            status: new Status('OK'),
            statusReason: new StatusReason(''),
            statusReasonId: new StatusReasonId(false),   
            promotion: new Promotion(false),
            totalAmount: new TotalAmount(0),
            markupClient: new MarkupClient($markupClient),
            typeClassId: new TypeClassId($typeClassId),            
            suggestedAccommodationSgl: new SuggestedAccommodationSgl($suggestedAccommodationSgl), 
            suggestedAccommodationDbl: new SuggestedAccommodationDbl($suggestedAccommodationDbl), 
            suggestedAccommodationTpl: new SuggestedAccommodationTpl($suggestedAccommodationTpl),  
            generateStatement: new GenerateStatement($generateStatement),
            protectedRate: new ProtectedRate($protectedRate), 
            viewProtectedRate: new ViewProtectedRate($viewProtectedRate), 
            fileReasonStatementId: new FileReasonStatementId($reasonStatementId), 
            passengerChanges: new PassengerChanges($passengerChanges),  
            itineraries: new FileItineraries([]), 
            passengers: new FilePassengers(array_map(function ($passenger) {
                return FilePassengerMapper::fromArray($passenger);
            }, $passengers)),            
            vips: new FileVips([]),
            serviceAmountLogs: new FileItinearyServiceAmountLogs([]),
            categories: new FileCategories(array_map(function ($category) {
                return FileCategoryMapper::fromArray($category);
            }, $categories)), 
            fileStatusReasons: new FileStatusReasons([]),
            createdAt: new CreatedAt(''),  
            statement: new Statement(0), 
            origin: new Origin("aurora"),
            stelaProcessing: new StelaProcessing(NULL),
            stelaProcessingError: new StelaProcessingError(NULL),
        );

    }

    public static function fromRequestCreateStela(array $params){
        
        $markupClient = isset($params['markup_client']) ? $params['markup_client'] : 0;         
        $serieReserveId = 0;
        $clientId = (int)$params['client_id'];
        $clientCode = (string)$params['client_code'];
        $clientName = $params['client_name'];
        $clientHaveCredit = isset($params['have_credit']) ? $params['have_credit'] : 0; 
        $clientCreditLine = isset($params['credit_line']) ? $params['credit_line'] : 0; 
        $executiveId = $params['executive_id'];
        $executiveCode = $params['executive_code'];
        $executiveCodeProcess = $params['executive_code_process'];
        $executiveCodeSale = $params['executive_code_sale'];
        $status = $params['status'];
        $reservationId = 0;
        $fileNumber = $params['file_number'];
        $orderNumber = null;
        $dateIn = Carbon::parse($params['date_in'])->format('Y-m-d');
        $dateOut = Carbon::parse($params['date_out'])->format('Y-m-d');
        $adults = (int)$params['adults'];
        $children = (int)$params['children'];
        $infants = (int)$params['infants'];
        $description = $params['description'];
        $fileCodeAgency = null;
        $typeClassId = $params['type_class_id'];
        $passengerChanges = 0;
        $totalPax = $adults + $children + $infants;
        $suggestedAccommodationSgl = $params['suggested_accommodation_sgl'] != null ? $params['suggested_accommodation_sgl'] : 0 ;
        $suggestedAccommodationDbl = $params['suggested_accommodation_dbl'] != null ? $params['suggested_accommodation_dbl'] : 0 ;
        $suggestedAccommodationTpl = $params['suggested_accommodation_tpl'] != null ? $params['suggested_accommodation_tpl'] : 0 ;
        $generateStatement = $params['generate_statement'];
        $protectedRate = 0;
        $viewProtectedRate = false;
        $reasonStatementId = $params['reason_statement_id'];    
        $categories = (isset($params['categories']) && is_array($params['categories'])) ? FileCategoryMapper::fromRequestToArray($params['categories']) : [];
        $origin = $params['origin'];  
        $stelaProcessing = $params['stela_processing'];  
        $passengers = [];
        // $sequence_number = 0;
        // for($i=0; $i<$adults; $i++){
        //    $sequence_number++;
        //    array_push($passengers, self::create_passenger($sequence_number, 'ADL', 'Adult', ($i+1))) ;
        // }

        // for($i=0; $i<$children; $i++){
        //     $sequence_number++;
        //     array_push($passengers, self::create_passenger($sequence_number, 'CHD', 'Child', ($i+1))) ;
        // }
 
        if($suggestedAccommodationSgl == 0 && $suggestedAccommodationDbl == 0 &&  $suggestedAccommodationTpl == 0 ){
            // throw new \DomainException('Select an accommodation');
        }

        return new File(
            id: new FileId(null),
            serieReserveId: new SerieReserveId($serieReserveId),
            clientId: new ClientId($clientId),
            clientCode: new ClientCode($clientCode),
            clientName: new ClientName($clientName),
            clientHaveCredit: new ClientHaveCredit($clientHaveCredit),
            clientCreditLine: new ClientCreditLine($clientCreditLine),
            reservationId: new ReservationId($reservationId),
            orderNumber: new OrderNumber($orderNumber),
            fileNumber: new FileNumber($fileNumber),
            reservationNumber: new ReservationNumber(null),
            budgetNumber: new BudgetNumber(null),
            sectorCode: new SectorCode(null),
            group: new Group(null),
            saleType: new SaleType(null),
            tariff: new Tariff(null),
            currency: new Currency('USD'),
            revisionStages: new RevisionStages(null),
            opeAssignStages: new OpeAssignStages(0),
            executiveId: new ExecutiveId($executiveId),
            executiveCode: new ExecutiveCode($executiveCode),
            executiveCodeSale: new ExecutiveCodeSale($executiveCodeSale),
            executiveCodeProcess: new ExecutiveCodeProcess($executiveCodeProcess),
            applicant: new Applicant(null),
            fileCodeAgency: new FileCodeAgency($fileCodeAgency),
            description: new Description($description),
            lang: new Lang('EN'),
            dateIn: new DateIn($dateIn),
            dateOut: new DateOut($dateOut),
            adults: new Adults($adults),
            children: new Children($children),
            infants: new Infants($infants),
            useInvoice: new UseInvoice(false),
            observation: new Observation(null),
            totalPax: new TotalPax($totalPax),
            haveQuote: new HaveQuote(false),
            haveVoucher: new HaveVoucher(false),
            haveTicket: new HaveTicket(false),
            haveInvoice: new HaveInvoice(false),
            status: new Status($status),
            statusReason: new StatusReason(''),
            statusReasonId: new StatusReasonId(false),   
            promotion: new Promotion(false),
            totalAmount: new TotalAmount(0),
            markupClient: new MarkupClient($markupClient),
            typeClassId: new TypeClassId($typeClassId),            
            suggestedAccommodationSgl: new SuggestedAccommodationSgl($suggestedAccommodationSgl), 
            suggestedAccommodationDbl: new SuggestedAccommodationDbl($suggestedAccommodationDbl), 
            suggestedAccommodationTpl: new SuggestedAccommodationTpl($suggestedAccommodationTpl),  
            generateStatement: new GenerateStatement($generateStatement),
            protectedRate: new ProtectedRate($protectedRate), 
            viewProtectedRate: new ViewProtectedRate($viewProtectedRate), 
            fileReasonStatementId: new FileReasonStatementId($reasonStatementId), 
            passengerChanges: new PassengerChanges($passengerChanges),  
            itineraries: new FileItineraries([]), 
            passengers: new FilePassengers(array_map(function ($passenger) {
                return FilePassengerMapper::fromArray($passenger);
            }, $passengers)),            
            vips: new FileVips([]),
            serviceAmountLogs: new FileItinearyServiceAmountLogs([]),
            categories: new FileCategories(array_map(function ($category) {
                return FileCategoryMapper::fromArray($category);
            }, $categories)), 
            fileStatusReasons: new FileStatusReasons([]),
            createdAt: new CreatedAt(''),  
            statement: new Statement(0), 
            origin: new Origin($origin),
            stelaProcessing: new StelaProcessing($stelaProcessing),
            stelaProcessingError: new StelaProcessingError(NULL),
        );

    }


    public static function create_passenger($sequence_number=1, $type = 'ADL', $name='Adult', $index=1){
        return [
            "id" => null,
            "reservation_id" => NULL,
            "sequence_number" => $sequence_number,
            "order_number" => null,
            "frequent" => null,
            "document_type_id" => null,
            "doctype_iso" => null,
            "document_number" => null,
            "name" => "$name $index",
            "surnames" => "",
            "date_birth" => null,
            "type" => $type,
            "suggested_room_type" => null,
            "genre" => null,
            "email" => null,
            "phone" => null,
            "country_iso" => null,
            "city_iso" => null,
            "dietary_restrictions" => null,
            "medical_restrictions" => null,
            "notes" => null,
            "created_at" => "2024-08-15 04:26:48",
            "updated_at" => "2024-08-15 04:26:48",
            "deleted_at" => null,
            "document_url" => null,
            "document_type" => null
        ];
    }

    public static function fromRequestCreate(Request $request, ?int $fileId = null, ?bool $file_exist = null): File
    {          
        $clientParam = $request->input('client');
        $markupClient = 0;
        if(isset($clientParam['markups']) && count($clientParam['markups'])>0) {
           $markupClient = $clientParam['markups'][0]['hotel'] ?
            $clientParam['markups'][0]['hotel'] : $clientParam['markups'][0]['service'];
        }
        $entity = (string)$request->input('entity', null);
        $object_id = (string)$request->input('object_id', null);

        $serieReserveId = (int)$request->input('serie_id', 0);
        $clientId = (int)$request->input('client_id');
        $clientCode = (string)$request->input('client_code');
        $clientName = $clientParam['name'];
        $clientHaveCredit = isset($clientParam['have_credit']) ? $clientParam['have_credit'] : 0;
        $clientCreditLine = isset($clientParam['credit_line']) ? $clientParam['credit_line'] : 0;

        $reservationId = (int)$request->input('id');
        $fileNumber = (int)$request->input('file_code');
        $orderNumber = (int)$request->input('order_number', null);
        $dateIn = Carbon::parse($request->input('date_in'))->format('Y-m-d');
        $dateOut = Carbon::parse($request->input('date_out'))->format('Y-m-d');
        $adults = (int)$request->input('adults');
        $children = (int)$request->input('children', 0);
        $infants = (int)$request->input('infants', 0);
        $description = $request->input('customer_name', null);
        $fileCodeAgency = $request->input('file_code_agency', null);
        $typeClassId = $request->input('type_class_id', null);
        $executive = $request->input('executive', null);
        $executiveId = '';
        $executiveCode = '';
        $executiveCodeSale = '';
        $executiveCodeProcess = '';
        if($executive){
            $executiveId = $executive['id'];
            $executiveCode = $executive['code'];
            $executiveCodeSale = $executive['code'];
            $executiveCodeProcess = $executive['code'];
        }


        $generateStatement = (bool)$request->input('generate_statement', false);
        $protectedRate = 0;
        $viewProtectedRate = false;
        $fileReasonStatementId = $request->input('file_reason_statement_id', null);
        $categories = null; //$request->input('categories', null);
        // if(is_array($categories) and count($categories)>0){
        //     $categoryAurora = [];
        //     foreach($categories as $category){
        //         array_push($categoryAurora, $category['type_class_id']);
        //     }
        //     $categories = $categoryAurora;
        // }

        $passengerChanges = 0;
        $totalPax = $adults + $children + $infants;

        $hotels = $request->input('reservations_hotel', []);
        $services = $request->input('reservations_service', []);
        $flights = $request->input('reservations_flight', []);
        $passengers = $request->input('reservations_passenger', []);

        $itineraryServices = FileItineraryMapper::fromRequestToArray($services, $hotels, $flights, $passengers, null ,$file_exist);
        $passengers = FilePassengerMapper::fromRequestToArray($passengers);
        $categories = (isset($categories) && is_array($categories)) ? FileCategoryMapper::fromRequestToArray($categories) : [];

        if($entity == 'File' && $object_id !== null){
            $fileId = $object_id;
        }

        $protectedRate = false; 
        foreach($itineraryServices as $itineraries){
            if(in_array($itineraries['entity'], ['service','hotel']) and $itineraries['protected_rate']){
                $protectedRate = true; 
            }
        }

        // hacemos un calculo que actualiza esto despues de guardar la reserva completa.
        $accommodation = [
            'sgl' => 0,
            'dbl' => 0,
            'tpl' => 0,
        ];

        // foreach($itineraryServices as $service){
        //     if($service['entity'] == 'hotel'){
        //         foreach($service['rooms'] as $room){
        //             if($room['occupation'] == 1){
        //                 $accommodation['sgl'] = $accommodation['sgl'] + $room['total_rooms'];
        //             }
        //             if($room['occupation'] == 2){
        //                 $accommodation['dbl'] = $accommodation['dbl'] + $room['total_rooms'];
        //             }
        //             if($room['occupation'] == 3){
        //                 $accommodation['tpl'] = $accommodation['tpl'] + $room['total_rooms'];
        //             }
        //         }
        //         break;
        //     }
        // }

        $suggestedAccommodationSgl = $accommodation['sgl'];
        $suggestedAccommodationDbl = $accommodation['dbl'];
        $suggestedAccommodationTpl = $accommodation['tpl'];
 
        // dd("mas arriba",$itineraryServices);

        return new File(
            id: new FileId($fileId),
            serieReserveId: new SerieReserveId($serieReserveId),
            clientId: new ClientId($clientId),
            clientCode: new ClientCode($clientCode),
            clientName: new ClientName($clientName),
            clientHaveCredit: new ClientHaveCredit($clientHaveCredit),    
            clientCreditLine: new ClientCreditLine($clientCreditLine),                    
            reservationId: new ReservationId($reservationId),
            orderNumber: new OrderNumber($orderNumber),
            fileNumber: new FileNumber($fileNumber),
            reservationNumber: new ReservationNumber(null),
            budgetNumber: new BudgetNumber(null),
            sectorCode: new SectorCode(null),
            group: new Group(null),
            saleType: new SaleType(null),
            tariff: new Tariff(null),
            currency: new Currency('USD'),
            revisionStages: new RevisionStages(null),
            opeAssignStages: new OpeAssignStages(0),
            executiveId: new ExecutiveId($executiveId), 
            executiveCode: new ExecutiveCode($executiveCode), 
            executiveCodeSale: new ExecutiveCodeSale($executiveCodeSale),
            executiveCodeProcess: new ExecutiveCodeProcess($executiveCodeProcess),
            applicant: new Applicant(null),
            fileCodeAgency: new FileCodeAgency($fileCodeAgency),
            description: new Description($description),
            lang: new Lang('EN'),
            dateIn: new DateIn($dateIn),
            dateOut: new DateOut($dateOut),
            adults: new Adults($adults),
            children: new Children($children),
            infants: new Infants($infants),
            useInvoice: new UseInvoice(false),
            observation: new Observation(null),
            totalPax: new TotalPax($totalPax),
            haveQuote: new HaveQuote(false),
            haveVoucher: new HaveVoucher(false),
            haveTicket: new HaveTicket(false),
            haveInvoice: new HaveInvoice(false),
            status: new Status('OK'),
            statusReason: new StatusReason(''),
            statusReasonId: new StatusReasonId(false), 
            promotion: new Promotion(false),
            totalAmount: new TotalAmount(0),
            markupClient: new MarkupClient($markupClient),
            typeClassId: new TypeClassId($typeClassId),   
            suggestedAccommodationSgl: new SuggestedAccommodationSgl($suggestedAccommodationSgl), 
            suggestedAccommodationDbl: new SuggestedAccommodationDbl($suggestedAccommodationDbl), 
            suggestedAccommodationTpl: new SuggestedAccommodationTpl($suggestedAccommodationTpl),  
            generateStatement: new GenerateStatement($generateStatement), 
            protectedRate: new ProtectedRate($protectedRate),  
            viewProtectedRate: new ViewProtectedRate($viewProtectedRate), 
            fileReasonStatementId: new FileReasonStatementId($fileReasonStatementId),          
            passengerChanges: new PassengerChanges($passengerChanges),  
            itineraries: new FileItineraries(array_map(function ($itinerary) {
                return FileItineraryMapper::fromArray($itinerary);
            }, $itineraryServices)),
            passengers: new FilePassengers(array_map(function ($passenger) {
                return FilePassengerMapper::fromArray($passenger);
            }, $passengers)),
            vips: new FileVips([]),
            serviceAmountLogs: new FileItinearyServiceAmountLogs([]),
            categories: new FileCategories(array_map(function ($category) {
                return FileCategoryMapper::fromArray($category);
            }, $categories)),     
            fileStatusReasons: new FileStatusReasons([]),    
            createdAt: new CreatedAt(date('Y-m-d H:i:s')),    
            statement: new Statement(0),   
            origin: new Origin("aurora"),
            stelaProcessing: new StelaProcessing(NULL),
            stelaProcessingError: new StelaProcessingError(NULL),
        );
    }

    public static function fromRequestUpdate($request): array
    {
        $description = $request->__get('description', '');
        $date_in = $request->__get('date_in', '');
        $lang = $request->__get('lang', '');
        $passengers = $request->__get('passengers', []);

        return [
            'description' => $description,
            'date_in' => $date_in,
            'lang' => $lang,
            // 'serie_id' => $serie_id,
            'passengers' => $passengers,
        ];
    }

    public static function fromRequestSearch($request): array
    {
        $page = (int)$request->has('page') ? $request->page : 1;
        $per_page = (int)($request->has('per_page')) ? $request->input('per_page') : 10;
        $filter = (string)$request->has('filter') ? $request->filter : '';
        $date_range = ((string) ($request->has('date_range') && !empty($request->date_range)) ? explode(',',
            $request->date_range) : '');
        $filter_by = (string)($request->has('filter_by') && !empty($request->filter_by)) ? $request->filter_by : 'id';
        $filter_by_type = (string)(
            $request->has('filter_by_type') && !empty($request->filter_by_type)
        ) ? $request->filter_by_type : 'desc';
        $executive_code = (string)($request->has('executive_code')) ? $request->executive_code : '';
        $client_id = (string)($request->has('client_id')) ? $request->client_id : '';
        $client_code = (string)($request->has('client_code')) ? $request->client_code : '';
        $filter_next_days = (string)($request->has('filter_next_days')) ? $request->filter_next_days : '';
        $revision_stages = (string)($request->has('revision_stages')) ? $request->revision_stages : '';
        $complete = (string)($request->has('complete')) ? $request->complete : null;

        return [
            'page' => $page,
            'per_page' => $per_page,
            'filter' => $filter,
            'date_range' => $date_range,
            'filter_by' => $filter_by,
            'filter_by_type' => $filter_by_type,
            'executive_code' => $executive_code,
            'client_id' => $client_id,
            'client_code' => $client_code,
            'filter_next_days' => $filter_next_days,
            'revision_stages' => $revision_stages,
            'complete' => $complete
        ];
    }

    public static function fromRequestSerie($request): array
    {
        $serie_id = (int)($request->has('serie_id')) ? $request->__get('serie_id') : null;
        $file_id = $request->__get('file_id');

        return [
            'serie_id' => $serie_id,
            'file_id' => $file_id
        ];
    }

    public static function fromRequestStatus($request): array
    {
        $lang_iso = (!empty(@$request->__get('lang_iso'))) ? $request->input('lang_iso') : 'en';

        return [
            'lang_iso' => (string) $lang_iso
        ];
    }

    public static function fromEloquent(
        FileEloquentModel $fileEloquentModel,
        bool $withItinerary = true,
        bool $withPassengers = true,
        bool $withVips = true,
        bool $withCategories = true,
        bool $withFileStatusReasons = true
    ): File {
        $itineraries = $withItinerary ? array_map(function ($itineraries) {
            return FileItineraryMapper::fromArray($itineraries);
        }, $fileEloquentModel->itineraries?->toArray() ?? []) : [];
        
        $itinerary_amount_logs = [];
        foreach($fileEloquentModel->itineraries as $fileItineraries ) {
            if(count($fileItineraries->service_amount_logs) > 0) {
                foreach($fileItineraries->service_amount_logs as $amount_log ) {
                    array_push($itinerary_amount_logs,
                        (FileItineraryServiceAmountLogMapper::fromArray($amount_log->toArray()))
                    );
                }
            }

            if(count($fileItineraries->room_amount_logs)>0) {
                foreach($fileItineraries->room_amount_logs as $amount_log ) {
                    array_push($itinerary_amount_logs,
                        (FileItineraryRoomAmountLogMapper::fromArray($amount_log->toArray()))
                    );
                }
            }
        }
  
        $passengers = $withPassengers ? array_map(function ($passengers) {
            return FilePassengerMapper::fromArray($passengers);
        }, $fileEloquentModel->passengers?->toArray() ?? []) : [];

        $vips = $withVips ? array_map(function ($vips) {
            return FileVipMapper::fromArray($vips);
        }, $fileEloquentModel->vips?->toArray() ?? []) : [];

        $categories = $withCategories ? array_map(function ($category) {
            return FileCategoryMapper::fromArray($category);
        }, $fileEloquentModel->categories?->toArray() ?? []) : [];


        $fileStatusReason = $withFileStatusReasons ? array_map(function ($fileStatusReason) {
            return FileStatusReasonMapper::fromArray($fileStatusReason);
        }, $fileEloquentModel->fileStatusReason?->toArray() ?? []) : [];


        $statement = isset($fileEloquentModel->statement) ? $fileEloquentModel->statement->total : 0;

    //    dd($fileEloquentModel->toArray());
        return new File(
            id: new FileId($fileEloquentModel->id),
            serieReserveId: new SerieReserveId($fileEloquentModel->serie_reserve_id),
            clientId: new ClientId($fileEloquentModel->client_id),
            clientCode: new ClientCode($fileEloquentModel->client_code),
            clientName: new ClientName($fileEloquentModel->client_name),
            clientHaveCredit: new ClientHaveCredit($fileEloquentModel->client_have_credit ? $fileEloquentModel->client_have_credit : 0),
            clientCreditLine: new ClientCreditLine($fileEloquentModel->client_credit_line ? $fileEloquentModel->client_credit_line : 0),           
            reservationId: new ReservationId($fileEloquentModel->reservation_id),
            orderNumber: new OrderNumber($fileEloquentModel->order_number),
            fileNumber: new FileNumber($fileEloquentModel->file_number),
            reservationNumber: new ReservationNumber($fileEloquentModel->reservation_number),
            budgetNumber: new BudgetNumber($fileEloquentModel->budget_number),
            sectorCode: new SectorCode($fileEloquentModel->sector_code),
            group: new Group($fileEloquentModel->group),
            saleType: new SaleType($fileEloquentModel->sale_type),
            tariff: new Tariff($fileEloquentModel->tariff),
            currency: new Currency($fileEloquentModel->currency),
            revisionStages: new RevisionStages($fileEloquentModel->revision_stages),
            opeAssignStages: new OpeAssignStages($fileEloquentModel->ope_assign_stages),
            executiveId: new ExecutiveId($fileEloquentModel->executive_id ? $fileEloquentModel->executive_id : 0), 
            executiveCode: new ExecutiveCode($fileEloquentModel->executive_code),
            executiveCodeSale: new ExecutiveCodeSale($fileEloquentModel->executive_code_sale),
            executiveCodeProcess: new ExecutiveCodeProcess($fileEloquentModel->executive_code_process),
            applicant: new Applicant($fileEloquentModel->applicant),
            fileCodeAgency: new FileCodeAgency($fileEloquentModel->file_code_agency),
            description: new Description($fileEloquentModel->description),
            lang: new Lang($fileEloquentModel->lang),
            dateIn: new DateIn($fileEloquentModel->date_in),
            dateOut: new DateOut($fileEloquentModel->date_out),
            adults: new Adults($fileEloquentModel->adults),
            children: new Children($fileEloquentModel->children),
            infants: new Infants($fileEloquentModel->infants),
            useInvoice: new UseInvoice($fileEloquentModel->use_invoice),
            observation: new Observation($fileEloquentModel->observation),
            totalPax: new TotalPax($fileEloquentModel->total_pax),
            haveQuote: new HaveQuote($fileEloquentModel->have_quote),
            haveVoucher: new HaveVoucher($fileEloquentModel->have_voucher),
            haveTicket: new HaveTicket($fileEloquentModel->have_ticket),
            haveInvoice: new HaveInvoice($fileEloquentModel->have_invoice),
            status: new Status($fileEloquentModel->status),
            statusReason: new StatusReason($fileEloquentModel->statusreason),            
            statusReasonId: new StatusReasonId($fileEloquentModel->statusreasonid),   
            promotion: new Promotion($fileEloquentModel->promotion),
            totalAmount: new TotalAmount($fileEloquentModel->total_amount),
            markupClient: new MarkupClient($fileEloquentModel->markup_client),
            typeClassId: new TypeClassId($fileEloquentModel->type_class_id),
            suggestedAccommodationSgl: new SuggestedAccommodationSgl($fileEloquentModel->suggested_accommodation_sgl), 
            suggestedAccommodationDbl: new SuggestedAccommodationDbl($fileEloquentModel->suggested_accommodation_dbl), 
            suggestedAccommodationTpl: new SuggestedAccommodationTpl($fileEloquentModel->suggested_accommodation_tpl),  
            generateStatement: new GenerateStatement($fileEloquentModel->generate_statement), 
            protectedRate: new ProtectedRate($fileEloquentModel->protected_rate), 
            viewProtectedRate: new ViewProtectedRate($fileEloquentModel->view_protected_rate), 
            fileReasonStatementId: new FileReasonStatementId($fileEloquentModel->reason_statement_id),            
            passengerChanges: new PassengerChanges($fileEloquentModel->passenger_changes),
            itineraries: new FileItineraries($itineraries),
            passengers: new FilePassengers($passengers),
            vips: new FileVips($vips),
            serviceAmountLogs: new FileItinearyServiceAmountLogs($itinerary_amount_logs),
            categories: new FileCategories($categories),
            fileStatusReasons: new FileStatusReasons($fileStatusReason),    
            createdAt: new CreatedAt($fileEloquentModel->created_at), 
            statement: new Statement($statement),
            origin: new Origin($fileEloquentModel->origin),
            stelaProcessing: new StelaProcessing($fileEloquentModel->stela_processing),     
            stelaProcessingError: new StelaProcessingError($fileEloquentModel->stela_processing_error),  
        );
    }

    public static function toEloquent(File $file): FileEloquentModel
    {
        if (($file->id and $file->id->value() !== null) || $file->fileNumber->value()) {

            if($file->id->value()) {
                $fileEloquent = FileEloquentModel::where('id', '=', $file->id->value())->first();
            } else {
                if($file->fileNumber->value()) {
                    $fileEloquent = FileEloquentModel::where('file_number', '=', $file->fileNumber->value())->first();
                }
            }

            if($fileEloquent) {
               return $fileEloquent;
            }
            else
            {
                $fileEloquent = new FileEloquentModel();
            }
        }
        else
        {
            $fileEloquent = new FileEloquentModel();
        }
 
        $fileEloquent->serie_reserve_id = $file->serieReserveId->value();     
        $fileEloquent->client_id = $file->clientId->value();
        $fileEloquent->client_code = $file->clientCode->value();
        $fileEloquent->client_name = $file->clientName->value();
        $fileEloquent->client_have_credit = $file->clientHaveCredit->value();
        $fileEloquent->client_credit_line = $file->clientCreditLine->value();         
        $fileEloquent->reservation_id = $file->reservationId->value();
        $fileEloquent->order_number = $file->orderNumber->value();
        $fileEloquent->file_number = $file->fileNumber->value();
        $fileEloquent->reservation_number = $file->reservationNumber->value();
        $fileEloquent->budget_number = $file->budgetNumber->value();
        $fileEloquent->sector_code = $file->sectorCode->value();
        $fileEloquent->group = $file->group->value();
        $fileEloquent->sale_type = $file->saleType->value();
        $fileEloquent->tariff = $file->tariff->value();
        $fileEloquent->currency = $file->currency->value();
        $fileEloquent->revision_stages = $file->revisionStages->value();
        $fileEloquent->ope_assign_stages = $file->opeAssignStages->value();        
        $fileEloquent->executive_id = $file->executiveId->value();
        $fileEloquent->executive_code = $file->executiveCode->value();
        $fileEloquent->executive_code_sale = $file->executiveCodeSale->value();
        $fileEloquent->executive_code_process = $file->executiveCodeProcess->value();
        $fileEloquent->applicant = $file->applicant->value();
        $fileEloquent->file_code_agency = $file->fileCodeAgency->value();
        $fileEloquent->description = $file->description->value();
        $fileEloquent->lang = $file->lang->value();
        $fileEloquent->date_in = $file->dateIn->value();
        $fileEloquent->date_out = $file->dateOut->value();
        $fileEloquent->adults = $file->adults->value();
        $fileEloquent->children = $file->children->value();
        $fileEloquent->infants = $file->infants->value();
        $fileEloquent->use_invoice = $file->useInvoice->value();
        $fileEloquent->observation = $file->observation->value();
        $fileEloquent->total_pax = $file->totalPax->value();
        $fileEloquent->have_quote = $file->haveQuote->value();
        $fileEloquent->have_voucher = $file->haveVoucher->value();
        $fileEloquent->have_ticket = $file->haveTicket->value();
        $fileEloquent->have_invoice = $file->haveInvoice->value();
        $fileEloquent->status = $file->status->value();
        $fileEloquent->promotion = $file->promotion->value();
        $fileEloquent->total_amount = $file->totalAmount->value();
        $fileEloquent->markup_client = $file->markupClient->value();
        $fileEloquent->type_class_id = $file->typeClassId->value();
        $fileEloquent->suggested_accommodation_sgl = $file->suggestedAccommodationSgl->value();
        $fileEloquent->suggested_accommodation_dbl = $file->suggestedAccommodationDbl->value();
        $fileEloquent->suggested_accommodation_tpl = $file->suggestedAccommodationTpl->value(); 
        $fileEloquent->generate_statement = $file->generateStatement->value();
        $fileEloquent->file_reason_statement_id = $file->fileReasonStatementId->value();   
        $fileEloquent->protected_rate = $file->protectedRate->value();
        $fileEloquent->view_protected_rate = $file->viewProtectedRate->value();         
        $fileEloquent->created_at = $file->createdAt->value(); 
        $fileEloquent->origin = $file->origin->value(); 
        $fileEloquent->stela_processing = $file->stelaProcessing->value(); 
        $fileEloquent->stela_processing_error = $file->stelaProcessingError->value(); 
        return $fileEloquent;
    }

    public static function toArray(File $file): array
    {
        $fileEloquent = new FileEloquentModel();
        $fileEloquent->id = $file->id->value();
        $fileEloquent->serie_reserve_id = $file->serieReserveId->value();
        $fileEloquent->client_id = $file->clientId->value();
        $fileEloquent->client_code = $file->clientCode->value();
        $fileEloquent->client_name = $file->clientName->value();
        $fileEloquent->client_have_credit = $file->clientHaveCredit->value();  
        $fileEloquent->client_credit_line = $file->clientCreditLine->value();                     
        $fileEloquent->reservation_id = $file->reservationId->value();
        $fileEloquent->order_number = $file->orderNumber->value();
        $fileEloquent->file_number = $file->fileNumber->value();
        $fileEloquent->reservation_number = $file->reservationNumber->value();
        $fileEloquent->budget_number = $file->budgetNumber->value();
        $fileEloquent->sector_code = $file->sectorCode->value();
        $fileEloquent->group = $file->group->value();
        $fileEloquent->sale_type = $file->saleType->value();
        $fileEloquent->tariff = $file->tariff->value();
        $fileEloquent->currency = $file->currency->value();
        $fileEloquent->revision_stages = $file->revisionStages->value();
        $fileEloquent->ope_assign_stages = $file->opeAssignStages->value();        
        $fileEloquent->executive_id = $file->executiveId->value();
        $fileEloquent->executive_code = $file->executiveCode->value();
        $fileEloquent->executive_code_sale = $file->executiveCodeSale->value();
        $fileEloquent->executive_code_process = $file->executiveCodeProcess->value();
        $fileEloquent->applicant = $file->applicant->value();
        $fileEloquent->file_code_agency = $file->fileCodeAgency->value();
        $fileEloquent->description = $file->description->value();
        $fileEloquent->lang = $file->lang->value();
        $fileEloquent->date_in = $file->dateIn->value();
        $fileEloquent->date_out = $file->dateOut->value();
        $fileEloquent->adults = $file->adults->value();
        $fileEloquent->children = $file->children->value();
        $fileEloquent->infants = $file->infants->value();
        $fileEloquent->use_invoice = $file->useInvoice->value();
        $fileEloquent->observation = $file->observation->value();
        $fileEloquent->total_pax = $file->totalPax->value();
        $fileEloquent->have_quote = $file->haveQuote->value();
        $fileEloquent->have_voucher = $file->haveVoucher->value();
        $fileEloquent->have_ticket = $file->haveTicket->value();
        $fileEloquent->have_invoice = $file->haveInvoice->value();
        $fileEloquent->status = $file->status->value();
        $fileEloquent->promotion = $file->promotion->value();
        $fileEloquent->total_amount = $file->totalAmount->value();
        $fileEloquent->markup_client = $file->markupClient->value();
        $fileEloquent->type_class_id = $file->typeClassId->value();
        $fileEloquent->suggested_accommodation_sgl = $file->suggestedAccommodationSgl->value();
        $fileEloquent->suggested_accommodation_dbl = $file->suggestedAccommodationDbl->value();
        $fileEloquent->suggested_accommodation_tpl = $file->suggestedAccommodationTpl->value(); 
        $fileEloquent->generate_statement = $file->generateStatement->value();
        $fileEloquent->protected_rate = $file->protectedRate->value();
        $fileEloquent->view_protected_rate = $file->viewProtectedRate->value();         
        $fileEloquent->file_reason_statement_id = $file->fileReasonStatementId->value();        
        $fileEloquent->itineraries = collect([]);
        $fileEloquent->vips = collect([]);
        $fileEloquent->passengers = collect([]);
        $fileEloquent->created_at = $file->createdAt->value(); 
        $fileEloquent->origin = $file->origin->value();
        $fileEloquent->stela_processing = $file->stelaProcessing->value();
        $fileEloquent->stela_processing_error = $file->stelaProcessingError->value(); 
        
        if(isset($file->vips))
        {
            foreach($file->vips as $file_vip)
            {
                $fileEloquent->vips->add($file_vip);
            }
        }

        if(isset($file->itineraries))
        {
            foreach($file->itineraries as $itinerary)
            {
                $fileEloquent->itineraries->add($itinerary);
            }
        }

        if(isset($file->passengers))
        {
            foreach($file->passengers as $passenger)
            {
                $fileEloquent->passengers->add($passenger);
            }
        }

        return $fileEloquent->toArray();
    }

    public static function fromArray(array $file): File
    {
        $fileEloquentModel = new FileEloquentModel($file);
        $fileEloquentModel->id = $file['id'] ?? null;
        return self::fromEloquent($fileEloquentModel);
    }
}
