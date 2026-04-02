<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media  only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 100%; background-color: #FFF;">
<div style="font-family:verdana,sans-serif">
    <span style="color:rgb(80,0,80)">
        <div style="text-align:center">
            <a style="font-family: Arial, &#039;Helvetica Neue&#039;, Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;" href="{{ url('/') }}" target="_blank">
                <img src="{{ asset('images/logo.png') }}" style="max-height:50px;max-width:100%;" title="LimaTours" alt="LimaTours" />
            </a>
        </div>
        <h2 style="text-align:center;line-height:24.375px">
            <span style="color:rgb(0,0,0)">PROMEMORIA:</span>
        </h2>
        <h2 style="text-align:center;line-height:24.375px">
            <span style="color:rgb(204,2,1)">COMPLETA I DETTAGLI DELLA TUA PRENOTAZIONE</span>
        </h2>
        <div>
            <span style="color:rgb(204,2,1)">
                <br>
            </span>
        </div>
    </span>
    <div style="text-align:center">
        <img src="{{ asset('images/maca-formulario.png') }}" />
    </div>
    <div style="color:rgb(80,0,80)">
        <span style="color:rgb(204,2,1)"><br></span>
    </div>
    <p style="color:rgb(80,0,80);margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        <span style="color:rgb(0,0,0);font-size:large">Ciao {{ $data['file']['RAZONCLI'] }},</span>
        <br>
    </p>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        <font size="4" color="#000000">
            <br>
        </font>
    </p>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        Ti ricordiamo che devi completare le informazioni passeggeri / informazioni dei voli della tua prenotazione <b style="color:rgb(204,2,1)">{{ $data['file']['NROREF'] }} - {{ @$data['file']['DESCRI'] }}</b>, il viaggio inizia il&nbsp;<b style="color:rgb(204,2,1)">{{ date("d/m/Y", strtotime($data['file']['DIAIN'])) }}</b>
    </p>

    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        Per completare le informazioni in sospeso, fare clic sul seguente collegamento:
    </p>

    <p style="text-align:center">
        <a href="https://aurora.limatours.com.pe/register_file/{{ $data['file']['NROREF'] }}?lang=it&paxs={{ $data['file']['CNTMAXPAXS'] }}&canadl={{ $data['file']['CANADL'] }}&canchd={{ $data['file']['CANCHD'] }}&caninf={{ $data['file']['CANINF'] }}"
           target="_blank"
           style="display:block;padding:15px;background-color:#BF0000;color:#FFF;border-radius:10px;">
            Completa i dati
        </a>
    </p>

    <p style="text-align:center">Il team di LimaTours</p>
</div>
</body>
</html>
