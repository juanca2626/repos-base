<?php
namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Model\OperationServiceZero;
use Src\Modules\File\Domain\Repositories\OperationServiceZeroRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\ServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\OperationServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\DetailServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\RatesServiceZeroEloquentModel;

class OperationServiceZeroRepository implements OperationServiceZeroRepositoryInterface
{
    
public function filter($service): mixed
    {
        $params = $service;   //->toArray();

        $query = OperationServiceZeroEloquentModel::query();

        if (isset($params['city_in'])) {
            $query->where('city_in', $params['city_in']);
        }

        if (isset($params['city_out'])) {
            $query->where('city_out', $params['city_out']);
        }

        if (isset($params['start_date'])) {
            $query->where('start_date', $params['start_date']);
        }

        if (isset($params['end_date'])) {
            $query->where('end_date', $params['end_date']);
        }

        // Agrega más condiciones de filtrado según sea necesario

        return $query->get();
    }



}