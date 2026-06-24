<table>
    <thead>
        <tr>
            <th  align="center"> <b>Ticket Name</b></th>
            <th  align="center" style="width:150px;"><b>Ticket Type</b></th>
            <th  align="center"style="width:150px;"><b>Problem Type</b></th>
            <th  align="center" style="width:100px;"><b>Owner</b></th>
            <th  align="center" style="width:150px;"><b>Created Date</b></th>
            <th  align="center" style="width:150px;"><b>Status</b></th>
            <th  align="center" style="width:250px;"><b>Assigned Members</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $ticket->ticket_name }}</td>
            <td>{{ $ticket->ticketType->ticket_type ?? 'N/A' }}</td>
            <td>{{ $ticket->problemType->problem_type ?? 'N/A' }}</td>
            <td>{{ $ticket->TicketOwner->name ?? 'N/A' }}</td>
            <td>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y h:i A') }}</td>
            <td>{{ $ticket->ticketStatus->proj_status_name ?? 'Unknown' }}</td>
            <td> 
                 @if ($ticket->AssignedTicketMembers->isEmpty()) 
                        <ul><li>No members assigned</li></ul>
                @else 
                    <ul>
                        @foreach ($ticket->AssignedTicketMembers as $member) 
                            <li> {{ ($member->user->name ?? 'Unknown') }} </li>
                        @endforeach  
                    </ul>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>