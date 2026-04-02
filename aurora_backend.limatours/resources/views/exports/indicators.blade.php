<html>
<table>
    <thead>
    <tr>
        <td>
            <b>Clientes nuevos con Acceso a A2</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $access_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>


<table>
    <thead>
    <tr>
        <td>
            <b>Usuarios nuevos con Acceso a A2 (Asociados a clientes)</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $user_access_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Clientes nuevos que han accedido a A2</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $login_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Usuarios nuevos que han accedido a A2 (Asociados a clientes)</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $user_login_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Cotizaciones realizadas por clientes</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $quote_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Files creados por clientes</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $reservation_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Files creados por clientes (Fecha de Inicio)</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $reservation_ope_clients_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Cotizaciones realizadas por ejecutivas</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $quote_executives_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Files creados por ejecutivas</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $reservation_executives_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

<table>
    <thead>
    <tr>
        <td>
            <b>Files creados por ejecutivas (Fecha de Inicio)</b>
        </td>
    </tr>
    </thead>
</table>

<table>
    <thead>
    <tr>
        <td>
            
        </td>
        @foreach($years as $year)
        <td>
            <b>{{ $year }}</b>
        </td>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <b>TOTAL</b>
            </td>
            @foreach($years as $year)
            <td>
                {{ $reservation_ope_executives_a2[$year] }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>

</html>
