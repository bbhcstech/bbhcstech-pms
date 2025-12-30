<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="{{ asset('admin/assets/img/favicon.png') }}" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .container h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .container p {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            color: #555;
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
        }
        .form-group input:focus {
            border-color: #007bff;
        }
        .btn {
            background: #007bff;
            color: #fff;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: #d9534f;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify OTP</h1>
        <p>Please enter the OTP sent to your email to verify your login.</p>

        <form method="POST" action="{{ route('verify-otp') }}">
            @csrf
            <div class="form-group">
                <label for="otp">OTP Code:</label>
                <input type="text" name="otp" id="otp" placeholder="Enter your OTP" required>
                @if ($errors->has('otp'))
                    <span class="error">{{ $errors->first('otp') }}</span>
                @endif
            </div>
            <button type="submit" class="btn">Verify</button>
        </form>
    </div>
</body>
</html>
