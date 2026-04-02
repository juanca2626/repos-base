<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * This namespace is applied to your siteminder controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $siteminderNamespace = 'App\Http\Siteminder\Controllers';

    /**
     * This namespace is applied to your erevmax controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $erevmaxNamespace = 'App\Http\Erevmax\Controllers';

    /**
     * This namespace is applied to your travelclick controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $travelclickNamespace = 'App\Http\Travelclick\Controller';

    protected $servicesNamespace = 'App\Http\Services\Controllers';

    /**
     * This namespace is applied to your travelclick controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $hyperguestNamespace = 'App\Http\Hyperguest\Controllers';

    /**
     * This namespace is applied to your tourcms controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $tourcmsNamespace = 'App\Http\Tourcms\Controller';

    /**
     * This namespace is applied to your despegar controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $despegarNamespace = 'App\Http\Despegar\Controller';
    /**
     * This namespace is applied to your expedia controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $expediaNamespace = 'App\Http\Expedia\Controller';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    protected $soapServerNamespace = 'App\Http\SoapServer\Controller';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    protected $trainsNamespace = 'App\Http\TrainServices\Controller';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    protected $pentagramaNamespace = 'App\Http\Pentagrama\Controller';

    protected $GYGNamespace = 'App\Http\GYG\Controller';

    protected $allotmentNamespace = 'App\Http\Allotment\Controller';

    protected $seriesNamespace = 'App\Http\Series\Controller';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapSiteminderRoutes();

        $this->mapErevmaxRoutes();

        $this->mapTravelclickRoutes();
        $this->mapServicesRoutes();
        $this->mapHyperguestRoutes();

        $this->mapTourcmsRoutes();
        $this->mapTrainsRoutes();
        $this->mapDespegarRoutes();
        $this->mapExpediaRoutes();
        $this->mapSoapServerRoutes();
        $this->mapRequestReportsAuroraRoutes();
        $this->mapPentagramaRoutes();
        $this->mapGYGRoutes();
        $this->mapAllotmentHotelsRoutes();
        $this->mapSeriesRoutes();

        //
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware(['api','setLocale'])
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api siteminder" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapSiteminderRoutes()
    {
        Route::prefix('channel/siteminder')
            ->middleware('api')
            ->namespace($this->siteminderNamespace)
            ->group(base_path('routes/siteminder.php'));
    }

    /**
     * Define the "api siteminder" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapErevmaxRoutes()
    {
        Route::prefix('channel/erevmax')
            ->middleware('api')
            ->namespace($this->erevmaxNamespace)
            ->group(base_path('routes/erevmax.php'));
    }

    /**
     * Define the "api travelclick" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTravelclickRoutes()
    {
        Route::prefix('channel/travelclick')
            ->middleware('api')
            ->namespace($this->travelclickNamespace)
            ->group(base_path('routes/travelclick.php'));
    }

    protected function mapHyperguestRoutes()
    {
        Route::prefix('channel/hyperguest')
//            ->middleware('api')
            ->namespace($this->hyperguestNamespace)
            ->group(base_path('routes/hyperguest.php'));
    }

    /**
     * Define the "api services" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapServicesRoutes()
    {
        Route::prefix('services')
            ->middleware(['api','setLocale'])
            ->namespace($this->servicesNamespace)
            ->group(base_path('routes/services.php'));

    }

    /**
     * Define the "api tourcms" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTourcmsRoutes()
    {
        Route::prefix('api/channel/tourcms')
            ->middleware('api')
            ->namespace($this->tourcmsNamespace)
            ->group(base_path('routes/tourcms.php'));
    }

    /**
     * Define the "api tourcms" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapTrainsRoutes()
    {
        Route::prefix('api/channel/trains')
            ->middleware('api')
            ->namespace($this->trainsNamespace)
            ->group(base_path('routes/trains.php'));
    }

    /**
     * Define the "api despegar" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapDespegarRoutes()
    {
        Route::prefix('api/channel/despegar')
            ->middleware('api')
            ->namespace($this->despegarNamespace)
            ->group(base_path('routes/despegar.php'));
    }

    /**
     * Define the "api expedia" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapExpediaRoutes()
    {
        Route::prefix('api/channel/expedia')
            ->middleware('api')
            ->namespace($this->expediaNamespace)
            ->group(base_path('routes/expedia.php'));
    }

    /**
     * Define the "api expedia" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapRequestReportsAuroraRoutes()
    {
        Route::prefix('api/reports_aurora')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/reports.php'));
    }

    /**
     * Define the "soap server" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapSoapServerRoutes()
    {
        Route::prefix('soap')
            ->middleware('api')
            ->namespace($this->soapServerNamespace)
            ->group(base_path('routes/soap.php'));
    }

     /**
     * Define the "api pentagrama" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPentagramaRoutes()
    {
        Route::prefix('api/channel/pentagrama')
            ->middleware('api')
            ->namespace($this->pentagramaNamespace)
            ->group(base_path('routes/pentagrama.php'));
    }

     /**
     * Define the "api GYG" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapGYGRoutes()
    {
        Route::prefix('api/channel/gyg')
            ->middleware('api')
            ->namespace($this->GYGNamespace)
            ->group(base_path('routes/gyg.php'));
    }

     /**
     * Define the "api pentagrama" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAllotmentHotelsRoutes()
    {
        Route::prefix('api/allotment/hotels')
            ->middleware('api')
            ->namespace($this->allotmentNamespace)
            ->group(base_path('routes/allotment.php'));
    }

    protected function mapSeriesRoutes()
    {
        Route::prefix('api/series')
            ->middleware(['api','setLocale'])
            ->namespace($this->seriesNamespace)
            ->group(base_path('routes/series.php'));
    }


}
