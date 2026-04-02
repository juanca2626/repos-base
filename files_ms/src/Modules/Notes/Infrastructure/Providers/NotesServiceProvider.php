<?php
namespace Src\Modules\Notes\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider; 
use Src\Modules\Notes\Domain\Readers\NoteClassificationReader; 
use Src\Modules\Notes\Infrastructure\Readers\FileOneDbNoteClassificationReaderHttp;

class NotesServiceProvider extends ServiceProvider
{
    public function register(): void
    {        
        $this->app->bind(
            NoteClassificationReader::class,
            FileOneDbNoteClassificationReaderHttp::class
        );  
    }
}
