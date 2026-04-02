<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileExistsQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly int|null $file_id=null, private readonly int|null $file_number=null)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array|null
    {
        return $this->fileRepository->validateFileExist($this->file_id,$this->file_number);
        
    }
}
