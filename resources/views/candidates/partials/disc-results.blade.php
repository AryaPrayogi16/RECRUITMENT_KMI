<section id="disc-section" class="content-section">
    <h2 class="section-title">
        <i class="fas fa-chart-pie"></i>
        Hasil Tes DISC 3D - Analisis Kepribadian Komprehensif
    </h2>

    @if($candidate->disc3DTestResult)
        {{-- ✅ FIXED: Define pattern data BEFORE using it in JavaScript --}}
        @php
            $patternData = null;
            if ($candidate->disc3DTestResult && $candidate->disc3DTestResult->most_pattern) {
                $patternData = \App\Models\Disc3DPatternCombination::where('pattern_code', $candidate->disc3DTestResult->most_pattern)->first();
            }
            
            // ✅ ENHANCED: Get dominant interpretation data for character analysis
            $dominantInterpretation = null;
            if ($candidate->disc3DTestResult && $candidate->disc3DTestResult->primary_type) {
                $primaryType = $candidate->disc3DTestResult->primary_type;
                $dominantDimension = null;
                
                // Determine which segment value is highest for MOST
                $segments = [
                    'D' => $candidate->disc3DTestResult->most_d_segment ?? 1,
                    'I' => $candidate->disc3DTestResult->most_i_segment ?? 1,
                    'S' => $candidate->disc3DTestResult->most_s_segment ?? 1,
                    'C' => $candidate->disc3DTestResult->most_c_segment ?? 1
                ];
                
                $dominantDim = array_search(max($segments), $segments);
                $dominantLevel = max($segments);
                
                // Try to get interpretation data (adjust table name as needed)
                try {
                    $dominantInterpretation = \App\Models\Disc3DProfileInterpretation::where('dimension', $dominantDim)
                                                ->where('segment_level', $dominantLevel)
                                                ->first();
                } catch (Exception $e) {
                    // Create mock interpretation if table doesn't exist
                    $dominantInterpretation = (object) [
                        'dimension' => $dominantDim,
                        'segment_level' => $dominantLevel,
                        'characteristics' => ['Karakteristik utama'],
                        'work_style' => ['Gaya kerja'],
                        'motivators' => ['Motivator utama']
                    ];
                }
            }
        @endphp

        {{-- ✅ ENHANCED: Pass DISC data with pattern combination to JavaScript --}}
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
                    // Use real data from database with proper array handling
                    allStrengths: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['strengths'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['strengths'] 
                            : []
                    ) !!},
                    
                    allDevelopmentAreas: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['development_areas'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['development_areas'] 
                            : []
                    ) !!},

                    behavioralTendencies: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['tendencies'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['tendencies'] 
                            : []
                    ) !!},

                    communicationPreferences: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['communication'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['communication'] 
                            : (is_array($candidate->disc3DTestResult->communication_style_most ?? null) 
                                ? $candidate->disc3DTestResult->communication_style_most 
                                : []
                            )
                    ) !!},

                    motivators: {!! json_encode(
                        is_array($candidate->disc3DTestResult->motivators_most ?? null) 
                            ? $candidate->disc3DTestResult->motivators_most 
                            : (is_array($candidate->disc3DTestResult->behavioral_insights['motivators'] ?? null) 
                                ? $candidate->disc3DTestResult->behavioral_insights['motivators'] 
                                : []
                            )
                    ) !!},

                    stressIndicators: {!! json_encode(
                        is_array($candidate->disc3DTestResult->stress_indicators ?? null) 
                            ? $candidate->disc3DTestResult->stress_indicators 
                            : []
                    ) !!},

                    workEnvironment: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['work_environment'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['work_environment'] 
                            : (is_array($candidate->disc3DTestResult->work_style_most ?? null) 
                                ? $candidate->disc3DTestResult->work_style_most 
                                : []
                            )
                    ) !!},

                    decisionMaking: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['decision_making'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['decision_making'] 
                            : []
                    ) !!},

                    leadershipStyle: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['leadership'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['leadership'] 
                            : []
                    ) !!},

                    conflictResolution: {!! json_encode(
                        is_array($candidate->disc3DTestResult->behavioral_insights['conflict_resolution'] ?? null) 
                            ? $candidate->disc3DTestResult->behavioral_insights['conflict_resolution'] 
                            : []
                    ) !!},

                    // Use real detailed summaries from database
                    detailedWorkStyle: {!! json_encode($candidate->disc3DTestResult->work_style_summary ?? $candidate->disc3DTestResult->overall_profile ?? "Belum tersedia analisis gaya kerja") !!},
                    
                    detailedCommunicationStyle: {!! json_encode($candidate->disc3DTestResult->communication_summary ?? $candidate->disc3DTestResult->personality_profile ?? "Belum tersedia analisis gaya komunikasi") !!},
                    
                    publicSelfAnalysis: {!! json_encode($candidate->disc3DTestResult->public_self_summary ?? "Belum tersedia analisis diri publik") !!},
                    
                    privateSelfAnalysis: {!! json_encode($candidate->disc3DTestResult->private_self_summary ?? "Belum tersedia analisis diri pribadi") !!},
                    
                    adaptationAnalysis: {!! json_encode($candidate->disc3DTestResult->adaptation_summary ?? "Belum tersedia analisis adaptasi") !!}
                },
                // ✅ NEW: Pattern combination data - ambil dari tabel pattern berdasarkan most_pattern code
                patternCombination: @if($patternData)
                    {!! json_encode([
                        'pattern_code' => $patternData->pattern_code,
                        'pattern_name' => $patternData->pattern_name,
                        'description' => $patternData->description,
                        'strengths' => $patternData->strengths ?? [],
                        'weaknesses' => $patternData->weaknesses ?? [],
                        'ideal_environment' => $patternData->ideal_environment ?? [],
                        'communication_tips' => $patternData->communication_tips ?? [],
                        'career_matches' => $patternData->career_matches ?? []
                    ]) !!}
                @else
                    {
                        'pattern_code' => null,
                        'pattern_name' => 'Pattern tidak dikenal',
                        'description' => 'Deskripsi tidak tersedia',
                        'strengths' => [],
                        'weaknesses' => [],
                        'ideal_environment' => [],
                        'communication_tips' => [],
                        'career_matches' => []
                    }
                @endif,
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

        {{-- ✅ ENHANCED: KOMBINASI POLA KEPRIBADIAN + ANALISIS KARAKTER DISC --}}
        @if($patternData)
        <div class="disc-pattern-combination">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-puzzle-piece" style="color: #8b5cf6;"></i>
                Kombinasi Pola Kepribadian & Analisis Karakter
            </h3>
            
            {{-- Header Pattern Information --}}
            <div class="disc-pattern-header">
                <div class="disc-pattern-info">
                    <h4 class="disc-pattern-name" id="discPatternName">
                        {{ $patternData->pattern_name }}
                    </h4>
                    <p class="disc-pattern-code">
                        Kode Pattern: <strong>{{ $patternData->pattern_code }}</strong>
                    </p>
                    <div class="disc-pattern-description" id="discPatternDescription">
                        {{ $patternData->description }}
                    </div>
                </div>
            </div>

            {{-- ✅ NEW: Ringkasan Karakter (pindahan dari Analisis Karakter DISC) --}}
            <div class="disc-character-main">
                <div class="disc-character-summary">
                    <h5 class="summary-title">
                        <i class="fas fa-lightbulb"></i>
                        Ringkasan Karakter
                    </h5>
                    <p class="summary-content">
                        @php
                            $primaryType = $candidate->disc3DTestResult->primary_type ?? 'D';
                            $patternName = $patternData->pattern_name;
                            $mainStrengths = $patternData->strengths ? implode(' dan ', array_slice($patternData->strengths, 0, 2)) : 'berbagai kekuatan';
                        @endphp
                        
                        Kandidat menunjukkan karakter <strong>{{ $patternName }}</strong> dengan kecenderungan dimensi <strong>{{ $primaryType }}</strong>. 
                        Memiliki {{ $mainStrengths }} sebagai kekuatan utama. 
                        Cocok untuk peran yang membutuhkan {{ $patternData->ideal_environment ? strtolower($patternData->ideal_environment[0] ?? 'fleksibilitas') : 'adaptabilitas' }} dalam bekerja.
                    </p>
                </div>
            </div>

            <div class="disc-pattern-analysis-grid">
                {{-- 1. Kekuatan Pattern (dari Kombinasi Pola Kepribadian) --}}
                @if($patternData->strengths && count($patternData->strengths) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-gem" style="color: #10b981;"></i>
                        Kekuatan Pattern
                    </h4>
                    <div class="disc-trait-tags" id="discPatternStrengthTags">
                        @foreach($patternData->strengths as $strength)
                            <span class="disc-trait-tag pattern-strength">{{ $strength }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 2. Area Perhatian (dari Kombinasi Pola Kepribadian) --}}
                @if($patternData->weaknesses && count($patternData->weaknesses) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-exclamation-circle" style="color: #f59e0b;"></i>
                        Area Perhatian
                    </h4>
                    <div class="disc-trait-tags" id="discPatternWeaknessTags">
                        @foreach($patternData->weaknesses as $weakness)
                            <span class="disc-trait-tag pattern-weakness">{{ $weakness }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 3. Lingkungan Ideal (dari Kombinasi Pola Kepribadian) --}}
                @if($patternData->ideal_environment && count($patternData->ideal_environment) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-seedling" style="color: #059669;"></i>
                        Lingkungan Ideal
                    </h4>
                    <div class="disc-trait-tags" id="discPatternEnvironmentTags">
                        @foreach($patternData->ideal_environment as $environment)
                            <span class="disc-trait-tag pattern-environment">{{ $environment }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 4. Tips Komunikasi (dari Kombinasi Pola Kepribadian) --}}
                @if($patternData->communication_tips && count($patternData->communication_tips) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-bullhorn" style="color: #0891b2;"></i>
                        Tips Komunikasi
                    </h4>
                    <div class="disc-trait-tags" id="discPatternCommunicationTags">
                        @foreach($patternData->communication_tips as $tip)
                            <span class="disc-trait-tag pattern-communication">{{ $tip }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ✅ NEW: 5. Cara Memotivasi (pindahan dari Analisis Karakter DISC) --}}
                @if(isset($dominantInterpretation) && $dominantInterpretation->motivators && count($dominantInterpretation->motivators) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-rocket" style="color: #ef4444;"></i>
                        Cara Memotivasi
                    </h4>
                    <div class="disc-trait-tags" id="discMotivationTags">
                        @foreach(array_slice($dominantInterpretation->motivators, 0, 4) as $motivator)
                            <span class="disc-trait-tag pattern-motivation">{{ $motivator }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- 6. Role/Posisi Cocok (dari Kombinasi Pola Kepribadian) --}}
                @if($patternData->career_matches && count($patternData->career_matches) > 0)
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-briefcase" style="color: #7c3aed;"></i>
                        Role/Posisi Cocok
                    </h4>
                    <div class="disc-trait-tags" id="discPatternCareerTags">
                        @foreach($patternData->career_matches as $career)
                            <span class="disc-trait-tag pattern-career">{{ $career }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>


        </div>
        @endif

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
                    {{-- Score cards for MOST - Menampilkan segment values --}}
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
                    {{-- Score cards for LEAST - Menampilkan segment values --}}
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

        {{-- COMPREHENSIVE PERSONALITY ANALYSIS --}}
        @php
            // Safe array extraction with proper type checking
            $behavioralInsights = $candidate->disc3DTestResult->behavioral_insights ?? [];
            
            // Extract arrays safely from various sources
            $strengthsArray = [];
            if (isset($behavioralInsights['strengths']) && is_array($behavioralInsights['strengths'])) {
                $strengthsArray = $behavioralInsights['strengths'];
            }
            
            $developmentArray = [];
            if (isset($behavioralInsights['development_areas']) && is_array($behavioralInsights['development_areas'])) {
                $developmentArray = $behavioralInsights['development_areas'];
            }
            
            $tendenciesArray = [];
            if (isset($behavioralInsights['tendencies']) && is_array($behavioralInsights['tendencies'])) {
                $tendenciesArray = $behavioralInsights['tendencies'];
            }
            
            $communicationArray = [];
            if (isset($behavioralInsights['communication']) && is_array($behavioralInsights['communication'])) {
                $communicationArray = $behavioralInsights['communication'];
            } elseif (is_array($candidate->disc3DTestResult->communication_style_most ?? null)) {
                $communicationArray = $candidate->disc3DTestResult->communication_style_most;
            }
            
            $motivatorsArray = [];
            if (is_array($candidate->disc3DTestResult->motivators_most ?? null)) {
                $motivatorsArray = $candidate->disc3DTestResult->motivators_most;
            } elseif (isset($behavioralInsights['motivators']) && is_array($behavioralInsights['motivators'])) {
                $motivatorsArray = $behavioralInsights['motivators'];
            }
            
            $stressArray = [];
            if (is_array($candidate->disc3DTestResult->stress_indicators ?? null)) {
                $stressArray = $candidate->disc3DTestResult->stress_indicators;
            }
            
            $workEnvArray = [];
            if (isset($behavioralInsights['work_environment']) && is_array($behavioralInsights['work_environment'])) {
                $workEnvArray = $behavioralInsights['work_environment'];
            } elseif (is_array($candidate->disc3DTestResult->work_style_most ?? null)) {
                $workEnvArray = $candidate->disc3DTestResult->work_style_most;
            }
            
            $decisionArray = [];
            if (isset($behavioralInsights['decision_making']) && is_array($behavioralInsights['decision_making'])) {
                $decisionArray = $behavioralInsights['decision_making'];
            }
            
            $leadershipArray = [];
            if (isset($behavioralInsights['leadership']) && is_array($behavioralInsights['leadership'])) {
                $leadershipArray = $behavioralInsights['leadership'];
            }
            
            $conflictArray = [];
            if (isset($behavioralInsights['conflict_resolution']) && is_array($behavioralInsights['conflict_resolution'])) {
                $conflictArray = $behavioralInsights['conflict_resolution'];
            }
            
            // Check if any analysis data exists
            $hasAnalysisData = !empty($strengthsArray) || !empty($developmentArray) || !empty($tendenciesArray) || 
                              !empty($communicationArray) || !empty($motivatorsArray) || !empty($stressArray) || 
                              !empty($workEnvArray) || !empty($decisionArray) || !empty($leadershipArray) || !empty($conflictArray);
        @endphp

        @if($hasAnalysisData)
        <div class="disc-comprehensive-analysis">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-brain" style="color: #7c3aed;"></i>
                Analisis Kepribadian Komprehensif
            </h3>
            
            <div class="disc-analysis-mega-grid">
                
                {{-- Show only if strengths data exists and is array --}}
                @if(!empty($strengthsArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-star" style="color: #059669;"></i>
                        Kelebihan & Kekuatan
                    </h4>
                    <div class="disc-trait-tags" id="discAllStrengthTags">
                        @foreach($strengthsArray as $strength)
                            <span class="disc-trait-tag strength">{{ $strength }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if development areas data exists and is array --}}
                @if(!empty($developmentArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-arrow-up" style="color: #dc2626;"></i>
                        Area Pengembangan
                    </h4>
                    <div class="disc-trait-tags" id="discAllDevelopmentTags">
                        @foreach($developmentArray as $area)
                            <span class="disc-trait-tag development">{{ $area }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if behavioral tendencies data exists and is array --}}
                @if(!empty($tendenciesArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-user-cog" style="color: #4f46e5;"></i>
                        Kecenderungan Perilaku
                    </h4>
                    <div class="disc-trait-tags" id="discBehavioralTags">
                        @foreach($tendenciesArray as $tendency)
                            <span class="disc-trait-tag behavioral">{{ $tendency }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if communication preferences data exists and is array --}}
                @if(!empty($communicationArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-comments" style="color: #0891b2;"></i>
                        Preferensi Komunikasi
                    </h4>
                    <div class="disc-trait-tags" id="discCommunicationTags">
                        @foreach($communicationArray as $comm)
                            <span class="disc-trait-tag communication">{{ $comm }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if motivators data exists and is array --}}
                @if(!empty($motivatorsArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-fire" style="color: #ea580c;"></i>
                        Motivator Utama
                    </h4>
                    <div class="disc-trait-tags" id="discMotivatorTags">
                        @foreach($motivatorsArray as $motivator)
                            <span class="disc-trait-tag motivator">{{ $motivator }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if stress indicators data exists and is array --}}
                @if(!empty($stressArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-exclamation-triangle" style="color: #d97706;"></i>
                        Indikator Stres
                    </h4>
                    <div class="disc-trait-tags" id="discStressTags">
                        @foreach($stressArray as $stress)
                            <span class="disc-trait-tag stress">{{ $stress }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if work environment data exists and is array --}}
                @if(!empty($workEnvArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-building" style="color: #7c2d12;"></i>
                        Lingkungan Kerja Ideal
                    </h4>
                    <div class="disc-trait-tags" id="discEnvironmentTags">
                        @foreach($workEnvArray as $env)
                            <span class="disc-trait-tag environment">{{ $env }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if decision making data exists and is array --}}
                @if(!empty($decisionArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-lightbulb" style="color: #ca8a04;"></i>
                        Gaya Pengambilan Keputusan
                    </h4>
                    <div class="disc-trait-tags" id="discDecisionTags">
                        @foreach($decisionArray as $decision)
                            <span class="disc-trait-tag decision">{{ $decision }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if leadership data exists and is array --}}
                @if(!empty($leadershipArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-crown" style="color: #9333ea;"></i>
                        Gaya Kepemimpinan
                    </h4>
                    <div class="disc-trait-tags" id="discLeadershipTags">
                        @foreach($leadershipArray as $leadership)
                            <span class="disc-trait-tag leadership">{{ $leadership }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Show only if conflict resolution data exists and is array --}}
                @if(!empty($conflictArray))
                <div class="disc-analysis-card">
                    <h4 class="disc-analysis-title">
                        <i class="fas fa-handshake" style="color: #059669;"></i>
                        Resolusi Konflik
                    </h4>
                    <div class="disc-trait-tags" id="discConflictTags">
                        @foreach($conflictArray as $conflict)
                            <span class="disc-trait-tag conflict">{{ $conflict }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

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
        </div>
    @endif
</section>