<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Management</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263445;
        }

        .container { max-width: 1400px; margin: auto; }

        .navigation {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .navigation a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 { margin: 0 0 8px; }

        .subtitle { color: #64748b; }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-view { background: #e0ecff; color: #1d4ed8; }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            margin-bottom: 20px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        input, select {
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        input { flex: 1; min-width: 220px; }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th { background: #f8fafc; color: #475569; }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .pending { background: #fef3c7; color: #92400e; }
        .in-progress { background: #dbeafe; color: #1e40af; }
        .completed { background: #dcfce7; color: #166534; }
        .cancelled { background: #fee2e2; color: #991b1b; }

        .alert {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }

        .empty {
            text-align: center;
            padding: 35px;
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

    <nav class="navigation">
        <a href="{{ route('residents.index') }}">Residents</a>
        <a href="{{ route('service-requests.index') }}">Service Requests</a>
    </nav>

    <div class="header">
        <div>
            <h1>Service Request Management</h1>
            <div class="subtitle">View, search, and manage resident service requests.</div>
        </div>

        <a href="{{ route('service-requests.create') }}" class="btn btn-primary">
            + New Service Request
        </a>
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(!empty($queryErrors))
        <div class="alert error">
            @foreach($queryErrors as $messages)
                @foreach((array) $messages as $message)
                    <div>{{ $message }}</div>
                @endforeach
            @endforeach
        </div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('service-requests.index') }}" class="filters">

            <input
                type="text"
                name="search"
                placeholder="Search service type or description..."
                value="{{ $search }}"
            >

            <select name="status">
                <option value="">All Statuses</option>
                @foreach(['Pending', 'In Progress', 'Completed', 'Cancelled'] as $status)
                    <option value="{{ $status }}"
                        {{ $selectedStatus === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>

            <a href="{{ route('service-requests.index') }}" class="btn btn-view">
                Clear
            </a>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resident ID</th>
                        <th>Service Type</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($serviceRequests as $serviceRequest)
                        @php
                            $statusClass = match($serviceRequest->status) {
                                'Pending' => 'pending',
                                'In Progress' => 'in-progress',
                                'Completed' => 'completed',
                                'Cancelled' => 'cancelled',
                                default => '',
                            };
                        @endphp

                        <tr>
                            <td>{{ $serviceRequest->id }}</td>
                            <td>{{ $serviceRequest->resident_id }}</td>
                            <td>{{ $serviceRequest->service_type }}</td>
                            <td>{{ $serviceRequest->date_requested }}</td>
                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $serviceRequest->status }}
                                </span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('service-requests.show', $serviceRequest->id) }}"
                                    class="btn btn-view"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                No Service Requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
