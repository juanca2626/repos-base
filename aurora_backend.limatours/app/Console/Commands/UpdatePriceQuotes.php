<?php

namespace App\Console\Commands;

use App\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePriceQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotes:update_price_estimated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Quote::where(function ($query) {
            $query->orWhere('estimated_price', '=', 0);
            $query->orWhereNull('estimated_price');
        })
        ->where('status', '=', 1)
        ->chunk(20, function ($quotes) {

            $lang = 'en'; $baseUrlExtra = 'https://auroraback.limatours.com.pe';

            foreach($quotes as $quote)
            {
                if(empty($quote->estimated_price) || $quote->estimated_price == 0)
                {
                    $mount_total = NULL;

                    if($quote->operation == 'passengers')
                    {
                        $quote_people = DB::table('quote_people')
                            ->where('quote_id', '=', $quote->id)
                            ->first();

                        if($quote_people)
                        {
                            $client = new \GuzzleHttp\Client();
                            $request = $client->get($baseUrlExtra . '/quote_passengers_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                            $response = (array) json_decode($request->getBody()->getContents(), true);
                            $mount_total = NULL; $count = 0; $accommodations = ['', 'SGL', 'DBL', 'TPL'];

                            if(isset($response['data'][0]['passengers']))
                            {
                                foreach($response['data'][0]['passengers'] as $k => $v)
                                {
                                    $_name = explode("-", $v['first_name']);
                                    $_key = array_search(trim(last($_name)), $accommodations);

                                    if($_key > 0)
                                    {
                                        $count += $_key;
                                    }
                                    else
                                    {
                                        $count += 1;
                                    }

                                    if($mount_total == NULL)
                                    {
                                        $mount_total = (float) $v['total'];
                                    }
                                    else
                                    {
                                        $mount_total += (float) $v['total'];
                                    }
                                }
                            }

                            if(((float) $quote_people->adults + (float) $quote_people->child) > $count)
                            {
                                if($mount_total != NULL AND $mount_total > 0)
                                {
                                    $mount_total = (float) ($mount_total * $quote_people->adults);
                                }
                            }
                        }
                    }

                    if($quote->operation == 'ranges')
                    {
                        $client = new \GuzzleHttp\Client();
                        $request = $client->get($baseUrlExtra . '/quote_ranges_frontend?quote_id=' . $quote->id . '&lang=' . $lang);
                        $response = (array) json_decode($request->getBody()->getContents(), true);

                        if(isset($response['ranges']))
                        {
                            foreach($response['ranges'] as $k => $v)
                            {
                                if($mount_total == NULL)
                                {
                                    $mount_total = (float) $v['promedio'];
                                }
                                else
                                {
                                    if($v['promedio'] <= $mount_total AND $v['promedio'] > 0)
                                    {
                                        $mount_total = (float) $v['promedio'];
                                    }
                                }
                            }
                        }
                    }

                    $mount_total = (float) number_format($mount_total, 4, ".", "");
                    $quote->estimated_price = $mount_total;
                    $quote->save();
                }
            }
        });
    }
}
