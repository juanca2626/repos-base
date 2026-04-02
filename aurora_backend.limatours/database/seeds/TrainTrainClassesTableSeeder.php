<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainTrainClassesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_train_train_classes = File::get("database/data/train_train_classes.json");
        $train_train_classes = json_decode($file_train_train_classes, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($train_train_classes,$created_at) {
            foreach ($train_train_classes as $train_train_class) {
                DB::table('train_train_classes')->insert([
                    'id' => $train_train_class["id"],
                    'code' => $train_train_class["code"],
                    'train_class_id' => $train_train_class["train_class_id"],
                    'train_id' => $train_train_class["train_id"],
                    'name' => $train_train_class["name"],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
            }
        });
    }
}
