<html>
    <table>
        <thead>
        <tr>
            <td style="background-color: #8d0a0d;color: #ffffff;" >
                <b>Slug</b>
            </td>
            @foreach( $data["languages"] as $language)
                <td style="background-color: #8d0a0d;color: #ffffff;">{{ $language["iso"] }}</td>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach( $data["translations"] as $translation)
            <tr>
                <td>{{$translation["slug"]}}</td>
                @foreach( $translation["translations"] as $translation)
                    <td>{{ $translation["value"] }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</html>
