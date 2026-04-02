<html>
@foreach($hotels_group as $hotel)
    <table>
        <tr>
            <th><b>{{$hotel['id']}} - {{$hotel['name']}}</b></th>
        </tr>
        <tr>
            <th><b>Tarifas:</b></th>
        </tr>
        @foreach($hotel['rate_plans'] as $rate_plan)
            <tr>
                <th>{{$rate_plan['id']}} - {{$rate_plan['name']}}</th>
                <th>{{secure_url("/#/hotels/" . $hotel['id'] . "/manage_hotel/rates/rates/cost/edit/". $rate_plan['id'])}}</th>
            </tr>
        @endforeach
    </table>

@endforeach
<style>
    th, td {
        padding: 15px;
        text-align: left;
    }

    th {
        background-color: #04AA6D;
        color: white;
    }
</style>
</html>
