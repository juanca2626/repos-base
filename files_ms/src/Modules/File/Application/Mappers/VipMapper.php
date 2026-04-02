<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\Vip;
use Src\Modules\File\Domain\ValueObjects\Vip\EntityVip;
use Src\Modules\File\Domain\ValueObjects\Vip\IsoVip;
use Src\Modules\File\Domain\ValueObjects\Vip\Name;
use Src\Modules\File\Domain\ValueObjects\Vip\UserId;
use Src\Modules\File\Domain\ValueObjects\Vip\VipId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\VipEloquentModel;
use Illuminate\Support\Str;

class VipMapper
{
    public static function fromRequest(Request $request, ?int $vipId = null): Vip
    {
        $user_id = $request->input('user_id', 0);
        $entity = $request->input('entity');
        $name = $request->input('name');
        $iso = Str::slug($name);

        return new Vip(
            id: $vipId,
            userId: new UserId($user_id),
            entityVip: new EntityVip($entity),
            name: new Name($name),
            isoVip: new IsoVip($iso),
        );
    }

    public static function fromRequestSearch(Request $request): array
    {
        $name = ($request->has('name')) ? $request->__get('name') : '';

        return [
            'name' => $name,
            'per_page' => 100,
            'page' => 0
        ];
    }

    public static function fromArray($vip): Vip
    {
        $vipEloquentModel = new VipEloquentModel($vip);
        $vipEloquentModel->id = $vip['id'] ?? null;
        return self::fromEloquent($vipEloquentModel);
    }

    public static function fromEloquent(VipEloquentModel $vipEloquentModel): Vip
    {
        return new Vip(
            id: $vipEloquentModel->id,
            userId: new UserId($vipEloquentModel->user_id),
            entityVip: new EntityVip($vipEloquentModel->entity),
            name: new Name($vipEloquentModel->name),
            isoVip: new IsoVip($vipEloquentModel->iso),
        );
    }

    public static function toEloquent(Vip $vip): VipEloquentModel
    {
        $vipEloquent = new VipEloquentModel();
        if ($vip->id) {
            $vipEloquent = VipEloquentModel::query()->findOrFail($vip->id);
        }

        $vipEloquent->user_id = $vip->userId->value();
        $vipEloquent->entity = $vip->entityVip->value();
        $vipEloquent->name = $vip->name->value();
        $vipEloquent->iso = $vip->isoVip->value();
        return $vipEloquent;
    }
}
