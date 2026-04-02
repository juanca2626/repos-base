<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Arr;
use Src\Modules\File\Application\Mappers\FileItineraryMapper;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface; 
use Src\Modules\File\Domain\ValueObjects\FileItinerary\StartTime;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileHotelRoomEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryRoomAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryServiceAmountLogEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel; 
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileItineraryAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileItineraryDetailMapper;
use Src\Modules\File\Application\Mappers\FileServiceAccommodationMapper;
use Src\Modules\File\Application\Mappers\FileServiceAmountLogMapper;
use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Application\Mappers\FileServiceMapper;
use Src\Modules\File\Application\Mappers\FileServiceUnitMapper;
use Src\Modules\File\Application\Mappers\FileTemporaryMasterServiceMapper;
use Src\Modules\File\Application\Mappers\FileTemporaryServiceCompositionMapper;
use Src\Modules\File\Application\Mappers\FileTemporaryServiceDetailMapper;
use Src\Modules\File\Application\Mappers\FileTemporaryServiceMapper;
use Src\Modules\File\Application\Mappers\FileTemporaryServiceUnitMapper;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Domain\Model\FileItineraryAccommodation;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\Model\FileTemporaryMasterService;
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\Repositories\FileTemporaryServiceRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Modules\File\Domain\ValueObjects\File\FileTemporaryServices;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileClassificationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileRoomAccommodationEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryMasterServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceUnitEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\TypeComponentServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\TypeCompositionEloquentModel;

use function PHPSTORM_META\map;

class FileTemporaryServiceRepository implements FileTemporaryServiceRepositoryInterface
{    
    public function create(FileTemporaryService $fileTemporaryService): FileTemporaryService
    {   
        return DB::transaction(function () use ($fileTemporaryService) {

            $fileTemporaryServiceEloquent = $this->saveFileTemporaryService($fileTemporaryService);                    
            foreach($fileTemporaryService->services as $fileService){
                $fileService->fileTemporaryServiceId->setValue($fileTemporaryServiceEloquent->id);
                $fileServiceEloquent = $this->saveFileService($fileService);  
                $this->saveFileServiceCompositions($fileServiceEloquent, (array) $fileService->getCompositions()); 
            }
            $this->saveDetails($fileTemporaryServiceEloquent, (array) $fileTemporaryService->getFileItineraryDetails());

            return FileTemporaryServiceMapper::fromEloquent($fileTemporaryServiceEloquent);
        });
    }

    protected function saveFileTemporaryService(FileTemporaryService $fileTemporaryService): FileTemporaryServiceEloquentModel
    {
        $fileItineraryEloquent = FileTemporaryServiceMapper::toEloquent($fileTemporaryService);
        $fileItineraryEloquent->save();
        return $fileItineraryEloquent;
    } 

    protected function saveDetails(FileTemporaryServiceEloquentModel $fileTemporaryServiceEloquent, array $detailsData): void
    {
        foreach($detailsData as $detail){
            $fileTemporaryServiceMapper = FileTemporaryServiceDetailMapper::toEloquent($detail);   
                
            $fileTemporaryServiceEloquent->details()->save($fileTemporaryServiceMapper);
           
        }
    }
    

    protected function saveFileService(FileTemporaryMasterService $fileService): FileTemporaryMasterServiceEloquentModel
    {
        $fileEloquent = FileTemporaryMasterServiceMapper::toEloquent($fileService);
        $fileEloquent->save();

        return $fileEloquent;
    }

 

    protected function saveFileServiceCompositions(FileTemporaryMasterServiceEloquentModel $fileServiceEloquent, array $compositions): void
    {     
        $classification = FileClassificationEloquentModel::where('iso', 'S')->first(['id']);
        $typeComposition = TypeCompositionEloquentModel::where('code', '3')->first(['id']);
        $typeComponentService = TypeComponentServiceEloquentModel::where('code', 'SVS')->first(['id']);
        
        foreach ($compositions as $composition) { 
            // dd($composition->units->units);
            $composition->fileClassificationId->setValue($classification->id);
            $composition->typeCompositionId->setValue($typeComposition->id);
            $composition->typeComponentServiceId->setValue($typeComponentService->id);

            $fileServiceCompositionEloquent = FileTemporaryServiceCompositionMapper::toEloquent($composition);
            $fileServiceEloquent->compositions()->save($fileServiceCompositionEloquent);           
            $this->saveFileServiceUnits($fileServiceCompositionEloquent, $composition->units);             
        }
    }

    protected function saveFileServiceUnits(FileTemporaryServiceCompositionEloquentModel $fileServiceCompositionEloquent, $units): void
    { 
 
        foreach ($units as $unit) {  
            $fileServiceUnitEloquent = FileTemporaryServiceUnitMapper::toEloquent($unit);
            $fileServiceCompositionEloquent->units()->save($fileServiceUnitEloquent);                  
        }
 
    }    
 
    public function searchItineraryQueryServiceTemporary(array $params): FileTemporaryServices
    {

     
        $fileTemporaryService = FileTemporaryServiceEloquentModel::query()->with([      
            'services.compositions'=> function ($query) {
                $query->with('units');
                $query->with('supplier'); 
            },
            'details'
        ])->whereIn('object_code', $params['codes'])->get(); 


        $services = $fileTemporaryService ? array_map(function ($itineraries) {
            return FileTemporaryServiceMapper::fromArray($itineraries);
        }, $fileTemporaryService?->toArray() ?? []) : [];

        return  new FileTemporaryServices($services);

        return [];
    }
  
   
    public function findById(int $id): FileTemporaryService
    {
   
        $fileTemporaryService = FileTemporaryServiceEloquentModel::query()->with([      
            'services.compositions'=> function ($query) {
                $query->with('units');
                $query->with('supplier'); 
            },
            'details'
        ])->findOrFail($id); 

        return FileTemporaryServiceMapper::fromArray($fileTemporaryService->toArray());
    }
      
}
