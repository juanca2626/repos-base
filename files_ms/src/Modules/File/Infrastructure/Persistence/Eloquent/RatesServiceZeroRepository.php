<?php
namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Src\Modules\File\Domain\Model\RatesServiceZero;
use Src\Modules\File\Domain\Repositories\RatesServiceZeroRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\RatesServiceZeroEloquentModel;

class RatesServiceZeroRepository implements RatesServiceZeroRepositoryInterface
{
    
public function filter($service): mixed
    {
        $params = $service; //->toArray();

        $query = RatesServiceZeroEloquentModel::query();

        if (isset($params['type_passenger'])) {
            $query->where('type_passenger', $params['type_passenger']);
        }

        if (isset($params['passenger_range_min'])) {
            $query->where('passenger_range_min', $params['passenger_range_min']);
        }

        if (isset($params['passenger_range_max'])) {
            $query->where('passenger_range_max', $params['passenger_range_max']);
        }

        if (isset($params['net_cost'])) {
            $query->where('net_cost', $params['net_cost']);
        }
        if (isset($params['service_tax'])) {
            $query->where('service_tax', $params['service_tax']);
        }

        // Agrega más condiciones de filtrado según sea necesario

        return $query->get();
    }



}