<table>

    @if( $type == 'SIM' )

        <tr>
            <td colspan="4">
                <strong>{{trans('export_excel.package_rates.title_prices_per_person',[],$lang)}}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <strong>{{ $title }}</strong>
            </td>
        </tr>
        <thead>
        <tr>
            <th>{{trans('export_excel.package_rates.hotel_category',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.simple',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.double',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.triple',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.child_with_bed',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.child_without_bed',[],$lang)}}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td>{{ $c['simple'] }}</td>
                <td>{{ $c['double']  }}</td>
                <td>{{ $c['triple']  }}</td>
                <td>{{ $c['child_with_bed']  }}</td>
                <td>{{ $c['child_without_bed']  }}</td>
            </tr>
        @endforeach
        </tbody>
    @else
        <tr>
            <td colspan="4">
                <strong>{{trans('export_excel.package_rates.price_per_person',[],$lang)}}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="{{ 1 + count( $headers ) }}">
                <strong>{{ $title }}</strong>
            </td>
        </tr>
        <thead>
        <tr>
            <th>{{trans('export_excel.package_rates.hotel_category',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.child_with_bed',[],$lang)}}</th>
            <th>{{trans('export_excel.package_rates.child_without_bed',[],$lang)}}</th>
            @foreach($headers as $h)
                <th>{{ $h['header_name'] }}</th>
            @endforeach

        </tr>
        </thead>
        <tbody>
        @foreach($categories as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td>{{ $c['rates_child']['child_with_bed']}}</td>
                <td>{{ $c['rates_child']['child_without_bed']}}</td>

                @foreach($c['rates'] as $rate)
                    <td>{{ $rate['rate_value'] }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>

    @endif

</table>
