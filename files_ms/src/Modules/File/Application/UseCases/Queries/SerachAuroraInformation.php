<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryServiceByCancellation;

class SerachAuroraInformation implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->searchByCommunication($this->params);
    }
}
