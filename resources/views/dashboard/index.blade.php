<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSMS Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263445;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .navigation {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .navigation a {
            text-decoration: none;
            color: #2563eb;
            background: #ffffff;
            padding: 11px 18px;
            border-radius: 7px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        .navigation a.active,
        .navigation a:hover {
            background: #2563eb;
            color: #ffffff;
        }

        .section-title {
            font-size: 20px;
            margin: 30px 0 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
        }

        .stat-card {
            background: #ffffff;
            padding: 22px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 700;
            color: #2563eb;
        }

        .recent-card {
            background: #ffffff;
            padding: 22px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-top: 30px;
            overflow-x: auto;
        }

        .recent-card h2 {
            margin: 0 0 20px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
        }

        .view-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        @media (max-width: 900px) {
            .requests-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 18px;
            }

            .stats-grid,
            .requests-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>CSMS Dashboard</h1>
    <p class="subtitle">
        Community Services Management System overview
    </p>

    <nav class="navigation">
        <a href="{{ route('dashboard') }}" class="active">Dashboard</a>
        <a href="{{ route('residents.index') }}">Residents</a>
        <a href="{{ route('service-requests.index') }}">Service Requests</a>
    </nav>

    <h2 class="section-title">Resident Summary</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Residents</div>
            <div class="stat-value">{{ $residentTotal }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Active Residents</div>
            <div class="stat-value">{{ $activeResidents }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Inactive Residents</div>
            <div class="stat-value">{{ $inactiveResidents }}</div>
        </div>
    </div>

    <h2 class="section-title">Service Request Summary</h2>

    <div class="requests-grid">
        <div class="stat-card">
            <div class="stat-label">Total Requests</div>
            <div class="stat-value">{{ $serviceRequestTotal }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $pendingRequests }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">In Progress</div>
            <div class="stat-value">{{ $inProgressRequests }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ $completedRequests }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Cancelled</div>
            <div class="stat-value">{{ $cancelledRequests }}</div>
        </div>
    </div>

    <h2 class="section-title">Open Service Requests</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Open Requests (Pending + In Progress)</div>
            <div class="stat-value">{{ $openRequests }}</div>
        </div>
    </div>

    <div class="recent-card">
        <h2>Five Most Recent Service Requests</h2>

        @if (count($recentServiceRequests) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Resident ID</th>
                        <th>Service Type</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($recentServiceRequests as $request)
                        <tr>
                            <td>#{{ $request->id }}</td>
                            <td>{{ $request->resident_id }}</td>
                            <td>{{ $request->service_type }}</td>
                            <td>{{ $request->date_requested }}</td>
                            <td>
                                <span class="status">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('service-requests.show', $request->id) }}"
                                    class="view-link"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                No service requests found.
            </div>
        @endif
    </div>

</div>
</body>
</html>
