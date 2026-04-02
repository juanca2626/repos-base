<?php

namespace Src\Modules\File\Domain\Events;

use Src\Modules\File\Domain\Model\File;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatedFileEvent
{
    use Dispatchable, SerializesModels;

    public int $id;
    public File $file; 
    public array $itinerariesData;

    public function __construct($id, File $file, array $itinerariesData)
    {
        $this->id = $id;
        $this->file = $file; 
        $this->itinerariesData = $itinerariesData;
    }
}
