<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class HotelTranslationImport implements ToCollection
{
    /**
     * @param  Collection  $rows
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                DB::table('hotels')
                    ->where('id', (int)$row[0])->update(
                        ['name' => rtrim($row[1])]
                    );
                DB::table('translations')->where('type', 'hotel')
                    ->where('slug', 'hotel_description')
                    ->where('language_id', 1)
                    ->where('object_id', (int)$row[0])->update(
                        ['value' => rtrim($row[2])]
                    );
                DB::table('translations')->where('type', 'hotel')
                    ->where('slug', 'hotel_description')
                    ->where('language_id', 2)
                    ->where('object_id', (int)$row[0])->update(
                        ['value' => rtrim($row[3])]
                    );
                DB::table('translations')->where('type', 'hotel')
                    ->where('slug', 'hotel_description')
                    ->where('language_id', 3)
                    ->where('object_id', (int)$row[0])->update(
                        ['value' => rtrim($row[4])]
                    );
            }
        });
    }
}
