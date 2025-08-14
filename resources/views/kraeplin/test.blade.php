<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test 1 - {{ $candidate->candidate_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .test-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .column-container {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            width: 120px;
            margin: 0 auto;
        }
        
        .column-container.active {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .column-container.completed {
            border-color: #10b981;
            background: #ecfdf5;
        }
        
        .number-item {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            background: white;
            border: 1px solid #d1d5db;
            margin-bottom: 2px;
            border-radius: 4px;
        }
        
        .answer-input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            margin: 2px 0;
        }
        
        .answer-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .answer-input.correct {
            background: #dcfce7;
            border-color: #10b981;
        }
        
        .answer-input.incorrect {
            background: #fee2e2;
            border-color: #ef4444;
        }
        
        .timer-display {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            padding: 16px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .timer-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            animation: pulse 1s infinite;
        }
        
        .timer-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            animation: pulse 0.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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
        
        .column-selector {
            display: grid;
            grid-template-columns: repeat(16, 1fr);
            gap: 4px;
            margin-bottom: 20px;
        }
        
        .column-button {
            width: 32px;
            height: 32px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .column-button.current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .column-button.completed {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .column-button:disabled {
            cursor: not-allowed;
            opacity: 0.5;
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-4 px-4">
        <!-- Header -->
        <div class="test-container mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Test 1</h1>
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
            
            <!-- Column Selector -->
            <div class="column-selector" id="columnSelector">
                @for($i = 1; $i <= 32; $i++)
                    <button class="column-button" data-column="{{ $i }}" id="colBtn{{ $i }}">{{ $i }}</button>
                @endfor
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="space-y-4">
                    <button class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors" 
                            onclick="nextColumn()" id="nextBtn">
                        Kolom Berikutnya →
                    </button>
                    
                    <button class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors" 
                            onclick="finishTest()" id="finishBtn" style="display: none;">
                        Selesaikan Test
                    </button>
                </div>
            <!-- Left Panel - Stats -->
            <div class="lg:col-span-1" style="display: none;">
                <!-- Timer -->
                <div class="timer-display" id="timer">15</div>
                
                <!-- Stats -->
                <div class="stats-panel" >
                    <div class="grid grid-cols-2 gap-4">
                        <div class="stat-item">
                            <div class="stat-value" id="currentColumn">1</div>
                            <div class="stat-label">Kolom</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="answeredCount">0</div>
                            <div class="stat-label">Terjawab</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="correctCount">0</div>
                            <div class="stat-label">Benar</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="accuracyPercent">0%</div>
                            <div class="stat-label">Akurasi</div>
                        </div>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="space-y-4">
                    <button class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors" 
                            onclick="nextColumn()" id="nextBtn">
                        Kolom Berikutnya →
                    </button>
                    
                    <button class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors" 
                            onclick="finishTest()" id="finishBtn" style="display: none;">
                        Selesaikan Test
                    </button>
                </div>
            </div>

            <!-- Main Test Area -->
            <div class="lg:col-span-3">
                <div class="test-container">
                    <div id="testArea">
                        <!-- Test columns will be loaded here -->
                        @foreach($questions as $columnNumber => $pairs)
                            <div class="column-test" id="column{{ $columnNumber }}" style="display: none;">
                                <div class="text-center mb-4">
                                    <h3 class="text-lg font-semibold">Kolom {{ $columnNumber }}</h3>
                                    <p class="text-sm text-gray-600">Kerjakan sebanyak mungkin dalam 15 detik</p>
                                </div>
                                
                                <div class="column-container">
                                    <!-- Numbers -->
                                    @foreach($pairs as $index => $pair)
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="number-item" style="width: 35px;">{{ $pair['value1'] }}</div>
                                            <div class="text-lg font-bold">+</div>
                                            <div class="number-item" style="width: 35px;">{{ $pair['value2'] }}</div>
                                            <div class="text-lg font-bold">=</div>
                                            <input type="number" 
                                                   class="answer-input" 
                                                   min="0" max="9" 
                                                   data-column="{{ $columnNumber }}"
                                                   data-row="{{ $pair['row_number'] }}"
                                                   data-correct="{{ $pair['correct_answer'] }}"
                                                   id="input{{ $columnNumber }}_{{ $pair['row_number'] }}"
                                                   onchange="submitAnswer(this)"
                                                   onkeypress="handleKeyPress(event, this)">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white p-8 rounded-lg text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-lg font-semibold">Menyimpan hasil test...</p>
            <p class="text-sm text-gray-600 mt-2">Mohon tunggu, jangan tutup halaman</p>
        </div>
    </div>

    <script>
        // Test configuration
        const SESSION_ID = {{ $session->id }};
        const TOTAL_COLUMNS = 32;
        const TIME_PER_COLUMN = 15;
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Test state
        let currentColumn = 1;
        let timeLeft = TIME_PER_COLUMN;
        let timer = null;
        let answeredTotal = 0;
        let correctTotal = 0;
        let startTime = Date.now();
        let columnStartTime = Date.now();
        
        // Store all answers locally
        let allAnswers = [];
        
        console.log('Test initialized with SESSION_ID:', SESSION_ID);
        
        // Initialize test
        document.addEventListener('DOMContentLoaded', function() {
            showColumn(1);
            startTimer();
            updateStats();
        });
        
        function showColumn(columnNumber) {
            // Hide all columns
            document.querySelectorAll('.column-test').forEach(col => {
                col.style.display = 'none';
            });
            
            // Show current column
            const column = document.getElementById('column' + columnNumber);
            if (column) {
                column.style.display = 'block';
                currentColumn = columnNumber;
                columnStartTime = Date.now();
                
                // Update UI
                document.getElementById('currentColumn').textContent = columnNumber;
                updateColumnSelector();
                updateProgress();
                
                // Focus first input
                const firstInput = column.querySelector('.answer-input');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
                
                // Show/hide finish button
                if (columnNumber === TOTAL_COLUMNS) {
                    document.getElementById('finishBtn').style.display = 'block';
                } else {
                    document.getElementById('finishBtn').style.display = 'none';
                }
            }
        }
        
        function startTimer() {
            clearInterval(timer);
            timeLeft = TIME_PER_COLUMN;
            updateTimerDisplay();
            
            timer = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    autoAdvanceColumn();
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const display = document.getElementById('timer');
            display.textContent = timeLeft.toString().padStart(2, '0');
            
            // Change color based on time left
            display.className = 'timer-display';
            if (timeLeft <= 3) {
                display.classList.add('timer-danger');
            } else if (timeLeft <= 7) {
                display.classList.add('timer-warning');
            }
        }
        
        function updateColumnSelector() {
            document.querySelectorAll('.column-button').forEach((btn, index) => {
                const colNum = index + 1;
                btn.className = 'column-button';
                
                if (colNum === currentColumn) {
                    btn.classList.add('current');
                } else if (colNum < currentColumn) {
                    btn.classList.add('completed');
                }
                
                // Disable future columns
                btn.disabled = colNum > currentColumn;
            });
        }
        
        function updateProgress() {
            const progress = ((currentColumn - 1) / TOTAL_COLUMNS) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }
        
        function updateStats() {
            document.getElementById('answeredCount').textContent = answeredTotal;
            document.getElementById('correctCount').textContent = correctTotal;
            
            const accuracy = answeredTotal > 0 ? Math.round((correctTotal / answeredTotal) * 100) : 0;
            document.getElementById('accuracyPercent').textContent = accuracy + '%';
        }
        
        function submitAnswer(input) {
            const value = parseInt(input.value);
            if (isNaN(value) || value < 0 || value > 9) {
                input.value = '';
                return;
            }
            
            // Ambil data dari input dengan validasi
            const columnAttr = input.dataset.column;
            const rowAttr = input.dataset.row;
            const correctAttr = input.dataset.correct;
            
            if (!columnAttr || !rowAttr || !correctAttr) {
                console.error('Missing data attributes on input:', {
                    column: columnAttr,
                    row: rowAttr,
                    correct: correctAttr,
                    input: input
                });
                return;
            }
            
            const column = parseInt(columnAttr);
            const row = parseInt(rowAttr);
            const correct = parseInt(correctAttr);
            const timeSpent = Math.max(0, Math.min(15, TIME_PER_COLUMN - timeLeft));
            
            // Validasi data sebelum disimpan
            if (isNaN(column) || column < 1 || column > 32) {
                console.error('Invalid column:', column);
                return;
            }
            if (isNaN(row) || row < 1 || row > 26) {
                console.error('Invalid row:', row);
                return;
            }
            if (isNaN(correct) || correct < 0 || correct > 9) {
                console.error('Invalid correct answer:', correct);
                return;
            }
            
            // Visual feedback
            input.className = 'answer-input'; // Reset ke class default saja
            if (value === correct) {
                correctTotal++;
            }
            
            answeredTotal++;
            updateStats();
            
            // Check if this answer already exists (avoid duplicates)
            const existingIndex = allAnswers.findIndex(a => a.column === column && a.row === row);
            
            const answerData = {
                column: column,
                row: row,
                user_answer: value,
                time_spent: Math.round(timeSpent) // Pastikan integer
            };
            
            // Validasi final sebelum menyimpan
            if (answerData.column >= 1 && answerData.column <= 32 &&
                answerData.row >= 1 && answerData.row <= 26 &&
                answerData.user_answer >= 0 && answerData.user_answer <= 9 &&
                answerData.time_spent >= 0 && answerData.time_spent <= 15) {
                
                if (existingIndex >= 0) {
                    // Update existing answer
                    allAnswers[existingIndex] = answerData;
                } else {
                    // Add new answer
                    allAnswers.push(answerData);
                }
                
                // Auto-save to localStorage
                localStorage.setItem('kraeplin_answers_' + SESSION_ID, JSON.stringify(allAnswers));
                
                console.log('Answer saved:', answerData);
            } else {
                console.error('Invalid answer data:', answerData);
                return;
            }
            
            // Move to next input
            const nextInput = findNextInput(input);
            if (nextInput) {
                nextInput.focus();
            }
        }
        
        function findNextInput(currentInput) {
            const column = document.getElementById('column' + currentColumn);
            const inputs = column.querySelectorAll('.answer-input');
            
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i] === currentInput && i < inputs.length - 1) {
                    return inputs[i + 1];
                }
            }
            return null;
        }
        
        function handleKeyPress(event, input) {
            if (event.key === 'Enter') {
                const nextInput = findNextInput(input);
                if (nextInput) {
                    nextInput.focus();
                } else {
                    nextColumn();
                }
            }
        }
        
        function nextColumn() {
            if (currentColumn >= TOTAL_COLUMNS) {
                finishTest();
                return;
            }
            
            showColumn(currentColumn + 1);
            startTimer();
        }
        
        function autoAdvanceColumn() {
            if (currentColumn >= TOTAL_COLUMNS) {
                finishTest();
            } else {
                showColumn(currentColumn + 1);
                startTimer();
            }
        }
        
        function finishTest() {
            if (allAnswers.length === 0) {
                alert('Tidak ada jawaban yang tersimpan. Pastikan Anda telah menjawab minimal beberapa soal.');
                return;
            }
            
            // Validasi semua jawaban sebelum submit
            const validAnswers = allAnswers.filter(answer => {
                return answer.column >= 1 && answer.column <= 32 &&
                       answer.row >= 1 && answer.row <= 26 &&
                       answer.user_answer >= 0 && answer.user_answer <= 9 &&
                       answer.time_spent >= 0 && answer.time_spent <= 15;
            });
            
            if (validAnswers.length === 0) {
                alert('Tidak ada jawaban yang valid. Silakan jawab beberapa soal terlebih dahulu.');
                return;
            }
            
            if (validAnswers.length !== allAnswers.length) {
                console.warn('Some answers were filtered out:', {
                    total: allAnswers.length,
                    valid: validAnswers.length,
                    invalid: allAnswers.filter(a => !validAnswers.includes(a))
                });
            }
            
            // LANGSUNG SUBMIT TANPA KONFIRMASI
            clearInterval(timer);
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Calculate total duration
            const totalDuration = Math.max(1, Math.round((Date.now() - startTime) / 1000));
            
            // Prepare bulk data dengan data yang sudah divalidasi
            const testData = {
                session_id: SESSION_ID,
                answers: validAnswers, // Gunakan validAnswers
                total_duration: totalDuration
            };
            
            console.log('Submitting test data:', {
                session_id: SESSION_ID,
                answers_count: validAnswers.length,
                total_duration: totalDuration,
                sample_answers: validAnswers.slice(0, 3)
            });
            
            // Submit test
            fetch('/kraeplin/submit-test', {
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
                    localStorage.removeItem('kraeplin_answers_' + SESSION_ID);
                   
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error submitting test:', error);
                document.getElementById('loadingOverlay').style.display = 'none';
                
                // Show detailed error to user
                alert('Terjadi kesalahan: ' + error.message + '\n\nSilakan coba lagi atau hubungi administrator jika masalah berlanjut.');
                
                // Show retry button
                document.getElementById('finishBtn').innerHTML = '🔄 Coba Kirim Lagi';
                document.getElementById('finishBtn').style.display = 'block';
            });
        }
        
        // Recovery from localStorage (if page refresh)
        window.addEventListener('load', function() {
            const savedAnswers = localStorage.getItem('kraeplin_answers_' + SESSION_ID);
            if (savedAnswers) {
                try {
                    allAnswers = JSON.parse(savedAnswers);
                    
                    // Restore visual state and stats
                    let totalAnswered = 0;
                    let totalCorrect = 0;
                    
                    allAnswers.forEach(answer => {
                        const input = document.getElementById(`input${answer.column}_${answer.row}`);
                        if (input) {
                            input.value = answer.user_answer;
                            const correct = parseInt(input.dataset.correct);
                            
                            if (answer.user_answer === correct) {
                                input.classList.add('correct');
                                totalCorrect++;
                            } else {
                                input.classList.add('incorrect');
                            }
                            totalAnswered++;
                        }
                    });
                    
                    answeredTotal = totalAnswered;
                    correctTotal = totalCorrect;
                    updateStats();
                    
                    console.log('Recovered', allAnswers.length, 'answers from localStorage');
                } catch (e) {
                    console.error('Error recovering saved answers:', e);
                    localStorage.removeItem('kraeplin_answers_' + SESSION_ID);
                }
            }
        });
    </script>
</body>
</html>