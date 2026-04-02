<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            font-size: 12px;
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
        
        .title {
            color: #5A5A58;
            font-size: 20px;
            text-align: left;
            border-bottom: 1px solid #808080;
            padding-bottom: 10;
        }

        .subtitle {
            color: #5A5A58;
            font-size: 13px;
            text-align: left;
            font-weight: 100;
            padding-top: 5;
            padding-bottom: 5;
        }

        .subtitle2 {
            color: #5A5A58;
            font-size: 20px;
            text-align: left;
            font-weight: 100;
            padding-top: 10;
            padding-bottom: 5;
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
            border: 0px solid #5A5A58;
        }
        table th { 
            border-top: 1px solid #808080;
            border-bottom: 1px solid #808080;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 14px;
            text-align: left;
        }
        table td {
            text-align: left;
            border: 0px solid #5A5A58;
            padding-top: 5;
            padding-bottom: 5;            
        }
        table td.left {
            text-align: left; 
        }
        
        section {
            margin-bottom: 15px;
            color: #5A5A58;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1 class="title">Reporte de servicios</h1> 
        <!-- <h3 class="subtitle">{{ $client }}</h3> -->
        <h3 class="subtitle">FILE: {{ $file_number }}</h3>
        <h3 class="subtitle">REF: {{ $description }}</h3>
        
        @if(count($skeleton['services'])> 0 )
        <h3 class="subtitle2">Services</h3>
        <table>
            <thead>
                <th style="width: 10%;  text-align: left;">Date</th>
                <th style="width: 15%;  text-align: left;">Services</th>
                <th style="width: 70%; text-align: left;">Description</th> 
            </thead>
            <tbody>
                @foreach ($skeleton['services'] as $key => $tour)   
  
                    @foreach ($tour['itineraries'] as $index => $item)      
                                            
                        @php
                            $style = "";
                            if($index == (count($tour['itineraries']) - 1 )){
                                $style = "border-bottom: 1px solid #808080;";
                            }
                        @endphp

                        @if($index == 0)
                            <tr  >
                                <td style="{{ $style }}">{{ $tour['date'] }} </td>
                                <td style="{{ $style}}">{{ strtoupper(trim($tour['city_in_name'])) }}</td>
                                <td style="{{ $style}}" class="left">
                                    @if($item['entity'] == 'flights')    
                                        {{ $item['city_in_name'] }} / {{ $item['city_out_name'] }} {{ $flight['airline_number'] }} {{ $item['start_time'] }}
                                    @else
                                      {{ $item['start_time'] }} {{ $item['name']}}
                                    @endif
                                </td> 
                            </tr> 
                        @else
                            <tr >                            
                                <td style="{{ $style}}"></td>
                                <td style="{{ $style}}"></td>
                                <td style="{{ $style}}" class="left">
                                    @if($item['entity'] == 'flights')    
                                        {{ $item['city_in_name'] }} / {{ $item['city_out_name'] }} {{ $flight['airline_number'] }} {{ $item['start_time'] }}
                                    @else
                                      {{ $item['start_time'] }} {{ $item['name']}}
                                    @endif
                                </td> 
                            </tr> 
                        @endif

                    @endforeach
                @endforeach
            </tbody>
        </table>
        @endif 

        @if(count($skeleton['hotels'])> 0 )
        <br>
        <h3 class="subtitle2">Hotels</h3>
        <table>
            <thead>
                <th style="width: 10%; text-align: left;">City</th>
                <th style="width: 35%;text-align: left;">Hotel</th>
                <th style="width: 20%; text-align: left;">Confirmation</th> 
                <th style="width: 30%; text-align: left;">Type of room</th> 
                <th style="width: 10%; text-align: left;">Start</th> 
                <th style="width: 10%; text-align: left;">Ends</th> 
                <th style="width: 5%; text-align: left;">State</th>  
            </thead>
            <tbody>  
                @foreach ($skeleton['hotels'] as $index => $hotel)                                                                 
                    <tr >                            
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
        @endif           

        @if(count($skeleton['trains'])> 0 )
        <br>
        <h3 class="subtitle2">TRENES</h3>
        <table>
            <thead>
                <th style="width: 10%;">City</th>
                <th style="width: 35%;">Services</th>
                <th style="width: 10%;">Confirmation</th>
                <th style="width: 5%;">Pax</th>
                <th style="width: 10%;">Departure</th>
                <th style="width: 10%;">Hour</th>
                <th style="width: 10%;">Arrival</th>
                <th style="width: 5%;">Hour</th>
                <th style="width: 5%;">State</th>
            </thead>
            <tbody>
                @foreach ($skeleton['trains'] as $train)
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
        @endif

        @if(count($skeleton['flights'])> 0 )
            <br>
            <h3 class="subtitle2">Flights</h3>
            <table>
                <thead>
                    <th>City</th>
                    <th>Services</th>
                    <th>Confirmation</th>
                    <th>Pax</th>
                    <th>Departure</th>
                    <th>Hour</th>
                    <th>Arrival</th>
                    <th>Hour</th>
                    <th>State</th>
                </thead>
                <tbody>                 
                    @foreach ($skeleton['flights'] as $flight)
                        <tr>
                            <td>{{ strtoupper(trim($flight['city_in_iso'])) }} 1</td>
                            <td>{{ strtoupper(trim($flight['name'])) }} 2</td>
                            <td>{{ strtoupper(trim($flight['confirmation'])) }} 3</td>
                            <td>{{ strtoupper(trim($flight['pax'])) }} 4</td>
                            <td>{{ strtoupper(trim($flight['date_in'])) }}</td>
                            <td>{{ strtoupper(trim($flight['start_time'])) }}</td>
                            <td>{{ strtoupper(trim($flight['date_out'])) }}</td>
                            <td>{{ strtoupper(trim($flight['departure_time'])) }}</td>
                            <td>{{ strtoupper(trim($flight['status'])) }}</td>
                        </tr>                
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>
</body>
</html>