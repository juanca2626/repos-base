<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url("{{ public_path('fonts/Montserrat-Regular.ttf')}}")
        }

        @font-face {
            font-family: 'Montserrat';
            font-weight: bold;
            src: url("{{ public_path('fonts/Montserrat-Bold.ttf')}}")
        }
    
        @page {
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 30px;
            margin-right: 30px;
        }
    
        body {
            font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            color: #333;
            font-size: 9px
        }
    
        .container {
            background: #fff;
        }
    
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    
        .logo {
            padding: 10px 5px;
            color: #fff;
            width: 100px;
        }
    
        .logo img {
            max-height: 50px;
            margin-right: 10px;
        }
    
        .invoice-info h3 {
            margin-top: 0;
        }
    
        table {
            width: 100%;
            border-collapse: collapse;
        }
    
        .services th,
        .services td {
            padding: 8px;
            text-align: center;
        }
    
        .services th {
            font-weight: bold;
        }
    
        .services tfoot td {
            font-weight: bold;
        }
    
        .payment-details {
            margin-top: 40px;
        }
    
        .payment-details ul {
            list-style: none;
            padding: 0;
        }
    
        .payment-details ul li {
            margin: 5px 0;
        }
    
        .fw-bold {
            font-weight: bold;
        }
    
        .circle {
            width: 14px;
            height: 11px;
            background-color: #55a3ff;
            border-radius: 50%;
            font-size: 7px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <table style="margin-top: 20px">
                <tr>
                    <td style="vertical-align: top">
                        <img src="{{public_path('images/logo_limatours.png')}}" style="width: 140px" alt="">
                    </td>
                    <td style="text-align: right">
                        <img style="margin-top: 15px" src="{{public_path('images/certified.jpeg')}}" style="width: 100px" alt="">
                        <img style="margin-top: 10px" src="{{public_path('images/worldcob.jpeg')}}" style="width: 50px" alt="">
                        <img style="margin-top: 10px" src="{{public_path('images/gold-member.jpeg')}}" style="width: 50px" alt="">
                    </td>
                </tr>
                <tr style="font-size:7px">
                    <td>{{ $statemet['date'] }}</td>
                    <td style="text-align:right">{{ $limatours['address'] }}</td>
                </tr>
                <tr>
                    <td style="font-size:9px"><strong>{{ $client_aurora->pais }}</strong></td>
                    <td style="text-align:right;font-size:8px">{{ $limatours['city'] }}</td>
                </tr>
                <tr style="font-size:7px">
                    <td>{{ $client_aurora->ciudad }}</td>
                    <td style="text-align:right">RUC: {{ $limatours['ruc'] }}</td>
                </tr>
            </table>                    
            <div class="vat-info">
                <p>VAT/NIF/ID: {{ $client_aurora->ruc }}</p>
            </div>
        </header>

        <section class="invoice-info">
            <h3 style="text-align:center">{{ $trad->invoice }}: {{ $file['file_number'] }}</h3>
            <table style="margin-top: 20px">
                <tr>
                    <td style="width:30%;padding: 5px">{{ $trad->your_ref }}:  {{ $file['order_number'] }}</td>
                    <td style="width:70%">{{ $trad->name_passenger_group }}: <strong> {{ $file['description'] }}</strong></td>
                </tr>
                <tr>
                    <td style="width:50%;padding:5px">{{ $trad->arrival_date }}: <strong>{{ $file['date_in'] }}</strong></td>
                    <td style="width:50%;text-align:right">{{ $trad->departure_date }}: <strong>{{ $file['date_out'] }}</strong></td>
                </tr>
            </table>
        </section>
        <div style="padding: 10px;text-align:right;background-color: #fafafa;margin-top:10px; vertical-align: middle">            
            <img src="{{public_path('images/clock.png')}}" style="width:12px;margin-top:2px;margin-right:3px" alt=""><span style="float:right"> {{ $trad->deadline }}: <strong>{{ $statemet['deadline'] }}</strong></span>
        </div>

        <div style="margin-top:10px;text-align:left;border-top:0.5px solid #e9e9e9;border-bottom:0.5px solid #e9e9e9;padding: 10px 0px;">{{ $trad->message_1 }}:</div>

        <table class="services" style="margin-top: 20px">
            <thead>
                <tr>
                    <th style="text-align: left">{{ $trad->description }}</th>
                    <th>{{ $trad->qty }}</th>
                    <th>{{ $trad->unit_price }}</th>
                    <th>{{ $trad->total }} USD</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($statemet['details'] as $key => $detail)
                <tr style="background-color: #f6f6f6">
                    <td style="text-align: left">{{ $detail['description'] }}</td>
                    <td>{{ $detail['quantity'] }}</td>
                    <td>{{ $detail['unit_price'] }}</td>
                    <td>{{ $detail['amount'] }}</td>
                </tr>
                <tr>
                    <td>  </td>
                    <td>  </td>
                    <td>  </td>
                    <td>  </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td style="background-color: #e9e9e9">{{ $trad->total }}:</td>
                    <td style="background-color: #e9e9e9"></td>
                    <td style="background-color: #e9e9e9; text-align: center"><strong>{{ $statemet['total'] }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <section class="payment-details">
            <table style="margin-bottom: 10px;margin-top: 20px">
                <tbody>
                    <tr>
                        <td style="font-size:10px;color:#737373"><strong>{{ $trad->payment_details }}:</strong></td>
                        <td style="text-align: right"><img style="float: right;width:50px" src="{{  public_path('images/litopay.svg') }}" alt=""></td>
                    </tr>
                </tbody>
            </table>
            <div style="color:#212529;border-top:0.5px solid #e9e9e9;border-bottom:0.5px solid #e9e9e9;padding: 10px 0px;"> {{ $trad->message_2 }}</div>
            <table class="end-table" style="color:#737373;margin-top: 20px">
                <tr>
                    <td style="vertical-align: top">{{ $trad->bank }}:</td>
                    <td><strong>{{ $limatours['bank']['name'] }}</strong></td>
                    <td>{{ $trad->swift }}</td>
                    <td>{{ $trad->credit }}</td>
                    <td>{{ $trad->account }}</td>
                </tr>
                <tr></tr>                
                <tr>
                    <td></td>
                    <td>{{ $limatours['bank']['address'] }}</td>
                    <td><strong>{{ $limatours['swift'] }}</strong></td>
                    <td><strong>{{ $limatours['credit_to'] }}</strong></td>
                    <td><strong>{{ $limatours['ruc'] }}</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $limatours['bank']['city'] }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 15px"> </td>
                </tr>
            </table>
            <table style="color:#333333;margin-top: 20px">
                <tr style="background-color:#f4f4f4;">
                    <td style="position: relative;vertical-align:top;width:50%;padding-left:10px;padding-top:10px; align-items: center;"> <img src="{{ public_path('images/exclamation.svg') }}" style="width: 9px;margin-right:3px" alt=""> <span style="position:absolute;top:10px;left:27px"> Para <strong>identificar su pago</strong>, por favor <strong>incluya</strong> la siguiente referencia:</span></td>
                    <td style="width:50%;text-align: left;vertical-align:top;padding-top:10px;padding-bottom:15px">
                        <table>
                            <tr>
                                <td style="position:relative"><div class="circle" style="text-align: center;vertical-align:center;padding-top:3px">1</div><span style="position:absolute;left:18px;top: 0px">{{ $trad->file_number }}</span></td>
                            </tr>
                            <tr>
                                <td style="position:relative"><div class="circle" style="text-align: center;vertical-align:center;padding-top:3px">2</div><span style="position:absolute;left:18px;top: 0px">{{ $trad->statement }}</span></td>
                            </tr>
                            <tr>
                                <td style="position:relative"><div class="circle" style="text-align: center;vertical-align:center;padding-top:3px">3</div><span style="position:absolute;left:18px;top: 0px">{{ $trad->group_name }}</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </section>
    </div>
</body>
</html>