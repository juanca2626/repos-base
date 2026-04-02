<?php

namespace Src\Modules\File\Domain\Model;

use Carbon\Carbon;   
use Src\Modules\File\Domain\ValueObjects\FileCategory\Category;
use Src\Modules\File\Domain\ValueObjects\FileCategory\FileId;
use Src\Modules\File\Domain\ValueObjects\FileCategory\CategoryId; 
use Src\Modules\File\Domain\ValueObjects\FileCategory\CreatedAt;

use Src\Shared\Domain\Entity;

class FileCategory extends Entity
{
    public function __construct( 
        public readonly ?int $id,
        public readonly FileId $fileId,
        public readonly CategoryId $categoryId,        
        public readonly CreatedAt $createdAt, 
        public readonly Category $category,
 
    ) {
    }

    public function getDate(): string
    {
        return Carbon::parse($this->createdAt)->format('d/m/Y H:i:s');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_id' => $this->fileId->value(),
            'category_id' => $this->categoryId->value(),             
            'created_at' => $this->createdAt->value()
        ];
    }

}
