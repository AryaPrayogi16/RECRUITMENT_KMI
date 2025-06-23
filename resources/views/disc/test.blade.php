<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DISC - {{ $candidate->candidate_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .test-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .question-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .question-card.answered {
            border-color: #10b981;
            background: #ecfdf5;
        }
        
        .question-text {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .rating-options {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 16px;
        }
        
        .rating-option {
            flex: 1;
            text-align: center;
            padding: 12px 8px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .rating-option:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .rating-option.selected {
            border-color: #3b82f6;
            background: #3b82f6;
            color: white;
        }
        
        .rating-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .rating-label {
            font-size: 11px;
            font-weight: 500;
            line-height: 1.2;
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
        
        .navigation-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(16, 185, 129, 0.3);
        }
        
        .question-navigation {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            justify-content: center;
        }
        
        .question-nav-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .question-nav-btn.current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .question-nav-btn.answered {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .question-nav-btn:hover:not(.current) {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        @media (max-width: 768px) {
            .rating-options {
                flex-direction: column;
                gap: 12px;
            }
            
            .rating-option {
                display: flex;
                align-items: center;
                text-align: left;
                padding: 16px;
            }
            
            .rating-number {
                margin-right: 12px;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="test-container mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Test DISC</h1>
                    <p class="text-sm text-gray-600">Kandidat: <strong>{{ $candidate->candidate_code }}</strong></p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Test Code</div>
                    <div class="font-mono font-bold">{{ $session->test_code }}</div>
                    <div class="text-xs text-blue-600 mt-1">{{ ucwords(str_replace('_', ' ', $session->test_type)) }}</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-fill" id="progressBar" style="width: 0%"></div>
            </div>
            
            <!-- Stats -->
            <div class="stats-panel">
                <div class="grid grid-cols-3 gap-4">
                    <div class="stat-item">
                        <div class="stat-value" id="currentQuestion">1</div>
                        <div class="stat-label">Pertanyaan</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="answeredCount">0</div>
                        <div class="stat-label">Terjawab</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="remainingCount">{{ $questionsCount ?? count($questions) }}</div>
                        <div class="stat-label">Tersisa</div>
                    </div>
                </div>
            </div>
            
            <!-- Question Navigation -->
            <div class="question-navigation" id="questionNavigation">
                @foreach($questions as $index => $question)
                    <button class="question-nav-btn" data-question="{{ $index + 1 }}" id="navBtn{{ $index + 1 }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Test Area -->
        <div class="test-container">
            <div id="testArea">
                @foreach($questions as $index => $question)
                    <div class="question-container" id="question{{ $index + 1 }}" style="display: none;">
                        <div class="question-card">
                            <div class="question-text">
                                {{ $question->question_text_id ?? $question->question_text_en }}
                            </div>
                            
                            <div class="text-sm text-gray-500 mb-4">
                                Seberapa setuju Anda dengan pernyataan di atas?
                            </div>
                            
                            <div class="rating-options">
                                <div class="rating-option" data-value="1" data-question="{{ $question->id }}">
                                    <div class="rating-number">1</div>
                                    <div class="rating-label">Sangat Tidak Setuju</div>
                                </div>
                                <div class="rating-option" data-value="2" data-question="{{ $question->id }}">
                                    <div class="rating-number">2</div>
                                    <div class="rating-label">Tidak Setuju</div>
                                </div>
                                <div class="rating-option" data-value="3" data-question="{{ $question->id }}">
                                    <div class="rating-number">3</div>
                                    <div class="rating-label">Netral</div>
                                </div>
                                <div class="rating-option" data-value="4" data-question="{{ $question->id }}">
                                    <div class="rating-number">4</div>
                                    <div class="rating-label">Setuju</div>
                                </div>
                                <div class="rating-option" data-value="5" data-question="{{ $question->id }}">
                                    <div class="rating-number">5</div>
                                    <div class="rating-label">Sangat Setuju</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="navigation-buttons">
                            @if($index > 0)
                                <button class="btn btn-secondary" onclick="goToQuestion({{ $index }})">
                                    ← Sebelumnya
                                </button>
                            @endif
                            
                            @if($index < count($questions) - 1)
                                <button class="btn btn-primary" onclick="goToQuestion({{ $index + 2 }})" id="nextBtn{{ $index + 1 }}" disabled>
                                    Selanjutnya →
                                </button>
                            @else
                                <button class="btn btn-success" onclick="finishTest()" id="finishBtn" style="display: none;">
                                    Selesaikan Test
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white p-8 rounded-lg text-center max-w-md">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-lg font-semibold">Menyimpan hasil test...</p>
            <p class="text-sm text-gray-600 mt-2">Mohon tunggu, jangan tutup halaman</p>
        </div>
    </div>

    <script>
        // Test configuration
        const TOTAL_QUESTIONS = {{ $questionsCount ?? count($questions) }};
        const SESSION_ID = {{ intval($session->id ?? 0) }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Test state
        let currentQuestionIndex = 1;
        let answeredCount = 0;
        let startTime = Date.now();
        let questionStartTime = Date.now();
        
        // Store all answers locally
        let allAnswers = [];
        
        console.log('DISC Test initialized with SESSION_ID:', SESSION_ID);
        
        // Initialize test
        document.addEventListener('DOMContentLoaded', function() {
            showQuestion(1);
            updateStats();
            
            // Add click handlers for rating options
            document.querySelectorAll('.rating-option').forEach(option => {
                option.addEventListener('click', function() {
                    const questionId = parseInt(this.dataset.question);
                    const value = parseInt(this.dataset.value);
                    selectRating(this, questionId, value);
                });
            });
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key >= '1' && e.key <= '5') {
                    const value = parseInt(e.key);
                    const currentQuestion = document.querySelector(`#question${currentQuestionIndex}`);
                    const option = currentQuestion.querySelector(`[data-value="${value}"]`);
                    if (option) {
                        const questionId = parseInt(option.dataset.question);
                        selectRating(option, questionId, value);
                    }
                } else if (e.key === 'ArrowLeft' && currentQuestionIndex > 1) {
                    goToQuestion(currentQuestionIndex - 1);
                } else if (e.key === 'ArrowRight' && currentQuestionIndex < TOTAL_QUESTIONS) {
                    const isAnswered = allAnswers.some(a => a.question_id === getCurrentQuestionId());
                    if (isAnswered) {
                        goToQuestion(currentQuestionIndex + 1);
                    }
                }
            });
        });
        
        function showQuestion(questionNumber) {
            // Hide all questions
            document.querySelectorAll('.question-container').forEach(q => {
                q.style.display = 'none';
            });
            
            // Show current question
            const question = document.getElementById('question' + questionNumber);
            if (question) {
                question.style.display = 'block';
                currentQuestionIndex = questionNumber;
                questionStartTime = Date.now();
                
                // Update UI
                updateStats();
                updateNavigation();
                updateProgress();
                
                // Restore selected answer if exists
                restoreSelectedAnswer(questionNumber);
                
                // Show finish button on last question if all answered
                if (questionNumber === TOTAL_QUESTIONS && answeredCount === TOTAL_QUESTIONS) {
                    document.getElementById('finishBtn').style.display = 'block';
                }
            }
        }
        
        function selectRating(selectedOption, questionId, value) {
            const questionContainer = selectedOption.closest('.question-container');
            const questionCard = questionContainer.querySelector('.question-card');
            
            // Remove selected class from all options in this question
            questionContainer.querySelectorAll('.rating-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            selectedOption.classList.add('selected');
            
            // Mark question card as answered
            questionCard.classList.add('answered');
            
            // Calculate time spent
            const timeSpent = Math.max(1, Math.round((Date.now() - questionStartTime) / 1000));
            
            // Store answer
            saveAnswer(questionId, value, timeSpent);
            
            // Update navigation
            updateStats();
            updateNavigation();
            
            // Enable next button
            const nextBtn = document.getElementById(`nextBtn${currentQuestionIndex}`);
            if (nextBtn) {
                nextBtn.disabled = false;
            }
            
            // Auto-advance after short delay (optional)
            if (currentQuestionIndex < TOTAL_QUESTIONS) {
                setTimeout(() => {
                    goToQuestion(currentQuestionIndex + 1);
                }, 800);
            } else {
                // Last question - show finish button
                document.getElementById('finishBtn').style.display = 'block';
            }
        }
        
        function saveAnswer(questionId, response, timeSpent) {
            // Check if answer already exists
            const existingIndex = allAnswers.findIndex(a => a.question_id === questionId);
            
            const answerData = {
                question_id: questionId,
                response: response,
                time_spent: timeSpent
            };
            
            if (existingIndex >= 0) {
                // Update existing answer
                allAnswers[existingIndex] = answerData;
            } else {
                // Add new answer
                allAnswers.push(answerData);
                answeredCount++;
            }
            
            // Auto-save to localStorage
            localStorage.setItem('disc_answers_' + SESSION_ID, JSON.stringify(allAnswers));
            
            console.log('Answer saved:', answerData);
        }
        
        function restoreSelectedAnswer(questionNumber) {
            const questionId = getCurrentQuestionId();
            const existingAnswer = allAnswers.find(a => a.question_id === questionId);
            
            if (existingAnswer) {
                const question = document.getElementById('question' + questionNumber);
                const option = question.querySelector(`[data-value="${existingAnswer.response}"]`);
                if (option) {
                    option.classList.add('selected');
                    question.querySelector('.question-card').classList.add('answered');
                    
                    // Enable next button
                    const nextBtn = document.getElementById(`nextBtn${questionNumber}`);
                    if (nextBtn) {
                        nextBtn.disabled = false;
                    }
                }
            }
        }
        
        function getCurrentQuestionId() {
            const currentQuestion = document.querySelector(`#question${currentQuestionIndex}`);
            const option = currentQuestion.querySelector('.rating-option[data-question]');
            return option ? parseInt(option.dataset.question) : null;
        }
        
        function goToQuestion(questionNumber) {
            if (questionNumber >= 1 && questionNumber <= TOTAL_QUESTIONS) {
                showQuestion(questionNumber);
            }
        }
        
        function updateStats() {
            document.getElementById('currentQuestion').textContent = currentQuestionIndex;
            document.getElementById('answeredCount').textContent = answeredCount;
            document.getElementById('remainingCount').textContent = TOTAL_QUESTIONS - answeredCount;
        }
        
        function updateNavigation() {
            document.querySelectorAll('.question-nav-btn').forEach((btn, index) => {
                const questionNum = index + 1;
                btn.className = 'question-nav-btn';
                
                if (questionNum === currentQuestionIndex) {
                    btn.classList.add('current');
                } else if (allAnswers.some(a => {
                    // Get question ID for this question number
                    const questionEl = document.getElementById('question' + questionNum);
                    const option = questionEl?.querySelector('.rating-option[data-question]');
                    const questionId = option ? parseInt(option.dataset.question) : null;
                    return questionId && a.question_id === questionId;
                })) {
                    btn.classList.add('answered');
                }
                
                // Add click handler
                btn.onclick = () => goToQuestion(questionNum);
            });
        }
        
        function updateProgress() {
            const progress = (answeredCount / TOTAL_QUESTIONS) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        function finishTest() {
            if (allAnswers.length === 0) {
                alert('Tidak ada jawaban yang tersimpan. Pastikan Anda telah menjawab minimal beberapa pertanyaan.');
                return;
            }
            
            if (answeredCount < TOTAL_QUESTIONS) {
                const unanswered = TOTAL_QUESTIONS - answeredCount;
                if (!confirm(`Anda belum menjawab ${unanswered} pertanyaan. Apakah Anda yakin ingin menyelesaikan test sekarang?`)) {
                    return;
                }
            }
            
            if (confirm('Apakah Anda yakin ingin menyelesaikan test DISC? Test tidak dapat dilanjutkan setelah diselesaikan.')) {
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                // Calculate total duration
                const totalDuration = Math.max(1, Math.round((Date.now() - startTime) / 1000));
                
                // Prepare test data
                const testData = {
                    session_id: SESSION_ID,
                    answers: allAnswers,
                    total_duration: totalDuration
                };
                
                console.log('Submitting DISC test data:', {
                    session_id: SESSION_ID,
                    answers_count: allAnswers.length,
                    total_duration: totalDuration
                });
                
                // Submit test
                fetch('/disc/submit-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(testData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            let errorMessage = `HTTP ${response.status}`;
                            try {
                                const errorData = JSON.parse(text);
                                errorMessage = errorData.message || errorMessage;
                            } catch (e) {
                                errorMessage = text || errorMessage;
                            }
                            throw new Error(errorMessage);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success response:', data);
                    if (data.success) {
                        // Clear localStorage after successful submit
                        localStorage.removeItem('disc_answers_' + SESSION_ID);
                        alert('Test DISC berhasil diselesaikan!');
                        window.location.href = data.redirect_url;
                    } else {
                        throw new Error(data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('Error submitting DISC test:', error);
                    document.getElementById('loadingOverlay').style.display = 'none';
                    
                    alert('Terjadi kesalahan: ' + error.message + '\n\nSilakan coba lagi atau hubungi administrator jika masalah berlanjut.');
                    
                    // Show retry button
                    document.getElementById('finishBtn').innerHTML = '🔄 Coba Kirim Lagi';
                });
            }
        }
        
        // Prevent data loss
        window.addEventListener('beforeunload', function(e) {
            if (allAnswers.length > 0) {
                e.preventDefault();
                e.returnValue = 'Test sedang berlangsung. Data Anda akan hilang jika meninggalkan halaman.';
            }
        });
        
        // Recovery from localStorage (if page refresh)
        window.addEventListener('load', function() {
            const savedAnswers = localStorage.getItem('disc_answers_' + SESSION_ID);
            if (savedAnswers) {
                try {
                    allAnswers = JSON.parse(savedAnswers);
                    answeredCount = allAnswers.length;
                    
                    // Restore visual state
                    allAnswers.forEach(answer => {
                        // Find the question element and restore selection
                        document.querySelectorAll('.rating-option').forEach(option => {
                            if (parseInt(option.dataset.question) === answer.question_id && 
                                parseInt(option.dataset.value) === answer.response) {
                                option.classList.add('selected');
                                option.closest('.question-card').classList.add('answered');
                            }
                        });
                    });
                    
                    updateStats();
                    updateNavigation();
                    updateProgress();
                    
                    console.log('Recovered', allAnswers.length, 'answers from localStorage');
                } catch (e) {
                    console.error('Error recovering saved answers:', e);
                    localStorage.removeItem('disc_answers_' + SESSION_ID);
                }
            }
        });
    </script>
</body>
</html>