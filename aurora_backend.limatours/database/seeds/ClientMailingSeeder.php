<?php

use App\Client;
use App\ClientMailing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientMailingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('client_mailing')->truncate();
        $clients = Client::select('id', 'code')->status(1)
        ->whereNotIn('code', ['9GETYG', '5DESPE', '2HBEDS', '5DESPE', '5VIAT', '2EXPE', '2CRAIG', '2LEX', '2CELEB', '3REITV', '3STEXP',
        '2SAEX', '3ABD', '3PRINT', '3HOLLA', '2SILVE', '2GOHAG', '3CRYST', '3PWPTO', '2INFI', '2AZAMA', '9PRIMA',
        '9TWR', '9FOXT', '6WEXTG', '6SATSP', '6INCAT', '9RIVER', '7TORBI', '7MIKJP', '7ISMJP', '7OOT', '7HIS',
        '7THOMP', '9IDEAT', '2CRINT', '9WENDY', '9VIVI', '9THESO', '9CRUEX', '9COSMT', '9BETRA', '9INTU', '9FIELD'])->get();
        $client_mailing_data = [];
        foreach ($clients as $client) {
            $weekly = 1;
            $day_before = 1;
            $daily = 1;
            $survey = 1;
            $whatsapp = 1;
            $status = 1;
            if (in_array($client->code, array('4TERRA', '4KIVOT', '9ALIDA'))) {
                $weekly = 0;
            }
            if (in_array($client->code, array('4KIVOT'))) {
                $day_before = 0;
            }
            if (in_array($client->code, array(
                '4EVAC', '2YAMP', '2ALLUR', '9GEBEC', '9SUREI', '9HPLAN',
                '4TERRA', '4KIVOT', '2EXPIN'))) {
                $survey = 0;
            }
            $client_mailing_data[] = [
                'clients_id' => $client->id,
                'weekly' => $weekly,
                'day_before' => $day_before,
                'daily' => $daily,
                'survey' => $survey,
                'whatsapp' => $whatsapp,
                'status' => $status
            ];
        }

        foreach ($client_mailing_data as $client_mailing){
            ClientMailing::create($client_mailing);
        }
    }
}
