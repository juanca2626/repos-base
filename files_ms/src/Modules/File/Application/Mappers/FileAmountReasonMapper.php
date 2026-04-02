<?php

namespace Src\Modules\File\Application\Mappers;

use Src\Modules\File\Domain\Model\FileAmountReason;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Name;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\InfluencesSale;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Area;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\FileAmountReasonId;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Process;
use Src\Modules\File\Domain\ValueObjects\FileAmountReason\Visible;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileAmountReasonEloquentModel;

class FileAmountReasonMapper
{
    public static function fromRequestSearch($request): array
    {
        $page = (int) $request->has('page') ? $request->page : 1;
        $per_page = (int) ($request->has('per_page')) ? $request->input('per_page') : 10;
        $filter = (string) $request->has('filter') ? $request->filter : '';
        $area = (string) $request->has('area') ? $request->area : 'COMERCIAL';
        $process = (string) $request->has('process') ? $request->process : 'modificar_costo';
        $visible = (string) $request->has('visible') ? $request->visible : 1;
        
        return [
            'page' => $page,
            'per_page' => $per_page,
            'filter' => $filter,
            'area' => $area,
            'process' => $process,
            'visible' => $visible,
        ];
    }

    public static function fromArray(array $fileAmountReason): FileAmountReason
    {
        $fileAmountReasonModel = new FileAmountReasonEloquentModel($fileAmountReason);
        $fileAmountReasonModel->id = $fileAmountReason['id'] ?? null;

        return self::fromEloquent($fileAmountReasonModel);
    }

    public static function fromEloquent(FileAmountReasonEloquentModel $fileAmountReason): FileAmountReason
    {
        return new FileAmountReason(
            id: $fileAmountReason->id,
            name: new Name($fileAmountReason->name),
            influencesSale: new InfluencesSale($fileAmountReason->influences_sale),
            area: new Area($fileAmountReason->area),
            visible: new Visible($fileAmountReason->visible),
            process: new Process($fileAmountReason->process),
        );
    }

    public static function toEloquent(FileAmountReason $fileAmountReason): FileAmountReasonEloquentModel
    {
        $fileAmountReasonModel = new FileAmountReasonEloquentModel();
        if ($fileAmountReason->id) {
            $fileAmountReasonModel = FileAmountReasonEloquentModel::query()
                ->findOrFail($fileAmountReasonModel->id);
        }

        $fileAmountReasonModel->name = $fileAmountReasonModel->name->value();
        $fileAmountReasonModel->icon = $fileAmountReasonModel->icon->value();
        return $fileAmountReasonModel;
    }
}
