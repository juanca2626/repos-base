<?php

use App\Client;
use App\ClientContact;
use Illuminate\Database\Seeder;

class ClientContactsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_client_contact = File::get("database/data/client_contacts.json");
        $data_json_classifications = json_decode($file_client_contact, true);
        foreach ($data_json_classifications as $data_client_contact) {

            $client = Client::where('code', $data_client_contact["client_code"]);

            if( $client->count() > 0 ){
                $newClass = new ClientContact();
                $newClass->client_id = $client->first()->id;
                $newClass->order = $data_client_contact["order"];
                $newClass->type_code = $data_client_contact["type_code"];
                $newClass->name = $data_client_contact["name"];
                $newClass->surname = $data_client_contact["surname"];
                $newClass->email = $data_client_contact["email"];
                $newClass->phone = $data_client_contact["phone"];
                $newClass->birthday = $data_client_contact["birthday"];
                $try_date = explode('/', $data_client_contact["birthday"]);
                $newClass->birthday_date =
                    ( count($try_date) > 2 )
                        ? convertDate($data_client_contact["birthday"], '/', '-', 1)
                        : $data_client_contact["birthday"];
                $newClass->save();
            }
        }
    }
}
