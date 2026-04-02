<?php

namespace App\Http\Multichannel\Hyperguest\Jobs;

use App\Http\Multichannel\Hyperguest\Services\HotelSyncService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class HotelSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $limit = null;
    private $countries = [];
    private $from = 0;
    private $hotel_ids = [];

    public function __construct(int $limit = 0, array $countries = [], int $from = 0, array $hotel_ids = [])
    {
        if ($limit > 0) {
            $this->limit = $limit;
        }

        $this->countries = $countries;
        $this->from = $from;
        $this->hotel_ids = $hotel_ids;

        // Define the queue name
        $this->queue = 'sync_hyperguest_pull';
    }

    public function handle(HotelSyncService $service)
    {
        try {
            $service->sync(
                $this->limit,
                $this->countries,
                $this->from,
                $this->hotel_ids
            );
        } catch (Exception $e) {

        } catch (GuzzleException $e) {

        } catch (\Throwable $e) {

        }
    }
}
