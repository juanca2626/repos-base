<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator; 
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;

interface FileMasterTableRepositoryInterface
{   
    public function master_tables(array $params): array;    
   
}
