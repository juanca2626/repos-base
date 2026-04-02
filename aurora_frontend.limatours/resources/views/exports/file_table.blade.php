@php
    $total = 0;
    foreach($tabla as $row){
        $total = $total  + $row['DEBE'];
    }
@endphp

<table border="1" class="cuadro" style="margin-top: 0px!important;border-bottom: 0px solid!important;border-left: 0px solid!important">
    <thead>
    <tr>
        <td class="header" width="7%"  style="border-bottom: 1px solid!important;border-style: dashed;" >{{strtoupper(trans('files.label.nro'))}}</td>
        <td class="header" width="61%" style="border-bottom: 1px solid!important;border-style: dashed;">{{strtoupper(trans('files.label.description'))}}</td>
        <td class="header" width="15%" style="border-bottom: 1px solid!important;border-style: dashed;">{{strtoupper(trans('files.label.price_unit'))}}</td>
        <td class="header" width="17%" style="border-bottom: 1px solid!important;border-style: dashed;">{{strtoupper(trans('files.label.total'))}} $USD</td> 
    </tr>
    </thead>
    <tbody>    
        @if(isset($tabla))

            @foreach ($tabla as $report)
            <tr>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-bottom: 0px solid!important;border-top: 0px solid!important">{{ substr("000".$report['NROITE'], -3) }}</td>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-bottom: 0px solid!important;border-top: 0px solid!important">{{ $report['DESCRI'] }}</td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-bottom: 0px solid!important;border-top: 0px solid!important">{{  number_format(floatval($report['IMPCOM']), 2, '.', ',')   }}</td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-bottom: 0px solid!important;border-top: 0px solid!important">{{ number_format($report['DEBE'], 2, '.', ',')  }}</td> 
            </tr>    
            @endforeach

            @for($i=0; $i<(21 - count($tabla)); $i++)
            <tr>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important;"><div style="color:white!important">-</div></td>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td> 
            </tr>                      
            @endfor

        @else

            @for($i=0; $i<21; $i++)
            <tr>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important;"><div style="color:white!important">-</div></td>
                <td style="padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td>
                <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-top: 0px solid!important"></td> 
            </tr>                      
            @endfor

        @endif

        <tr>
            <td colspan="3" style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-top: 1px solid!important;border-left: 0px solid!important!important;border-bottom: 0px solid!important;border-style: dashed;">{{strtoupper(trans('files.label.total_venta'))}}  :</td>  
            <td style="text-align: right;padding-top: 0px!important;padding-bottom: 0px!important;border-bottom: 1px solid!important;border-top: 1px solid!important;border-style: dashed;">{{ number_format($total, 2, '.', ',') }}</td> 
        </tr>  

    </tbody>
</table>
