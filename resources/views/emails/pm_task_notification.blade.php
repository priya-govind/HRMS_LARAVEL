<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Task Notification' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { padding: 20px; }
        .footer { margin-top: 30px; font-size: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 14px; }
        th { background-color: #f4f4f4; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <p>Dear Team,</p>

        <h2>{{ $subject }}</h2>
        <p>{{ $mailMessage }}</p>

        @if($show_team==true)
            <h3>Assigned Members</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignedMembers ?? [] as $emp)
                            <tr>
                                <td>{{ $emp['name'] }}</td>
                                <td>{{ $emp['email'] }}</td>
                                <td>{{ $emp['role'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        @endif

        <div class="footer">
            <p>Regards,<br>
            {{ $sender['name'] }}<br>
            {{ $sender['role'] }}</p>
        </div>
    </div>
</body>
</html>