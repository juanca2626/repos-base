<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileVip\Vip;
use Src\Modules\File\Domain\ValueObjects\FileVip\Vips;
use Src\Modules\File\Domain\ValueObjects\Vip\VipId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileVipEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\VipEloquentModel;

class FileVipMapper
{
    public static function fromRequestSearch(Request $request): array
    {
        $file_id = ($request->has('file_id')) ? $request->__get('file_id') : '';

        return [
            'file_id' => $file_id,
        ];
    }

    public static function fromRequest(Request $request, int $fileId = null, ?int $fileVipId = null): FileVip
    {
        $vipId = (int) $request->input('vip_id', 0);
        $vip = VipEloquentModel::query()->where('id', '=', $vipId)->first()->toArray() ?? [];

        return new FileVip(
            id: $fileVipId,
            fileId: new FileId($fileId),
            vipId: new VipId($vipId),
            vip: new Vip($vip),
        );
    }

    public static function fromArray(array $fileVips): FileVip
    {
        $fileVipEloquentModel = new FileVipEloquentModel($fileVips);
        $fileVipEloquentModel->id = $fileVips['id'] ?? null;

        if(isset($fileVips['vip']))
        {
            $fileVipEloquentModel->vip = $fileVips['vip'];
        }

        return self::fromEloquent($fileVipEloquentModel);
    }

    public static function fromEloquent(FileVipEloquentModel $fileVipEloquentModel): FileVip
    {
        $vip = VipEloquentModel::query()->where('id', '=', $fileVipEloquentModel->vip_id)
            ->first()->toArray() ?? [];

        return new FileVip(
            id: $fileVipEloquentModel->id,
            fileId: new FileId($fileVipEloquentModel->file_id),
            vipId: new VipId($fileVipEloquentModel->vip_id),
            vip: new Vip($vip)
        );
    }

    public static function toEloquent(FileVip $fileVip): FileVipEloquentModel
    {
        $fileVipEloquent = new FileVipEloquentModel();
        if ($fileVip->id) {
            $fileVipEloquent = FileVipEloquentModel::query()->findOrFail($fileVip->id);
        }

        $fileVipEloquent->file_id = $fileVip->fileId->value();
        $fileVipEloquent->vip_id = $fileVip->vipId->value();

        return $fileVipEloquent;
    }
}
