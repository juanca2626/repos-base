<?php

namespace App\Jobs;

use App\Reservation;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\ChannelLogs;

class WebhookN8nCreateFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ChannelLogs;

    protected $reservation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $baseUrl = config('services.n8n.webhook');

            if (empty($baseUrl)) {
                return;
            }

            // Load necessary relationships to get the data
            $this->reservation->load(['client', 'reservationsHotel', 'reservationsService', 'executive']);

            $datesCheckIn = collect();
            $datesCheckOut = collect();

            if ($this->reservation->reservationsHotel->count() > 0) {
                foreach ($this->reservation->reservationsHotel as $reservationHotel) {
                    $datesCheckIn->add($reservationHotel['check_in']);
                    $datesCheckOut->add($reservationHotel['check_out']);
                }
            }

            if ($this->reservation->reservationsService->count() > 0) {
                foreach ($this->reservation->reservationsService as $reservationService) {
                    $datesCheckIn->add($reservationService['date']);
                    $datesCheckOut->add($reservationService['date']);
                }
            }

            $payload = [
                'fileNumber' => $this->reservation->file_code,
                'groupName' => $this->reservation->customer_name,
                'dateIn' => $datesCheckIn->min(),
                'dateOut' => $datesCheckOut->max(),
                'clientName' => $this->reservation->client ? $this->reservation->client->code : null,
                'advisorEmail' => $this->reservation->executive ? $this->reservation->executive->email : null,
                'advisorName' => $this->reservation->executive ? $this->reservation->executive->name : null,
            ];

            Log::info('Webhook N8n Create File', $payload);

            // Send to webhook via POST using Guzzle
            $url = $baseUrl . '/create-files-limatours';
            $status = false;
            $responseBody = '';

            $headers = [
                'Content-Type' => 'application/json'
            ];

            if ($url) {
                $client = new Client();
                $response = $client->post($url, [
                    'json' => $payload,
                    'headers' => $headers
                ]);
                $status = true;
                $responseBody = $response->getBody()->getContents();
            }

            Log::info('Webhook N8n Create File - responseBody ', ['responseBody' => $responseBody]);

            $this->putXmlLogAurora(
                $url,
                json_encode($payload),
                $responseBody,
                json_encode($headers),
                '',
                'webhook-n8n-create-file',
                $this->reservation->id,
                $status,
                'json'
            );

        } catch (\Exception $e) {

            $this->putXmlLogAurora(
                config('services.n8n.webhook') . '/create-files-limatours',
                json_encode($payload ?? []),
                $e->getMessage(),
                json_encode($headers ?? []),
                '',
                'webhook-n8n-create-file',
                $this->reservation->id ?? null,
                false,
                'json'
            );
        }
    }
}
