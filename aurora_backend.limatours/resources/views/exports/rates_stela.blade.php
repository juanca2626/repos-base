<html>
<table>
    <thead>
    <tr>
        <td>
            <b>Hotel</b>
        </td>
        <td>
            <b>Enlace</b>
        </td>
    </tr>
    @foreach($data['date_range_hotels'] as $key => $value)
        <?php $hotel = $value['rate_plan_room']['room']['hotel']; ?>
        <?php if(@$hotel['id'] > 0): ?>
        <tr>
            <td>
                <b>{{ $hotel['channels'][0]['pivot']['code'] }} - {{ $hotel['name'] }}</b>
            </td>
            <td>
                <b>{{ url('/#/hotels/' . $hotel['id'] . '/manage_hotel/rates/rates/cost/edit/' . $value['rate_plan_room']['rates_plans_id']) }}</b>
            </td>
        </tr>
        <?php endif; ?>
    @endforeach
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Servicio</b>
        </td>
        <td>
            <b>Enlace</b>
        </td>
    </tr>
    @foreach($data['date_range_services'] as $key => $value)
        <?php $service = $value['service_rate']['service']; ?>
        <?php if(@$service['id'] > 0): ?>
        <tr>
            <td>
                <b>{{ $service['aurora_code'] }} - {{ $service['name'] }}</b>
            </td>
            <td>
                <b>{{ url('/#/services_new/' . $service['id'] . '/manage_service/rates/cost/edit/' . $value['service_rate_id']) }}</b>
            </td>
        </tr>
        <?php endif; ?>
    @endforeach
</table>
</html>
