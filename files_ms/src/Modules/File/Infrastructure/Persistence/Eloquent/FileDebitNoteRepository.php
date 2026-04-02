<?php

namespace Src\Modules\File\Infrastructure\Persistence\Eloquent;
 
use Illuminate\Support\Facades\DB;   
use Src\Modules\File\Domain\Repositories\FileDebitNoteRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCreditNoteDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCreditNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileDebitNoteDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileDebitNoteEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;  

class FileDebitNoteRepository implements FileDebitNoteRepositoryInterface
{
 
    
    public function searchAll(int $file_id,array $filters): array
    {
        $fileCreditNote = FileDebitNoteEloquentModel::with('details')->where('file_id', $file_id)->get()->toArray();        
        return $fileCreditNote;
    }
    
    /**
     * @param int $id 
     * @return bool
     */
    public function create(int $file_id, array $params): bool
    { 
        $fileEloquent = FileEloquentModel::with('debit_notes.details')->findOrFail($file_id); 

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
            $file_debit_note_id = null;
 
            if( isset($fileEloquent->debit_notes) and $fileEloquent->debit_notes->count()>0){ 
                $fileEloquent->debit_notes->date = date('Y-m-d');
                $fileEloquent->debit_notes->total = $total + $fileEloquent->debit_notes->total;
                $fileEloquent->debit_notes->save();
                $file_debit_note_id = $fileEloquent->debit_notes->id;
            }else{

                $fileDebitNoteEloquentModel = FileDebitNoteEloquentModel::create([
                    'file_id' => $fileEloquent->id, 
                    'date' => date('Y-m-d'),  
                    'total' => $total 
                ]);   
                $file_debit_note_id = $fileDebitNoteEloquentModel->id;
            }


            foreach($credit_note_details as $detail)
            {            
                FileDebitNoteDetailEloquentModel::create([
                    'file_debit_note_id' => $file_debit_note_id, 
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
    public function update(int $file_id, int $debit_node_id,  array $params): bool
    { 
        $fileCreditNoteEloquentModel = FileDebitNoteEloquentModel::with('details')->findOrFail($debit_node_id); 

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
                FileDebitNoteDetailEloquentModel::create([
                    'file_debit_note_id' => $fileCreditNoteEloquentModel->id, 
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
    public function delete(int $file_id, int $debit_node_id): bool
    {
        $fileCreditNoteEloquentModel = FileDebitNoteEloquentModel::with('details')->findOrFail($debit_node_id);
        $fileCreditNoteEloquentModel->details()->delete();
        $fileCreditNoteEloquentModel->delete();
        return true;
    }
 
}
