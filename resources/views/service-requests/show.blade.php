<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Details</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263445;
        }

        .container { max-width: 900px; margin: auto; }

        .navigation { margin-bottom: 25px; }

        .navigation a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
            margin-right: 15px;
        }

        .card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            margin-bottom: 20px;
        }

        h1 { margin-top: 0; }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .detail-label {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .detail-value {
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .description {
            grid-column: 1 / -1;
            line-height: 1.6;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending { background: #fef3c7; color: #92400e; }
        .in-progress { background: #dbeafe; color: #1e40af; }
        .completed { background: #dcfce7; color: #166534; }
        .cancelled { background: #fee2e2; color: #991b1b; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 11px 17px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-success { background: #16a34a; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-secondary { background: #e2e8f0; color: #334155; }

        .alert {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }

        .terminal {
            color: #64748b;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            body { padding: 15px; }
            .details { grid-template-columns: 1fr; }
            .description { grid-column: auto; }
        }
    </style>
</head>
<body>

<div class="container">

    <nav class="navigation">
        <a href="{{ route('residents.index') }}">Residents</a>
        <a href="{{ route('service-requests.index') }}">Service Requests</a>
    </nav>

    <div class="card">

        <h1>Service Request Details</h1>

        <p>Request #{{ $serviceRequest->id }}</p>

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

        @php
            $statusClass = match($serviceRequest->status) {
                'Pending' => 'pending',
                'In Progress' => 'in-progress',
                'Completed' => 'completed',
                'Cancelled' => 'cancelled',
                default => '',
            };
        @endphp

        <div class="details">

            <div>
                <div class="detail-label">Service Request ID</div>
                <div class="detail-value">{{ $serviceRequest->id }}</div>
            </div>

            <div>
                <div class="detail-label">Resident ID</div>
                <div class="detail-value">{{ $serviceRequest->resident_id }}</div>
            </div>

            <div>
                <div class="detail-label">Service Type</div>
                <div class="detail-value">{{ $serviceRequest->service_type }}</div>
            </div>

            <div>
                <div class="detail-label">Date Requested</div>
                <div class="detail-value">{{ $serviceRequest->date_requested }}</div>
            </div>

            <div>
                <div class="detail-label">Current Status</div>
                <div>
                    <span class="status {{ $statusClass }}">
                        {{ $serviceRequest->status }}
                    </span>
                </div>
            </div>

            <div class="description">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ $serviceRequest->description }}</div>
            </div>

        </div>

        <div class="actions">

            @if($serviceRequest->status === 'Pending')

                <form method="POST"
                    action="{{ route('service-requests.status', $serviceRequest->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="In Progress">
                    <button type="submit" class="btn btn-primary">
                        Start Processing
                    </button>
                </form>

                <form method="POST"
                    action="{{ route('service-requests.status', $serviceRequest->id) }}"
                    onsubmit="return confirm('Are you sure you want to cancel this request?');">
                    @csrf
                    <input type="hidden" name="status" value="Cancelled">
                    <button type="submit" class="btn btn-danger">Cancel Request</button>
                </form>

            @elseif($serviceRequest->status === 'In Progress')

                <form method="POST"
                    action="{{ route('service-requests.status', $serviceRequest->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="btn btn-success">
                        Mark as Completed
                    </button>
                </form>

                <form method="POST"
                    action="{{ route('service-requests.status', $serviceRequest->id) }}"
                    onsubmit="return confirm('Are you sure you want to cancel this request?');">
                    @csrf
                    <input type="hidden" name="status" value="Cancelled">
                    <button type="submit" class="btn btn-danger">Cancel Request</button>
                </form>

            @else

                <p class="terminal">
                    This request is {{ strtolower($serviceRequest->status) }}.
                    No further status changes are available.
                </p>

            @endif

            <a href="{{ route('service-requests.index') }}"
                class="btn btn-secondary">
                Back to Service Requests
            </a>

        </div>

    </div>

</div>

</body>
</html>
