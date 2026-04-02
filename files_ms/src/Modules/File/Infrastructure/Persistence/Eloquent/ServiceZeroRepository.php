<?php
namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Model\ServiceZero;
use Src\Modules\File\Domain\Model\OperationServiceZero;
use Src\Modules\File\Domain\Repositories\ServiceZeroRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\ServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\OperationServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\DetailServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\RatesServiceZeroEloquentModel;
use Illuminate\Support\Facades\DB;

class ServiceZeroRepository implements ServiceZeroRepositoryInterface
{
    public function save($service): mixed
    {
       
        return DB::transaction(function () use ($service) {
            $serviceZeroData = [
                'time' => $service->time,
                'type' => $service->type,
                'privacy' => $service->privacy,
                'name' => $service->name,
                'status' => 'Pending',
                'skeleton' => $service->skeleton ?? '',
                'itinerary' => $service->itinerary ?? '',
                'supplier_code' => $service->supplier_code ?? '',
                'supplier' => $service->supplier ?? '',
                'created_at' => now(), 
                'updated_at' => now(),
            ];

            $serviceEloquent = ServiceZeroEloquentModel::create($serviceZeroData);
            //dd($serviceEloquent);
            try {
               $operations= $this->saveOperations($serviceEloquent->id, $service);
               $details= $this->saveDetails($serviceEloquent->id, $service);
               $rates= $this->saveRates($serviceEloquent->id, $service);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }


             $serviceData = [
            "time" => $serviceEloquent->time,
            "type" => $serviceEloquent->type,
            "privacy" => $serviceEloquent->privacy,
            "name" => $serviceEloquent->name,
            "skeleton" => $serviceEloquent->skeleton,
            "itinerary" => $serviceEloquent->itinerary,
            "supplier_code" => $serviceEloquent->supplier_code,
            "supplier" => $serviceEloquent->supplier,
            "operation_service" => $this->getOperationServiceData($serviceEloquent->id),
            "detail_service" => $this->getDetailServiceData($serviceEloquent->id),
            "rates" => $this->getRatesData($serviceEloquent->id),
        ];

        return $serviceData;
            //return $serviceEloquent->toArray();
        });
    }

    private function saveOperations($fileServiceZeroId, $service)
    {
        foreach ($service->operation_service ?? [] as $operation) {
            $operationServiceData = [
                'city_in' => $operation['city_in'],
                'city_out' => $operation['city_out'],
                'start_date' => \DateTime::createFromFormat('d-m-Y', $operation['start_date'])->format('Y-m-d'),
                'end_date' => \DateTime::createFromFormat('d-m-Y', $operation['end_date'])->format('Y-m-d'),
                'start_validity' => \DateTime::createFromFormat('d-m-Y', $operation['start_validity'])->format('Y-m-d'),
                'end_validity' => \DateTime::createFromFormat('d-m-Y', $operation['end_validity'])->format('Y-m-d'),
                'days_operation' => json_encode($operation['days_operation'] ?? []),
                'operating_hours' => json_encode($operation['operating_hours'] ?? []),
                'code_country' => $operation['code_country'],
                'country' => $operation['country'],
                'file_service_zero_id' => $fileServiceZeroId,
            ];
             $operation = OperationServiceZeroEloquentModel::create($operationServiceData);
        $operations[] = $operation->toArray();
        return $operations;
            //OperationServiceZeroEloquentModel::create($operationServiceData);
        }
    }

    private function saveDetails($fileServiceZeroId, $service)
    {
        $details = [];
        foreach ($service->detail_service ?? [] as $detail) {
            $detailServiceData = [
                'days_before_cancellation' => $detail['days_before_cancellation'],
                'penalty_amount' => $detail['penalty_amount'],
                'min_passengers' => $detail['min_passengers'],
                'max_passengers' => $detail['max_passengers'],
                'min_age' => $detail['min_age'],
                'max_age' => $detail['max_age'],
                'child_age_min' => $detail['child_age_min'],
                'child_age_max' => $detail['child_age_max'],
                'infant_age_min' => $detail['infant_age_min'],
                'infant_age_max' => $detail['infant_age_max'],
                'file_service_zero_id' => $fileServiceZeroId,
            ];
        $detail = DetailServiceZeroEloquentModel::create($detailServiceData);
        $details[] = $detail->toArray();
         return $details;
            //DetailServiceZeroEloquentModel::create($detailServiceData);
        }
    }

    

      private function saveRates($fileServiceZeroId, $service)
    {
        $rates = [];
        foreach ($service->rates ?? [] as $rate) {
            $rateTypes = ['adult', 'child', 'guide', 'tour_conductor'];

            foreach ($rateTypes as $type) {
                if (isset($rate[$type])) {
                    foreach ($rate[$type]['cost_net'] ?? [] as $costNet) {
                        $rateServiceData = [
                            'type_passenger' => $type, // Proporcionar un valor para el campo type_passenger
                            'passenger_range_min' => $costNet['range']['min'] ?? null,
                            'passenger_range_max' => $costNet['range']['max'] ?? null,
                            'net_cost' => $costNet['price'],
                            'file_service_zero_id' => $fileServiceZeroId,
                        ];
                        $rate = RatesServiceZeroEloquentModel::create($rateServiceData);
                                $rates[] = $rate->toArray();
                        return $rates;
                        //RatesServiceZeroEloquentModel::create($rateServiceData);
                    }
                }
            }
        }
    }



    private function getOperationServiceData($fileServiceZeroId)
{
    $operations = OperationServiceZeroEloquentModel::where('file_service_zero_id', $fileServiceZeroId)->get();
    return $operations->toArray();
}

private function getDetailServiceData($fileServiceZeroId)
{
    $details = DetailServiceZeroEloquentModel::where('file_service_zero_id', $fileServiceZeroId)->get();
    return $details->toArray();
}

private function getRatesData($fileServiceZeroId)
{
    $rates = RatesServiceZeroEloquentModel::where('file_service_zero_id', $fileServiceZeroId)->get();
    return $rates->toArray();
}

public function filter($params)
{
    if (!is_array($params)) {
        throw new \InvalidArgumentException('El parámetro debe ser un arreglo');
    }

    
    $per_page = $params['per_page'] ?? 10; 

    $filteredServiceZero = ServiceZeroEloquentModel::with('operationServiceZero','detailServiceZero','rates');

    foreach ($params as $key => $value) {
        if (!empty($value)) {
            if ($key == 'city_in') {
                $filteredServiceZero->whereHas('operationServiceZero', function ($query) use ($value) {
                    $query->where('city_in', $value);
                });
            } elseif ($key !== 'per_page') {
                $filteredServiceZero->where($key, $value);
            }
        }
    }

    $filteredServiceZero = $filteredServiceZero->paginate($per_page);

    return $filteredServiceZero;
}





}