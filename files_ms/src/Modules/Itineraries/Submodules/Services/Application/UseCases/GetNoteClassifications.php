<?php

namespace Src\Modules\Notes\Application\UseCases;

use Src\Modules\Notes\Domain\Readers\NoteClassificationReader;  
use Src\Shared\Logging\Traits\LogsDomainEvents; 

class GetNoteClassifications
{
    use LogsDomainEvents;

    public function __construct(
       private NoteClassificationReader $noteClassificationReader
    ) {}

    public function execute(): array
    {    
        $cities = $this->noteClassificationReader->all();         
        return $cities;        
    }
}

 