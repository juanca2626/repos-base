<?php
/****
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Src\Modules\File\Application\UseCases\SendVoucherNotificationUseCase;

class SendVoucherNotification extends Command
{
    protected $signature = 'voucher:send-notification';
    protected $description = 'Send voucher notification 48 hours before the service start time';

    private FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository;
    private SendVoucherNotificationUseCase $sendVoucherNotificationUseCase;

    public function __construct(
        FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository,
        SendVoucherNotificationUseCase $sendVoucherNotificationUseCase
    ) {
        parent::__construct();
        $this->fileServiceCompositionRepository = $fileServiceCompositionRepository;
        $this->sendVoucherNotificationUseCase = $sendVoucherNotificationUseCase;
    }

    public function handle()
    {
        try {
            // Obtener todos los servicios que comienzan dentro de 48 horas
            $services = $this->fileServiceCompositionRepository->findServicesStartingSoon();

            // Verificar si hay servicios para procesar
            if ($services->isEmpty()) {
                Log::info('No services found for notification.');
                return;
            }

            foreach ($services as $service) {
                // Llamar al UseCase para enviar la notificación
                $this->sendVoucherNotificationUseCase->execute($service);
            }

            $this->info('Notifications sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error sending voucher notifications', ['error' => $e->getMessage()]);
        }
    }

    
}
***/