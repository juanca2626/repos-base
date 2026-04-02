<table class="table table-striped">
    <thead>
    <tr>
        <th>APELLIDOS</th>
        <th>NOMBRES</th>
        <th>TIPO</th>
        <th>GENERO</th>
        <th>FECHA NACIMIENTO</th>
        <th>TIPO DOCUMENTO</th>
        <th>NUMERO DOCUMENTO</th>
        <th>CORREO ELECTRONICO</th>
        <th>TELEFONO</th>
        <th>OBSERVACIONES</th>
    </tr>
    </thead>
    <tbody>
    @if(sizeof($data) > 0)
        <?php $tipos = ['ADL' => 'ADULTO', 'CHD' => 'NIÑO', 'INF' => 'INFANTE']; ?>
        @foreach($data as $key => $passenger)
            <tr>
                <td class="center">{{ $passenger['apellidos'] }}</td>
                <td class="center">{{ $passenger['nombres'] }}</td>
                <td class="center">{{ $tipos[$passenger['tipo']] }}</td>
                <td class="center">{{ $passenger['sexo'] }}</td>
                <td class="center">{{ $passenger['fecnac'] }}</td>
                <td class="center">{{ $passenger['tipdoc'] }}</td>
                <td class="center">{{ $passenger['nrodoc'] }}</td>
                <td class="center">{{ $passenger['correo'] }}</td>
                <td class="center">{{ $passenger['celula'] }}</td>
                <td class="center">{{ @$passenger['observ'] }}</td>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>
