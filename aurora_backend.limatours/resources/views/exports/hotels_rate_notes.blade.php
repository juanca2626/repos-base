<html>
    <table>
        <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>HOTEL NAME</th>
            <th style="min-width: 500px;">NOTA ES</th>
            <th style="min-width: 500px;">NOTA EN</th>
            <th style="min-width: 500px;">NOTA PT</th>
        </tr>
        @foreach( $hotels as $hotel)
                <tr style="background: #bcbcbc;">
                    <td>
                        {{ $hotel['id'] }}
                    </td>
                    <td>
                        {{ $hotel['channels'][0]['pivot']['code'] }}
                    </td>
                    <td>{{ $hotel['name'] }}</td>

                    @foreach( $hotel['translations'] as $translation)

                        @if($translation['language_id']==1 and $translation['slug']=="summary")
                            <td>{{ $translation['value'] }}</td>
                        @endif
                        @if($translation['language_id']==2 and $translation['slug']=="summary")
                            <td>{{ $translation['value'] }}</td>
                        @endif
                        @if($translation['language_id']==3 and $translation['slug']=="summary")
                            <td>{{ $translation['value'] }}</td>
                        @endif

                    @endforeach
                </tr>

        @endforeach
    </table>
    <style>
        th, td {
            padding: 15px;
            text-align: left;
        }th {
             background-color: #04AA6D;
             color: white;
         }
    </style>
</html>
