<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryAmountSaleCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $itineraryId, private readonly array $params)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileItineraryRepository->updateAmountSale($this->itineraryId, $this->params);
    }
}
