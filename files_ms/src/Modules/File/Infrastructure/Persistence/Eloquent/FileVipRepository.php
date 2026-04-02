<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Application\Mappers\FileVipMapper;
use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileVipEloquentModel;
use Src\Modules\File\Presentation\Http\Resources\FileVipResource;

class FileVipRepository implements FileVipRepositoryInterface
{
    public function create(FileVip $fileVip): FileVip
    {
        return DB::transaction(function () use ($fileVip) {
            $fileVipEloquent = FileVipEloquentModel::query()
                ->where('file_id', '=', $fileVip->fileId->value())
                ->where('vip_id', '=', $fileVip->vipId->value())
                ->first();

            if(!$fileVipEloquent)
            {
                $fileVipEloquent = FileVipMapper::toEloquent($fileVip);
            }

            $fileVipEloquent->save();
            return FileVipMapper::fromEloquent($fileVipEloquent);
        });
    }

    /**
     * @param int $id
     * @return File|null
     */
    public function findById(int $id): ?FileVip
    {
        $fileVipEloquent = FileVipEloquentModel::query()->findOrFail($id);
        return FileVipMapper::fromEloquent($fileVipEloquent);
    }

    /**
     * @param int $id
     * @param File $userData
     * @return bool
     */
    public function update(int $id, FileVip $fileVip): bool
    {
        $fileVipEloquentModel = FileVipEloquentModel::query()->findOrFail($id);
        $fileVipEloquentModel->file_id = $fileVip->fileId->value();
        $fileVipEloquentModel->vip_id = $fileVip->vipId->value();
        $fileVipEloquentModel->save();
        // $file_vip = FileVipMapper::fromEloquent($fileVipEloquentModel);
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $file_id, int $vip_id): bool
    {
        $fileVipEloquent = FileVipEloquentModel::query()
            ->where('file_id', '=', $file_id)->where('vip_id', '=', $vip_id);
        $fileVipEloquent->delete();
        return true;
    }

    public function searchFileVipsQuery(array $filters): LengthAwarePaginator
    {
        $fileVipEloquent = FileVipEloquentModel::query();

        if(!empty($filters['file_id']))
        {
            $fileVipEloquent = $fileVipEloquent->where('file_id', $filters['file_id']);
        }

        if(!empty($filters['vip_id']))
        {
            $fileVipEloquent = $fileVipEloquent->where('vip_id', $filters['vip_id']);
        }

        
        $perPage = $filters['per_page']; $page = $filters['page']; $count = $fileVipEloquent->count();

        $file_vips = [];
        foreach ($fileVipEloquent->paginate($perPage, ['*'], 'page', $page) as $fileVip)
        {
            $file_vip = FileVipMapper::fromEloquent($fileVip);
            $file_vips[] = $file_vip;
        }

        return new LengthAwarePaginator(
            $file_vips,
            $count,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
