<?php

namespace App\Providers;

use Src\Modules\File\Application\EventHandlers\CreatedFileEventHandler;
use Src\Modules\File\Domain\Events\CreatedFileEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider; 
use Src\Modules\File\Application\EventHandlers\FilePassToOpeHandler;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        CreatedFileEvent::class => [
            CreatedFileEventHandler::class, 
        ],
        FilePassToOpeEvent::class => [ 
            FilePassToOpeHandler::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
