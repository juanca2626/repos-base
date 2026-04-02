<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileNumberBasicInfoQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int $id, private readonly array $params)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array | null
    {
        return $this->fileRepository->find_file_number_basic_info($this->id, $this->params);

    }
}
