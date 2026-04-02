<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryHotelByCancellation;

class SerachFileItineraryHotelByCancellation implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly array $file,
        private readonly int $fileItineraryId, private readonly array $params)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileItineraryRepository
            ->serachFileItineraryByCancellation(
                (new FileItineraryHotelByCancellation($this->file, $this->fileItineraryId , $this->params, true)
            )->jsonSerialize());
    }
}
