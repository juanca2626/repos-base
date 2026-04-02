<img src="{{ public_path('images/invoices.jpg') }}" style="margin-top: -30px">

 
<table border="0" style="margin-top: 10px!important" >
    <tr>
        <td style="padding: 0px!important"  width="69%" > 

            {{ \Carbon\Carbon::now()->locale($lang)->translatedFormat('d F Y')  }} <br />  <!-- \\de   \\de -->

            {{ $detalle['user']['RAZON'] }}  <br />
            {{ $detalle['user']['DIRECC'] }}  <br />
            {{ $detalle['user']['PAIS'] }}  <br />
            {{strtoupper(trans('files.label.ruc'))}} : {{ $detalle['user']['CUIT'] }}            
        </td>
        <td style="padding: 0px!important; vertical-align: middle!important">
            Av.Juan de Arona N.755 Piso 3 <br />
            LIMA-PERU  <br />
            {{strtoupper(trans('files.label.ruc'))}} : 20536830376
        </td>
    </tr>
</table>

<table border="0" style="margin-bottom: 5px!important" >

   
    <tr>
        <td class="header" colspan="2" style="margin-bottom: 20px!important;padding: 0px!important"> 
            @if($document == 'invoice')
            {{strtoupper(trans('files.label.invoice'))}} No: {{ $cabecera['NROREF'] }}
            @endif  

            @if($document == 'credit_note')
                {{strtoupper(trans('files.label.credit_note'))}} No: {{ $cabecera['NROREF'] }}
            @endif 
            
            @if($document == 'debit_note')
                {{strtoupper(trans('files.label.debit_note'))}} No: {{ $cabecera['NROREF'] }}
            @endif 

        </td>
    </tr>
    

 



    <tr>
        <td width="20%" style="padding: 0px!important">{{strtoupper(trans('files.label.ref'))}} :</td>
        <td class="header" style="padding: 0px!important">{{strtoupper(trans('files.label.name_pax_group'))}} : {{ $cabecera['DESCRI'] }}</td>
    </tr>    
</table>

<table border="0" style="margin-bottom: 5px!important;margin-top: 0px!important;" >
 
    <tr>
        <td width="40%" style="padding: 0px!important">{{strtoupper(trans('files.label.arrival_date'))}} : {{ $cabecera['DIAIN2'] }}</td>
        <td class="header" style="padding: 0px!important">{{strtoupper(trans('files.label.departure_date'))}} : {{ $cabecera['DIAOUT2'] }}</td>
    </tr>    
</table>

<table border="0" style="margin-bottom: 5px!important;margin-top: 0px!important;" >
 
    <tr>
        <td style=" border-top: 1px solid!important;padding-left:0px!important;padding-top:0px!important;padding-bottom:0px!important;border-style: dashed;">{{strtoupper(trans('files.label.for_service'))}} :</td> 
    </tr>    
</table>

 
@include('exports.file_table')
 

<table border="0" style="margin-top: 40px!important;" >
 
    <tr>
        <td style="padding: 0px!important">
            {{strtoupper(trans('files.label.indicar_banco'))}}            
        </td> 
    </tr>    
</table>

<table border="0" >
 
    <tr>
        <td width="30%" style="padding: 0px!important; vertical-align: top;padding-left:20px">
            {{strtoupper(trans('files.label.banco'))}}
        </td> 
        <td width="2%"  style="padding: 0px!important;vertical-align: top;">:</td>
        <td style="padding: 0px!important;  vertical-align: top;">
            BANCO INTERAMERICANO DE FINANZAS <br />
            Rivera Navarrete Av. 600 <br />
            Lima 27, Peru <br />
        </td>         
    </tr>    
</table>

<table border="0"  >
 
    <tr>
        <td width="30%" style="padding: 0px!important; vertical-align: top;padding-left:20px">
            SWIFT <br /> 
            {{strtoupper(trans('files.label.a_orden'))}} <br /> 
            {{strtoupper(trans('files.label.cuenta'))}} No <br />
        </td> 
        <td width="2%" style="padding: 0px!important;vertical-align: top;">:<br /> :<br /> :</td>
        <td style="padding: 0px!important;  vertical-align: top;">
            BIFSPEPL  <br />
            LIMA TOURS S.A.C <br />
            7000721979 <br />
        </td>         
    </tr>  
       
</table>

<table border="0" style="margin-bottom: 0px!important">
 
    <tr>
        <td style="padding: 0px!important">
            {{strtoupper(trans('files.label.sirvace'))}} 
        </td> 
    </tr>    
</table>