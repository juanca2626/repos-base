<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryHotelByCancellation;

class SerachFileCanceledServices implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(
        private readonly int $fileId)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->canceledServices($this->fileId);
    }
}
