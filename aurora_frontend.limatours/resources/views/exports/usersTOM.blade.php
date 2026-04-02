<?php $users = $data; ?>

<table class="table table-striped">
    <thead>
    <tr>
        <th class="center">Región</th>
        <th class="center">Jefe Regional</th>
        <th>Ejecutivo</th>
        <th>Cliente</th>
        <th>Razon Social</th>
        <th>Pais</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $key => $executive)
        <tr>
            <th class="center">C{{ $executive['REGION'] }}</th>
            <th class="center">{{ $executive['JEFE_REGIONAL'] }}</th>
            <td class="center">{{ $executive['NOMESP'] }}</td>
            <td class="center">{{ $executive['NOMING'] }}</td>
            <td class="center">{{ $executive['RAZON'] }}</td>
            <td class="center">{{ $executive['PAIS'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
