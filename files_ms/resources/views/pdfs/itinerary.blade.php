<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            font-size: 11px;
            text-align: justify;
            font-family: sans-serif;
        }
        /* @page {
            margin: 0;
        }*/
        
        .first-page {
            position: absolute;
            top: -50px;
            left: -50px;
            width: 115%;
            height: 110%;
            z-index: -1;
        }
        .content {
            page-break-before: always;
            /* padding: 2cm; */
        }
        .title {
            color: #5A5A58;
            font-size: 10px
        }
        .tour-title, .tour-subtitle {
            color: #B3B182;
            font-weight: bold;
        }
        .tour-subtitle {
            margin-bottom: 8px;
        }
        .tours .item {
            margin-bottom: 8px;
            color: #808080;
        }
        .tours li {
            color: #545454;
            font-size: 10px;
            margin-bottom: 3px;
            font-weight: bold;
        }        
        .divisor {
            height: 2px;
            border: none;
            background-color: #AEA792;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #5A5A58;
        }
        table th {
            background-color: #A69F88;
            border: 1px solid #5A5A58;
        }
        table td {
            text-align: center;
            border: 1px solid #5A5A58;
        }
        section {
            margin-bottom: 15px;
            color: #5A5A58;
        }
    </style>
</head>
<body>
 
    @if($portadaURL != "") 
        <img class="first-page" src="data:image/png;base64,{{ base64_encode(file_get_contents($portadaURL)) }}" alt="">
    @endif
 
    <div class="content">
        <h3 class="title">{{ $trad->dayToday }}</h3>
        <hr class="divisor">
        <div class="tours">
            @foreach ($itineraries['itineraries'] as $key => $tour)
                <div>
                    <div class="tour-title">{{ $trad->day . ' ' . ($tour['day']).' | '. $tour['city_in_name'] . "-" . $tour['city_out_name'] }} </div>
                    <div class="tour-subtitle"> {{ $tour['date'] }} </div>
                    @foreach ($tour['itineraries'] as $item)

                        @if($item['entity'] == 'hotel')

                        <ul>                      
                            <li> {{ $trad->accommodation . ' ' . $item['name'].' ' }} @if ($item['hotel_detail']['url']) <a href="{{ $item['hotel_detail']['url']}}">{{$item['name']}}</a> @endif </li>
                            <li> {{ 'CHECKIN: ' . $item['start_time'].' CHECKOUT: ' . $item['departure_time'] }} </li>
                            <li> {{  $trad->supplyIncluded . ' ' . $item['hotel_detail']['meal'].' ' }} </li>
                        </ul>

                        @elseif($item['entity'] == 'flight')
                        
                            <div class="item">{{ $item['start_time'].' '.$item['city_in_name'] . " / " . $item['city_out_name'] . " - " . $item['airline_name'] . " " . $item['airline_number'] }} {{ $item['description']}} </div>

                        @else
                            <div class="item">{{ $item['start_time'] }} {{ $item['description']}} </div>
                        @endif
                    
                    @endforeach
                    
                </div>
                <br><br>
            @endforeach
        </div>
        <br><br>
        <h3 style="color: #b3b182;text-align:center;background-color: #DAD7C6;border-top: 3px solid #B3B182;border-bottom: 3px solid #B3B182">{{ $trad->endService }}</h3>
        <br>
        <h3 class="title">{{ $trad->tltHotel }}</h3>
        <table>
            <thead style="color:#fff">
                <th>{{ $trad->thCity }}</th>
                <th>{{ $trad->thHotel }}</th>
                <th>{{ $trad->thConfirmation }}</th>
                <th>{{ $trad->thtipHab }}</th>
                <th>{{ $trad->thDel }}</th>
                <th>{{ $trad->thAl }}</th>
                <th>{{ $trad->thStatus }}</th>
            </thead>
            <tbody>
                @foreach ($itineraries['hotels'] as $hotel)
                    <tr>
                        <td>{{ strtoupper(trim($hotel['city'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['hotel'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['confirmation'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['room'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['date_in'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['date_out'])) }}</td>
                        <td>{{ strtoupper(trim($hotel['status'])) }}</td>
                    </tr>                
                @endforeach
            </tbody>
        </table>
        <br>
        <h3 class="title">TRENES</h3>
        <table>
            <thead style="color:#fff">
                <th>{{ $trad->thCity }}</th>
                <th>{{ $trad->thService }}</th>
                <th>{{ $trad->thConfirmation }}</th>
                <th>{{ $trad->thCpax }}</th>
                <th>{{ $trad->thDeparture }}</th>
                <th>{{ $trad->thHour }}</th>
                <th>{{ $trad->thArrival }}</th>
                <th>{{ $trad->thHour }}</th>
                <th>{{ $trad->thStatus }}</th>
            </thead>
            <tbody>
                @foreach ($itineraries['trains'] as $train)
                    <tr>
                        <td>{{ strtoupper(trim($train['city'])) }}</td>
                        <td>{{ strtoupper(trim($train['name'])) }}</td>
                        <td>{{ strtoupper(trim($train['confirmation'])) }}</td>
                        <td>{{ strtoupper(trim($train['pax'])) }}</td>
                        <td>{{ strtoupper(trim($train['date_in'])) }}</td>
                        <td>{{ strtoupper(trim($train['start_time'])) }}</td>
                        <td>{{ strtoupper(trim($train['date_out'])) }}</td>
                        <td>{{ strtoupper(trim($train['departure_time'])) }}</td>
                        <td>{{ strtoupper(trim($train['status'])) }}</td>
                    </tr>                
                @endforeach
            </tbody>
        </table>
        <br>
        <br>
        <section>
            <h3 class="title">{{ $trad->titleNotInclude }}</h3>
            <hr class="divisor">
            <ul>
                <li> {{ $trad->textNotInclude_line1 }} </li>
                <li> {{ $trad->textNotInclude_line2 }} </li>
                <li> {{ $trad->textNotInclude_line3 }} </li>
                <li> {{ $trad->textNotInclude_line4 }} </li>
                <li> {{ $trad->textNotInclude_line5 }} </li>
                <li> {{ $trad->textNotInclude_line6 }} </li>
                <li> {{ $trad->textNotInclude_line7 }} </li>
            </ul>
        </section>
        <section>
            <h3 class="title">{{ $trad->titleinfoImportant }}</h3>
            <hr class="divisor">
            <p>{{ $trad->textinfoImportant }}</p>
        </section>
        <section>
            <h3 class="title">{{ $trad->titleGeneralImportant }}</h3>
            <hr class="divisor">
            <p>{{ $trad->textGeneralImportant }}</p>
            <p>{{  $trad->textGeneralImportant_2 }}<a href="{{ $trad->linkGeneraldomation1 }}">{{ $trad->textGeneraldomation1 }}</a></p>
        </section>
        <section>
            <h3 class="title">{{ $trad->titleRecommendationForTransfers }}</h3>
            <hr class="divisor">
            <div>{{ $trad->subtitleRecommendationForTransfers }}</div>
            <ul>            
                <li>{{ $trad->textRecommendationForTransfers_1 }}. </li>
            </ul>
        </section>
        <section>
            <h3 class="title">{{ $trad->titleImportant }}</h3>
            <hr class="divisor">
            <div>{{ $trad->textImportant }}</div>
            <ul>
              
                <li> {{ $trad->textImportant_data1 }} </li>
                <li> {{ $trad->textImportant_data2 }} </li>
                <li> {{ $trad->textImportant_data3 }} </li>
                <li> {{ $trad->textImportant_data4 }} </li>
   
            </ul>
            <div>{{ $trad->textImportant_2 }}</div>
            <ul>
                <li> {{ $trad->textImportant_data6 }} </li>
                <li> {{ $trad->textImportant_data7 }} </li>
                <li> {{ $trad->textImportant_data8 }} </li>
                <li> {{ $trad->textImportant_data9 }} </li>
            </ul>
            <br><br><br>
            <div style="position:relative">
                <table style="position:absolute;margin-right: 10px;width:300px;left:200px">
                    <tr>
                        <th></th>
                        <th>{{ $trad->thPeso }}</th>
                        <th>{{ $trad->size }}</th>
                    </tr>
                    <tr>
                        <td>{{ $trad->tdbolso }}</td>
                        <td>5Kg/11lb</td>
                        <td>{{ $trad->size_value }}</td>
                    </tr>
                </table>
                <img style="position: absolute; right:80px; top:-30px" src="{{ storage_path('bag.jpeg')}}" alt="">
            </div>
        </section>
        <br><br><br><br>
        <section>
            <h3 class="title">{{ $trad->titleRecommendationForTraveling }}</h3>
            <hr class="divisor">
            <p>{{ $trad->textRecommendationForTraveling_1 }}</p>
            <p>{{ $trad->textRecommendationForTraveling_2 }}<a href="{{ $trad->textRecommendationForTraveling_link }}">{{ $trad->textRecommendationForTraveling_here }}</a></p>
        </section>
        <section>
            <h3 class="title">{{ $trad->masi_title }}</h3>
            <hr class="divisor">
            <p>{{ $trad->masi_parrafo_1 }}</p>
            <ul>
                <li> {{ $trad->masi_texto_1 }} </li>
                <li> {{ $trad->masi_texto_2 }} </li>
                <li> {{ $trad->masi_texto_3 }} </li>
                <li> {{ $trad->masi_texto_4 }} </li>
                <li> {{ $trad->masi_texto_5 }} </li>
                <li> {{ $trad->masi_texto_6 }} </li>
            </ul>
        </section>
        <p class="asterisks">**********************************************</p>
        <section>
            <h3 class="title">{{ $trad->masi_title_numbers }}</h3>
            <hr class="divisor">        
            <ul>            
                <li> {{ $trad->masi_numbers_1 }} </li>                
                <li> {{ $trad->masi_numbers_2 }} </li>
                <li> {{ $trad->masi_numbers_3 }} </li>
                <li> {{ $trad->masi_numbers_4 }} </li>
                <li> {{ $trad->masi_numbers_5 }} </li>
            </ul>
        </section>
        <section>
            <h3 class="title">{{ $trad->disclaimer_title }}</h3>
            <hr class="divisor">
            <p>{{ $trad->disclaimer_text }}</p>        
        </section>
    </div>
</body>
</html>