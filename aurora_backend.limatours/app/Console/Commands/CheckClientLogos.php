<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class CheckClientLogos extends Command
{
    protected $signature = 'check:logos';
    protected $description = 'Verifica rápidamente los logos de clientes activos usando Guzzle concurrente y actualiza los caídos.';

    protected $client;
    protected $concurrency = 30;
    protected $defaultLogo = 'https://res.cloudinary.com/litodti/image/upload/aurora/logos/LITO.jpg';

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'timeout' => 5,
            'verify' => false,
            'allow_redirects' => true,
        ]);
    }

    public function handle()
    {
        $this->info("⏳ Iniciando verificación de logos...");

        $totalOk = 0;
        $totalError = 0;
        $errors = [];

        DB::table('clients')
            ->select('id', 'name', 'logo')
            ->where('status', 1)
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->whereNotIn('logo', [
                'https://res.cloudinary.com/litodti/image/upload/aurora/logos/masi.png',
                'masi.png'
            ])
            ->orderBy('id')
            ->chunk(1000, function ($clients) use (&$totalOk, &$totalError, &$errors) {
                $requests = [];
                $clientList = $clients->values()->all();

                foreach ($clientList as $client) {
                    $url = trim($client->logo);
                    $this->line("Verificando: {$client->id} - {$client->name} - {$url}");

                    if (empty($url)) {
                        $this->updateToDefault($client);
                        $totalError++;
                        $errors[] = [
                            'id' => $client->id,
                            'name' => $client->name ?: '(sin nombre)',
                            'logo' => '(vacío)',
                            'error' => 'Sin URL (actualizado por defecto)'
                        ];
                        continue;
                    }

                    $requests[] = new Request('HEAD', $url, ['Accept' => '*/*']);
                }

                $pool = new Pool($this->client, $requests, [
                    'concurrency' => $this->concurrency,
                    'fulfilled' => function ($response, $index) use (&$totalOk, &$totalError, &$errors, $clientList) {
                        $client = $clientList[$index];
                        if ($response->getStatusCode() === 200) {
                            $this->info("✔ OK: {$client->logo}");
                            $totalOk++;
                        } else {
                            $this->error("✖ ERROR: {$client->logo} (HTTP {$response->getStatusCode()}) → logo actualizado por defecto");
                            $this->updateToDefault($client);
                            $totalError++;
                            $errors[] = [
                                'id' => $client->id,
                                'name' => $client->name ?: '(sin nombre)',
                                'logo' => $client->logo,
                                'error' => "HTTP " . $response->getStatusCode(),
                            ];
                        }
                    },
                    'rejected' => function ($reason, $index) use (&$totalError, &$errors, $clientList) {
                        $client = $clientList[$index];
                        $this->error("✖ FALLÓ: {$client->logo} → logo actualizado por defecto");
                        $this->updateToDefault($client);
                        $totalError++;
                        $errors[] = [
                            'id' => $client->id,
                            'name' => $client->name ?: '(sin nombre)',
                            'logo' => $client->logo,
                            'error' => $reason instanceof RequestException
                                ? $reason->getMessage()
                                : (string) $reason,
                        ];
                    },
                ]);

                $pool->promise()->wait();
            });

        $this->line("\n✅ Correctos: {$totalOk}");
        $this->error("❌ Fallidos (actualizados): {$totalError}\n");

        if (!empty($errors)) {
            $this->table(['ID', 'Nombre', 'Logo', 'Error'], array_slice($errors, 0, 20));
            $this->line("... mostrando primeros 20 errores.\n");
        }

        // 🔹 Actualizar masivamente los logos "masi.png" antes de verificar
        $this->updateMasiLogos();

        $this->info("✅ Verificación completada.");


    }

    /**
     * Actualiza todos los clientes con logo 'masi.png' al logo por defecto
     */
    private function updateMasiLogos()
    {
        $count = DB::table('clients')
            ->whereIn('logo', [
                'https://res.cloudinary.com/litodti/image/upload/aurora/logos/masi.png',
                'masi.png'
            ])
            ->where('status', 1)
            ->update(['logo' => $this->defaultLogo]);

        if ($count > 0) {
            $this->warn("⚠️ Se actualizaron {$count} clientes con logo 'masi.png' al logo por defecto.");
        } else {
            $this->info("✅ No se encontraron logos 'masi.png' para actualizar.");
        }
    }

    /**
     * Actualiza el logo de un cliente individualmente al logo por defecto
     */
    private function updateToDefault($client)
    {
        DB::table('clients')
            ->where('id', $client->id)
            ->update(['logo' => $this->defaultLogo]);

        $this->warn("→ Logo del cliente {$client->id} ({$client->name}) actualizado al logo por defecto.");
    }
}
