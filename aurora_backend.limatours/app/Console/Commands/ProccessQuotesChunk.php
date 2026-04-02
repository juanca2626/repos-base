<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Quote;

class ProccessQuotesChunk extends Command
{
    protected $signature = 'quotes:proccess-chunk';
    protected $description = '';

    public function handle()
    {
        $this->info('Iniciando procesamiento en chunks...');

        Quote::where('order_position', '>', 0)
            ->where('order_related', '>', 0)
            ->whereNull('date_received')
            ->where('created_at', '>', '2024-01-01')
            ->select('order_related') // select explícito para eficiencia
            ->chunk(50, function ($quotesChunk) {
                $orders = $quotesChunk->pluck('order_related')->toArray();

                $client = new \GuzzleHttp\Client();
                $link = sprintf('%sapi/v1/orders/all', config('services.stella.domain'));

                try {
                    $response = $client->post($link, [
                        'form_params' => [
                            'orders' => $orders,
                        ]
                    ]);

                    $data = json_decode($response->getBody(), true);
                    $this->procesarRespuesta($data);

                } catch (\Exception $e) {
                    $this->info($e->getMessage());
                }
            });

        $this->info('Procesamiento finalizado.');
    }

    protected function procesarRespuesta(array $data)
    {
        if (!isset($data['data'])) {
            return;
        }

        foreach ($data['data'] as $order) {
            $quote = Quote::where('order_related', '=', $order['nroped'])
                ->where('order_position', '=', $order['nroord'])
                ->whereNull('date_received')->first();

            if($quote)
            {
                $quote->date_received = $order['fecrec'];
                $quote->save();

                $this->info('Cotización #' . $quote->id . ' - ' . $order['fecrec']);
            }
        }
    }
}
