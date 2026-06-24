<table>
    <thead>
        <tr>
            <th  align="center"> <b>Item Type</b></th>
            <th  align="center" style="width:150px;"><b>Item Category</b></th>
            <th  align="center" style="width:150px;"><b>Brand Name</b></th>
            <th  align="center"style="width:150px;"><b>Item Name</b></th>
            <th  align="center" style="width:100px;"><b>Serial Number</b></th>
            <th  align="center" style="width:150px;"><b>Assigned Employee Name</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $ticket->item_type  }}</td>
            <td>{{ $ticket->item_category_name  }}</td>
            <td>{{ $ticket->itemBrand->brand_name  ?? 'N/A' }}</td>
            <td>{{ $ticket->item_name  }}</td>
             <td>{{ $ticket->serial_number ?? 'N/A' }}</td>
            <td>{{ $ticket->assignments->map(fn($a) => $a->employee->name)->implode(', ') ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>