<table>
    <thead>
        <tr>
            <th  align="center"> <b>Inventory Type</b></th>
            <th  align="center" style="width:150px;"><b>Brand Name</b></th>
            <th  align="center"style="width:150px;"><b>Inventory Name</b></th>
            <th  align="center" style="width:100px;"><b>Serial Number</b></th>
            <th  align="center" style="width:150px;"><b>Assigned Employee Name</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $ticket->AssetType->item_type_name  }}</td>
            <td>{{ $ticket->AssetBrand->brand_name  ?? 'N/A' }}</td>
            <td>{{ $ticket->asset_name ?? 'N/A' }}</td>
             <td>{{ $ticket->serial_number ?? 'N/A' }}</td>
            <td>{{ $ticket->assignments->map(fn($a) => $a->employee->name)->implode(', ') ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>