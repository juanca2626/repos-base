<?php

namespace App\Providers;

use App\JobStatusList;
use App\LogRate;
use App\Mail\NotifyUserOfCompletedExportServicesRates;
use App\Notifications\NotifyUserOfCompletedAssociationRates;
use App\Notifications\NotifyUserOfCompletedAssociationServiceRate;
use App\Observers\ReservationObserver;
use App\RatesPlans;
use App\Reservation;
use App\ServiceRate;
use App\User;
use App\Client;
use App\Observers\ClientObserver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Client::observe(ClientObserver::class);

        Reservation::observe(ReservationObserver::class);

        Queue::after(function (JobProcessed $event) {
            $payload = $event->job->payload();

            //Todo Job que exporta (excel) el plan tarifario de servicios de un año
            if ($event->job->resolveName() == 'App\Jobs\ExportRatesServicesByYear' or $event->job->resolveName() == 'App\Jobs\ExportRatesServicesByYearTest') {
                $job = unserialize($payload['data']['command']);
                $rate_log_update = LogRate::find($job->log_rate_id);
                if ($rate_log_update) {
                    $rate_log_update->document_job_status = 1;
                    $rate_log_update->save();
                }
                Mail::to($job->user->email)
                    ->send(new NotifyUserOfCompletedExportServicesRates($job->user, $job->service_year,
                        $job->document_name_store, $job->lang));
            }

            //Todo Job Proceso de bloqueo de tarifa de un hotel
            if ($event->job->resolveName() == 'App\Jobs\StoreClientsAssociations') {
                $job = unserialize($payload['data']['command']);
                $rate_logs_update = JobStatusList::where('object_id', $job->rate_plan_id)
                    ->where('entity', 'RatePlanAssociation')
                    ->where('year', $job->year)
                    ->where('job_status', 0)
                    ->get(['id', 'user_id', 'email_notification', 'data']);
                if ($rate_logs_update->count() > 0) {
                    $user = User::find($job->user_id);
                    $rates_plans = RatesPlans::find($job->rate_plan_id);
                    foreach ($rate_logs_update as $value) {
                        $find = JobStatusList::find($value['id']);
                        $find->job_status = 1;
                        $find->save();
                        Mail::to($value['email_notification'])
                            ->send(new NotifyUserOfCompletedAssociationRates($user, $job->year, $rates_plans,
                                json_decode($find['data'])));
                    }
                }
            }

            //Todo Job Proceso de bloqueo de tarifa de un servicio
            if ($event->job->resolveName() == 'App\Jobs\StoreServiceRateClientsAssociations') {
                $job = unserialize($payload['data']['command']);
                $rate_logs_update = JobStatusList::where('object_id', $job->rate_plan_id)
                    ->where('entity', 'ServiceRateAssociation')
                    ->where('year', $job->year)
                    ->where('job_status', 0)
                    ->get(['id', 'user_id', 'email_notification', 'data']);
                if ($rate_logs_update->count() > 0) {
                    $user = User::find($job->user_id);
                    $rates_plans = ServiceRate::find($job->rate_plan_id);
                    foreach ($rate_logs_update as $value) {
                        $find = JobStatusList::find($value['id']);
                        $find->job_status = 1;
                        $find->save();
                        Mail::to($value['email_notification'])
                            ->send(new NotifyUserOfCompletedAssociationServiceRate($user, $job->year, $rates_plans,
                                json_decode($find['data'])));
                    }
                }
            }

            //Todo Job Proceso de bloqueo de tarifa de un servicio
            if ($event->job->resolveName() == 'App\Jobs\SaveBlockedServicesByDestination') {
                $job = unserialize($payload['data']['command']);
                $rate_logs_update = JobStatusList::where('object_id', $job->client_id)
                    ->where('entity', 'SaveBlockedServicesByDestination')
                    ->where('client_id', $job->client_id)
                    ->where('year', $job->period)
                    ->where('job_status', 0)
                    ->get(['id', 'user_id', 'email_notification', 'data']);
                if ($rate_logs_update->count() > 0) {
                    foreach ($rate_logs_update as $value) {
                        $find = JobStatusList::find($value['id']);
                        $find->job_status = 1;
                        $find->save();
                    }
                }
            }
        });

        Blade::if('env', function ($environment) {
            return app()->environment($environment);
        });

        Schema::defaultStringLength(191);
    }
}
