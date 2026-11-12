<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Resident | CSMS</title>
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
            max-width: 750px;
            margin: 30px auto;
        }

        .card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }

        h1 { margin-top: 0; }

        .subtitle {
            color: #718096;
            margin-bottom: 25px;
        }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-secondary { background: #e5e7eb; color: #374151; }

        .note {
            background: #eff6ff;
            color: #1e40af;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            body { padding: 15px; }
            .card { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">

        <h1>Register New Resident</h1>
        <p class="subtitle">Enter the resident's information below.</p>

        <div class="note">
            Resident ID is automatically generated. New residents are
            automatically assigned Active status.
        </div>

        <form method="POST" action="{{ route('residents.store') }}">
            @csrf

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    value="{{ old('first_name') }}"
                    required
                >
                @error('first_name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    value="{{ old('last_name') }}"
                    required
                >
                @error('last_name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    value="{{ old('address') }}"
                    required
                >
                @error('address')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input
                    type="tel"
                    id="contact_number"
                    name="contact_number"
                    value="{{ old('contact_number') }}"
                    placeholder="09XXXXXXXXX"
                    maxlength="11"
                    required
                >
                @error('contact_number')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    Register Resident
                </button>

                <a href="{{ route('residents.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>

</body>
</html>
