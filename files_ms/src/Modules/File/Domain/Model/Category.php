<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\Category\Name; 
use Src\Shared\Domain\Entity;  

class Category extends Entity
{    
    public function __construct(
        public readonly ?int $id, 
        public readonly Name $name        
    ) {
 
    }
    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 
            'name' => $this->name->value(),
             
        ];
    }
  

}
