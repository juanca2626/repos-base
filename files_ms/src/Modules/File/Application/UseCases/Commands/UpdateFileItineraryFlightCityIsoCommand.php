<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryFlightCityIsoCommand implements CommandInterface {

    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $fileId, private readonly int $fileItineraryFlightId, private readonly array $params)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    public function execute() : array {
        return $this->repository->updateCityIso($this->fileId, $this->fileItineraryFlightId, $this->params);
    }
 }
