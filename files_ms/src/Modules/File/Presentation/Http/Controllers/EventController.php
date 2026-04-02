<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EventBridgeService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Traits\ApiResponse;
use Src\Modules\File\Application\Mappers\FileServiceCompositionMapper;
use Src\Modules\File\Domain\Events\FilePassToOpeEvent;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel;
use Src\Modules\File\Infrastructure\Jobs\ProcessFilePassToOpeJob;
use Src\Modules\File\Presentation\Http\Traits\AuthUser;

class EventController extends Controller
{

    use ApiResponse, AuthUser;

    protected $eventBridgeService;

    public function __construct(EventBridgeService $eventBridgeService)
    {
        $this->eventBridgeService = $eventBridgeService;
    }

    public function sendEvent(Request $request)
    {

        $detail = [
            'key1' => 'Juan Carlos',
            'key2' => 'value2',
        ];
        $source = 'a3.files';
        $detailType = 'file.to-ope';
        $eventBusName = 'default'; // o el nombre de tu bus de eventos

        $result = $this->eventBridgeService->putEvent($detail, $source, $detailType, $eventBusName);

        return response()->json($result);
    }
    

    public function user(Request $request)
    {
        try{
            $user = $this->getAuthUser($request);
            return response()->json($user['id']);
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\DomainException $domainException) {
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        // dd($request->header('authorization'));

    }

    public function update_send_communication(Request $request)
    {
        try{

            FileCompositionSupplierEloquentModel::where('send_communication', 0)->chunk(5000, function ($suppliers) {
                foreach ($suppliers as $supplier) {
                     if($supplier->reservation_for_send == 1){
                        $supplier->send_communication = 'S';
                     }else{
                        $supplier->send_communication = 'N';
                     }
                     $supplier->save();
                }
            });

            return response()->json("procesado");
        }catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }


    }

    public function sqs(Request $request)
    {
         dd(config('services.amazon.env_master_service'));
    }

    public function sendToOpe(Request $request)
    {
        ProcessFilePassToOpeJob::dispatchSync($request->file_id);
        // event(new FilePassToOpeEvent($request->file_id));
    }
}
