<?php

namespace Src\Modules\Catalogs\Application\UseCases;

use Src\Modules\Catalogs\Domain\Readers\ExecutiveReader;
// use Src\Modules\Catalogs\Domain\Exceptions\CatalogPersistenceException;
use Src\Modules\Catalogs\Domain\Errors\CatalogErrorCode;
use Src\Shared\Logging\Traits\LogsDomainEvents; 

class GetExecutives
{
    use LogsDomainEvents;

    public function __construct(
       private ExecutiveReader $executiveReader
    ) {}

    public function execute(): array
    {    
        // $this->logError(
        //     message: 'Error retrieving executives',
        //     errorCode: CatalogErrorCode::PROCESS_FAILED 
        // );

        $this->logInfo('Fetching executives 222222222');
        
        $executives = $this->executiveReader->all();         
        return $executives;        
    }
}

 