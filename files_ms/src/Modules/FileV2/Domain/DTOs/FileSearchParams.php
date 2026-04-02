<?php

namespace Src\Modules\FileV2\Domain\DTOs;

class FileSearchParams
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 10,
        public ?string $filter = null,
        public ?array $dateRange = null,
        public string $filterBy = 'id',
        public string $filterByType = 'desc',
        public ?string $executiveCode = null,
        public ?string $clientId = null,
        public ?string $clientCode = null,
        public ?string $filterNextDays = null,
        public ?string $revisionStages = null,
        public ?bool $complete = null,
    ) {}
}