<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchInA2DetailsServiceQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): File|array
    {
        return $this->fileRepository->searchInA2DetailsService($this->params);        
    }
}
