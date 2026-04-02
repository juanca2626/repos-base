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
            <span style="color:rgb(0,0,0)">REMINDER:</span>
        </h2>
        <h2 style="text-align:center;line-height:24.375px">
            <span style="color:rgb(204,2,1)">COMPLETE THE DETAILS OF YOUR RESERVATION</span>
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
        <span style="color:rgb(0,0,0);font-size:large">Hello {{ $data['file']['RAZONCLI'] }},</span>
        <br>
    </p>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        <font size="4" color="#000000">
            <br>
        </font>
    </p>
    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        We remind you that you must complete the passenger information of your reservation <b style="color:rgb(204,2,1)">{{ $data['file']['NROREF'] }} - {{ @$data['file']['DESCRI'] }}</b>, the trip starts on&nbsp;<b style="color:rgb(204,2,1)">{{ date("d/m/Y", strtotime($data['file']['DIAIN'])) }}</b>
    </p>

    <p style="margin:0cm 0cm 8pt;font-family:Calibri,sans-serif;text-align:center;line-height:13.91px">
        To complete the pending information, click on the following link:
    </p>

    <p style="text-align:center">
        <a href="https://aurora.limatours.com.pe/register_paxs/{{ $data['file']['NROREF'] }}?lang=en&paxs={{ $data['file']['CNTMAXPAXS'] }}&canadl={{ $data['file']['CANADL'] }}&canchd={{ $data['file']['CANCHD'] }}&caninf={{ $data['file']['CANINF'] }}"
           target="_blank"
        style="display:block;padding:15px;background-color:#BF0000;color:#FFF;border-radius:10px;">
            Complete the data
        </a>
    </p>

    <p style="text-align:center">The LimaTours team</p>
</div>
</body>
</html>
