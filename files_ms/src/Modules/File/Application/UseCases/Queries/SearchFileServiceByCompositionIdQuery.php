<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileServiceByCompositionIdQuery implements QueryInterface
{
    private FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository;

    public function __construct(private readonly int $composition_id)
    {
        $this->fileServiceCompositionRepository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileServiceCompositionRepository->findServicesByCompositionId($this->composition_id);
    }
}
