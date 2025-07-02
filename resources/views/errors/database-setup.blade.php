
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Required</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <div class="text-6xl mb-4">🔧</div>
            <h1 class="text-3xl font-bold text-gray-800">Database Setup Required</h1>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-700">{{ $message ?? 'Database tables are missing.' }}</p>
        </div>
        
        @if(isset($missing_tables) && count($missing_tables) > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Missing Tables:</h3>
                <ul class="text-sm text-yellow-700">
                    @foreach($missing_tables as $table)
                        <li>• {{ $table }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="text-center space-x-4">
            <a href="{{ route('job.application.form') }}" 
               class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                Kembali ke Form
            </a>
            
            @if(isset($candidate_code))
                <a href="{{ route('disc3d.instructions', $candidate_code) }}" 
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600">
                    Coba Lagi
                </a>
            @endif
        </div>
        
        @if(app()->environment('local'))
            <div class="mt-6 bg-gray-50 rounded p-4">
                <h4 class="font-semibold text-gray-700">Debug Info:</h4>
                <p class="text-sm text-gray-600">Candidate: {{ $candidate_code ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-600">Time: {{ now() }}</p>
            </div>
        @endif
    </div>
</body>
</html>