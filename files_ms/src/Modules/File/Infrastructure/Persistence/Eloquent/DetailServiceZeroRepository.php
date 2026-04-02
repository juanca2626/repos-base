<?php
namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Model\DetailServiceZero;
use Src\Modules\File\Domain\Repositories\DetailServiceZeroRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\OperationServiceZeroEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\DetailServiceZeroEloquentModel;

class DetailServiceZeroRepository implements DetailServiceZeroRepositoryInterface
{
    
public function filter($service): mixed
    {
        $params = $service; //->toArray();

        $query = DetailServiceZeroEloquentModel::query();

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
        return $query->get();
    }



}