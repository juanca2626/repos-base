<html>
<table>
    <thead>
    <tr>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>ROL</b>
        </th>
        <th style="background-color: #bd0d12;color: #ffffff;">
            <b>PERMISOS</b>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach($roles as $key => $role)
        <tr>
            <td style="background-color: #bd0d12;color: #ffffff;">
                {{ $role['role'] }}
            </td>
            <td style="background-color: #bd0d12;color: #ffffff;">
            </td>
        </tr>
        @foreach($role['permissions'] as $key => $permission)
            <tr>
                <td></td>
                <td>{{$key}}</td>
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
</html>
