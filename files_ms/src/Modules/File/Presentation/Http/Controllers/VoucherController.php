<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use Src\Modules\File\Infrastructure\Jobs\ProcessVoucherJob;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Src\Modules\File\Infrastructure\ExternalServices\AwsNotificationLog\AwsNotificationLog;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Traits\ApiResponse; 
use Src\Modules\File\Application\UseCases\Queries\FindFileServiceCompositionQuery;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;
use Illuminate\Support\Facades\Http;

class VoucherController extends Controller
{
   use ApiResponse;
       private FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository;


    public function __construct(FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository)
    {
        $this->fileServiceCompositionRepository = $fileServiceCompositionRepository;
            

    }
    public function triggerJob(Request $request): JsonResponse
    {
        try {
             // Obtener todos los registros relacionados con el file_id
            $fileServiceCompositions = $this->fileServiceCompositionRepository->findServicesByFileId($request->file_id);

           if (empty($fileServiceCompositions)) {
                return response()->json([
                    'message' => 'No related records found for the given file_id.',
                ], 404);
            }

            $jobsScheduled = 0;

            foreach ($fileServiceCompositions as $composition) {
               
                // Verificar si el campo use_voucher es igual a 1
                if ((int) $composition['use_voucher'] === 1) {
                    
                    // Construir datos para el Job
                    $awsData = $this->sendNotification($composition);

                    //dd($awsData);
                    // Programar el Job para ejecutarse en 1 minuto
                    ProcessVoucherJob::dispatch($awsData)->delay(now()->addMinutes(1));

                    // Programar el Job para ejecutarse en 48 horas
                   // ProcessVoucherJob::dispatch($notificationData)->delay(now()->addHours(48));

                    $jobsScheduled++;
                }
            }
            //return $this->successResponse(ResponseAlias::HTTP_OK, $response);
              return response()->json([
            'message' => "Successfully scheduled $jobsScheduled notifications.",
        ]);
            return response()->json(['message' => 'Job dispatched successfully']);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        
    }

    
  public function sendNotification($data)
    {
      
        // Construcción del mensaje que deseas enviar a SQS
        $message = [
            'queueName' => 'sqs-notifications-dev',  // Nombre de la cola
            'metadata' => [
                'origin' => 'A3-Front',
                'destination' => 'A3 && Informix',
                'user' => $data['user'] ?? '',
                'service' => 'files',
                'notify' => $data['notify'] ?? ['lsv@limatours.com.pe'],
            ],
            'payload' => [
                [
                    'template' => 'vouchers',
                    'data' => json_encode(['name' => 'Pedro Alegria']),
                    'module' => '1111111111111',
                    'submodule' => 'vouchers',
                    'object_id' => $data['id'],
                    'to' => $data['to'] ?? ['pedro.alegria@gmail.com'],
                    'cc' => $data['cc'] ?? [],
                    'bcc' => [],
                    'subject' => 'Notification Voucher',
                    'body' => '<b>Orden de compra N°: 2111212</b>',
                    'replyTo' => [],
                    'attachments' => $data['attachments'] ?? [],
                ],
            ],
        ];

        return $message; // Devolvemos los datos a enviar a la cola
    }


    public function notificationAwsLogs(Request $request){
        $aws = new AwsNotificationLog();
        $response = $aws->getLogs($request->input('object_id'));
        return $this->successResponse(ResponseAlias::HTTP_OK, $response);
    }


    public function getAllServicesByFile(int $fileId)
    {
        try {
             // Obtener todos los registros relacionados con el file_id
            $fileServiceCompositions = $this->fileServiceCompositionRepository->findServicesByFileId($fileId);

           if (empty($fileServiceCompositions)) {
                return response()->json([
                    'message' => 'No related records found for the given file_id.',
                ], 404);
            }
                return $this->successResponse(ResponseAlias::HTTP_OK, $fileServiceCompositions);
               
            } catch (\DomainException $domainException) {
                return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    
}