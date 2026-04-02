<?php

namespace Src\Modules\Passengers\Application\UseCases;

use Src\Modules\Passengers\Infrastructure\Persistence\PassengerRepository;
use Src\Modules\Passengers\Application\Transformers\PassengerTransformer;

class GetPassengersByFile
{
    private $repo;

    public function __construct(PassengerRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $fileId): array
    {
        $passengers = $this->repo->byFile($fileId);

        return collect($passengers)
            ->map(fn($p) => PassengerTransformer::transform($p))
            ->values()
            ->toArray();
    }
}
