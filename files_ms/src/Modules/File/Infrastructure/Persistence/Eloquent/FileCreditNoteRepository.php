<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
 
use Illuminate\Support\Facades\DB;   
use Src\Modules\File\Domain\Repositories\FileCreditNoteRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCreditNoteDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCreditNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;

class FileCreditNoteRepository implements FileCreditNoteRepositoryInterface
{
 
    
    public function searchAll(int $file_id,array|null $filters): array
    {
        $fileCreditNote = FileCreditNoteEloquentModel::with('details')->where('file_id', $file_id)->get()->toArray();        
        return $fileCreditNote;
    }
    
    /**
     * @param int $id 
     * @return bool
     */
    public function create(int $file_id, array $params): bool
    { 
        $fileEloquent = FileEloquentModel::with('credit_notes.details')->findOrFail($file_id); 

        $credit_note_details = collect($params['details']);
        
        if($credit_note_details->count() == 0)
        {
            throw new \DomainException("There must be at least 1 record");
        }
        
        $credit_note_details = $credit_note_details->map(function($detail) {
            $detail['amount'] = $detail['quantity'] * $detail['unit_price'];         
            return $detail;
        });
        
        DB::transaction(function () use ($fileEloquent, $credit_note_details) { 

            $total = $credit_note_details->sum('amount'); 
            $file_credit_note_id = null;
 
            if( isset($fileEloquent->credit_notes) and $fileEloquent->credit_notes->count()>0){ 
                $fileEloquent->credit_notes->date = date('Y-m-d');
                $fileEloquent->credit_notes->total = $total + $fileEloquent->credit_notes->total;
                $fileEloquent->credit_notes->save();
                $file_credit_note_id = $fileEloquent->credit_notes->id;
            }else{

                $fileCreditNoteEloquentModel = FileCreditNoteEloquentModel::create([
                    'file_id' => $fileEloquent->id, 
                    'date' => date('Y-m-d'), 
                    'total' => $total 
                ]);  
                $file_credit_note_id = $fileCreditNoteEloquentModel->id;
            } 

            foreach($credit_note_details as $detail)
            {            
                FileCreditNoteDetailEloquentModel::create([
                    'file_credit_note_id' => $file_credit_note_id, 
                    'description' => $detail['description'], 
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'amount' => $detail['amount'] 
                ]);                                          
            }          
                    
        }); 

        return true;
    }
 

        /**
     * @param int $id 
     * @return bool
     */
    public function update(int $file_id, int $credit_node_id,  array $params): bool
    { 
        $fileCreditNoteEloquentModel = FileCreditNoteEloquentModel::with('details')->findOrFail($credit_node_id); 

        $credit_note_details = collect($params['details']);
        
        if($credit_note_details->count() == 0)
        {
            throw new \DomainException("There must be at least 1 record");
        }
        
        $credit_note_details = $credit_note_details->map(function($detail) {
            $detail['amount'] = $detail['quantity'] * $detail['unit_price'];         
            return $detail;
        });
        
        DB::transaction(function () use ($fileCreditNoteEloquentModel, $credit_note_details) { 

            $subTotal = $credit_note_details->sum('amount'); 
            $igv = config('global.igv') * $subTotal; 
            $total = $subTotal + $igv; 
        
            $fileCreditNoteEloquentModel->details()->delete();
            
            foreach($credit_note_details as $detail)
            {                            
                FileCreditNoteDetailEloquentModel::create([
                    'file_credit_note_id' => $fileCreditNoteEloquentModel->id, 
                    'description' => $detail['description'], 
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'amount' => $detail['amount'] 
                ]);                                                                     
            }                                                         
 
            $fileCreditNoteEloquentModel->total = $total; 
        }); 

        return true;
    }

    /**
     * @param int $id 
     * @return bool
     */
    public function delete(int $file_id, int $credit_node_id): bool
    {
        $fileCreditNoteEloquentModel = FileCreditNoteEloquentModel::with('details')->findOrFail($credit_node_id);
        $fileCreditNoteEloquentModel->details()->delete();
        $fileCreditNoteEloquentModel->delete();
        return true;
    }

}
