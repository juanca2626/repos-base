<?php

namespace Src\Modules\File\Domain\Repositories;
 
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\ValueObjects\File\FileTemporaryServices;

interface FileTemporaryServiceRepositoryInterface
{ 
    public function create(FileTemporaryService $fileData): FileTemporaryService;    
    public function searchItineraryQueryServiceTemporary(array $params): FileTemporaryServices;
    public function findById(int $id): FileTemporaryService;
}
