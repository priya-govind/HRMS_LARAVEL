@component('mail::message')
# Hi {{ $user->name }},

{{ $title }}
@if (count($assigned) > 0)
Members Assigned:
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr><th>Name</th><th>Team</th><th>Role</th></tr>
    </thead>
    <tbody>
        @foreach ($assigned as $member)
            @if (!($excludeSelf && $member['user_id'] == $user->id))
                <tr>
                    <td>{{ $member['name'] }}</td>
                    <td>{{ $member['team_name'] }}</td>
                    <td>{{ $member['role_name'] }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table><br/>
@endif
@if (count($reporting) > 0)
### Reporting Members:
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr><th>Name</th><th>Team</th><th>Role</th></tr>
    </thead>
    <tbody>
        @foreach ($reporting as $member)
            @if (!($excludeSelf && $member['user_id'] == $user->id))
                <tr>
                    <td>{{ $member['name'] }}</td>
                    <td>{{ $member['team_name'] }}</td>
                    <td>{{ $member['role_name'] }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@endif
Thanks,<br>
Fortgrid Team
@endcomponent