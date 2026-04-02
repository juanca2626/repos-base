<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class FileItineraryUpdateFlightDateCommand implements CommandInterface {

    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly int $id, private readonly array $params)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }
    public function execute():array
    {
        return $this->repository->updateDate($this->file_id, $this->id, $this->params);
    }
}
