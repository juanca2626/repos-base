<html>
<table>
    <thead>
    <tr>
        <th>
            <b>Hotel</b>
        </th>
        <th>
            <b>Tarifas</b>
        </th>
        <th>
            <b>Enlace</b>
        </th>
        <th>
            <b>Notas</b>
        </th>
    </tr>
    </thead>
</table>
@foreach($hotels as $key => $value)
    <table>
        <tbody>
        <tr>
            <td>
                <b>{{ $value['id'] }} - {{ $value['name'] }}</b>
            </td>
        </tr>
        </tbody>
    </table>
    <table>
        <tbody>
        <tr>
            <td></td>
            <td>
                <table>
                    @foreach($value['rates_plans'] as $key => $rate)
                        <tr>
                            <td>
                                {{ $rate->id }} - {{ $rate->name }}
                            </td>
                            <td>
                                {{ secure_url('/#/hotels/' . $value->id . '/manage_hotel/rates/rates/cost/edit/'. $rate->id ) }}
                            </td>
                            <td>
                                {{$rate->translations_notes_es}}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        </tbody>
    </table>
@endforeach
</html>
