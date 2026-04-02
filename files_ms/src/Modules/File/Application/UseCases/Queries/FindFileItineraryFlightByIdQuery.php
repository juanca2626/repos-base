<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileItineraryFlightByIdQuery implements QueryInterface
{
    private FileItineraryFlightRepositoryInterface $fileRepository;

    public function __construct(private readonly int $id)
    {
        $this->fileRepository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->findById($this->id);
        
    }
}
