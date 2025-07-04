<section id="disc-section" class="content-section">
    <h2 class="section-title">
        <i class="fas fa-chart-pie"></i>
        Hasil Tes DISC 3D - Analisis Kepribadian Komprehensif
    </h2>

    @if($candidate->disc3DTestResult)
        {{-- FIXED: Pass Enhanced DISC data using SEGMENTS to JavaScript --}}
        <script>
            window.discData = {
                // FIXED: Gunakan segment values (1-7) untuk MOST dan LEAST
                most: {
                    D: {{ $candidate->disc3DTestResult->most_d_segment ?? 1 }},
                    I: {{ $candidate->disc3DTestResult->most_i_segment ?? 1 }},
                    S: {{ $candidate->disc3DTestResult->most_s_segment ?? 1 }},
                    C: {{ $candidate->disc3DTestResult->most_c_segment ?? 1 }}
                },
                least: {
                    D: {{ $candidate->disc3DTestResult->least_d_segment ?? 1 }},
                    I: {{ $candidate->disc3DTestResult->least_i_segment ?? 1 }},
                    S: {{ $candidate->disc3DTestResult->least_s_segment ?? 1 }},
                    C: {{ $candidate->disc3DTestResult->least_c_segment ?? 1 }}
                },
                // CHANGE tetap menggunakan segment values yang bisa minus
                change: {
                    D: {{ $candidate->disc3DTestResult->change_d_segment ?? 0 }},
                    I: {{ $candidate->disc3DTestResult->change_i_segment ?? 0 }},
                    S: {{ $candidate->disc3DTestResult->change_s_segment ?? 0 }},
                    C: {{ $candidate->disc3DTestResult->change_c_segment ?? 0 }}
                },
                // Percentages tetap disimpan untuk reference/tooltip
                percentages: {
                    most: {
                        D: {{ $candidate->disc3DTestResult->most_d_percentage ?? 0 }},
                        I: {{ $candidate->disc3DTestResult->most_i_percentage ?? 0 }},
                        S: {{ $candidate->disc3DTestResult->most_s_percentage ?? 0 }},
                        C: {{ $candidate->disc3DTestResult->most_c_percentage ?? 0 }}
                    },
                    least: {
                        D: {{ $candidate->disc3DTestResult->least_d_percentage ?? 0 }},
                        I: {{ $candidate->disc3DTestResult->least_i_percentage ?? 0 }},
                        S: {{ $candidate->disc3DTestResult->least_s_percentage ?? 0 }},
                        C: {{ $candidate->disc3DTestResult->least_c_percentage ?? 0 }}
                    }
                },
                profile: {
                    primary: '{{ $candidate->disc3DTestResult->primary_type ?? "D" }}',
                    secondary: '{{ $candidate->disc3DTestResult->secondary_type ?? "I" }}',
                    primaryLabel: '{{ $candidate->disc3DTestResult->primary_type_label ?? "Unknown Type" }}',
                    secondaryLabel: '{{ $candidate->disc3DTestResult->secondary_type_label ?? "Unknown" }}',
                    primaryPercentage: {{ $candidate->disc3DTestResult->primary_percentage ?? 0 }},
                    summary: {!! json_encode($candidate->disc3DTestResult->summary ?? "Belum tersedia") !!}
                },
                analysis: {
                    // Enhanced comprehensive analysis with all traits
                    allStrengths: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['strengths'] ?? [
                        'Kepemimpinan Natural', 'Pengambilan Keputusan Cepat', 'Orientasi Hasil Tinggi', 
                        'Komunikasi Persuasif', 'Kemampuan Memotivasi', 'Keberanian Mengambil Risiko',
                        'Inisiatif Tinggi', 'Fokus pada Pencapaian', 'Kemampuan Delegasi',
                        'Daya Juang Tinggi', 'Visioner', 'Energi Tinggi'
                    ]) !!},
                    
                    allDevelopmentAreas: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['development_areas'] ?? [
                        'Kesabaran dalam Proses', 'Perhatian pada Detail', 'Konsistensi Follow-up',
                        'Mendengarkan Feedback', 'Fleksibilitas Metode', 'Empati yang Lebih Dalam',
                        'Manajemen Stres', 'Kontrol Emosi', 'Delegasi yang Efektif'
                    ]) !!},

                    behavioralTendencies: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['tendencies'] ?? [
                        'Mengambil Kendali Situasi', 'Berbicara Langsung pada Inti', 'Membuat Keputusan Cepat',
                        'Fokus pada Hasil Akhir', 'Mendorong Perubahan', 'Berani Konfrontasi',
                        'Multitasking Efektif', 'Networking Aktif', 'Kompetitif'
                    ]) !!},

                    communicationPreferences: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['communication'] ?? [
                        'Komunikasi Langsung', 'Presentasi yang Dinamis', 'Diskusi Berorientasi Solusi',
                        'Feedback yang Konstruktif', 'Meeting yang Efisien', 'Laporan Ringkas',
                        'Brainstorming Aktif', 'Negosiasi Asertif'
                    ]) !!},

                    motivators: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['motivators'] ?? [
                        'Pencapaian Target', 'Pengakuan Prestasi', 'Tantangan Baru',
                        'Otoritas dan Tanggung Jawab', 'Kompetisi Sehat', 'Perubahan dan Inovasi',
                        'Hasil yang Terukur', 'Pengaruh pada Keputusan'
                    ]) !!},

                    stressIndicators: {!! json_encode($candidate->disc3DTestResult->stress_indicators ?? [
                        'Ketidakpastian Berkepanjangan', 'Proses yang Terlalu Lambat', 'Micromanagement',
                        'Rutinitas yang Monoton', 'Konflik Interpersonal', 'Kekurangan Informasi',
                        'Deadline yang Tidak Realistis', 'Perubahan Mendadak'
                    ]) !!},

                    workEnvironment: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['work_environment'] ?? [
                        'Lingkungan Dinamis', 'Tim yang Responsif', 'Budaya Meritokrasi',
                        'Struktur yang Fleksibel', 'Akses pada Manajemen Senior', 'Resource yang Memadai',
                        'Teknologi Terkini', 'Ruang untuk Inovasi'
                    ]) !!},

                    decisionMaking: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['decision_making'] ?? [
                        'Berdasarkan Data dan Intuisi', 'Cepat dan Tegas', 'Mempertimbangkan Dampak',
                        'Melibatkan Stakeholder Kunci', 'Fokus pada ROI', 'Berani Mengambil Risiko Terkalkulasi'
                    ]) !!},

                    leadershipStyle: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['leadership'] ?? [
                        'Transformational Leadership', 'Delegasi Efektif', 'Coaching dan Mentoring',
                        'Setting Ekspektasi Tinggi', 'Leading by Example', 'Inspirational Communication'
                    ]) !!},

                    conflictResolution: {!! json_encode($candidate->disc3DTestResult->behavioral_insights['conflict_resolution'] ?? [
                        'Pendekatan Langsung', 'Fokus pada Solusi', 'Win-Win Solution',
                        'Mediasi Objektif', 'Komunikasi Terbuka', 'Escalation Jika Diperlukan'
                    ]) !!},

                    detailedWorkStyle: {!! json_encode($candidate->disc3DTestResult->overall_profile ?? "Bekerja dengan tempo tinggi dan fokus pada hasil. Menyukai lingkungan yang dinamis dengan kebebasan untuk mengambil keputusan.") !!},
                    
                    detailedCommunicationStyle: {!! json_encode($candidate->disc3DTestResult->personality_profile ?? "Komunikasi yang langsung, jelas, dan persuasif. Mampu menyampaikan visi dan memotivasi tim.") !!},
                    
                    publicSelfAnalysis: {!! json_encode($candidate->disc3DTestResult->public_self_summary ?? "Di lingkungan publik, menampilkan sosok yang percaya diri, tegas, dan berorientasi pada hasil.") !!},
                    
                    privateSelfAnalysis: {!! json_encode($candidate->disc3DTestResult->private_self_summary ?? "Secara pribadi, lebih reflektif dan mempertimbangkan berbagai aspek sebelum mengambil keputusan.") !!},
                    
                    adaptationAnalysis: {!! json_encode($candidate->disc3DTestResult->adaptation_summary ?? "Mengalami tekanan untuk tampil lebih dominan dan ekspresif di lingkungan kerja.") !!}
                },
                session: {
                    testCode: '{{ $candidate->latestDisc3DTest->test_code ?? "N/A" }}',
                    completedDate: '{{ $candidate->latestDisc3DTest->completed_at ? $candidate->latestDisc3DTest->completed_at->format("d M Y") : "N/A" }}',
                    duration: '{{ $candidate->latestDisc3DTest->formatted_duration ?? "N/A" }}'
                }
            };
        </script>

        {{-- COMPACT HEADER SUMMARY --}}
        <div class="disc-header-summary">
            <div class="disc-profile-grid">
                {{-- Profile Type --}}
                <div>
                    <div class="disc-profile-type">
                        <div class="type-code" id="discTypeCode">
                            {{ ($candidate->disc3DTestResult->primary_type ?? 'D') . ($candidate->disc3DTestResult->secondary_type ?? 'I') }}
                        </div>
                        <div class="type-label">Profile Type</div>
                    </div>
                </div>
                
                {{-- Primary Info --}}
                <div class="disc-primary-info">
                    <h3 id="discPrimaryType">{{ $candidate->disc3DTestResult->primary_type_label ?? 'Unknown Type' }}</h3>
                    <p id="discSecondaryInfo">Sekunder: {{ $candidate->disc3DTestResult->secondary_type_label ?? 'Unknown' }}</p>
                    <div class="disc-meta-badges">
                        <span class="disc-meta-badge">
                            <i class="fas fa-percentage"></i> 
                            <span id="discPrimaryPercentage">{{ number_format($candidate->disc3DTestResult->primary_percentage ?? 0, 1) }}</span>% Dominan
                        </span>
                        <span class="disc-meta-badge">
                            <i class="fas fa-chart-bar"></i> 
                            <span id="discSegmentPattern">
                                {{ ($candidate->disc3DTestResult->most_d_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_i_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_s_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_c_segment ?? 1) }}
                            </span>
                        </span>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div>
                    <div class="disc-quick-stats">
                        <div class="stats-label">Completed</div>
                        <div class="stats-value" id="discCompletedDate">
                            {{ $candidate->latestDisc3DTest->completed_at ? $candidate->latestDisc3DTest->completed_at->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALL THREE GRAPHS DISPLAYED SIMULTANEOUSLY --}}
        <div class="disc-comprehensive-graphs">
            <h3 class="disc-graph-section-title">
                <i class="fas fa-chart-line" style="color: #4f46e5;"></i>
                Analisis DISC 3D - Tiga Dimensi Kepribadian
            </h3>
            
            <div class="disc-all-graphs-container">
                {{-- MOST Graph --}}
                <div class="disc-single-graph-container">
                    <div id="discMostGraph" class="disc-graph-area"></div>
                    <div class="disc-graph-description">
                        <strong>MOST (Topeng/Publik):</strong> Menunjukkan bagaimana Anda berperilaku di depan umum atau dalam situasi kerja formal.
                    </div>
                    {{-- FIXED: Score cards for MOST - Menampilkan segment values --}}
                    <div class="disc-scores-mini-grid">
                        <div class="disc-score-mini dominance">
                            <span class="dim-label">D</span>
                            <span class="score-value" id="mostScoreD">{{ $candidate->disc3DTestResult->most_d_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="mostSegmentD">{{ $candidate->disc3DTestResult->most_d_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini influence">
                            <span class="dim-label">I</span>
                            <span class="score-value" id="mostScoreI">{{ $candidate->disc3DTestResult->most_i_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="mostSegmentI">{{ $candidate->disc3DTestResult->most_i_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini steadiness">
                            <span class="dim-label">S</span>
                            <span class="score-value" id="mostScoreS">{{ $candidate->disc3DTestResult->most_s_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="mostSegmentS">{{ $candidate->disc3DTestResult->most_s_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini conscientiousness">
                            <span class="dim-label">C</span>
                            <span class="score-value" id="mostScoreC">{{ $candidate->disc3DTestResult->most_c_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="mostSegmentC">{{ $candidate->disc3DTestResult->most_c_segment ?? 1 }}</span></span>
                        </div>
                    </div>
                </div>

                {{-- LEAST Graph --}}
                <div class="disc-single-graph-container">
                    <div id="discLeastGraph" class="disc-graph-area"></div>
                    <div class="disc-graph-description">
                        <strong>LEAST (Inti/Pribadi):</strong> Menggambarkan kepribadian alami Anda yang sesungguhnya tanpa pengaruh eksternal.
                    </div>
                    {{-- FIXED: Score cards for LEAST - Menampilkan segment values --}}
                    <div class="disc-scores-mini-grid">
                        <div class="disc-score-mini dominance">
                            <span class="dim-label">D</span>
                            <span class="score-value" id="leastScoreD">{{ $candidate->disc3DTestResult->least_d_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="leastSegmentD">{{ $candidate->disc3DTestResult->least_d_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini influence">
                            <span class="dim-label">I</span>
                            <span class="score-value" id="leastScoreI">{{ $candidate->disc3DTestResult->least_i_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="leastSegmentI">{{ $candidate->disc3DTestResult->least_i_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini steadiness">
                            <span class="dim-label">S</span>
                            <span class="score-value" id="leastScoreS">{{ $candidate->disc3DTestResult->least_s_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="leastSegmentS">{{ $candidate->disc3DTestResult->least_s_segment ?? 1 }}</span></span>
                        </div>
                        <div class="disc-score-mini conscientiousness">
                            <span class="dim-label">C</span>
                            <span class="score-value" id="leastScoreC">{{ $candidate->disc3DTestResult->least_c_segment ?? 1 }}</span>
                            <span class="segment-value">Seg. <span id="leastSegmentC">{{ $candidate->disc3DTestResult->least_c_segment ?? 1 }}</span></span>
                        </div>
                    </div>
                </div>

                {{-- CHANGE Graph --}}
                <div class="disc-single-graph-container">
                    <div id="discChangeGraph" class="disc-graph-area"></div>
                    <div class="disc-graph-description">
                        <strong>CHANGE (Adaptasi):</strong> Menunjukkan tekanan dan adaptasi yang dialami antara kepribadian publik dan pribadi.
                    </div>
                    {{-- Score cards for CHANGE - tetap sama (bisa minus) --}}
                    <div class="disc-scores-mini-grid">
                        <div class="disc-score-mini dominance">
                            <span class="dim-label">D</span>
                            <span class="score-value" id="changeScoreD">{{ $candidate->disc3DTestResult->change_d_segment ?? 0 > 0 ? '+' : '' }}{{ $candidate->disc3DTestResult->change_d_segment ?? 0 }}</span>
                            <span class="segment-value">Change</span>
                        </div>
                        <div class="disc-score-mini influence">
                            <span class="dim-label">I</span>
                            <span class="score-value" id="changeScoreI">{{ $candidate->disc3DTestResult->change_i_segment ?? 0 > 0 ? '+' : '' }}{{ $candidate->disc3DTestResult->change_i_segment ?? 0 }}</span>
                            <span class="segment-value">Change</span>
                        </div>
                        <div class="disc-score-mini steadiness">
                            <span class="dim-label">S</span>
                            <span class="score-value" id="changeScoreS">{{ $candidate->disc3DTestResult->change_s_segment ?? 0 > 0 ? '+' : '' }}{{ $candidate->disc3DTestResult->change_s_segment ?? 0 }}</span>
                            <span class="segment-value">Change</span>
                        </div>
                        <div class="disc-score-mini conscientiousness">
                            <span class="dim-label">C</span>
                            <span class="score-value" id="changeScoreC">{{ $candidate->disc3DTestResult->change_c_segment ?? 0 > 0 ? '+' : '' }}{{ $candidate->disc3DTestResult->change_c_segment ?? 0 }}</span>
                            <span class="segment-value">Change</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rest of the comprehensive analysis sections remain the same --}}
        {{-- COMPREHENSIVE PERSONALITY ANALYSIS --}}
        <div class="disc-comprehensive-analysis">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-brain" style="color: #7c3aed;"></i>
                Analisis Kepribadian Komprehensif
            </h3>
            
            <div class="disc-analysis-mega-grid">
                
                {{-- All Strengths --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-star" style="color: #059669;"></i>
                        Semua Kelebihan & Kekuatan
                    </h4>
                    <div class="disc-trait-tags" id="discAllStrengthTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- All Development Areas --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-arrow-up" style="color: #dc2626;"></i>
                        Area Pengembangan
                    </h4>
                    <div class="disc-trait-tags" id="discAllDevelopmentTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Behavioral Tendencies --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-user-cog" style="color: #4f46e5;"></i>
                        Kecenderungan Perilaku
                    </h4>
                    <div class="disc-trait-tags" id="discBehavioralTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Communication Preferences --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-comments" style="color: #0891b2;"></i>
                        Preferensi Komunikasi
                    </h4>
                    <div class="disc-trait-tags" id="discCommunicationTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Motivators --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-fire" style="color: #ea580c;"></i>
                        Motivator Utama
                    </h4>
                    <div class="disc-trait-tags" id="discMotivatorTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Stress Indicators --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-exclamation-triangle" style="color: #d97706;"></i>
                        Indikator Stres
                    </h4>
                    <div class="disc-trait-tags" id="discStressTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Work Environment --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-building" style="color: #7c2d12;"></i>
                        Lingkungan Kerja Ideal
                    </h4>
                    <div class="disc-trait-tags" id="discEnvironmentTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Decision Making --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-lightbulb" style="color: #ca8a04;"></i>
                        Gaya Pengambilan Keputusan
                    </h4>
                    <div class="disc-trait-tags" id="discDecisionTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Leadership Style --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-crown" style="color: #9333ea;"></i>
                        Gaya Kepemimpinan
                    </h4>
                    <div class="disc-trait-tags" id="discLeadershipTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>

                {{-- Conflict Resolution --}}
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-handshake" style="color: #059669;"></i>
                        Resolusi Konflik
                    </h4>
                    <div class="disc-trait-tags" id="discConflictTags">
                        {{-- Will be populated by JavaScript --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAILED BEHAVIORAL ANALYSIS --}}
        <div class="disc-detailed-analysis">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-search" style="color: #1e40af;"></i>
                Analisis Perilaku Mendalam
            </h3>
            
            <div class="disc-detailed-grid">
                {{-- Detailed Work Style --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-briefcase"></i>
                        Gaya Kerja Detail
                    </h4>
                    <p class="disc-detailed-content" id="discDetailedWorkStyle">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>

                {{-- Detailed Communication Style --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-microphone"></i>
                        Gaya Komunikasi Detail
                    </h4>
                    <p class="disc-detailed-content" id="discDetailedCommStyle">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>

                {{-- Public Self Analysis --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-mask"></i>
                        Analisis Diri Publik (MOST)
                    </h4>
                    <p class="disc-detailed-content" id="discPublicSelfAnalysis">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>

                {{-- Private Self Analysis --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-heart"></i>
                        Analisis Diri Pribadi (LEAST)
                    </h4>
                    <p class="disc-detailed-content" id="discPrivateSelfAnalysis">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>

                {{-- Adaptation Analysis --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-exchange-alt"></i>
                        Analisis Adaptasi (CHANGE)
                    </h4>
                    <p class="disc-detailed-content" id="discAdaptationAnalysis">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>

                {{-- Overall Profile Summary --}}
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-file-alt"></i>
                        Ringkasan Profil Keseluruhan
                    </h4>
                    <p class="disc-detailed-content" id="discProfileSummary">
                        {{-- Will be populated by JavaScript --}}
                    </p>
                </div>
            </div>
        </div>

        {{-- SESSION DETAILS --}}
        <div class="disc-session-info">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-info-circle" style="color: #6b7280;"></i>
                Informasi Sesi Tes
            </h3>
            
            <div class="disc-session-grid">
                <div class="disc-session-item">
                    <span class="disc-session-label">Kode Tes</span>
                    <span class="disc-session-value" id="discTestCode">{{ $candidate->latestDisc3DTest->test_code ?? 'N/A' }}</span>
                </div>
                <div class="disc-session-item">
                    <span class="disc-session-label">Tanggal Penyelesaian</span>
                    <span class="disc-session-value" id="discTestDate">
                        {{ $candidate->latestDisc3DTest->completed_at ? $candidate->latestDisc3DTest->completed_at->format('d M Y H:i') : 'N/A' }}
                    </span>
                </div>
                <div class="disc-session-item">
                    <span class="disc-session-label">Durasi Pengerjaan</span>
                    <span class="disc-session-value" id="discTestDuration">{{ $candidate->latestDisc3DTest->formatted_duration ?? 'N/A' }}</span>
                </div>
                <div class="disc-session-item">
                    <span class="disc-session-label">Status</span>
                    <span class="disc-session-value">
                        <span class="disc-status-badge completed">Selesai</span>
                    </span>
                </div>
            </div>
        </div>

    @else
        {{-- Empty State --}}
        <div class="empty-state">
            <i class="fas fa-chart-pie"></i>
            <p>Kandidat belum menyelesaikan tes DISC 3D</p>
            
            @if($candidate->canStartDisc3DTest())
                <div style="margin-top: 20px;">
                    <a href="{{ route('disc.instructions', $candidate->candidate_code) }}" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i>
                        Mulai Tes DISC 3D
                    </a>
                </div>
            @elseif(!$candidate->hasCompletedKraeplinTest())
                <div class="empty-note">
                    Kandidat harus menyelesaikan tes Kraeplin terlebih dahulu
                </div>
            @endif
        </div>
    @endif
</section>