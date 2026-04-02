<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\FileItineraryDescription;

interface FileItineraryDescriptionRepositoryInterface
{
    public function create(FileItineraryDescription $fileData): void;
}
