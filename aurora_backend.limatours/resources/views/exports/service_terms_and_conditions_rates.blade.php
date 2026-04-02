<table>
    <thead>
    <tr></tr>
    <tr>
        <td></td>
        <td></td>
        <td style="font-size: 24px;">{{ trans('services_rate.' . $year_view . '.title_terms_and_conditions',[],$lang) }}</td>
        <td></td>
        <td></td>
    </tr>
    </thead>
    <tbody>
    <tr></tr>
    <tr></tr>
    {{-- Condiciones Generales del Catálogo de Tarifas --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.conditions_generals.title',['year' => $year],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.conditions_generals.text_line_1',['year' => $year],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.conditions_generals.text_line_2',['year' => $year],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 1.- INFORMACIÓN GENERAL DEL CATÁLOGO DE TARIFAS 2022 --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.general_information.title',['year' => $year],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general_information.text_line_1',['year' => $year],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general_information.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 2.- TÉRMINOS Y CONDICIONES --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.terms_and_conditions.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.terms_and_conditions.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.terms_and_conditions.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.terms_and_conditions.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 3.- CONDICIONES DEL SERVICIO --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.service_conditions.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_21',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_4',[],$lang) }}
        </td>
    </tr>

    @if(trans()->has('services_rate.' . $year_view . '.service_conditions.sub_title', [], $lang))
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.service_conditions.sub_title',[],$lang) }}</b>
        </td>
    </tr>
    @endif
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_5',['year' => $year],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_7',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_71',[],$lang) }}
        </td>
    </tr>
    @if(trans()->has('services_rate.' . $year_view . '.service_conditions.text_line_8', [], $lang))
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_conditions.text_line_8',[],$lang) }}
        </td>
    </tr>
    @endif
    <tr></tr>

    @if(trans()->has('services_rate.' . $year_view . '.service_condition_restrinction.title', [], $lang))

    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.service_condition_restrinction.title',[],$lang) }}</b>
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_1',[],$lang) }}
        </td>
    </tr>
    
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_2',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_3',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_4',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_5',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_6',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_7',[],$lang) }}
        </td>
    </tr>

    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_8',[],$lang) }}
        </td>
    </tr>
     <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_condition_restrinction.text_line_9',[],$lang) }}
        </td>
    </tr>
    @endif
    <tr></tr>
    {{-- 4.- SERVICIOS NO INCLUIDOS  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.service_not_included.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_5',[],$lang) }}
        </td>
    </tr>
    @if(trans()->has('services_rate.' . $year_view . '.service_not_included.text_line_6', [], $lang))
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.service_not_included.text_line_6',[],$lang) }}
        </td>
    </tr>
    @endif
    <tr></tr>
    {{-- 5.- CANCELACIONES Y NO SHOWS  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.sub_title_1',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title_column_1',[],$lang) }}</b>
        </td>
        <td></td>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title_column_2',[],$lang) }}</b>
        </td>
        <td></td>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title_column_3',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_1_text_1',[],$lang) }}
        </td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_2_text_1',[],$lang) }}
        </td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_3_text_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_1_text_2',[],$lang) }}
        </td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_2_text_2',[],$lang) }}
        </td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_3_text_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_5',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    @if(trans()->has('services_rate.' . $year_view . '.cancellations_no_shows.sub_title_2', [], $lang))
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.sub_title_2',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_7',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_8',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    @endif
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.sub_title_3',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_9',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title_column_4',[],$lang) }}</b>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.title_column_5',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_4_text_1',[],$lang) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_5_text_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_4_text_2',[],$lang) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_5_text_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_4_text_3',[],$lang) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_5_text_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_4_text_4',[],$lang) }}
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.column_5_text_4',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_10',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_11',[],$lang) }}
        </td>
    </tr>    
    <tr></tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_12_title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.cancellations_no_shows.text_line_12',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 6.- NOTAS DE OPERATIVIDAD DEL DESTINO:  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.destination_operation.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_5',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.destination_operation.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 7.- PAGOS  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.payments.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.payments.text_line_1',[],$lang) }}
        </td>
    </tr>
    @if(trans()->has('services_rate.' . $year_view . '.payments.text_line_2', [], $lang))
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.payments.text_line_2',[],$lang) }}
        </td>
    </tr>
    @endif
    <tr></tr>
    {{-- 8.- GENERAL  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.general.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_5',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_7',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.general.text_line_8',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 9.- RESERVA DE LOS SERVICIOS TURÍSTICOS OFRECIDOS POR LIMATOURS  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.reservation_of_tourist.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.reservation_of_tourist.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.reservation_of_tourist.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.reservation_of_tourist.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.reservation_of_tourist.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 10.- RECLAMACIONES Y RESPONSABILIDAD. CLÁUSULA DE EXENCIÓN DE RESPONSABILIDAD  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.claims_and_liability.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_5',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_7',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_8',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_9',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_10',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_11',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_12',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_13',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_14',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_15',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.claims_and_liability.sub_title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_16',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_17',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_18',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_19',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_20',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_21',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_22',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_23',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_24',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.claims_and_liability.text_line_25',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 11.- CONTENIDOS Y CONFIDENCIALIDAD  --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.sub_title_1',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_1',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_2',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_3',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_4',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_5',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_6',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_7',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_8',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_9',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_10',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_11',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_12',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_13',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.sub_title_2',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_14',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_15',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_16',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_17',[],$lang) }}
        </td>
    </tr>
    <tr>
        <td>
            {{ trans('services_rate.' . $year_view . '.contents_and_confidentiality.text_line_18',[],$lang) }}
        </td>
    </tr>
    <tr></tr>
    {{-- 12. DERECHOS DE PROPIEDAD Y OTROS DERECHOS --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.sub_title_1',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_1',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_2',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_3',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_4',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_5',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_6',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_7',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_8',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_9',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td><b>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.sub_title_2',[],$lang) }}</b></td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_10',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_11',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_12',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_13',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_14',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_15',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_16',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_17',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_18',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_19',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_20',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_21',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_22',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_23',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.property_right_other_rights.text_line_24',[],$lang) }}</td>
    </tr>
    <tr></tr>
    {{-- 13.- ANTISOBORNO, RESTRICCIONES COMERCIALES Y ÉTICA COMERCIAL --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_1',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_2',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_3',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_4',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_5',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_6',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_7',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_8',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_9',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_10',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_11',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_12',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_13',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.anti_bribery_commercial.text_line_14',[],$lang) }}</td>
    </tr>
    <tr></tr>
    {{-- 14.- DESARROLLO SOSTENIBLE --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.sustainable_development.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_1',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_2',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_3',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_4',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_5',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_6',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_7',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_8',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_9',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_10',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.sustainable_development.text_line_11',[],$lang) }}</td>
    </tr>
    <tr></tr>
    {{-- 15.- CESIÓN DE DERECHOS --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.cession_of_rights.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.cession_of_rights.text_line_1',[],$lang) }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.cession_of_rights.text_line_2',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.cession_of_rights.text_line_3',[],$lang) }}</td>
    </tr>
    <tr></tr>
    {{-- 16.- LEGISLACIÓN Y FUERO --}}
    <tr>
        <td>
            <b>{{ trans('services_rate.' . $year_view . '.legislation_and_jurisdiction.title',[],$lang) }}</b>
        </td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.legislation_and_jurisdiction.text_line_1',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.legislation_and_jurisdiction.text_line_2',[],$lang) }}</td>
    </tr>
    <tr>
        <td>{{ trans('services_rate.' . $year_view . '.legislation_and_jurisdiction.text_line_3',[],$lang) }}</td>
    </tr>
    </tbody>
</table>
