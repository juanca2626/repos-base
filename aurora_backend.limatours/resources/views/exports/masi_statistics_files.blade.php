<html>
<table>
    <thead>
        <tr>
            <th style="font-weight: bold">TIPO DE FILE</th>
            <th style="font-weight: bold">REGIÓN</th>
            <th style="font-weight: bold">PAÍS</th>
            <th style="font-weight: bold">CLIENTE</th>
            <th style="font-weight: bold">NÚMERO DE FILE</th>
            <th style="font-weight: bold">FECHA DE LLEGADA</th>
            <th style="font-weight: bold">EJECUTIVA</th>
            <th style="font-weight: bold">NÚMERO DE TELÉFONO</th>
            <th style="font-weight: bold">CORREO ELECTRÓNICO</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($files as $file)
        <tr>
            <td>{{trim($file['type'])}}</td>
            <td>{{trim($file['region'])}}</td>
            <td>{{trim($file['country'])}}</td>
            <td>{{trim($file['client'])}}</td>
            <td>{{trim($file['nroref'])}}</td>
            <td>{{trim($file['arrive_date'])}}</td>
            <td>{{trim($file['executive'])}}</td>
            <td>{{trim($file['phone_number'])}}</td>
            <td>{{trim($file['email'])}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</html>
