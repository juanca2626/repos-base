<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Src\Modules\File\Application\Mappers\FileCompositionSupplierMapper;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel;
use Src\Modules\File\Domain\Model\FileCompositionSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class ServiceCompositionSuppliersController extends Controller
{
    use ApiResponse;
    public function updateOrCreateSupplier(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'reservation_for_send' => 'required|string',
            'assigned' => 'required|string',
            'for_assign' => 'required|string',
            'code_request_book' => 'required|string',
            'code_request_invoice' => 'required|string',
            'code_request_voucher' => 'required|string',
            'policies_cancellation_service' => 'required|array',
            'send_communication' => 'required|string',
        ]);
          $validatedData['policies_cancellation_service'] = json_encode($validatedData['policies_cancellation_service'], true);


       $newSupplierEloquent = DB::transaction(function () use ($id, $validatedData) {
            $supplier = FileCompositionSupplierEloquentModel::where('file_service_composition_id', $id)->first();

            if ($supplier) {
                $supplier->delete();
            }

            $newSupplier = FileCompositionSupplierMapper::fromRequestCreate(array_merge($validatedData, [
                'file_service_composition_id' => $id
            ]));

            $newSupplierEloquent = FileCompositionSupplierMapper::toEloquent($newSupplier);
            $newSupplierEloquent->save();
            return $newSupplierEloquent;
        });
         return $this->successResponse(ResponseAlias::HTTP_OK, $newSupplierEloquent);
    }
}