<?php
 
namespace Src\Modules\FileV2\Application\Transformers;

class FileListTransformer
{
    public static function transform($file): array
    {
        return [
            'id' => $file->id,
            'file_number' => $file->file_number,
            'client_code' => $file->client_code,
            'status' => $file->status,
            'date_in' => $file->date_in,
            'date_out' => $file->date_out,

            // flags (de tus subqueries)
            'has_itinerary' => !is_null($file->itinerary),
            'has_vip' => !is_null($file->vip),
        ];
    }
}