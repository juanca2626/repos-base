<table>
    <tr>
        <td></td>
        <td>LIMA TOURS S.A.C </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>Fecha {{ date('d/m/Y') }}</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>Empresa de Viajes y Turismo </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr> 
    <tr>
        <td></td>
        <td colspan="10"></td> 
    </tr> 
    <tr>
        <td></td>
        <td colspan="10" style="text-align: center; font-weight: bold;"> ROOMING  LIST</td> 
    </tr>  
    <tr>
        <td></td>
        <td colspan="10" style="text-align: center; font-weight: bold;"> REF: {{ $file_number }} - {{ $file_description }}</td> 
    </tr>    
    <tr>
        <td></td>
        <td colspan="10"> HOTEL: {{ $hotel['hotel'] }} </td> 
    </tr>
    <tr>
        <td></td>
        <td colspan="10"> LLEGA : {{ $hotel['date_in'] }} </td> 
    </tr> 
    <tr>
        <td></td>
        <td colspan="10"> SALE : {{ $hotel['date_out'] }} </td> 
    </tr>                                 
</table> 


<table border="1">
    <thead>
        <tr>
            <td></td>
            <th style="background-color: #a2a9a9; width: 150px;  text-align: center">APELLIDO Y NOMBRES</th>
            <th style="background-color: #a2a9a9;  text-align: center">S</th>
            <th style="background-color: #a2a9a9;  text-align: center">Tip</th>
            <th style="background-color: #a2a9a9;  text-align: center">Na</th>
            <th style="background-color: #a2a9a9;  text-align: center">Nro.</th> 
            <th style="background-color: #a2a9a9;width: 80px;  text-align: center">Documento</th> 
            <th style="background-color: #a2a9a9;width: 80px;  text-align: center">Fec.Nac.</th> 
            <th style="background-color: #a2a9a9;width: 150px;  text-align: center">Restriccion Medica</th> 
            <th style="background-color: #a2a9a9;width: 150px;  text-align: center">Restriccion Alimenticia</th>   
        </tr>

        @foreach($hotel['rooms'] as $room)
        <tr>
            <td></td>
            <td colspan="9">{{ $room['room'] }}</td>                  
        </tr>
        @foreach($room['passengers'] as $passenger)
            <tr>
                <td></td>
                <td>{{ $passenger['name'] }} {{ $passenger['surnames'] }}</td>
                <td>{{ $passenger['genre'] }}</td>
                <td>{{ $passenger['type'] }}</td>
                <td>{{ $passenger['country_iso'] }}</td>
                <td>{{ $passenger['doctype_iso'] }}</td> 
                <td>{{ $passenger['document_number'] }}</td> 
                <td>{{ $passenger['date_birth'] }}</td> 
                <td>{{ $passenger['medical_restrictions'] }}</td> 
                <td>{{ $passenger['dietary_restrictions'] }}</td>     
            </tr>  
        @endforeach              
        @endforeach
    </thead>
<tbody>
 
</tbody>
</table>

<style>
    table th  {
        background-color: #a2a9a9;
        text-align: center;
    }
</style>