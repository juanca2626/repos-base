<?php

namespace Src\Modules\Catalogs\Infrastructure\Readers;

use Src\Modules\Catalogs\Domain\Readers\ExecutiveReader;
use Src\Shared\Infrastructure\Integrations\Aurora\Client\AuroraHttpClient;

class AuroraExecutiveReaderHttp implements ExecutiveReader
{
    public function __construct(
        private AuroraHttpClient $client
    ) {}

    public function all(): array
    {
        $response = $this->client->get('/api/a3/executives');

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to fetch executives from Aurora');
        }

        return $response->json();
    }
}