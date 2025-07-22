<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test 2 - {{ $candidate->candidate_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .test-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .section-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .section-card.active {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .section-card.completed {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section-number {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 16px;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        
        .section-number.completed {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .choice-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .choice-item:hover {
            border-color: #3b82f6;
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .choice-item.selected-most {
            border-color: #dc2626;
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .choice-item.selected-least {
            border-color: #2563eb;
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .choice-text {
            font-size: 16px;
            font-weight: 500;
            color: #1f2937;
            line-height: 1.5;
            width: 100%;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
        
        .stats-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 8px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .selection-badges {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .selection-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            opacity: 0.3;
            transition: opacity 0.3s ease;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .selection-badge.most {
            background: #dc2626;
        }
        
        .selection-badge.least {
            background: #2563eb;
        }
        
        .selection-badge.active {
            opacity: 1;
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 32px;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.4);
        }
        
        .instruction-panel {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .most-instruction {
            color: #dc2626;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .least-instruction {
            color: #2563eb;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .test-container {
                padding: 20px;
            }
            
            .section-card {
                padding: 24px;
            }
            
            .navigation-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .selection-badges {
                flex-direction: column;
                gap: 8px;
            }
            
            .selection-badge {
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-4 px-4">
        <!-- Header -->
        <div class="test-container mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Test 2</h1>
                    <p class="text-sm text-gray-600">Kandidat: <strong>{{ $candidate->candidate_code }}</strong></p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Test Code</div>
                    <div class="font-mono font-bold">{{ $session->test_code }}</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-fill" id="progressBar" style="width: 0%"></div>
            </div>
            
            <!-- Stats Panel -->
            <div class="stats-panel">
                <div class="grid grid-cols-4 gap-4">
                    <div class="stat-item">
                        <div class="stat-value" id="currentSection">1</div>
                        <div class="stat-label">Section</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="completedCount">0</div>
                        <div class="stat-label">Selesai</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="remainingCount">24</div>
                        <div class="stat-label">Tersisa</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="progressPercent">0%</div>
                        <div class="stat-label">Progress</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Panel -->
        <div class="instruction-panel">
            <div class="most-instruction">MOST: Pilih pernyataan yang PALING menggambarkan Anda</div>
            <div class="least-instruction">LEAST: Pilih pernyataan yang PALING TIDAK menggambarkan Anda</div>
            <div class="text-sm text-gray-600 mt-2">
                Anda harus memilih satu MOST dan satu LEAST untuk setiap section
            </div>
        </div>

        <!-- Test Area -->
        <div class="test-container">
            <div id="testArea">
                @if(isset($sections) && $sections->count() > 0)
                    @foreach($sections as $index => $section)
                        <div class="section-container" id="section{{ $section->section_number ?? ($index + 1) }}" style="display: none;">
                            <div class="section-card {{ $index == 0 ? 'active' : '' }}">
                                <div class="section-header">
                                    <div class="section-number">
                                        {{ $section->section_number ?? ($index + 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">Section {{ $section->section_number ?? ($index + 1) }}</h3>
                                        <p class="text-sm text-gray-600">{{ $section->section_title ?? 'Pilih Most dan Least' }}</p>
                                    </div>
                                </div>
                                
                                <div class="choices-container">
                                    @if(isset($section->choices) && count($section->choices) > 0)
                                        @php
                                            // Randomize choices for this section
                                            $randomizedChoices = $section->choices->shuffle();
                                        @endphp
                                        @foreach($randomizedChoices as $choice)
                                            <div class="choice-item" 
                                                 data-choice-id="{{ $choice->id }}" 
                                                 data-dimension="{{ $choice->choice_dimension ?? 'D' }}"
                                                 data-section="{{ $section->id }}">
                                                <div class="choice-text">{{ $choice->choice_text ?? 'Choice text' }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                
                                <!-- Selection Badges -->
                                <div class="selection-badges">
                                    <div class="selection-badge most" id="mostBadge{{ $section->section_number ?? ($index + 1) }}">
                                        MOST: Belum dipilih
                                    </div>
                                    <div class="selection-badge least" id="leastBadge{{ $section->section_number ?? ($index + 1) }}">
                                        LEAST: Belum dipilih
                                    </div>
                                </div>
                                
                                <!-- Navigation Buttons -->
                                <div class="navigation-buttons">
                                    <button class="btn btn-primary" onclick="nextSection()" 
                                            id="nextBtn{{ $section->section_number ?? ($index + 1) }}" disabled>
                                        @if($index < count($sections) - 1)
                                            Lanjut ke Section {{ ($section->section_number ?? ($index + 1)) + 1 }} →
                                        @else
                                            Selesaikan Test 2
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8">
                        <p class="text-red-600 font-semibold">No sections available. Please contact administrator.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white p-8 rounded-lg text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-lg font-semibold">Memproses hasil test...</p>
            <p class="text-sm text-gray-600 mt-2">Mohon tunggu, sedang menghitung profil Anda</p>
        </div>
    </div>

    <script>
        // ==========================================
        // DISC 3D SINGLE RUN TEST MANAGER V3.1
        // ==========================================
        
        // Test configuration
        const TOTAL_SECTIONS = 24;
        const SESSION_ID = {{ $session->id ?? 1 }};
        const CANDIDATE_CODE = '{{ $candidate->candidate_code ?? "UNKNOWN" }}';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Test state
        let currentSectionIndex = 1;
        let completedSections = 0;
        let allResponses = [];
        let startTime = Date.now();
        let sectionStartTime = Date.now();
        
        // Initialize test
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 Initializing DISC Single Run Test...');
            
            if (!SESSION_ID || !CANDIDATE_CODE || !CSRF_TOKEN) {
                console.error('❌ Missing required configuration');
                alert('Configuration error. Please refresh.');
                return;
            }
            
            showSection(1);
            updateStats();
            
            // Add click handlers
            document.querySelectorAll('.choice-item').forEach(choice => {
                choice.addEventListener('click', function() {
                    selectChoice(this);
                });
            });
            
            // Page unload protection
            window.addEventListener('beforeunload', function(e) {
                if (completedSections > 0 && completedSections < TOTAL_SECTIONS) {
                    e.preventDefault();
                    e.returnValue = `Test berlangsung (${completedSections}/24 section)`;
                }
            });
            
            console.log('✅ DISC Single Run Test initialized');
        });
        
        function showSection(sectionNumber) {
            // Hide all sections
            document.querySelectorAll('.section-container').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show current section
            const section = document.getElementById('section' + sectionNumber);
            if (section) {
                section.style.display = 'block';
                currentSectionIndex = sectionNumber;
                sectionStartTime = Date.now();
                
                // Update section card state
                const sectionCard = section.querySelector('.section-card');
                if (sectionCard) {
                    sectionCard.classList.add('active');
                }
                
                updateStats();
                
                console.log('📄 Showing section:', sectionNumber);
            }
        }
        
        function selectChoice(choiceElement) {
            const sectionId = parseInt(choiceElement.dataset.section);
            const choiceId = parseInt(choiceElement.dataset.choiceId);
            const dimension = choiceElement.dataset.dimension;
            
            if (!sectionId || !choiceId || !dimension) {
                console.error('❌ Invalid choice data');
                return;
            }
            
            const currentSection = choiceElement.closest('.section-container');
            if (!currentSection) return;
            
            const mostSelected = currentSection.querySelector('.choice-item.selected-most');
            const leastSelected = currentSection.querySelector('.choice-item.selected-least');
            
            // Handle selection logic
            if (choiceElement.classList.contains('selected-most')) {
                // Deselect most
                choiceElement.classList.remove('selected-most');
            } else if (choiceElement.classList.contains('selected-least')) {
                // Deselect least
                choiceElement.classList.remove('selected-least');
            } else if (!mostSelected) {
                // Select as most
                choiceElement.classList.add('selected-most');
            } else if (!leastSelected) {
                // Select as least (check not same as most)
                if (choiceElement === mostSelected) {
                    alert('❌ MOST dan LEAST tidak boleh sama.');
                    return;
                }
                choiceElement.classList.add('selected-least');
            } else {
                // Replace most selection
                mostSelected.classList.remove('selected-most');
                choiceElement.classList.add('selected-most');
            }
            
            updateSelectionBadges(currentSectionIndex);
            checkSectionCompletion(currentSectionIndex);
        }
        
        function updateSelectionBadges(sectionNumber) {
            const currentSection = document.getElementById('section' + sectionNumber);
            const mostSelected = currentSection.querySelector('.choice-item.selected-most');
            const leastSelected = currentSection.querySelector('.choice-item.selected-least');
            
            const mostBadge = document.getElementById('mostBadge' + sectionNumber);
            const leastBadge = document.getElementById('leastBadge' + sectionNumber);
            
            if (mostBadge) {
                if (mostSelected) {
                    const text = mostSelected.querySelector('.choice-text').textContent;
                    // Remove dimension from display, show only text
                    mostBadge.textContent = `MOST: ${text.substring(0, 40)}${text.length > 40 ? '...' : ''}`;
                    mostBadge.classList.add('active');
                } else {
                    mostBadge.textContent = 'MOST: Belum dipilih';
                    mostBadge.classList.remove('active');
                }
            }
            
            if (leastBadge) {
                if (leastSelected) {
                    const text = leastSelected.querySelector('.choice-text').textContent;
                    // Remove dimension from display, show only text
                    leastBadge.textContent = `LEAST: ${text.substring(0, 40)}${text.length > 40 ? '...' : ''}`;
                    leastBadge.classList.add('active');
                } else {
                    leastBadge.textContent = 'LEAST: Belum dipilih';
                    leastBadge.classList.remove('active');
                }
            }
        }
        
        function checkSectionCompletion(sectionNumber) {
            const currentSection = document.getElementById('section' + sectionNumber);
            const mostSelected = currentSection.querySelector('.choice-item.selected-most');
            const leastSelected = currentSection.querySelector('.choice-item.selected-least');
            
            const isCompleted = mostSelected && leastSelected;
            const nextBtn = document.getElementById('nextBtn' + sectionNumber);
            
            if (nextBtn) {
                nextBtn.disabled = !isCompleted;
            }
            
            // Update section card appearance
            const sectionCard = currentSection.querySelector('.section-card');
            if (sectionCard) {
                if (isCompleted) {
                    sectionCard.classList.add('completed');
                } else {
                    sectionCard.classList.remove('completed');
                }
            }
        }
        
        function nextSection() {
            const currentSection = document.getElementById('section' + currentSectionIndex);
            const mostSelected = currentSection.querySelector('.choice-item.selected-most');
            const leastSelected = currentSection.querySelector('.choice-item.selected-least');
            
            if (!mostSelected || !leastSelected) {
                alert('Silakan pilih MOST dan LEAST terlebih dahulu.');
                return;
            }
            
            // Store response locally (single run mode)
            const sectionId = parseInt(mostSelected.dataset.section);
            const timeSpent = Math.max(1, Math.round((Date.now() - sectionStartTime) / 1000));
            
            const response = {
                section_id: sectionId,
                most_choice_id: parseInt(mostSelected.dataset.choiceId),
                least_choice_id: parseInt(leastSelected.dataset.choiceId),
                time_spent: timeSpent
            };
            
            allResponses.push(response);
            completedSections++;
            
            console.log('💾 Response stored locally:', response);
            
            // Mark section as completed
            const sectionCard = currentSection.querySelector('.section-card');
            const sectionNumber = currentSection.querySelector('.section-number');
            if (sectionCard) sectionCard.classList.add('completed');
            if (sectionNumber) sectionNumber.classList.add('completed');
            
            // Check if test is complete
            if (currentSectionIndex >= TOTAL_SECTIONS) {
                completeTest();
                return;
            }
            
            // Move to next section
            showSection(currentSectionIndex + 1);
        }
        
        function completeTest() {
            if (allResponses.length !== TOTAL_SECTIONS) {
                alert(`Test belum lengkap. Baru ${allResponses.length} dari ${TOTAL_SECTIONS} section yang diselesaikan.`);
                return;
            }
            
            // Validate all responses
            for (let i = 0; i < allResponses.length; i++) {
                const response = allResponses[i];
                if (!response.section_id || !response.most_choice_id || !response.least_choice_id ||
                    response.most_choice_id === response.least_choice_id) {
                    alert(`Section ${i + 1} memiliki data yang tidak valid. Silakan periksa kembali.`);
                    return;
                }
            }
            
            if (!confirm('🎯 Menyelesaikan test 2 ?\n\nTest tidak dapat diubah setelah diselesaikan.')) {
                return;
            }
            
            showLoadingOverlay();
            
            const totalDuration = Math.max(1, Math.round((Date.now() - startTime) / 1000));
            
            // Submit all responses in bulk
            fetch('/disc3d/submit-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    session_id: SESSION_ID,
                    responses: allResponses,
                    total_duration: totalDuration
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('✅ Test completed:', data);
                
                if (data.success) {
                    // Clear local storage (reset)
                    clearLocalStorage();
                    
                    alert('Test 2 berhasil diselesaikan!');
                    
                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('❌ Test completion failed:', error);
                hideLoadingOverlay();
                alert('Gagal menyelesaikan test: ' + error.message);
            });
        }
        
        function updateStats() {
            const progress = (completedSections / TOTAL_SECTIONS) * 100;
            const remaining = TOTAL_SECTIONS - completedSections;
            
            document.getElementById('currentSection').textContent = currentSectionIndex;
            document.getElementById('completedCount').textContent = completedSections;
            document.getElementById('remainingCount').textContent = remaining;
            document.getElementById('progressPercent').textContent = Math.round(progress) + '%';
            
            const progressBar = document.getElementById('progressBar');
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
        }
        
        function clearLocalStorage() {
            try {
                // Clear any DISC related storage
                const keysToRemove = [];
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && (key.includes('disc3d') || key.includes('disc_3d'))) {
                        keysToRemove.push(key);
                    }
                }
                
                keysToRemove.forEach(key => {
                    localStorage.removeItem(key);
                    console.log('🧹 Removed storage key:', key);
                });
                
                console.log('✨ Local storage cleared successfully');
                
            } catch (error) {
                console.error('❌ Error clearing local storage:', error);
            }
        }
        
        function showLoadingOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'flex';
        }
        
        function hideLoadingOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        }
        
        console.log('🎯 Test 2 Single Run Test Ready! (Without Dimension Indicators)');
    </script>
</body>
</html>