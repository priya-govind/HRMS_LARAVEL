<table border="1">
    <thead>
        <tr>
            <th align="center" colspan="5" style="background: #d9f2d0; font-size:16px;font-weight:bold;" >FORTIGRID ICT OFFICE EXPENSES</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th align="center" style="background:#fbe3d6; font-size:12px; color:red; font-weight:bold;">Date</th>
            <th align="center" style="background:#dceaf7; font-size:12px; color:red; font-weight:bold;">Expenses</th>
            <th align="center" style="background:#dceaf7; font-size:12px; color:red; font-weight:bold;">Credit</th>
            <th align="center" style="background:#dceaf7; font-size:12px; color:red; font-weight:bold;">Debit</th>
            <th align="center" style="background:#f2f2f2; font-size:12px; color:red; font-weight:bold;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $txn)
            <tr>
                <td style="background:#fbe3d6;font-weight:bold;">{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d-M Y') }}</td>
                <td style="background:#dceaf7;font-weight:bold;">
                    @if($txn->transaction_type === 'debit')
                        @foreach($txn->items as $item)
                            {{ $item->expenseItem->expense_type_name }}@if(!$loop->last), @endif
                        @endforeach
                    @else
                        {{ $txn->remarks }}
                    @endif
                </td>
                <td style="background:#dceaf7;font-weight:bold;">
                    @if($txn->transaction_type === 'credit')
                        {{ $txn->amount }}
                    @endif
                </td>
                <td style="background:#dceaf7;font-weight:bold;">
                    @if($txn->transaction_type === 'debit')
                        {{ $txn->amount }}
                    @endif
                </td>
                <td style="background:#f2f2f2;font-weight:bold;">{{ $txn->available_amt }}</td>
            </tr>
        @endforeach
    </tbody>
</table>