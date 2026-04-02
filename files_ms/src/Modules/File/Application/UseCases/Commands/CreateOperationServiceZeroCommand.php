<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\OperationServiceZeroEloquentModel;

class CreateOperationServiceZeroCommand
{
    // Propiedades y métodos de la clase

   public function handle(array $operationServiceData)
    {
        // Aquí puedes usar el modelo Eloquent para crear la entrada en la base de datos
        return OperationServiceZeroEloquentModel::create($operationServiceData);
    }

  
}