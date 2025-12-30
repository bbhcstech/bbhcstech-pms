<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Layout</title>
    @vite('resources/css/app.css') <!-- Ensure you have configured Vite -->
</head>
<body>
    {{ $slot }}
</body>
</html>