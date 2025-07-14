{{-- Kraeplin Test Results Section --}}
<section id="kraeplin-section" class="content-section">
    <h2 class="section-title">
        <i class="fas fa-chart-line"></i>
        Hasil Tes Kraeplin
    </h2>

    @if($candidate->kraeplinTestResult)
        {{-- Summary Cards --}}
        <div style="margin-bottom: 30px;">
            <h3 class="info-card-title">
                <i class="fas fa-trophy"></i>
                Ringkasan Hasil Tes
            </h3>
            <div class="info-grid">
                <div class="info-card">
                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                        <i class="fas fa-check-circle"></i>
                        Akurasi & Penyelesaian
                    </h4>
                    <div class="info-row">
                        <span class="info-label">Total Soal Terjawab</span>
                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                            {{ $candidate->kraeplinTestResult->total_questions_answered ?? 0 }}
                            <span style="font-size: 0.8rem; color: #6b7280;">/ 832 soal</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jawaban Benar</span>
                        <span class="info-value" style="font-weight: 600; color: #059669;">
                            {{ $candidate->kraeplinTestResult->total_correct_answers ?? 0 }}
                            <span style="font-size: 0.8rem; color: #6b7280;">
                                ({{ number_format($candidate->kraeplinTestResult->accuracy_percentage ?? 0, 1) }}%)
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jawaban Salah</span>
                        <span class="info-value" style="font-weight: 600; color: #dc2626;">
                            {{ $candidate->kraeplinTestResult->total_wrong_answers ?? 0 }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tingkat Penyelesaian</span>
                        <span class="info-value" style="font-weight: 600; color: #7c3aed;">
                            {{ number_format($candidate->kraeplinTestResult->completion_rate ?? 0, 1) }}%
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                        <i class="fas fa-tachometer-alt"></i>
                        Kecepatan & Konsistensi
                    </h4>
                    <div class="info-row">
                        <span class="info-label">Kecepatan Rata-rata</span>
                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                            {{ $candidate->kraeplinTestResult->formatted_average_time ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Durasi Total</span>
                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                            {{ $candidate->kraeplinTestResult->testSession->formatted_duration ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Skor Keseluruhan</span>
                        <span class="info-value" style="font-weight: 700; color: #4f46e5; font-size: 1.1rem;">
                            {{ number_format($candidate->kraeplinTestResult->overall_score ?? 0, 1) }}/100
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Grade</span>
                        <span class="info-value">
                            <span class="status-badge grade-{{ strtolower($candidate->kraeplinTestResult->grade ?? 'n') }}" 
                                style="font-weight: 700; font-size: 1rem;">
                                {{ $candidate->kraeplinTestResult->grade ?? 'N/A' }}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                        <i class="fas fa-award"></i>
                        Kategori Performa
                    </h4>
                    <div style="text-align: center; padding: 20px 0;">
                        <div class="performance-category performance-{{ $candidate->kraeplinTestResult->performance_category ?? 'unknown' }}" 
                            style="display: inline-block; padding: 15px 25px; border-radius: 12px; font-weight: 600; font-size: 1.1rem;">
                            {{ $candidate->kraeplinTestResult->performance_category_label ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <p style="margin: 0; font-size: 0.9rem; color: #4a5568; line-height: 1.5; text-align: center;">
                            {{ $candidate->kraeplinTestResult->getScoreInterpretation() ?? 'Interpretasi tidak tersedia' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Analysis Charts --}}
        <div style="margin-bottom: 30px;">
            <h3 class="info-card-title">
                <i class="fas fa-chart-line"></i>
                Analisis Performa per Kolom
            </h3>
            
            {{-- Check if chart data is available --}}
            @php
                $hasChartData = $candidate->kraeplinTestResult->column_correct_count &&
                               $candidate->kraeplinTestResult->column_answered_count &&
                               $candidate->kraeplinTestResult->column_avg_time &&
                               $candidate->kraeplinTestResult->column_accuracy;
            @endphp

            @if($hasChartData)
                {{-- Chart Loading State --}}
                <div id="chartLoading" style="text-align: center; padding: 60px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top: 4px solid #4f46e5; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 15px; color: #6b7280;">Memuat grafik analisis...</p>
                </div>

                {{-- Chart Container --}}
                <div id="chartContainer" style="display: none;">
                    {{-- Chart Navigation Tabs --}}
                    <div style="margin-bottom: 20px;">
                        <div class="chart-nav" style="display: flex; background: white; border-radius: 12px; padding: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto;">
                            <button class="chart-tab active" data-chart="combined" style="flex: 1; min-width: 150px; padding: 12px 20px; border: none; background: #4f46e5; color: white; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                <i class="fas fa-chart-line"></i>
                                Gabungan (3 in 1)
                            </button>
                            <button class="chart-tab" data-chart="accuracy" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                <i class="fas fa-bullseye"></i>
                                Akurasi
                            </button>
                            <button class="chart-tab" data-chart="speed" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                <i class="fas fa-tachometer-alt"></i>
                                Soal Terjawab
                            </button>
                            <button class="chart-tab" data-chart="time" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                <i class="fas fa-clock"></i>
                                Kecepatan Pengerjaan
                            </button>
                        </div>
                    </div>

                    {{-- Chart Canvas --}}
                    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative; height: 500px;">
                        <canvas id="kraeplinChart"></canvas>
                    </div>

                    {{-- Chart Legend & Info --}}
                    <div style="background: white; border-radius: 12px; padding: 20px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div style="text-align: center;">
                                <div style="color: #1e40af; font-weight: 600; margin-bottom: 5px;">
                                    <i class="fas fa-circle" style="color: #1e40af; margin-right: 8px;"></i>
                                    Jawaban Benar
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280;">Jumlah jawaban benar per kolom (0-26)</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="color: #059669; font-weight: 600; margin-bottom: 5px;">
                                    <i class="fas fa-circle" style="color: #059669; margin-right: 8px;"></i>
                                    Soal Dijawab
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280;">Total soal yang dikerjakan per kolom (0-26)</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="color: #dc2626; font-weight: 600; margin-bottom: 5px;">
                                    <i class="fas fa-circle" style="color: #dc2626; margin-right: 8px;"></i>
                                    Waktu Rata-rata
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280;">Rata-rata waktu pengerjaan per soal dalam detik</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pass data to JavaScript --}}
                <script>
                    window.kraeplinTestData = {
                        column_correct_count: @json($candidate->kraeplinTestResult->column_correct_count),
                        column_answered_count: @json($candidate->kraeplinTestResult->column_answered_count),
                        column_avg_time: @json($candidate->kraeplinTestResult->column_avg_time),
                        column_accuracy: @json($candidate->kraeplinTestResult->column_accuracy)
                    };
                    
                    console.log('Kraeplin data passed to JS:', window.kraeplinTestData);
                </script>

            @else
                {{-- No Chart Data Message --}}
                <div style="text-align: center; padding: 60px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <i class="fas fa-chart-line" style="font-size: 3rem; color: #e5e7eb; margin-bottom: 15px;"></i>
                    <h4 style="color: #6b7280; margin-bottom: 10px;">Data Grafik Tidak Tersedia</h4>
                    <p style="color: #9ca3af; margin: 0;">Data detail per kolom tidak tersedia untuk membuat grafik analisis.</p>
                </div>
            @endif
        </div>

        {{-- Test Session Details --}}
        <div class="info-card">
            <h3 class="info-card-title">
                <i class="fas fa-info-circle"></i>
                Detail Sesi Tes
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="info-row">
                    <span class="info-label">Kode Tes</span>
                    <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->test_code ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Tes</span>
                    <span class="info-value">
                        {{ $candidate->kraeplinTestResult->testSession->completed_at ? $candidate->kraeplinTestResult->testSession->completed_at->format('d M Y H:i') : 'N/A' }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-accepted">Selesai</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durasi</span>
                    <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->formatted_duration ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

    @else
        {{-- ✅ SIMPLE: Empty State untuk kandidat belum mengerjakan tes --}}
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <p>Kandidat belum menyelesaikan tes Kraeplin</p>
        </div>
    @endif
</section>

<style>
/* Additional styles for empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.empty-note {
    margin-top: 20px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background-color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-accepted {
    background-color: #d1fae5;
    color: #065f46;
}

/* Grade badges */
.grade-a { background-color: #d1fae5; color: #065f46; }
.grade-b { background-color: #dbeafe; color: #1e40af; }
.grade-c { background-color: #fef3c7; color: #92400e; }
.grade-d { background-color: #fed7aa; color: #9a3412; }
.grade-e { background-color: #fecaca; color: #991b1b; }
.grade-n { background-color: #f3f4f6; color: #374151; }

/* Performance categories */
.performance-excellent { background-color: #d1fae5; color: #065f46; }
.performance-good { background-color: #dbeafe; color: #1e40af; }
.performance-average { background-color: #fef3c7; color: #92400e; }
.performance-below_average { background-color: #fed7aa; color: #9a3412; }
.performance-poor { background-color: #fecaca; color: #991b1b; }
.performance-unknown { background-color: #f3f4f6; color: #374151; }

/* Spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>