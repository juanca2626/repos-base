@php
    $billings = $data; 
    $totalAmountMust = 0;
    $totalPaymentAmount = 0; 
    $totalDebit = 0;
    $totalBalance = 0;
@endphp

<table class="table text-center table-facturacion">
    <thead>
    <tr>
        <th scope="col">{{ trans('board.th.customer') }}</th>
        <th scope="col">QRV</th>
        <th scope="col">{{ trans('board.th.file') }}</th>
        <th scope="col">{{ trans('board.th.group_name') }}</th>
        <th scope="col">{{ trans('board.th.number_guests') }}</th>
        <th scope="col">{{ trans('board.th.service_startdate') }}</th>
        <th scope="col">{{ trans('board.th.enddate_of_the_service') }}</th>
        <th scope="col">{{ trans('board.th.amount_must') }}</th>
        <th scope="col">{{ trans('board.th.debit') }}</th>
        <th scope="col">{{ trans('board.th.payment_amount') }}</th>
        <th scope="col">{{ trans('board.th.balance') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($billings as $key => $billing)
        <tr>
            <td>{{ $billing['CODCLI'] }}</td>
            <td>{{ $billing['CODOPE'] }}</td>
            <td>{{ $billing['NROREF'] }}</td>
            <td>{{ $billing['DESCRI'] }}</td>
            <td>{{ $billing['CANPAX'] }}</td>
            <td>{{ $billing['DIAIN'] }}</td>
            <td>{{ $billing['DIAOUT'] }}</td>
            <td>{{ (float)str_replace(',', '', $billing['STDEBE'] ?? 0) }}</td>
            <td>{{ (float)str_replace(',', '', $billing['NCDEBE'] ?? 0) }}</td>
            <td>{{ (float)str_replace(',', '', $billing['IMPAGO'] ?? 0) }}</td>
            <td>{{ (float)str_replace(',', '', $billing['saldo'] ?? 0) }}</td>
        </tr>
        @php 
           $totalAmountMust += (float)str_replace(',', '', $billing['STDEBE'] ?? 0);
           $totalDebit += (float)str_replace(',', '', $billing['NCDEBE'] ?? 0);
           $totalPaymentAmount += (float)str_replace(',', '', $billing['IMPAGO'] ?? 0);
           $totalBalance += (float)str_replace(',', '', $billing['saldo'] ?? 0);
        @endphp
    @endforeach
    <tr>
        <td colspan="7"><strong>Total</strong></td>
        <td><strong>{{ $totalAmountMust }}</strong></td>
        <td><strong>{{ $totalDebit }}</strong></td>
        <td><strong>{{ $totalPaymentAmount }}</strong></td>
        <td><strong>{{ $totalBalance }}</strong></td>
    </tr>
    </tbody>
</table>