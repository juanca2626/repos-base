<?php $hotels = $data['hoteles']; $file = $data['file']; ?>

<style type="text/css">
    * {
        font-family: 'Arial';
    }
</style>
<table>
    <tr>
        <td colspan="2" scope="row">
            <h2>REF.: {{ $file['DESCRI'] }}</h2>
        </td>
    </tr>
    <tr>
        <td colspan="2" scope="row">
            <h3>{{ strtoupper(trans('files.label.list_of_hotels')) }}</h3>
        </td>
    </tr>
    @foreach($hotels as $key => $hotel)
    <tr>
        <td style="vertical-align: top;">{{ $hotel['FECIN'] . ' - ' . $hotel['FECOUT'] }}</td>
        <td style="vertical-align: top;">
            <div>
                @if($hotel['RAZON'] != null)
                    <span style="display:block;">{{ $hotel['RAZON'] }}</span><br />
                @endif
                @if($hotel['DIRECC'] != null)
                    <span style="display:block;">{{ $hotel['DIRECC'] }}</span><br />
                @endif
                @if($hotel['CIUDAD'] != null)
                    <span style="display:block;">{{ $hotel['CIUDAD'] . ' ' . $hotel['PAIS'] }}</span><br />
                @endif
                @if($hotel['TELF'] != null)
                    <span style="display:block;">{{ $hotel['TELF'] }}</span><br />
                @endif
                @if($hotel['FAX'] != null)
                    <span style="display:block;">{{ $hotel['FAX'] }}</span><br />
                @endif
                @if($hotel['TIPOHAB'] != null)
                    <span>{!! $hotel['TIPOHAB'] !!}</span><br />
                @endif
                @if($hotel['TEXT2'] != null)
                    <span style="display:block;">{!! $hotel['TEXT2'] !!}</span><br />
                @endif
                RECORD: {{ $hotel['CONFIRMA'] }}
            </div>
        </td>
    </tr>
    @endforeach
</table>
