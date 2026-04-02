<?php

namespace Src\Modules\File\Application\EventHandlers;

use Carbon\Carbon;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent; 
use Src\Modules\File\Infrastructure\Jobs\ProcessFilePassToOpeJob; 

class FilePassToOpeHandler      
{
    public function handle(FilePassToOpeEvent $event): void
    {  
        dispatch(new ProcessFilePassToOpeJob($event->file_id));
    }
}
