<?php

namespace Src\Modules\File\Domain\Events;
 
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilePassToOpeEvent
{
    use Dispatchable, SerializesModels;

    public int $file_id; 

    public function __construct($file_id)
    {
        $this->file_id = $file_id; 
    }
}
