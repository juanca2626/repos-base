<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\Category; 
use Src\Modules\File\Domain\ValueObjects\Category\Name; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\CategoryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FilePassengerEloquentModel;

class CategoryMapper
{
 
    public static function fromArray(array $category): Category
    {
        $categoryEloquentModel = new CategoryEloquentModel($category);
        $categoryEloquentModel->id = $category['id'] ?? null;
        return self::fromEloquent($categoryEloquentModel);
    }


    public static function fromEloquent(CategoryEloquentModel $categoryEloquentModel): Category
    {
        return new Category(
            id: $categoryEloquentModel->id, 
            name: new Name($categoryEloquentModel->name),             
        );
    }

    public static function toEloquent(Category $category): CategoryEloquentModel
    {
        $categoryEloquentModel = null;

        if($category->id)
        {
            $categoryEloquentModel = CategoryEloquentModel::query(); 
            $categoryEloquentModel = $categoryEloquentModel->where('id', '=', $category->id);
            $categoryEloquentModel = $categoryEloquentModel->first();
        }
 
        if(!$categoryEloquentModel)
        {
            $categoryEloquentModel = new CategoryEloquentModel();
        }
 
        $categoryEloquentModel->name = $category->name->value();
        
        return $categoryEloquentModel;
    }
}
