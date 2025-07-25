<section id="disc-section" class="content-section">
    <h2 class="section-title">
        <i class="fas fa-chart-pie"></i>
        Hasil Tes DISC 3D - Analisis Kepribadian Komprehensif
    </h2>

{{-- ✅ NEW: CHARACTER ANALYSIS SECTION --}}
{{-- TAMBAHKAN SETELAH PATTERN COMBINATION SECTION DAN SEBELUM COMPREHENSIVE GRAPHS --}}
@if($candidate->disc3DTestResult && isset($patternData))
<div class="disc-character-analysis">
    <h3 class="disc-analysis-section-title">
        <i class="fas fa-user-circle" style="color: #8b5cf6;"></i>
        Analisis Karakter DISC
    </h3>
    
    {{-- Step 1: Karakter Utama --}}
    <div class="disc-character-main">
        <div class="disc-character-header">
            <h4 class="disc-character-title">
                <i class="fas fa-star"></i>
                Karakter Utama: {{ $patternData->pattern_name }}
            </h4>
            <p class="disc-character-description">
                {{ $patternData->description }}
            </p>
        </div>
        
        <div class="disc-character-traits-grid">
            {{-- Kekuatan --}}
            @if($patternData->strengths && count($patternData->strengths) > 0)
            <div class="disc-character-trait-card strengths">
                <h5 class="disc-trait-card-title">
                    <i class="fas fa-thumbs-up"></i>
                    Kekuatan Utama
                </h5>
                <div class="disc-trait-list">
                    @foreach(array_slice($patternData->strengths, 0, 4) as $strength)
                        <span class="disc-trait-item strength">{{ $strength }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Kelemahan --}}
            @if($patternData->weaknesses && count($patternData->weaknesses) > 0)
            <div class="disc-character-trait-card weaknesses">
                <h5 class="disc-trait-card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Area Perhatian
                </h5>
                <div class="disc-trait-list">
                    @foreach(array_slice($patternData->weaknesses, 0, 4) as $weakness)
                        <span class="disc-trait-item weakness">{{ $weakness }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        {{-- Character Summary --}}
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
    
    {{-- Step 2: Detail Dimensi Dominan --}}
    @if(isset($dominantInterpretation))
    @php
        $dominantDim = $dominantInterpretation->dimension;
        $dominantLevel = $dominantInterpretation->segment_level;
        $dimensionLabels = ['D' => 'Dominance', 'I' => 'Influence', 'S' => 'Steadiness', 'C' => 'Conscientiousness'];
    @endphp
    
    <div class="disc-dominant-dimension">
        <h4 class="disc-character-title">
            <i class="fas fa-chart-line"></i>
            Dimensi Dominan: {{ $dimensionLabels[$dominantDim] ?? $dominantDim }} (Level {{ $dominantLevel }})
        </h4>
        
        <div class="disc-dimension-details-grid">
            {{-- Karakteristik --}}
            @if($dominantInterpretation->characteristics && count($dominantInterpretation->characteristics) > 0)
            <div class="disc-dimension-detail-card">
                <h5 class="disc-detail-card-title">
                    <i class="fas fa-user"></i>
                    Karakteristik Utama
                </h5>
                <div class="disc-detail-list">
                    @foreach(array_slice($dominantInterpretation->characteristics, 0, 3) as $characteristic)
                        <span class="disc-detail-item">{{ $characteristic }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Gaya Kerja --}}
            @if($dominantInterpretation->work_style && count($dominantInterpretation->work_style) > 0)
            <div class="disc-dimension-detail-card">
                <h5 class="disc-detail-card-title">
                    <i class="fas fa-briefcase"></i>
                    Gaya Kerja
                </h5>
                <div class="disc-detail-list">
                    @foreach(array_slice($dominantInterpretation->work_style, 0, 3) as $workStyle)
                        <span class="disc-detail-item">{{ $workStyle }}</span>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Motivator --}}
            @if($dominantInterpretation->motivators && count($dominantInterpretation->motivators) > 0)
            <div class="disc-dimension-detail-card">
                <h5 class="disc-detail-card-title">
                    <i class="fas fa-fire"></i>
                    Motivator Utama
                </h5>
                <div class="disc-detail-list">
                    @foreach(array_slice($dominantInterpretation->motivators, 0, 3) as $motivator)
                        <span class="disc-detail-item">{{ $motivator }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        <div class="disc-dimension-insight">
            <p class="insight-content">
                <i class="fas fa-info-circle"></i>
                Dimensi <strong>{{ $dimensionLabels[$dominantDim] ?? $dominantDim }}</strong> pada level {{ $dominantLevel }} menunjukkan bahwa kandidat memiliki 
                {{ $dominantInterpretation->characteristics[0] ?? 'karakteristik yang kuat' }} dalam perilaku kerja sehari-hari. 
                Hal ini mempengaruhi cara kandidat {{ $dominantInterpretation->work_style[0] ?? 'bekerja dan berinteraksi' }} dengan tim dan tugas.
            </p>
        </div>
    </div>
    @endif
    
    {{-- Step 3: Insight Komunikasi & Management --}}
    <div class="disc-practical-recommendations">
        <h4 class="disc-character-title">
            <i class="fas fa-hands-helping"></i>
            Rekomendasi Praktis
        </h4>
        
        <div class="disc-recommendations-grid">
            {{-- Tips Komunikasi --}}
            <div class="disc-recommendation-card communication">
                <h5 class="disc-recommendation-title">
                    <i class="fas fa-comments"></i>
                    Cara Berkomunikasi
                </h5>
                <div class="disc-recommendation-content">
                    @if($patternData->communication_tips && count($patternData->communication_tips) > 0)
                        @foreach(array_slice($patternData->communication_tips, 0, 2) as $tip)
                            <p class="recommendation-item">• {{ $tip }}</p>
                        @endforeach
                    @else
                        <p class="recommendation-item">• Gunakan pendekatan yang sesuai dengan karakter {{ $patternData->pattern_name }}</p>
                    @endif
                </div>
            </div>
            
            {{-- Cara Memotivasi --}}
            <div class="disc-recommendation-card motivation">
                <h5 class="disc-recommendation-title">
                    <i class="fas fa-rocket"></i>
                    Cara Memotivasi
                </h5>
                <div class="disc-recommendation-content">
                    @if(isset($dominantInterpretation) && $dominantInterpretation->motivators && count($dominantInterpretation->motivators) > 0)
                        @foreach(array_slice($dominantInterpretation->motivators, 0, 2) as $motivator)
                            <p class="recommendation-item">• {{ $motivator }}</p>
                        @endforeach
                    @else
                        <p class="recommendation-item">• Berikan penghargaan sesuai dengan preferensi karakter</p>
                        <p class="recommendation-item">• Fokus pada {{ $patternData->strengths[0] ?? 'kekuatan utama' }} kandidat</p>
                    @endif
                </div>
            </div>
            
            {{-- Career Match --}}
            <div class="disc-recommendation-card career">
                <h5 class="disc-recommendation-title">
                    <i class="fas fa-briefcase"></i>
                    Role/Posisi Cocok
                </h5>
                <div class="disc-recommendation-content">
                    @if($patternData->career_matches && count($patternData->career_matches) > 0)
                        @foreach(array_slice($patternData->career_matches, 0, 3) as $career)
                            <p class="recommendation-item">• {{ $career }}</p>
                        @endforeach
                    @else
                        <p class="recommendation-item">• Posisi yang sesuai dengan karakter {{ $patternData->pattern_name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

        {{-- COMPREHENSIVE PERSONALITY ANALYSIS - TETAP SAMA --}}
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

        {{-- DETAILED BEHAVIORAL ANALYSIS - TETAP SAMA --}}
        @php
            $workStyleSummary = $candidate->disc3DTestResult->work_style_summary;
            $communicationSummary = $candidate->disc3DTestResult->communication_summary;
            $publicSelfSummary = $candidate->disc3DTestResult->public_self_summary;
            $privateSelfSummary = $candidate->disc3DTestResult->private_self_summary;
            $adaptationSummary = $candidate->disc3DTestResult->adaptation_summary;
            $overallProfile = $candidate->disc3DTestResult->overall_profile;
            $personalityProfile = $candidate->disc3DTestResult->personality_profile;
            
            $hasDetailedAnalysis = $workStyleSummary || $communicationSummary || $publicSelfSummary || 
                                  $privateSelfSummary || $adaptationSummary || $overallProfile || $personalityProfile;
        @endphp

        @if($hasDetailedAnalysis)
        <div class="disc-detailed-analysis">
            <h3 class="disc-analysis-section-title">
                <i class="fas fa-search" style="color: #1e40af;"></i>
                Analisis Perilaku Mendalam
            </h3>
            
            <div class="disc-detailed-grid">
                {{-- Show only if work style summary exists --}}
                @if($workStyleSummary || $overallProfile)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-briefcase"></i>
                        Gaya Kerja Detail
                    </h4>
                    <p class="disc-detailed-content" id="discDetailedWorkStyle">
                        {{ $workStyleSummary ?: $overallProfile }}
                    </p>
                </div>
                @endif

                {{-- Show only if communication summary exists --}}
                @if($communicationSummary || $personalityProfile)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-microphone"></i>
                        Gaya Komunikasi Detail
                    </h4>
                    <p class="disc-detailed-content" id="discDetailedCommStyle">
                        {{ $communicationSummary ?: $personalityProfile }}
                    </p>
                </div>
                @endif

                {{-- Show only if public self analysis exists --}}
                @if($publicSelfSummary)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-mask"></i>
                        Analisis Diri Publik (MOST)
                    </h4>
                    <p class="disc-detailed-content" id="discPublicSelfAnalysis">
                        {{ $publicSelfSummary }}
                    </p>
                </div>
                @endif

                {{-- Show only if private self analysis exists --}}
                @if($privateSelfSummary)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-heart"></i>
                        Analisis Diri Pribadi (LEAST)
                    </h4>
                    <p class="disc-detailed-content" id="discPrivateSelfAnalysis">
                        {{ $privateSelfSummary }}
                    </p>
                </div>
                @endif

                {{-- Show only if adaptation analysis exists --}}
                @if($adaptationSummary)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-exchange-alt"></i>
                        Analisis Adaptasi (CHANGE)
                    </h4>
                    <p class="disc-detailed-content" id="discAdaptationAnalysis">
                        {{ $adaptationSummary }}
                    </p>
                </div>
                @endif

                {{-- Show only if overall profile summary exists --}}
                @if($candidate->disc3DTestResult->summary)
                <div class="disc-detailed-card">
                    <h4 class="disc-detailed-title">
                        <i class="fas fa-file-alt"></i>
                        Ringkasan Profil Keseluruhan
                    </h4>
                    <p class="disc-detailed-content" id="discProfileSummary">
                        {{ $candidate->disc3DTestResult->summary }}
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- SESSION DETAILS - TETAP SAMA --}}
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