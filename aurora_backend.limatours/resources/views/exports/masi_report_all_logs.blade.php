<table class="table">
    <thead>
    <tr>
        <th>Fecha / Hora</th>
        <th>Cliente</th>
        <th>Nombre</th>
        <th>Email / Celular</th>
        <th>Tipo de Envío</th>
        <th>Tipo Mensaje</th>
        <th>Estado</th>
    </tr>
    </thead>
    <tbody>
        @foreach($data['data'] as $key => $detail)
        <tr>
            <td class="text-center">
                {{ date("d/m/Y", strtotime($detail['feclog'])) }} {{ $detail['horlog'] }}
            </td>
            <td class="text-center">{{ $detail['razon'] }}</td>
            <td class="text-center">{{ $detail['nombre'] }}</td>
            <td class="text-center">{{ $detail['email'] }}</td>
            <td class="text-center">
                <div>
                    @if($detail['tipo'] == 1)
                        E-mail
                    @endif
                    @if($detail['tipo'] == 2)
                        Whatsapp
                    @endif
                </div>
            </td>
            <td class="text-center">
                <div>
                    @if($detail['tipomensaje'] == 1)
                        1 semana antes
                    @endif
                    @if($detail['tipomensaje'] == 2)
                        24 horas antes
                    @endif
                    @if($detail['tipomensaje'] == 3)
                        Día a día
                    @endif
                    @if($detail['tipomensaje'] == 5)
                        Despedida
                    @endif
                </div>
            </td>
            <td class="text-center">{{ $detail['estado'] }}</td>
        </tr>
    @endforeach
</tbody>
</table>