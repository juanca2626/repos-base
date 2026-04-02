<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileNoteGeneralRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileNoteGeneralQuery implements QueryInterface{

    private FileNoteGeneralRepositoryInterface $repository;

    public function __construct(private readonly int $file_id)
    {
        $this->repository = app()->make(FileNoteGeneralRepositoryInterface::class);
    }

     /**
     * @return mixed
     */
    public function handle() :array{
        return $this->repository->index($this->file_id);
    }
}
