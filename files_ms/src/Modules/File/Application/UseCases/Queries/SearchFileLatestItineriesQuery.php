<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileLatestItineriesQuery implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->searchFileLatestItineriesQuery($this->params);
    }
}
