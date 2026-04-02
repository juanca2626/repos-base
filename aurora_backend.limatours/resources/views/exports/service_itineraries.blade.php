<?php $langs = ['ESPAÑOL', 'INGLES', 'PORTUGUES']; ?>
<table>
    <thead>
    <tr>
        <td>
            <b>ID</b>
        </td>
        <td>
            <b>REFERENCIA</b>
        </td>
        @foreach($langs as $key => $value)
            <td>
                <b>SERVICIO - {{ $value }}</b>
            </td>
            <td>
                <b>ITINERARIO - {{ $value }}</b>
            </td>
        @endforeach
        <td>
            <b>CANTIDAD (OCT {{ $data['year_from'] }} - SET {{ $data['year_to'] }})</b>
        </td>
    </tr>
    </thead>
    <tbody>
    @foreach($data['services'] as $key => $value)
        <tr>
            <td>
                {{ $value['id'] }}
            </td>
            <td>
                {{ $value['name'] }}
            </td>
            @foreach($langs as $k => $v)
                <td>
                    {{ (isset($value['service_translations'][$k]['name'])) ? $value['service_translations'][$k]['name'] : '' }}
                </td>
                <td>
                    {{ (isset($value['service_translations'][$k]['itinerary_commercial'])) ? $value['service_translations'][$k]['itinerary_commercial'] : '' }}
                </td>
            @endforeach
            <td>
                {{ $value['reservations_services_count'] }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>