<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DISC 3D - Instruksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .instruction-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            margin-bottom: 32px;
        }
        
        .disc-dimension {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 12px;
            margin: 8px;
            font-weight: 600;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .disc-dimension:hover {
            transform: translateY(-2px);
        }
        
        .disc-d { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .disc-i { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .disc-s { background: linear-gradient(135deg, #10b981, #059669); }
        .disc-c { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        
        .example-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #0284c7;
            border-radius: 12px;
            padding: 24px;
            margin: 16px 0;
        }
        
        .example-choices {
            display: grid;
            gap: 12px;
            margin: 16px 0;
        }
        
        .example-choice {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .example-choice:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        
        .example-choice.most-selected {
            border-color: #dc2626;
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .example-choice.least-selected {
            border-color: #2563eb;
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .choice-dimension {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            font-weight: bold;
            color: white;
            margin-right: 12px;
            font-size: 12px;
        }
        
        .dimension-d { background: #ef4444; }
        .dimension-i { background: #f59e0b; }
        .dimension-s { background: #10b981; }
        .dimension-c { background: #3b82f6; }
        
        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 24px 0;
        }
        
        .step-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            margin: 0 auto 16px;
        }
        
        .tips-grid {
            display: grid;
            md:grid-cols-2;
            gap: 20px;
            margin: 24px 0;
        }
        
        .tip-card {
            border-radius: 12px;
            padding: 24px;
            border: 1px solid transparent;
        }
        
        .tip-card.do {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border-color: #10b981;
        }
        
        .tip-card.dont {
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            border-color: #ef4444;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 18px 36px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.4);
        }
        
        .test-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        
        .info-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .info-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .info-value {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .info-label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin: 16px 0;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #ecfdf5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-warning {
            background: #fefbf2;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        @media (max-width: 768px) {
            .instruction-card {
                padding: 24px;
            }
            
            .hero-section {
                padding: 24px;
            }
            
            .steps-container {
                grid-template-columns: 1fr;
            }
            
            .tips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="max-w-5xl mx-auto py-8 px-4">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="text-4xl font-bold mb-4">Test DISC 3D</h1>
            <p class="text-xl mb-2">Analisis Kepribadian Profesional 3 Dimensi</p>
            <p class="text-lg opacity-90">PT Kayu Mebel Indonesia</p>
            <div class="mt-6 text-lg">
                <p>Kandidat: <strong>{{ $candidate->candidate_code }}</strong></p>
            </div>
        </div>

        <!-- Status Alert -->
        @if(isset($incompleteSession))
            <div class="alert alert-warning">
                <strong>⚠️ Test Ditemukan!</strong> Anda memiliki test DISC 3D yang belum selesai. Test akan dilanjutkan dari section terakhir yang dikerjakan.
            </div>
        @else
            <div class="alert alert-success">
                <strong>✅ Test Kraeplin Selesai</strong> - Anda dapat melanjutkan dengan Test DISC 3D.
            </div>
        @endif

        <!-- What is DISC 3D -->
        <div class="instruction-card">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Apa itu Test DISC 3D?</h2>
            
            <p class="text-lg text-gray-600 leading-relaxed mb-6">
                Test DISC 3D adalah penilaian kepribadian advanced yang menganalisis perilaku Anda dalam <strong>tiga dimensi</strong>: 
                Public Self (bagaimana Anda berperilaku di depan umum), Private Self (kepribadian alami Anda), 
                dan Adaptation Level (tekanan yang Anda alami saat beradaptasi).
            </p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="disc-dimension disc-d">
                    <span>D - Dominance</span>
                </div>
                <div class="disc-dimension disc-i">
                    <span>I - Influence</span>
                </div>
                <div class="disc-dimension disc-s">
                    <span>S - Steadiness</span>
                </div>
                <div class="disc-dimension disc-c">
                    <span>C - Compliance</span>
                </div>
            </div>

            <div class="test-info-grid">
                <div class="info-card">
                    <div class="info-icon">📊</div>
                    <div class="info-value">{{ $totalSections }}</div>
                    <div class="info-label">Total Sections</div>
                </div>
                <div class="info-card">
                    <div class="info-icon">🔄</div>
                    <div class="info-value">Auto</div>
                    <div class="info-label">Save</div>
                </div>
                <div class="info-card">
                    <div class="info-icon">🎯</div>
                    <div class="info-value">3D</div>
                    <div class="info-label">Analisis Dimensi</div>
                </div>
                <div class="info-card">
                    <div class="info-icon">📈</div>
                    <div class="info-value">Most/Least</div>
                    <div class="info-label">Format</div>
                </div>
            </div>
        </div>

        <!-- How it Works -->
        <div class="instruction-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Cara Kerja Test DISC 3D</h2>
            
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="text-lg font-semibold mb-2">Baca 4 Pernyataan</h3>
                    <p class="text-sm text-gray-600">Setiap section berisi 4 pernyataan yang mewakili dimensi D, I, S, dan C</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="text-lg font-semibold mb-2">Pilih MOST</h3>
                    <p class="text-sm text-gray-600">Pilih pernyataan yang <strong>PALING</strong> menggambarkan Anda</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="text-lg font-semibold mb-2">Pilih LEAST</h3>
                    <p class="text-sm text-gray-600">Pilih pernyataan yang <strong>PALING TIDAK</strong> menggambarkan Anda</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3 class="text-lg font-semibold mb-2">Lanjut Section</h3>
                    <p class="text-sm text-gray-600">Ulangi proses untuk 24 section total</p>
                </div>
            </div>
        </div>

        <!-- Example Section -->
        <div class="instruction-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Contoh Pengerjaan</h2>
            
            <div class="example-section">
                <h3 class="text-lg font-semibold text-blue-800 mb-4">Section Contoh: Gaya Kerja</h3>
                <p class="text-sm text-blue-700 mb-4">Pilih <span class="font-bold text-red-600">MOST</span> (paling menggambarkan) dan <span class="font-bold text-blue-600">LEAST</span> (paling tidak menggambarkan):</p>
                
                <div class="example-choices">
                    <div class="example-choice" onclick="selectExample(this, 'D')">
                        <div class="flex items-center">
                            <div class="choice-dimension dimension-d">D</div>
                            <div class="flex-1">Saya suka mengambil kendali dan memimpin proyek dengan tegas</div>
                        </div>
                    </div>
                    
                    <div class="example-choice" onclick="selectExample(this, 'I')">
                        <div class="flex items-center">
                            <div class="choice-dimension dimension-i">I</div>
                            <div class="flex-1">Saya senang berkomunikasi dan memotivasi tim</div>
                        </div>
                    </div>
                    
                    <div class="example-choice" onclick="selectExample(this, 'S')">
                        <div class="flex items-center">
                            <div class="choice-dimension dimension-s">S</div>
                            <div class="flex-1">Saya lebih suka bekerja dengan stabil dan konsisten</div>
                        </div>
                    </div>
                    
                    <div class="example-choice" onclick="selectExample(this, 'C')">
                        <div class="flex items-center">
                            <div class="choice-dimension dimension-c">C</div>
                            <div class="flex-1">Saya teliti dalam detail dan mengikuti prosedur</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-white rounded-lg border">
                    <div class="flex justify-between">
                        <div class="text-red-600 font-semibold">
                            MOST: <span id="mostSelected">Belum dipilih</span>
                        </div>
                        <div class="text-blue-600 font-semibold">
                            LEAST: <span id="leastSelected">Belum dipilih</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-sm text-blue-700">
                    <p><strong>Tips:</strong> Jika Anda cenderung suka memimpin, pilih D sebagai MOST. Jika Anda kurang suka detail, pilih C sebagai LEAST.</p>
                </div>
            </div>
        </div>

        <!-- Important Rules -->
        <div class="instruction-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Aturan Penting</h2>
            
            <div class="tips-grid">
                <div class="tip-card do">
                    <h3 class="text-lg font-semibold text-green-800 mb-4">✅ Yang Harus Dilakukan</h3>
                    <ul class="text-sm text-green-700 space-y-2">
                        <li>• Pilih berdasarkan perilaku <strong>alami</strong> Anda</li>
                        <li>• Jawab dengan <strong>spontan</strong> dan jujur</li>
                        <li>• Setiap section harus ada 1 MOST dan 1 LEAST</li>
                        <li>• MOST dan LEAST harus <strong>berbeda</strong></li>
                        <li>• Pikirkan situasi kerja pada umumnya</li>
                        <li>• Selesaikan semua 24 sections</li>
                    </ul>
                </div>
                
                <div class="tip-card dont">
                    <h3 class="text-lg font-semibold text-red-800 mb-4">❌ Yang Harus Dihindari</h3>
                    <ul class="text-sm text-red-700 space-y-2">
                        <li>• Jangan pilih berdasarkan ekspektasi perusahaan</li>
                        <li>• Jangan overthinking atau analisis berlebihan</li>
                        <li>• Jangan pilih MOST dan LEAST yang sama</li>
                        <li>• Jangan ubah jawaban tanpa alasan kuat</li>
                        <li>• Jangan refresh atau tutup halaman</li>
                        <li>• Jangan terburu-buru atau terlalu lama</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 3D Analysis -->
        <div class="instruction-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Analisis 3 Dimensi</h2>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center p-6 bg-gradient-to-b from-red-50 to-red-100 rounded-12">
                    <div class="text-4xl mb-4">🎭</div>
                    <h3 class="text-lg font-bold text-red-800 mb-2">MOST Graph</h3>
                    <p class="text-sm text-red-700">Public Self - Bagaimana Anda berperilaku di depan orang lain</p>
                </div>
                
                <div class="text-center p-6 bg-gradient-to-b from-blue-50 to-blue-100 rounded-12">
                    <div class="text-4xl mb-4">💎</div>
                    <h3 class="text-lg font-bold text-blue-800 mb-2">LEAST Graph</h3>
                    <p class="text-sm text-blue-700">Private Self - Kepribadian alami dan natural Anda</p>
                </div>
                
                <div class="text-center p-6 bg-gradient-to-b from-purple-50 to-purple-100 rounded-12">
                    <div class="text-4xl mb-4">🔄</div>
                    <h3 class="text-lg font-bold text-purple-800 mb-2">CHANGE Graph</h3>
                    <p class="text-sm text-purple-700">Adaptation - Tekanan yang dialami saat beradaptasi</p>
                </div>
            </div>
        </div>

        <!-- Technical Requirements -->
        <div class="instruction-card">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Persyaratan Teknis</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Perangkat & Koneksi</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li>• Koneksi internet stabil</li>
                        <li>• Browser modern (Chrome, Firefox, Edge)</li>
                        <li>• Laptop/Desktop direkomendasikan</li>
                        <li>• Layar minimal 12 inch untuk kenyamanan</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Lingkungan Test</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li>• Tempat tenang tanpa gangguan</li>
                        <li>• Kondisi pikiran fresh dan fokus</li>
                        <li>• Jangan multitasking selama test</li>
                        <li>• Kerjakan dengan santai tanpa terburu-buru</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ready to Start -->
        <div class="instruction-card text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Siap Memulai Test DISC 3D?</h2>
            <p class="text-lg text-gray-600 mb-6">
                Test terdiri dari <strong>{{ $totalSections }} sections</strong> dengan format Most/Least selection.
                <br>Kerjakan dengan santai dan fokus, tanpa batasan waktu.
            </p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800 font-semibold">⚠️ Perhatian Penting</p>
                <p class="text-sm text-yellow-700 mt-1">
                    Test tidak dapat dihentikan atau diulang setelah dimulai. 
                    Pastikan Anda sudah memahami instruksi dan siap mengerjakan.
                </p>
            </div>
            
            <form action="{{ route('disc3d.start', $candidate->candidate_code) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">
                    🎯 Mulai Test DISC 3D
                </button>
            </form>
            
            <p class="text-xs text-gray-500 mt-4">
                Dengan memulai test, Anda menyetujui bahwa test akan berjalan sesuai ketentuan yang diberikan.
            </p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md">
            <div class="flex items-center">
                <span class="mr-2">❌</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="fixed top-4 right-4 bg-yellow-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md">
            <div class="flex items-center">
                <span class="mr-2">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md">
            <div class="flex items-center">
                <span class="mr-2">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <script>
        // Example interaction functionality
        let exampleMost = null;
        let exampleLeast = null;
        
        function selectExample(element, dimension) {
            const choices = document.querySelectorAll('.example-choice');
            
            // If clicking on already selected MOST
            if (element.classList.contains('most-selected')) {
                element.classList.remove('most-selected');
                exampleMost = null;
                updateExampleDisplay();
                return;
            }
            
            // If clicking on already selected LEAST
            if (element.classList.contains('least-selected')) {
                element.classList.remove('least-selected');
                exampleLeast = null;
                updateExampleDisplay();
                return;
            }
            
            // If no MOST selected yet
            if (!exampleMost) {
                // Clear any existing selections
                choices.forEach(choice => {
                    choice.classList.remove('most-selected', 'least-selected');
                });
                
                element.classList.add('most-selected');
                exampleMost = {
                    element: element,
                    dimension: dimension,
                    text: element.querySelector('.flex-1').textContent
                };
            }
            // If MOST exists but no LEAST
            else if (!exampleLeast) {
                // Can't select same element for both
                if (element === exampleMost.element) {
                    alert('MOST dan LEAST tidak boleh sama! Pilih pernyataan yang berbeda.');
                    return;
                }
                
                element.classList.add('least-selected');
                exampleLeast = {
                    element: element,
                    dimension: dimension,
                    text: element.querySelector('.flex-1').textContent
                };
            }
            // Both exist - replace MOST
            else {
                // Clear existing selections
                choices.forEach(choice => {
                    choice.classList.remove('most-selected', 'least-selected');
                });
                
                element.classList.add('most-selected');
                exampleMost = {
                    element: element,
                    dimension: dimension,
                    text: element.querySelector('.flex-1').textContent
                };
                
                // Restore LEAST if it's different
                if (exampleLeast.element !== element) {
                    exampleLeast.element.classList.add('least-selected');
                } else {
                    exampleLeast = null;
                }
            }
            
            updateExampleDisplay();
        }
        
        function updateExampleDisplay() {
            const mostDisplay = document.getElementById('mostSelected');
            const leastDisplay = document.getElementById('leastSelected');
            
            if (exampleMost) {
                mostDisplay.textContent = `${exampleMost.dimension} - ${exampleMost.text.substring(0, 30)}...`;
                mostDisplay.parentElement.classList.add('text-red-600');
            } else {
                mostDisplay.textContent = 'Belum dipilih';
                mostDisplay.parentElement.classList.remove('text-red-600');
            }
            
            if (exampleLeast) {
                leastDisplay.textContent = `${exampleLeast.dimension} - ${exampleLeast.text.substring(0, 30)}...`;
                leastDisplay.parentElement.classList.add('text-blue-600');
            } else {
                leastDisplay.textContent = 'Belum dipilih';
                leastDisplay.parentElement.classList.remove('text-blue-600');
            }
        }
        
        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.fixed.top-4.right-4');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    alert.style.transition = 'all 0.3s ease';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
            
            // Add smooth scroll for any internal links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
        
        // Add keyboard shortcuts for better UX
        document.addEventListener('keydown', function(e) {
            // Press Enter or Space to start test (when form button is focused)
            if ((e.key === 'Enter' || e.key === ' ') && document.activeElement.type === 'submit') {
                e.preventDefault();
                document.activeElement.click();
            }
            
            // Example interaction shortcuts (1-4 for dimensions)
            if (e.key >= '1' && e.key <= '4') {
                const choiceIndex = parseInt(e.key) - 1;
                const choices = document.querySelectorAll('.example-choice');
                if (choices[choiceIndex]) {
                    const dimension = ['D', 'I', 'S', 'C'][choiceIndex];
                    selectExample(choices[choiceIndex], dimension);
                }
            }
        });
        
        // Add progress indicator animation
        function animateProgressIndicators() {
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.transform = 'translateY(-10px)';
                    card.style.transition = 'transform 0.3s ease';
                    setTimeout(() => {
                        card.style.transform = 'translateY(0)';
                    }, 200);
                }, index * 100);
            });
        }
        
        // Trigger animation on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateProgressIndicators();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        const testInfoGrid = document.querySelector('.test-info-grid');
        if (testInfoGrid) {
            observer.observe(testInfoGrid);
        }
        
        // Add tooltip functionality for dimension badges
        const discDimensions = document.querySelectorAll('.disc-dimension');
        discDimensions.forEach(dimension => {
            dimension.addEventListener('mouseenter', function() {
                const tooltips = {
                    'D - Dominance': 'Fokus pada hasil, tegas, suka tantangan',
                    'I - Influence': 'Komunikatif, antusias, suka berinteraksi',
                    'S - Steadiness': 'Stabil, sabar, konsisten, loyal',
                    'C - Compliance': 'Teliti, analitis, mengikuti prosedur'
                };
                
                const text = this.textContent.trim();
                const tooltip = tooltips[text];
                
                if (tooltip) {
                    // Create tooltip element
                    const tooltipEl = document.createElement('div');
                    tooltipEl.className = 'absolute bg-gray-800 text-white text-xs px-2 py-1 rounded shadow-lg z-50 whitespace-nowrap';
                    tooltipEl.textContent = tooltip;
                    tooltipEl.style.bottom = '100%';
                    tooltipEl.style.left = '50%';
                    tooltipEl.style.transform = 'translateX(-50%)';
                    tooltipEl.style.marginBottom = '5px';
                    
                    this.style.position = 'relative';
                    this.appendChild(tooltipEl);
                }
            });
            
            dimension.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.absolute');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    </script>
    <!-- ==========================================
         ENHANCED JAVASCRIPT DEBUGGING - TAMBAHKAN KE instructions.blade.php
         ========================================== -->
    <script>
    // Enhanced form debugging
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 DISC 3D Instructions page loaded');
        
        // Find the start test form
        const startForm = document.querySelector('form[action*="start"]');
        if (startForm) {
            console.log('✅ Start form found:', startForm);
            console.log('📍 Form action:', startForm.action);
            console.log('📍 Form method:', startForm.method);
            
            // Add form submission monitoring
            startForm.addEventListener('submit', function(e) {
                console.log('🚀 Form submission started...');
                console.log('📋 Form data:', new FormData(this));
                console.log('🔑 CSRF token:', this.querySelector('[name="_token"]')?.value);
                
                // Don't prevent default - let it submit
                // Just log what's happening
                
                // Show loading indicator
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Memulai Test...';
                    console.log('🔄 Submit button disabled');
                }
            });
            
            // Add click handler to submit button specifically
            const submitBtn = startForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    console.log('🖱️ Submit button clicked');
                    console.log('📍 Button element:', this);
                    console.log('📍 Form valid:', startForm.checkValidity());
                });
            }
        } else {
            console.warn('❌ Start form not found on page');
            // Try to find any forms
            const allForms = document.querySelectorAll('form');
            console.log('📋 All forms on page:', allForms);
        }
        
        // Monitor for any errors
        window.addEventListener('error', function(e) {
            console.error('❌ JavaScript error:', e.error);
        });
        
        // Monitor for network requests
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            console.log('🌐 Fetch request:', args);
            return originalFetch.apply(this, arguments)
                .then(response => {
                    console.log('📨 Fetch response:', response.status, response.url);
                    return response;
                })
                .catch(error => {
                    console.error('❌ Fetch error:', error);
                    throw error;
                });
        };
    });

    // Monitor page navigation
    window.addEventListener('beforeunload', function(e) {
        console.log('🔄 Page about to navigate away');
    });

    window.addEventListener('unload', function(e) {
        console.log('👋 Page unloading');
    });

    // Check if we're coming from a failed submission
    if (window.location.search.includes('error') || document.querySelector('.alert-danger')) {
        console.warn('⚠️ Page loaded with error state');
    }
    </script>
</body>
</html>