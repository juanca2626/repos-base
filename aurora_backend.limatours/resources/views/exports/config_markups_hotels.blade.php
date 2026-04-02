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
        <td>
            <b>Fecha</b>
        </td>
    </tr>
    @foreach($data as $item)
        <tr>
            <td>           
                <b>{{ $item['code'] }}</b>          
            </td>
            <td>
                {{   $item['hotel']   }}
            </td>
           
            <td style="width: 100%">
                {{ url('/#/hotels/' . $item['hotel_id'] . '/manage_hotel/rates/rates/cost/edit/' . $item['rate_plan_id']) }}
            </td> 
            <td>
                {{ $item['created_at'] }}
            </td> 
        </tr>
    @endforeach
</table>
</html>