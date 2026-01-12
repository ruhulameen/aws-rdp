<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS RDP Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-900">
<nav class="bg-blue-600 p-4 text-white shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ route('aws.index') }}" class="text-xl font-bold tracking-tight">CloudRDP Manager</a>
        <div>
            <a href="{{ route('aws.create') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                + Launch New RDP
            </a>
        </div>
    </div>
</nav>

<main class="container mx-auto mt-10 p-4">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
