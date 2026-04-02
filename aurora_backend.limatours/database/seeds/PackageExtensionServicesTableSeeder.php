<?php

use App\Language;
use App\PackagePlanRate;
use App\Service;
use App\TypeClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageExtensionServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function sumar_anios($fecha, $nAnios){
            $days = $nAnios * 365;
            $nuevafecha = strtotime ( '+' . $days . 'day' , strtotime ( $fecha ) ) ;
            return date ( 'Y-m-j' , $nuevafecha );
        }

        $file_package_services = File::get("database/data/package_extension_services.json");
        $json_package_services = json_decode($file_package_services, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($json_package_services,$created_at) {

            // Crear UNA CATEGORIA CON CODIGO X INCLUYENDO SUS TRADUCCIONES, PARA PONER POR MIENTRAS, (VERIFICAR Q NO EXISTA)
            $findTypeClass = TypeClass::where('code','X');
            if( $findTypeClass->count() == 0 ){
                $type_class_id = DB::table('type_classes')->insertGetId([
                    'code' => 'X',
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                $languages = Language::select('id')->get();
                $translations = [];
                foreach ($languages as $language) {
                    $translations[$language->id] = ['id' => '', 'typeclass_name' => 'Basico'];
                }
                $this->saveTranslation($translations, 'typeclass', $type_class_id);

            } else{
                $findTypeClass = $findTypeClass->first();
                $type_class_id = $findTypeClass->id;
            }

            $news_package_plan_rate_category_id = [];

            foreach ($json_package_services as $package_service) {
                $nropla = $package_service['package_plan_rate_code'];
                if( !(isset($news_package_plan_rate_category_id[$nropla])) ){
                    $package_plan_rate = PackagePlanRate::where('code',$nropla)->first();

                    $idCategory = DB::table('package_plan_rate_categories')->insertGetId([
                        'package_plan_rate_id' => $package_plan_rate->id,
                        'type_class_id' => $type_class_id
                    ]);

                    $news_package_plan_rate_category_id[$nropla] = $idCategory;
                }

                if( $package_service['type'] == 'hotel' ){

                    $hotel = DB::table('channel_hotel')->where('code', $package_service['code'])->first();
//                    var_export( $hotel ); die;
                    $object_id = $hotel->hotel_id;
                    $adult = $package_service['single'] + $package_service['double'] + $package_service['triple'];
                } else {
                    $service = Service::where('equivalence_aurora',$package_service['nroequ'])->first();
                    $object_id = $service->id;
                    $adult = $package_service['single'];
                }

                $year_date_in = (int) substr( $package_service['date_in'],0,4 );
                $year_date_out = (int) substr( $package_service['date_out'],0,4 );

                if( $year_date_in < 2020 ) {
                    $new_date_in = sumar_anios($package_service['date_in'], (2020 - $year_date_in));
                    $new_date_out = sumar_anios($package_service['date_out'],(2020 - $year_date_out));
                } else {
                    $new_date_in = $package_service['date_in'];
                    $new_date_out = $package_service['date_out'];
                }

                DB::table('package_services')->insert([
                    'type' => $package_service['type'],
                    'object_id' => $object_id,
                    'package_plan_rate_category_id'=>$news_package_plan_rate_category_id[$nropla],
                    'order' => $package_service['order'],
                    'date_in' => $new_date_in,
                    'date_out' => $new_date_out,
                    'adult' => $adult,
                    'child' => 0,
                    'infant' => 0,
                    'single' => $package_service['single'],
                    'double' => $package_service['double'],
                    'triple' => $package_service['triple'],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

            };
        });

    }
}
