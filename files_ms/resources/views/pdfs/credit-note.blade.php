<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <link rel="stylesheet" href="styles.css">
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
            margin-top: 30px;
            margin-bottom: 30px;
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
            padding: 0px 20px 20px 20px;
            border: 0.5px solid #e9e9e9;
            border-radius: 3px;

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

        .invoice-info {
            border: 0.5px solid #e9e9e9;
            border-radius: 3px;
            padding: 20px;
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
            font-weight: 600;
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
                    <td style="font-size:9px"><strong>{{ $client_aurora->pais }}</strong></td>
                    <td style="text-align:right">{{ $limatours['address'] }}</td>
                </tr>
                <tr>
                    <td>{{ strtoupper($client_aurora->address) }}</td>
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
            <h3 style="text-align:center">NOTA DE CRÉDITO</h3>
            <table style="margin-top: 20px">
                <tr>
                    <td style="width:30%;padding: 5px">{{ $trad->your_ref }}: {{ $file['order_number'] }}</td>
                    <td style="width:70%">{{ $trad->name_passenger_group }}: <strong>
                            {{ $file['description'] }}</strong></td>
                </tr>
                <tr>
                    <td style="width:50%;padding:5px">{{ $trad->arrival_date }}: <strong>{{ $file['date_in'] }}</strong>
                    </td>
                    <td style="width:50%;text-align:right">{{ $trad->departure_date }}:
                        <strong>{{ $file['date_out'] }}</strong>
                    </td>
                </tr>
            </table>
        </section>

        <table class="services" style="margin-top: 20px">
            <thead>
                <tr>
                    <th style="text-align: left">{{ $trad->description }}</th>
                    <th>{{ $trad->qty }}</th>
                    <th>{{ $trad->unit_price }}</th>
                    <th>{{ $trad->total }} USD</th>
                </tr>
            </thead>
            @if ($credit_notes)
                <tbody>
                    @foreach ($credit_notes['details'] as $key => $detail)
                        <tr style="background-color: #f6f6f6">
                            <td style="text-align: left">{{ $detail['description'] }}</td>
                            <td>{{ $detail['quantity'] }}</td>
                            <td>{{ $detail['unit_price'] }}</td>
                            <td>{{ $detail['amount'] }}</td>
                        </tr>
                        <tr>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                        </tr>
                    @endforeach                    
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td style="background-color: #e9e9e9">{{ $trad->total }}:</td>
                        <td style="background-color: #e9e9e9"></td>
                        <td style="background-color: #e9e9e9; text-align: center;">{{ $credit_notes['total'] }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>

</html>