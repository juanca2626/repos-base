<?php

namespace Src\Modules\File\Application\UseCases\Queries;
  
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchTokenHotelsQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly array $params, private readonly array $file)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->getEternalExtructureReservation($this->params, $this->file);
    }
}
