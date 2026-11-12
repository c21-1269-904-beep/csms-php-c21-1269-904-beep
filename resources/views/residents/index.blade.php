<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management | CSMS</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263445;
        }

        .container {
            max-width: 1400px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 { margin: 0 0 8px; }
        .subtitle { color: #718096; }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-edit { background: #e0ecff; color: #1d4ed8; }
        .btn-danger { background: #fee2e2; color: #b91c1c; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #dbeafe; color: #1e40af; }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            max-width: 550px;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th, td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
        }

        td { font-size: 14px; }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .active { background: #dcfce7; color: #166534; }
        .inactive { background: #e5e7eb; color: #475569; }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .empty {
            padding: 35px;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 600px) {
            body { padding: 15px; }
            .header { align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Resident Management</h1>
            <div class="subtitle">Manage registered community residents</div>
        </div>

        <a href="{{ route('residents.create') }}" class="btn btn-primary">
            + Register Resident
        </a>
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if(session('info'))
        <div class="alert info">{{ session('info') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">
            Please check the submitted information.
        </div>
    @endif

    <div class="card">

        <form method="GET"
              action="{{ route('residents.index') }}"
              class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search first name or last name..."
                value="{{ $search }}"
            >

            <button type="submit" class="btn btn-primary">Search</button>

            @if($search !== '')
                <a href="{{ route('residents.index') }}"
                   class="btn btn-edit">Clear</a>
            @endif
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($residents as $resident)
                    <tr>
                        <td>{{ $resident->id }}</td>
                        <td>{{ $resident->first_name }}</td>
                        <td>{{ $resident->last_name }}</td>
                        <td>{{ $resident->address }}</td>
                        <td>{{ $resident->contact_number }}</td>
                        <td>{{ $resident->email }}</td>

                        <td>
                            <span class="status {{ $resident->status === 'Active' ? 'active' : 'inactive' }}">
                                {{ $resident->status }}
                            </span>
                        </td>

                        <td>
                            <div class="actions">
                                <a href="{{ route('residents.edit', $resident->id) }}"
                                   class="btn btn-edit">Edit</a>

                                @if($resident->status === 'Active')
                                    <form method="POST"
                                          action="{{ route('residents.deactivate', $resident->id) }}"
                                          onsubmit="return confirm('Are you sure you want to deactivate this resident?');">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-danger">
                                            Deactivate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">
                            @if($search !== '')
                                No residents found matching "{{ $search }}".
                            @else
                                No residents registered yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <p class="subtitle">
            Total residents displayed: {{ $residents->count() }}
        </p>

    </div>
</div>
</body>
</html>
