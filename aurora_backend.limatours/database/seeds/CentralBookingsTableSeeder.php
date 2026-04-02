<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CentralBookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function(){

            $data_tourcms = \App\TourcmsHeader::with(['reserves.reserve_file'])
                ->withTrashed()->get();

            foreach ( $data_tourcms as $tourcms ){

                $sale = ((int)( $tourcms->booking_has_net_price ) )
                    ? $tourcms->balance
                    : $tourcms->sales_revenue - $tourcms->commission;

                $tags_tourcms = json_encode([
                   "channel_id" => $tourcms->channel_id,
                    "lead_customer_email" => $tourcms->lead_customer_email,
                    "agent_code" => $tourcms->agent_code,
                    "agent_ref" => $tourcms->agent_ref,
                    "sale" => $sale,
                ]);

                $file_number = "";

                if( count( $tourcms->reserves ) > 0 ){
                    $file_number = $tourcms->reserves[ count($tourcms->reserves) - 1 ]->reserve_file->file_number;
                }

                DB::table('central_bookings')->insert([
                    "channel_name" => $tourcms->channel_name,
                    "model" => 'App\TourcmsHeader',
                    "object_id" => $tourcms->id,
                    "code" => $tourcms->booking_id,
                    "tags" => $tags_tourcms,
                    "status" => $tourcms->status,
                    "made_date_time" => $tourcms->made_date_time,
                    "start_date" => $tourcms->start_date,
                    "end_date" => $tourcms->end_date,
                    "description" => $tourcms->booking_name,
                    "passenger" => $tourcms->lead_customer_name,
                    "agent" => $tourcms->agent_name,
                    "file_number" => $file_number,
                    "aurora_code" => "",
                    "type_service" => $tourcms->status_text,
                    "quantity_pax" => $tourcms->customer_count,
                    "created_at" => $tourcms->created_at,
                    "updated_at" => $tourcms->updated_at,
                    "deleted_at" => $tourcms->deleted_at,
                ]);
            }

            $data_expedia = \App\ExtensionExpediaService::with('header','reserves.file')->get();

            foreach ( $data_expedia as $expedia ){

                $tags_expedia =
                json_encode([
                    "passenger_email" => $expedia->passenger_email,
                    "booking_state" => $expedia->booking_state,
                    "name" => $expedia->name,
                    "booking_code" => $expedia->booking_code,
                    "header_code" => $expedia->header->code,
                    "header_email" => $expedia->header->email,
                ]);

                $file_number = "";

                if( count( $expedia->reserves ) > 0 ){
                    $file_number = $expedia->reserves[ count($expedia->reserves) - 1 ]->file->file_number;
                }

                DB::table('central_bookings')->insert([
                    "channel_name" => "Expedia",
                    "model" => 'App\ExtensionExpediaService',
                    "object_id" => $expedia->id,
                    "code" => $expedia->code,
                    "tags" => $tags_expedia,
                    "status" => $expedia->status,
                    "made_date_time" => $expedia->header->date_created,
                    "start_date" => $expedia->date_start,
                    "end_date" => null,
                    "description" => $expedia->detail,
                    "passenger" => $expedia->passenger,
                    "agent" => null,
                    "file_number" => $file_number,
                    "aurora_code" => $expedia->aurora_code,
                    "type_service" => $expedia->type,
                    "quantity_pax" => $expedia->paxs,
                    "created_at" => $expedia->created_at,
                    "updated_at" => $expedia->updated_at,
                    "deleted_at" => $expedia->deleted_at,
                ]);
            }

            $data_despegar = \App\ExtensionDespegarService::with('header','reserves.file')->get();

            foreach ( $data_despegar as $despegar ){

                $tags_despegar =
                    json_encode([
                        "name" => $despegar->name,
                        "header_code" => $despegar->header->code,
                        "header_email" => $despegar->header->email,
                    ]);

                $file_number = "";

                if( count( $despegar->reserves ) > 0 ){
                    $file_number = $despegar->reserves[ count($despegar->reserves) - 1 ]->file->file_number;
                }

                DB::table('central_bookings')->insert([
                    "channel_name" => "Despegar",
                    "model" => 'App\ExtensionDespegarService',
                    "object_id" => $despegar->id,
                    "code" => $despegar->code,
                    "tags" => $tags_despegar,
                    "status" => $despegar->status,
                    "made_date_time" => $despegar->header->date_created,
                    "start_date" => $despegar->date_from,
                    "end_date" => $despegar->date_to,
                    "description" => $despegar->description,
                    "passenger" => $despegar->passenger,
                    "agent" => $despegar->detail,
                    "file_number" => $file_number,
                    "aurora_code" => $despegar->aurora_code,
                    "type_service" => $despegar->type,
                    "quantity_pax" => $despegar->adults,
                    "created_at" => $despegar->created_at,
                    "updated_at" => $despegar->updated_at,
                    "deleted_at" => null,
                ]);
            }

            $data_ppentagrama = \App\ExtensionPentagramaService::with('header','reserves.file')->get();

            foreach ( $data_ppentagrama as $pentagrama ){

                $tags_pentagrama =
                json_encode([
                    "passenger_email" => $pentagrama->passenger_email,
                    "booking_state" => $pentagrama->booking_state,
                    "name" => $pentagrama->name,
                    "booking_code" => $pentagrama->booking_code,
                    "header_code" => $pentagrama->header->code,
                    "header_email" => $pentagrama->header->email,
                ]);

                $file_number = "";

                if( count( $pentagrama->reserves ) > 0 ){
                    $file_number = $pentagrama->reserves[ count($pentagrama->reserves) - 1 ]->file->file_number;
                }

                DB::table('central_bookings')->insert([
                    "channel_name" => "Pentagrama",
                    "model" => 'App\ExtensionPentagramaService',
                    "object_id" => $pentagrama->id,
                    "code" => $pentagrama->code,
                    "tags" => $tags_pentagrama,
                    "status" => $pentagrama->status,
                    "made_date_time" => $pentagrama->header->date_created,
                    "start_date" => $pentagrama->date_start,
                    "end_date" => null,
                    "description" => $pentagrama->detail,
                    "passenger" => $pentagrama->passenger,
                    "agent" => null,
                    "file_number" => $file_number,
                    "aurora_code" => $pentagrama->aurora_code,
                    "type_service" => $pentagrama->type,
                    "quantity_pax" => $pentagrama->paxs,
                    "created_at" => $pentagrama->created_at,
                    "updated_at" => $pentagrama->updated_at,
                    "deleted_at" => $pentagrama->deleted_at,
                ]);
            }
        });
    }
}
