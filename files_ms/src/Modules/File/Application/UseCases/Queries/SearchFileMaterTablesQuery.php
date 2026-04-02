<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileMasterTableRepositoryInterface; 
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Shared\Domain\QueryInterface;

class SearchFileMaterTablesQuery implements QueryInterface
{
    private FileMasterTableRepositoryInterface $filePassengerRepository;

    public function __construct(private readonly array $params)
    {
        $this->filePassengerRepository = app()->make(FileMasterTableRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->filePassengerRepository->master_tables($this->params);
    }
}
