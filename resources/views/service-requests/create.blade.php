<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Service Request</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #263445;
        }

        .container { max-width: 850px; margin: auto; }

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
        }

        h1 { margin-top: 0; }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .help {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-top: 6px;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .alert {
            padding: 13px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-secondary { background: #e2e8f0; color: #334155; }

        @media (max-width: 600px) {
            body { padding: 15px; }
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

        <h1>Submit New Service Request</h1>

        <p class="subtitle">
            Complete the information below to submit a service request.
        </p>

        @if($errors->any())
            <div class="alert">
                <strong>Please correct the following errors:</strong>

                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('service-requests.store') }}">
            @csrf

            <div class="form-group">
                <label for="resident_id">Resident ID</label>

                <input
                    type="number"
                    id="resident_id"
                    name="resident_id"
                    value="{{ old('resident_id') }}"
                    min="1"
                    required
                >

                @error('resident_id')
                    <div class="error">{{ $message }}</div>
                @enderror

                <small class="help">
                    Enter the ID of an existing active resident.
                </small>
            </div>

            <div class="form-group">
                <label for="service_type">Service Type</label>

                <input
                    type="text"
                    id="service_type"
                    name="service_type"
                    value="{{ old('service_type') }}"
                    required
                >

                @error('service_type')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    required
                >{{ old('description') }}</textarea>

                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="date_requested">Date Requested</label>

                <input
                    type="date"
                    id="date_requested"
                    name="date_requested"
                    value="{{ old('date_requested') }}"
                    required
                >

                @error('date_requested')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    Submit Request
                </button>

                <a
                    href="{{ route('service-requests.index') }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>
