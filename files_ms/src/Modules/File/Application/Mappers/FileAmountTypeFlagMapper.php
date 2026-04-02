<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileAmountTypeFlag;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Description;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Name;
use Src\Modules\File\Domain\ValueObjects\FileAmountTypeFlag\Icon;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountTypeFlagEloquentModel;

class FileAmountTypeFlagMapper
{
    public static function fromRequestSearch($request): array
    {
        $page = (int) $request->has('page') ? $request->page : 1;
        $per_page = (int) ($request->has('per_page')) ? $request->input('per_page') : 10;
        $filter = (string) $request->has('filter') ? $request->filter : '';

        return [
            'page' => $page,
            'per_page' => $per_page,
            'filter' => $filter,
        ];
    }

    public static function fromArray(array $file_amount_type_flag): FileAmountTypeFlag
    {
        $fileAmountTypeFlagModel = new FileAmountTypeFlagEloquentModel($file_amount_type_flag);
        $fileAmountTypeFlagModel->id = $file_amount_type_flag['id'] ?? null;
        return self::fromEloquent($fileAmountTypeFlagModel);
    }

    public static function fromEloquent(FileAmountTypeFlagEloquentModel $fileAmountTypeFlag): FileAmountTypeFlag
    {
        return new FileAmountTypeFlag(
            id: $fileAmountTypeFlag->id,
            name: new Name($fileAmountTypeFlag->name),
            description: new Description($fileAmountTypeFlag->description),
            icon: new Icon($fileAmountTypeFlag->icon),
        );
    }

    public static function toEloquent(FileAmountTypeFlag $fileAmountTypeFlag): FileAmountTypeFlag
    {
        $fileAmountTypeFlagModel = new FileAmountTypeFlagEloquentModel();
        if ($fileAmountTypeFlag->id) {
            $fileAmountTypeFlagModel = FileAmountTypeFlagEloquentModel::query()
                ->findOrFail($fileAmountTypeFlag->id);
        }

        $fileAmountTypeFlagModel->name = $fileAmountTypeFlag->name->value();
        $fileAmountTypeFlagModel->description = $fileAmountTypeFlag->description->value();
        $fileAmountTypeFlagModel->icon = $fileAmountTypeFlag->icon->value();
        return $fileAmountTypeFlag;
    }
}
