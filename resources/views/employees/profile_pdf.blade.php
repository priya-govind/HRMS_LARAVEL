<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Resume</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }

        /* Header as a table for reliable PDF alignment */
        .header-table {
            width: 100%;
            table-layout: fixed;    /* prevents content from pushing cells unpredictably */
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .header-table td {
            vertical-align: top;     /* keep image aligned at top */
            padding: 0;              /* avoid unexpected spacing */
        }

        .profile-block { padding-right: 16px; } /* small gap before image column */

        .profile-image {
            width: 120px;
            text-align: right;       /* image on the right side */
        }
        .profile-image img {
            display: block;          /* remove inline baseline gap */
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #555;
        }

        h1 { margin: 0; font-size: 20pt; }
        h2 { margin-top: 20px; font-size: 14pt; border-bottom: 1px solid #ccc; }
        ul { margin: 0; padding-left: 20px; }
        .section { margin-bottom: 15px; }
        .subheading { font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
       .header-table th, .header-table td {
            border: none;   /* remove borders */
            padding: 0;     /* keep it tight */
        }

.profile-block { padding-right: 12px; }

    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="profile-block">
                <h1>{{ $user->name }}</h1>
                <p>Email: {{ $user->email }}<br>Phone: {{ $user->phone_number }}</p>
                <p>Address: {{ $user->address }}</p>
            </td>
            <td class="profile-image">
                @if($user->image)
                    <img src="{{ public_path('images/'.$user->image) }}" alt="Profile Image">
                @endif
            </td>
        </tr>
    </table>

    <div class="section">
        <h2>Roles</h2>
        <ul>
            @foreach($user->roles as $role)
                <li>{{ $role->role_name }}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>