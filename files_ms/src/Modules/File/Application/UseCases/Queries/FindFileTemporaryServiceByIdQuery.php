<?php

namespace Src\Modules\File\Application\UseCases\Queries;
 
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\Repositories\FileTemporaryServiceRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileTemporaryServiceByIdQuery implements QueryInterface
{
    private FileTemporaryServiceRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $fileItineraryId)
    {
        $this->fileItineraryRepository = app()->make(FileTemporaryServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): FileTemporaryService
    {
        return $this->fileItineraryRepository->findById($this->fileItineraryId);
    }
}
