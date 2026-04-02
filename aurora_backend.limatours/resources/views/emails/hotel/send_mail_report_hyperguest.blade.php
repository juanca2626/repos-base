<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <style type="text/css" rel="stylesheet" media="all">
           
            body{
                background-color: #ffffff !important;
            }
            .destinatario{
                padding:20px 0 0 20px;
                font-size: 25px;
            }

            .comment{                            
                padding:5px 0 20px 20px;
            }
           
            table{
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 80%;
                margin-bottom: 60px;                
                background: #ffffff;
                float: left;
                margin-left: 20px;    
            }
            tbody {
                display: table-row-group;
                vertical-align: middle;
                border-color: inherit;
            }

            tbody tr{
                display: table-row;
                vertical-align: inherit;
                border-color: inherit;
            }
            table td, table th {
                border: 1px solid #ddd;
                padding: 8px;
            }

            .title_campos th{
                background-color: black;
                color:white;
            }

            table tr:nth-child(odd) {background-color: white;}
            table tr:nth-child(even) {background-color: #c8ced3;}
        </style>
    </head>

    <body>

        <p class="destinatario"><b>BUEN DÍA EQUIPO HYPERGUEST</b></p>
        
        @if($data['commentMail'] != '')
            <p class="comment">{{ $data['commentMail'] }}</p>
        @endif
        
        <table width="600">
            <thead class="thead-light">
                <tr class="title_campos">
                    <th>Reserva</th>          
                    <th>Hotel</th>        
                    <th>Pasajero</th>           
                    <th>Check in</th>            
                    <th>Check Out</th>            
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['listMail'] as $report)
                    <tr>                
                        <td>{{ $report['booking_id'] }}</td>
                            
                        <td> {{ $report['property_id'] }} | {{ $report['property_name'] }} </td>                    
                                
                        <td>{{ $report['lead_guest_name'] }}</td>
                                
                        <td>{{ $report['start_date'] }}</td>
                                
                        <td>{{ $report['end_date'] }}</td>
                                
                        <td>{{ $report['price_currency'] }} {{ $report['price_amount'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            
        </table>
    </body>

    
    

</html>

