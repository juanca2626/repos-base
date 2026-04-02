<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatusReasonEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\CreateFileStela;

class ProcessFileCreateFileStelaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
    use CreateFileStela;

    private array $params;

    /**
     * Create a new job instance.
     */
    public function __construct(array $params)
    {         
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    

        $results = $this->searchFileStela($this->params);        
        $clients = $this->getClientAurora($results);
        $exists = $this->getFilesExists($results);

        foreach($results as $file)
        {
            if(in_array($file['file_number'], $exists))
            {
                array_push($file_exists, $file);
                continue;
            }

            $client_id = NULL;
            $client_name = NULL;
            $stela_processing = 3;
            $stela_processing_error = "cliente no existe en aurora"; 

            if(isset($clients[$file['client_code']])){
                $client_id = $clients[$file['client_code']]['id'];
                $client_name = $clients[$file['client_code']]['name'];  
                $stela_processing = 1;
                $stela_processing_error = "";                    
            } 
                                
            $file['markup_client'] = 0;
            $file['file_number'] = $file['file_number'];
            $file['client_id'] = $client_id;
            $file['client_code'] = $file['client_code'];
            $file['client_name'] = $client_name;
            $file['executive_code'] = $file['executive_code'];
            $file['executive_code_process'] = $file['executive_code_process'];
            $file['executive_code_sale'] = $file['executive_code']; 
            $file['status'] = $file['status'];
            $file['date_in'] = $file['date_in'];
            $file['date_out'] = $file['date_in'];
            $file['adults'] = $file['adults'];
            $file['children'] = $file['children'];
            $file['infants'] = $file['infants'];            
            $file['description'] = $file['description'];
            $file['type_class_id'] = null;
            $file['suggested_accommodation_sgl'] = $file['suggested_accommodation_sgl'];
            $file['suggested_accommodation_dbl'] = $file['suggested_accommodation_dbl'];
            $file['suggested_accommodation_tpl'] = $file['suggested_accommodation_tpl'];
            $file['generate_statement'] = false;
            $file['reason_statement_id'] = null;
            $file['protected_rate'] = false;
            $file['view_protected_rate'] = false;
            $file['origin'] = 'stela';
            $file['stela_processing'] = $stela_processing;
            $file['stela_processing_error'] = $stela_processing_error;

            $fileEloquentModel = $this->fileToEloquentByStela($file);
            $fileEloquentModel->save();
            FileStatusReasonEloquentModel::create([
                'file_id' => $fileEloquentModel->id, 
                'status' => $file['status'], 
                'status_reason_id' => 1
            ]);
 
        }   
    }

    public function getFilesExists($results): array
    {
        $files = [];
        foreach($results as $result){
            array_push($files, $result['file_number']);
        }

        $files_db = FileEloquentModel::whereIn('file_number', $files)->pluck('file_number')->toArray();

        return $files_db;
    }

    
}
