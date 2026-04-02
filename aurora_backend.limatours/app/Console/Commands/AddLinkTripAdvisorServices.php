<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceTranslation;
use Illuminate\Console\Command;

class AddLinkTripAdvisorServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:link_trip_advisor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Links Trip Advisor Services';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $services_links = [
            [
                "service_code"=>"LIN40I",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294316-d15766700-Lima_City_Tour_and_Larco_Museum-Lima_Lima_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294316-d15766700-Lima_City_Tour_and_Larco_Museum-Lima_Lima_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"LIN435",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294316-d19915378-Pachacamac_Archaeological_Complex_and_Barranco-Lima_Lima_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294316-d19915378-Pachacamac_Archaeological_Complex_and_Barranco-Lima_Lima_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"LIN618",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294316-d15810124-Magic_Water_Circuit_with_Dinner_and_Folkloric_Show-Lima_Lima_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294316-d15810124-Magic_Water_Circuit_with_Dinner_and_Folkloric_Show-Lima_Lima_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"CUZ431",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d15830793-Cusco_City_Tour_and_Nearby_Archaeological_Sites-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d15830793-Cusco_City_Tour_and_Nearby_Archaeological_Sites-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"CUZ557",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d15830090-Full_Day_Tour_to_Sacred_Valley_Ollantaytambo_Chinchero_Yucay_Museum_and_Lunch-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d15830090-Full_Day_Tour_to_Sacred_Valley_Ollantaytambo_Chinchero_Yucay_Museum_and_Lunch-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"CUZ5PS",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d19011948-Full_Day_Hike_to_The_Rainbow_Mountain_Vinicunca-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d19011948-Full_Day_Hike_to_The_Rainbow_Mountain_Vinicunca-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"CUZ441",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d21169234-Half_Day_Tour_to_Maras_and_Moray-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d21169234-Half_Day_Tour_to_Maras_and_Moray-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"CUZ437",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d11451403-Half_Day_Tour_to_Tipon_Pikillaqta_and_Andahuaylillas-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d11451403-Half_Day_Tour_to_Tipon_Pikillaqta_and_Andahuaylillas-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
            [
                "service_code"=>"MPI500",
                "links"=>[
                    [
                        "language_id"=>1,
                        "link"=>"https://www.tripadvisor.com.pe/UserReviewEdit-g294314-d15830091-Full_Day_Machu_Picchu_Tour_From_Cusco-Cusco_Cusco_Region.html"
                    ],
                    [
                        "language_id"=>2,
                        "link"=>"https://www.tripadvisor.com/UserReviewEdit-g294314-d15830091-Full_Day_Machu_Picchu_Tour_From_Cusco-Cusco_Cusco_Region.html"
                    ]
                ]
            ],
        ];


        foreach ($services_links as $services_link){

            $service_id = Service::select('id')->where('aurora_code',$services_link["service_code"])->first()->id;

            $service_translation_spanish = ServiceTranslation::where('language_id',1)->where('service_id',$service_id)->first();

            $service_translation_english = ServiceTranslation::where('language_id',2)->where('service_id',$service_id)->first();


            foreach ($services_link["links"] as $link){
                if ($link["language_id"]== 1){
                    $service_translation_spanish->link_trip_advisor = $link["link"];
                    $service_translation_spanish->save();
                }
                if ($link["language_id"]== 2){
                    $service_translation_english->link_trip_advisor = $link["link"];
                    $service_translation_english->save();
                }
            }
        }

        $this->info('Links de trip advisor actualizados');
    }
}
