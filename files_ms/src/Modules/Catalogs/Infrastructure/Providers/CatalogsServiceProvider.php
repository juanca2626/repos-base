<?php
namespace Src\Modules\Catalogs\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Modules\Catalogs\Domain\Readers\CityReader;
use Src\Modules\Catalogs\Domain\Readers\ExecutiveReader; 
use Src\Modules\Catalogs\Infrastructure\Readers\ApiGwCityReaderHttp;
use Src\Modules\Catalogs\Infrastructure\Readers\AuroraExecutiveReaderHttp; 

class CatalogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ExecutiveReader::class,
            AuroraExecutiveReaderHttp::class
        );

        $this->app->bind(
            CityReader::class,
            ApiGwCityReaderHttp::class
        );        
 
    }
}
