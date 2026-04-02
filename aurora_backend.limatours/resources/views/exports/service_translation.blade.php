<html>
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>ID</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>CODIGO</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>Politica</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>NOTAS</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>NOMBRE</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>DESCRIPCION</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>ITINERARIO</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>SUMMARY</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>INCLUSIONES</b>
            </td>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>OPERATIVIDAD</b>
            </td>
        </tr>
        </thead>
        <tbody>
{{--        @dd($translation['name_commercial'])--}}
        @foreach( $translation as $trans_text)
{{--            @dd($trans_text)--}}
            @foreach( $trans_text as $trans)
{{--                @dd($trans)--}}
                <tr>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['id'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['code'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['police'] }}</b></td>
                    <td style="background-color: #d3e1ec"> <b>{{ $trans['notes'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ trim($trans['name']) }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ trim($trans['description']) }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['itinerary'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['summary'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['inclusions'] }}</b></td>
                   <td style="background-color: #d3e1ec"> <b>{{ $trans['operations'] }}</b></td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</html>
