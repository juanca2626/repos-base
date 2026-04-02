<html>
    <table>
        <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>NAME</th>
            <th>STATUS</th>
            <th>DESCRIPTION(ES)</th>
            <th>ADDRESS(ES)</th>
            <th>DESCRIPTION(EN)</th>
            <th>ADDRESS(EN)</th>
            <th>AMENITIES</th>
            <th>CHECK IN</th>
            <th>CHECK OUT</th>
            <th>STARS</th>
            <th>CLASS</th>
        </tr>
        @foreach( $hotels as $hotel)

                <tr style="background: #bcbcbc;">
                    <td>
                        {{ $hotel['id'] }}
                    </td>
                    <td>
                        {{ $hotel['channel'][0]['code'] }}
                    </td>
                    <td>
                        {{ $hotel['name'] }}
                    </td>
                    <td>
                        {{ $hotel['status'] }}
                    </td>
                    <td>
                        @if( count($hotel['translations']) > 0 )
                            @foreach( $hotel['translations'] as $translation )
                                @if( $translation['language_id'] == 1 and $translation['slug'] == "hotel_description" )
                                   {{ $translation['value'] }}
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if( count($hotel['translations']) > 0 )
                            @foreach( $hotel['translations'] as $translation )
                                @if( $translation['language_id'] ==1 and $translation['slug'] == "hotel_address" )
                                    {{ $translation['value'] }}
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if( count($hotel['translations']) > 0 )
                            @foreach( $hotel['translations'] as $translation )
                                @if( $translation['language_id'] ==2 and $translation['slug'] == "hotel_description" )
                                    {{ $translation['value'] }}
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if( count($hotel['translations']) > 0 )
                            @foreach( $hotel['translations'] as $translation )
                                @if( $translation['language_id'] ==2 and $translation['slug'] == "hotel_address" )
                                    {{ $translation['value'] }}
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @foreach( $hotel['amenity'] as $amenity )
                            {{ $amenity['translations'][0]['value'] }},
                        @endforeach
                    </td>
                    <td>
                        {{ $hotel['check_in_time'] }}
                    </td>
                    <td>
                        {{ $hotel['check_out_time'] }}
                    </td>
                    <td>
                        {{ $hotel['stars'] }}
                    </td>
                    <td>
                        {{ $hotel['typeclass']['translations'][0]['value'] }}
                    </td>
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
