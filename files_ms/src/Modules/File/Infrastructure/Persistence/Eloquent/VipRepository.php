<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\VipMapper;
use Src\Modules\File\Domain\Model\Vip;
use Src\Modules\File\Domain\Repositories\VipRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\VipEloquentModel;

class VipRepository implements VipRepositoryInterface
{
    public function create(Vip $vip): Vip
    {
        return DB::transaction(function () use ($vip) {
            $vipEloquent = VipMapper::toEloquent($vip);
            $vipEloquent->save();
            return VipMapper::fromEloquent($vipEloquent);
        });
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function findById(int $id): ?Vip
    {
        $vipEloquent = VipEloquentModel::query()->findOrFail($id);
        return VipMapper::fromEloquent($vipEloquent);
    }

    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function update(int $id, Vip $userData): bool
    {
        // TODO: Implement update() method.
        return false;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
        return false;
    }

    public function searchAllVipsQuery(array $filters): int
    {
        $vipEloquent = VipEloquentModel::query();

        if(!empty($filters['search']))
        {
            $search = $filters['search'];
            $vipEloquent = $vipEloquent->where(function ($query) use ($search) {
                $search = explode(" ", $search);

                foreach($search as $v)
                {
                    $query->orWhere('name', 'like', sprintf('%?%', $v));
                }
            });
        }

        return $vipEloquent->count();
    }

    public function searchVipsQuery(array $filters): LengthAwarePaginator
    {
        $vipEloquent = VipEloquentModel::query();

        if(!empty($filters['search']))
        {
            $search = $filters['search'];
            $vipEloquent = $vipEloquent->where(function ($query) use ($search) {
                $search = explode(" ", $search);

                foreach($search as $v)
                {
                    $query->orWhere('name', 'like', sprintf('%?%', $v));
                }
            });
        }

        $perPage = $filters['per_page']; $page = $filters['page']; $count = $vipEloquent->count();

        $vips = [];
        foreach ($vipEloquent->paginate($perPage, ['*'], 'page', $page) as $vip)
        {
            $vip = VipMapper::fromEloquent($vip); $vips[] = $vip;
        }

        return new LengthAwarePaginator(
            $vips,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
