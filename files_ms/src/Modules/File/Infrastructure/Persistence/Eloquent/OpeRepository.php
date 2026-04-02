<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileVipMapper;
use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\OpeRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeLogsModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassToOpeModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceCompositionEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileServiceEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\passToOpeEloquentModel;
use Src\Modules\File\Presentation\Http\Resources\FileVipResource;

class OpeRepository implements OpeRepositoryInterface
{


    public function searchFilesPassToOpe(array $params): bool{

        DB::transaction(function () use ($params) {

            $zone = $params['headquarter'];
            $file = $params['file'];

            $file = FileEloquentModel::query()->with([
                'itineraries'=> function ($query) {
                    $query->with('rooms.units.accommodations');
                    $query->with('rooms.units.nights');
                    $query->where('sent_to_ope', 1);
                },
                'itineraries.services.compositions'=> function ($query) {
                    $query->with('units.accommodations');
                    $query->with('supplier');
                },
                'itineraries.flights.accommodations.filePassenger',
                'itineraries.flights.accommodations',
                'passengers'

            ])->where('status', 'OK')->where('file_number', $file)->first();
            if($file){

                $dataServiceByZones = [];
                foreach($file->itineraries as $fileItinerary){
                    if($fileItinerary['city_in_iso'] == $zone){
                        array_push($dataServiceByZones, $fileItinerary);
                    }
                }
                if(count($dataServiceByZones)>0){
                    foreach($dataServiceByZones as $itineraries){

                        if($itineraries->entity == 'service'){
                            foreach($itineraries->services as $service){

                                foreach($service->compositions as $composition){
                                    if($composition->sent_to_ope == 1){
                                        $fileServiceComposition = FileServiceCompositionEloquentModel::find($composition['id']);
                                        $fileServiceComposition->is_in_ope=true;
                                        $fileServiceComposition->sent_to_ope=false;
                                        $fileServiceComposition->save();
                                    }
                                }

                                if($service->sent_to_ope == 1){
                                    $fileService = FileServiceEloquentModel::find($service->id);
                                    $fileService->is_in_ope=true;
                                    $fileService->sent_to_ope=false;
                                    $fileService->save();
                                }


                            }
                        }

                        $fileItinerary = FileItineraryEloquentModel::find($itineraries->id);
                        $fileItinerary->is_in_ope=true;
                        $fileItinerary->sent_to_ope=false;
                        $fileItinerary->save();
                    }

                    $log = new FilePassToOpeLogsModel();
                    $log->file_id = $file->id;
                    $log->type = 'is_in_ope';
                    $log->sede = $zone;
                    $log->processed_services = json_encode([
                        'sede' => $zone,
                        'servicesZones' => $dataServiceByZones
                    ]);
                    $log->save();

                }
            }
        });

        return true;
    }


    public function searchHistoryPassToOpe(array $filters): LengthAwarePaginator
    {
        $passToOpeEloquent = FilePassToOpeModel::with('file')->select('id', 'file_id', 'sede', 'created_at');

        $date_from = $filters['date_from'];
        $date_to = $filters['date_to'];

        if(!empty($date_from) and !empty($date_to))
        {
    
            $passToOpeEloquent = $passToOpeEloquent->whereBetween(DB::raw('DATE(created_at)') , [$date_from, $date_to]);
        }else{
            // $passToOpeEloquent = $passToOpeEloquent->where(DB::raw('DATE(created_at)'), date('Y-m-d'));
        }

        if(!empty($filters['file_number']))
        {          
            $passToOpeEloquent = $passToOpeEloquent->whereHas('file', function ($query) use($filters){
                $query->where('file_number', $filters['file_number']); 
            });
        }
        
        if(!empty($filters['sede']))
        {          
            $passToOpeEloquent = $passToOpeEloquent->where('sede', $filters['sede']);
        }
     
        $passToOpeEloquent = $passToOpeEloquent->orderBy('created_at', 'desc');

        $perPage = isset($filters['per_page']) ? $filters['per_page'] : 500; 
        $page = isset($filters['page']) ? $filters['page'] : 1; 
        $count = $passToOpeEloquent->count();

        $file_to_ope_hirtory = [];
        foreach ($passToOpeEloquent->paginate($perPage, ['*'], 'page', $page) as $fileOpe)
        {         
            $file_to_ope_hirtory[] = $fileOpe;
        }

        return new LengthAwarePaginator(
            $file_to_ope_hirtory,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
