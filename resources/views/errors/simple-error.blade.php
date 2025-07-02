<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Error' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg text-center">
        <div class="text-6xl mb-4">⚠️</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
        <p class="text-gray-600 mb-6">{{ $message ?? 'Silakan coba lagi nanti.' }}</p>
        
        @if(isset($debug) && $debug && app()->environment('local'))
            <div class="bg-red-50 border border-red-200 rounded p-4 mb-4 text-left">
                <strong>Debug Info:</strong>
                <pre class="text-xs mt-2 overflow-auto">{{ $debug }}</pre>
            </div>
        @endif
        
        <div class="space-x-4">
            @if(isset($back_url))
                <a href="{{ $back_url }}" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Kembali
                </a>
            @endif
            
            <button onclick="location.reload()" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Coba Lagi
            </button>
        </div>
        
        <div class="mt-6 text-sm text-gray-500">
            <p>Time: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
