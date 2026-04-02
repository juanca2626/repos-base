<html>
<table>
    <thead>
    <tr>
        <td style="background-color: #bd0d12;color: #ffffff;">
            <b>ID</b>
        </td>
        <td style="background-color: #bd0d12;color: #ffffff;">
            <b>HOTEL</b>
        </td>
        <td style="background-color: #bd0d12;color: #ffffff;">
            <b>URL</b>
        </td>
    </tr>
    @foreach($hotels as $key => $value)
    <tr>
        <td>
            {{ $value['id'] }}
        </td>
        <td>
            {{ $value['channels'][0]['pivot']['code'] }} - {{ $value['name'] }}
        </td>
        <td>
            {{ secure_url('/#/hotels/' . $value['id'] . '/manage_hotel/gallery') }}
        </td>
    </tr>
    @endforeach
</table>
</html>
