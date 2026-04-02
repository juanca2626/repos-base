<?php

namespace App\Imports;

use App\ServiceOperationActivity;
use App\ServiceTypeActivity;
use App\Translation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class OperabilityImport implements ToCollection
{
    public $type = 'translations';

    public function __construct($type = 'translations')
    {
        $this->type = $type;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $langs = ['es', 'en', 'pt']; $type = $this->type;

        DB::transaction(function () use ($rows, $langs, $type)
        {
            foreach ($rows as $index => $row)
            {
                if((int) $row[0] > 0)
                {
                    $count = ServiceTypeActivity::where('id', $row[0])
                        ->whereNull('deleted_at')
                        ->count();

                    if($count == 1)
                    {
                        if($type == 'translations')
                        {
                            foreach($langs as $k => $v)
                            {
                                $translation = Translation::whereNull('deleted_at')
                                    ->where('type', 'servicetypeactivity')
                                    ->where('language_id', ($k + 1))
                                    ->where('slug', 'type_activity_name')
                                    ->where('object_id', $row[0])
                                    ->first();

                                if ($translation == '' OR $translation == null)
                                {
                                    $translation = new Translation;
                                    $translation->type = 'servicetypeactivity';
                                    $translation->slug = 'type_activity_name';
                                    $translation->language_id = ($k + 1);
                                    $translation->object_id = $row[0];
                                }

                                $translation->value = $row[($k + 1)];
                                $translation->save();
                            }
                        }

                        if($type == 'refactor')
                        {
                            if($row[5] != null AND $row[5] != '')
                            {
                                $ids = explode("/", $row[5]);

                                foreach($ids as $k => $v)
                                {
                                    $v = trim($v);

                                    ServiceOperationActivity::where('service_operation_id', $v)
                                        ->update([
                                            'service_operation_id' => $row[0]
                                        ]);

                                    // ServiceTypeActivity::where('id', $v)->delete();
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
