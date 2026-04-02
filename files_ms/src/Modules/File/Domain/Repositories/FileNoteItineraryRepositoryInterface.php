<?php

namespace Src\Modules\File\Domain\Repositories;

interface FileNoteItineraryRepositoryInterface {
    public function listFileNoteItinerary(int $fileId, int $id): array;
    public function storeFileNoteItinerary(int $fileNoteId, int $id, array $params): array;
    public function updateFileNoteItinerary(int $fileNoteId, int $itinerary_id, int $note_id, array $params): array;
    public function deleteFileNoteItinerary(int $fileNoteId, int $note_id, array $params): bool;
    public function deleteFileNoteItineraryService(int $itinerary_id, array $params): bool;
}
