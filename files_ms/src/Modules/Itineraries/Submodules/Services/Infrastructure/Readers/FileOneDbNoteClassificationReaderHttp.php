<?php

namespace Src\Modules\Notes\Infrastructure\Readers;

use Src\Modules\Notes\Domain\Readers\NoteClassificationReader;
use Src\Shared\Infrastructure\Integrations\Cognito\Client\CognitoHttpClient;

class FileOneDbNoteClassificationReaderHttp implements NoteClassificationReader
{
    public function __construct(
        private CognitoHttpClient $client
    ) {}

    public function all(): array
    {
        $response = $this->client->get('api/v1/commercial/ifx/services/notes-classification');

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to fetch cities from FilesOneDb');
        }

        return $response->json();
    }
}