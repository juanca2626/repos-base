<?php

namespace Src\Modules\FileV2\Application\UseCases;

use Src\Modules\FileV2\Domain\DTOs\FileSearchParams;
use Src\Modules\FileV2\Infrastructure\Persistence\FileRepository;

class SearchFiles
{
    public function __construct(private FileRepository $repo) {}

    public function execute(FileSearchParams $params)
    {
        return $this->repo->search($params);
    }
}
