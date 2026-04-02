<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileCategory;
use Src\Modules\File\Domain\ValueObjects\FileCategory\CategoryId;
use Src\Modules\File\Domain\ValueObjects\FileCategory\Category;
use Src\Modules\File\Domain\ValueObjects\FileCategory\FileId; 
use Src\Modules\File\Domain\ValueObjects\FileCategory\CreatedAt; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCategoryEloquentModel;

use function PHPSTORM_META\map;

class FileCategoryMapper
{
    public static function fromRequestToArray(
        array $categories
    ): array {

        $categoryEntities = [];
        foreach($categories as $category) { 
            array_push($categoryEntities, [
                'id' => null,
                'file_id' => null,
                'category_id' => $category,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return $categoryEntities;
    }

    public static function fromArray(array $fileCategory): FileCategory
    {
        $fileCategoryEloquent = new FileCategoryEloquentModel($fileCategory);
        $fileCategoryEloquent->id = $fileCategory['id'] ?? null;
        
        return self::fromEloquent($fileCategoryEloquent);
    }

    public static function fromEloquent(FileCategoryEloquentModel $fileCategoryEloquent
    ): FileCategory {

        $category = collect();      
        if($fileCategoryEloquent->category?->toArray()){
           $category = $fileCategoryEloquent->category?->toArray();
           $category = CategoryMapper::fromArray($category);
        }

        return new FileCategory(
            id: $fileCategoryEloquent->id,
            fileId: new FileId($fileCategoryEloquent->file_id),
            categoryId: new CategoryId($fileCategoryEloquent->category_id),            
            createdAt: new CreatedAt($fileCategoryEloquent->created_at),
            category: new Category($category)  
        );
    }

    public static function toEloquent(FileCategory
        $fileCategory): FileCategoryEloquentModel
    {
        $fileCategoryEloquent = new FileCategoryEloquentModel();
        if ($fileCategory->id) {
            $fileCategoryEloquent = FileCategoryEloquentModel::query()
                ->findOrFail($fileCategory->id);
        }
                
        $fileCategoryEloquent->file_id = $fileCategory->fileId->value();
        $fileCategoryEloquent->category_id = $fileCategory->categoryId->value(); 
        $fileCategoryEloquent->created_at = $fileCategory->createdAt->value();
        return $fileCategoryEloquent;
    }

}
