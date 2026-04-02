<html>
<table>
    <thead>
    <tr>
        <td>
            <b>Código AURORA</b>
        </td>
        <td>
            <b>Hotel</b>
        </td>
        <td>
            <b>Enlace a tarifa protegida</b>
        </td> 
    </tr>
    @foreach($data as $key => $value)
        <tr>
            <td>           
                <b>{{ $value['channels'][0]['pivot']['code'] }}</b>          
            </td>
            <td>
                {{   $value['name']   }}
            </td>
           
            <td>
                {{ url('/#/hotels/' . $value['id'] . '/manage_hotel/rates/rates/cost/edit/' . $value['rates_plans'][0]['id']) }}
            </td> 
             
        </tr>
    @endforeach
</table>
</html>
