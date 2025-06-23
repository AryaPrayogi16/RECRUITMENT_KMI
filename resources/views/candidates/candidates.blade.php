@extends('layouts.hr')

@section('title', 'Hasil Test DISC - ' . $candidate->full_name)

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Hasil Test DISC</h1>
                <p class="text-gray-600 mt-1">
                    <strong>{{ $candidate->full_name }}</strong> - {{ $candidate->candidate_code }}
                </p>
                <p class="text-sm text-gray-500">
                    Posisi: {{ $candidate->position_applied }} | 
                    Test selesai: {{ $discResult->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('candidates.show', $candidate->id) }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    ← Kembali ke Profil
                </a>
                <button onclick="window.print()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    📄 Print
                </button>
                <a href="{{ route('candidates.export.single.pdf', $candidate->id) }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    📋 Export PDF
                </a>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Summary & Scores -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Summary Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-6">
                <h2 class="text-xl font-bold mb-3">Ringkasan Profil DISC</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-2xl font-bold">{{ $discResult->primary_type }}</div>
                        <div class="text-blue-100">Tipe Primer ({{ number_format($discResult->primary_percentage, 1) }}%)</div>
                        <div class="text-sm mt-1">{{ $discResult->primary_type_label }}</div>
                    </div>
                    <div>
                        <div class="text-lg font-semibold">{{ $discResult->secondary_type }}</div>
                        <div class="text-blue-100">Tipe Sekunder ({{ number_format($discResult->secondary_percentage, 1) }}%)</div>
                        <div class="text-sm mt-1">{{ $discResult->secondary_type_label }}</div>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-800 bg-opacity-50 rounded">
                    <p class="text-sm">{{ $discResult->profile_summary }}</p>
                </div>
            </div>

            <!-- DISC Scores -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Skor DISC</h3>
                
                <div class="space-y-4">
                    @php
                        $colors = [
                            'D' => ['bg' => 'bg-red-500', 'text' => 'text-red-600'],
                            'I' => ['bg' => 'bg-orange-500', 'text' => 'text-orange-600'], 
                            'S' => ['bg' => 'bg-green-500', 'text' => 'text-green-600'],
                            'C' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600']
                        ];
                        $scores = [
                            'D' => $discResult->d_percentage,
                            'I' => $discResult->i_percentage,
                            'S' => $discResult->s_percentage,
                            'C' => $discResult->c_percentage
                        ];
                        $labels = [
                            'D' => 'Dominance (Dominan)',
                            'I' => 'Influence (Pengaruh)',
                            'S' => 'Steadiness (Kestabilan)', 
                            'C' => 'Conscientiousness (Ketelitian)'
                        ];
                    @endphp
                    
                    @foreach($scores as $dimension => $percentage)
                        <div class="flex items-center space-x-4">
                            <div class="w-24 text-sm font-medium {{ $colors[$dimension]['text'] }}">
                                {{ $dimension }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600">{{ $labels[$dimension] }}</span>
                                    <span class="text-sm font-semibold {{ $colors[$dimension]['text'] }}">
                                        {{ number_format($percentage, 1) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="{{ $colors[$dimension]['bg'] }} h-3 rounded-full transition-all duration-500" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            <div class="w-16 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white {{ $colors[$dimension]['bg'] }} rounded-full">
                                    {{ $discResult->segments[$dimension] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        Profil: {{ $discResult->personality_profile }}
                    </span>
                </div>
            </div>

            <!-- Work Analysis -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Analisis untuk Pekerjaan</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Strengths -->
                    <div>
                        <h4 class="font-medium text-green-700 mb-3">✅ Kekuatan</h4>
                        <div class="space-y-2">
                            @if($discResult->full_profile && isset($discResult->full_profile['analysis']['strengths']))
                                @foreach($discResult->full_profile['analysis']['strengths'] as $strength)
                                    <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm text-green-800">
                                        {{ $strength }}
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm text-green-800">
                                    Kepemimpinan yang kuat
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm text-green-800">
                                    Orientasi pada hasil
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm text-green-800">
                                    Pengambilan keputusan cepat
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Development Areas -->
                    <div>
                        <h4 class="font-medium text-yellow-700 mb-3">🎯 Area Pengembangan</h4>
                        <div class="space-y-2">
                            @if($discResult->full_profile && isset($discResult->full_profile['analysis']['development_areas']))
                                @foreach($discResult->full_profile['analysis']['development_areas'] as $area)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-sm text-yellow-800">
                                        {{ $area }}
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-sm text-yellow-800">
                                    Meningkatkan kesabaran dalam tim
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-sm text-yellow-800">
                                    Mengembangkan kemampuan mendengarkan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Work & Communication Style -->
                <div class="mt-6 grid md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-700 mb-2">💼 Gaya Kerja</h4>
                        <p class="text-sm text-blue-800">
                            @if($discResult->full_profile && isset($discResult->full_profile['analysis']['work_style']))
                                {{ $discResult->full_profile['analysis']['work_style'] }}
                            @else
                                Cepat, independen, fokus pada hasil dan pencapaian target.
                            @endif
                        </p>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h4 class="font-medium text-purple-700 mb-2">💬 Gaya Komunikasi</h4>
                        <p class="text-sm text-purple-800">
                            @if($discResult->full_profile && isset($discResult->full_profile['analysis']['communication_style']))
                                {{ $discResult->full_profile['analysis']['communication_style'] }}
                            @else
                                Langsung, singkat, fokus pada hasil dan tindakan.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Chart & Recommendations -->
        <div class="space-y-6">
            <!-- DISC Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik DISC</h3>
                <div class="relative h-64">
                    <canvas id="discChart"></canvas>
                </div>
            </div>

            <!-- Test Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Test</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jenis Test:</span>
                        <span class="font-medium">{{ $discSession->test_type_label ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Waktu Mulai:</span>
                        <span class="font-medium">{{ $discSession->started_at ? $discSession->started_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Waktu Selesai:</span>
                        <span class="font-medium">{{ $discSession->completed_at ? $discSession->completed_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Durasi:</span>
                        <span class="font-medium">{{ $discSession->formatted_duration ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pertanyaan Dijawab:</span>
                        <span class="font-medium">{{ $discSession->answers()->count() }} pertanyaan</span>
                    </div>
                </div>
            </div>

            <!-- Recommended Roles -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Peran yang Cocok</h3>
                <div class="space-y-2">
                    @foreach($discResult->recommended_roles as $role)
                        <div class="bg-purple-100 border border-purple-200 rounded-lg px-3 py-2 text-center">
                            <span class="text-purple-800 font-medium">{{ $role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- HR Notes Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan HR</h3>
                <textarea class="w-full h-32 border border-gray-300 rounded-lg p-3 text-sm" 
                          placeholder="Tambahkan catatan evaluasi untuk kandidat ini..."></textarea>
                <button class="mt-3 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                    Simpan Catatan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create DISC Radar Chart
    const ctx = document.getElementById('discChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Dominance', 'Influence', 'Steadiness', 'Conscientiousness'],
            datasets: [{
                label: 'DISC Scores',
                data: [
                    {{ $discResult->d_percentage }},
                    {{ $discResult->i_percentage }},
                    {{ $discResult->s_percentage }},
                    {{ $discResult->c_percentage }}
                ],
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgb(59, 130, 246)',
                pointBackgroundColor: [
                    '#dc2626', // D - Red
                    '#ea580c', // I - Orange  
                    '#16a34a', // S - Green
                    '#2563eb'  // C - Blue
                ],
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(59, 130, 246)',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            elements: {
                line: {
                    borderWidth: 2
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        font: {
                            size: 10
                        }
                    },
                    pointLabels: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.r.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
@media print {
    .no-print, nav, button, .bg-blue-600, .bg-gray-600, .bg-green-600 {
        display: none !important;
    }
    
    .shadow {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }
    
    body {
        background: white !important;
    }
}
</style>
@endsection