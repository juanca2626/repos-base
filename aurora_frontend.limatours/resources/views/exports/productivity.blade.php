<?php $_data = $data['data'];
$_response = $data['response'];
$include = explode(",", $table); ?>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>N° de file</th>
            <th>Venta</th>
            <th>Beneficio</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        @foreach($_response as $f => $_file)
        <tr>
            <th class="text-mutted">
                {{ $f }}
            </th>
            <th class="text-mutted">
                @if($_file['view'] == 'C')
                {{ $_file['items'][0]['razon'] }}
                @else
                {{ $_file['items'][0]['razon_qr'] }}
                @endif
            </th>
            <th class="text-mutted">{{ $_file['quantity'] }}</th>
            <td class="text-mutted">{{ number_format($_file['venta'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($_file['beneficio'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($_file['porcentaje'], 0)  }}%</td>
        </tr>

        @if(in_array($f, $include))
        @foreach($_file['detail'] as $i => $_item)
        <tr>
            <td class="text-mutted">{{ $f . ' - ' . $i }}</td>
            <td class="text-mutted">
                @if($_file['view'] == 'C')
                {{ $_item['items'][0]['razon'] }}
                @else
                {{ $_item['items'][0]['razon_qr'] }}
                @endif
            </td>
            <td class="text-mutted">{{ $_item['quantity'] }}</td>
            <td class="text-mutted">{{ number_format($_item['venta'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($_item['beneficio'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($_item['porcentaje'], 0)  }}%</td>
        </tr>

        @if(in_array($i, $include) AND in_array($f, $include))
        @foreach($_item['items'] as $_i => $__item)
        <tr>
            <td class="text-mutted">{{ $f . ' - ' . $i . ' - ' . $__item['nroref'] }}</td>
            <td class="text-mutted">
                {{ $__item['nombre_file'] }}
            </td>
            <td class="text-mutted">{{ $__item['nroref'] }}</td>
            <td class="text-mutted">{{ number_format($__item['venta'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($__item['beneficio'], 2, ".", ",") }}</td>
            <td class="text-mutted">{{ number_format($__item['porcentaje'], 0)  }}%</td>
        </tr>
        @endforeach
        @endif
        @endforeach
        @endif
        @endforeach
    </tbody>
</table>