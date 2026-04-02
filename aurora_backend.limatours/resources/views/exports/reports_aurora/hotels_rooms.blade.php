<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>ID HOTEL</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>NOMBRE DE HOTEL</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>ID DE HAB.</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>NOMBRE HABITACIÓN - (ES)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>DESCRIPCIÓN HABITACIÓN - (ES)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>NOMBRE HABITACIÓN - (EN)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>DESCRIPCIÓN HABITACIÓN - (EN)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>NOMBRE HABITACIÓN - (PT)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>DESCRIPCIÓN HABITACIÓN - (PT)</b></th>
        <th style="background-color: #bd0d12;color: #ffffff;"><b>ESTADO DE HAB.</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($hotels as $hotel)
        <tr>
            <td style="background-color: #bd0d12;color: #ffffff;">
                {{ $hotel['id'] }}
            </td>
            <td style="background-color: #bd0d12;color: #ffffff;">
                {{ $hotel['name'] }}
            </td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
            <td style="background-color: #bd0d12;color: #ffffff;"></td>
        </tr>
        @foreach($hotel['rooms'] as $room)
            <tr>
                <td></td>
                <td></td>
                <td>{{ $room['id'] }}</td>
                {{-- Inicializamos las variables para cada idioma --}}
                @php
                    $name_es = $description_es = $name_en = $description_en = $name_pt = $description_pt = '';
                @endphp

                {{-- Recorremos las traducciones de nombre --}}
                @foreach($room['room_name_translations'] as $translation)
                    @if($translation['language_id'] == 1)
                        @php $name_es = $translation['value']; @endphp
                    @elseif($translation['language_id'] == 2)
                        @php $name_en = $translation['value']; @endphp
                    @elseif($translation['language_id'] == 3)
                        @php $name_pt = $translation['value']; @endphp
                    @endif
                @endforeach

                {{-- Recorremos las traducciones de descripción --}}
                @foreach($room['room_description_translations'] as $translation)
                    @if($translation['language_id'] == 1)
                        @php $description_es = $translation['value']; @endphp
                    @elseif($translation['language_id'] == 2)
                        @php $description_en = $translation['value']; @endphp
                    @elseif($translation['language_id'] == 3)
                        @php $description_pt = $translation['value']; @endphp
                    @endif
                @endforeach

                {{-- Imprimimos las traducciones --}}
                <td>{{ $name_es }}</td>
                <td>{{ $description_es }}</td>
                <td>{{ $name_en }}</td>
                <td>{{ $description_en }}</td>
                <td>{{ $name_pt }}</td>
                <td>{{ $description_pt }}</td>

                {{-- Estado de la habitación --}}
                <td>{{ $room['state'] == 1 ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
</html>
