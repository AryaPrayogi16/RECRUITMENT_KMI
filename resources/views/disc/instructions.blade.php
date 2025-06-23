<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DISC - Instruksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .instruction-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .example-card {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 8px;
            padding: 16px;
            margin: 12px 0;
        }
        .disc-type {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            margin: 4px;
            font-weight: 600;
            color: white;
        }
        .disc-d { background: #ef4444; }
        .disc-i { background: #f59e0b; }
        .disc-s { background: #10b981; }
        .disc-c { background: #3b82f6; }
        
        .rating-scale {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin: 12px 0;
        }
        .rating-item {
            text-align: center;
            flex: 1;
            padding: 8px 4px;
        }
        .rating-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
            font-weight: bold;
            color: white;
        }
        .rating-1 { background: #dc2626; }
        .rating-2 { background: #ea580c; }
        .rating-3 { background: #d97706; }
        .rating-4 { background: #16a34a; }
        .rating-5 { background: #059669; }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .test-option {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin: 8px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .test-option:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }
        .test-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Test DISC</h1>
            <p class="text-lg text-gray-600">PT Kayu Mebel Indonesia</p>
            <p class="text-sm text-gray-500 mt-2">Kandidat: <strong>{{ $candidate->candidate_code }}</strong></p>
            <div class="mt-4 text-sm text-green-600">
                ✅ Test Kraeplin telah selesai. Lanjutkan dengan Test DISC.
            </div>
        </div>

        <!-- Instructions -->
        <div class="instruction-card">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Petunjuk Test DISC</h2>
            
            <div class="space-y-6">
                <!-- What is DISC Test -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Apa itu Test DISC?</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Test DISC adalah penilaian kepribadian yang mengukur 4 dimensi perilaku utama dalam lingkungan kerja. 
                        Test ini membantu memahami gaya komunikasi, motivasi, dan preferensi kerja Anda.
                    </p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="disc-type disc-d">
                            <span>D - Dominance</span>
                        </div>
                        <div class="disc-type disc-i">
                            <span>I - Influence</span>
                        </div>
                        <div class="disc-type disc-s">
                            <span>S - Steadiness</span>
                        </div>
                        <div class="disc-type disc-c">
                            <span>C - Compliance</span>
                        </div>
                    </div>
                </div>

                <!-- How to answer -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Cara Menjawab</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-600">
                        <li>Baca setiap pernyataan dengan seksama</li>
                        <li>Pilih seberapa setuju Anda dengan pernyataan tersebut</li>
                        <li>Jawab berdasarkan cara Anda <strong>biasanya berperilaku</strong> di tempat kerja</li>
                        <li>Tidak ada jawaban yang benar atau salah</li>
                        <li>Jawab dengan jujur dan spontan</li>
                        <li>Hindari jawaban "netral" kecuali benar-benar diperlukan</li>
                    </ol>
                </div>

                <!-- Rating Scale -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Skala Penilaian</h3>
                    <div class="rating-scale">
                        <div class="rating-item">
                            <div class="rating-number rating-1">1</div>
                            <div class="text-xs font-medium">Sangat Tidak Setuju</div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-number rating-2">2</div>
                            <div class="text-xs font-medium">Tidak Setuju</div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-number rating-3">3</div>
                            <div class="text-xs font-medium">Netral</div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-number rating-4">4</div>
                            <div class="text-xs font-medium">Setuju</div>
                        </div>
                        <div class="rating-item">
                            <div class="rating-number rating-5">5</div>
                            <div class="text-xs font-medium">Sangat Setuju</div>
                        </div>
                    </div>
                </div>

                <!-- Example -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Contoh Pernyataan</h3>
                    <div class="example-card">
                        <h4 class="font-semibold text-blue-800 mb-2">Pernyataan: "Saya suka mengambil inisiatif dalam memimpin proyek."</h4>
                        <p class="text-sm text-blue-700 mb-3">Jika Anda biasanya suka memimpin dan mengambil tanggung jawab, pilih <strong>"Setuju" (4)</strong> atau <strong>"Sangat Setuju" (5)</strong></p>
                        <p class="text-sm text-blue-700">Jika Anda lebih suka mengikuti arahan orang lain, pilih <strong>"Tidak Setuju" (2)</strong> atau <strong>"Sangat Tidak Setuju" (1)</strong></p>
                    </div>
                </div>

                <!-- Test Options -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Pilih Jenis Test</h3>
                    <div id="testOptions">
                        <div class="test-option" data-type="core_16">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">Test DISC Singkat (Direkomendasikan)</h4>
                                    <p class="text-sm text-gray-600">16 pertanyaan inti • Waktu: ~5-8 menit</p>
                                    <p class="text-xs text-green-600 mt-1">✅ Akurat dan efisien untuk kebutuhan recruitment</p>
                                </div>
                                <div class="text-blue-600 font-bold">16 Soal</div>
                            </div>
                        </div>
                        
                        <div class="test-option" data-type="full_50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">Test DISC Lengkap</h4>
                                    <p class="text-sm text-gray-600">Semua pertanyaan • Waktu: ~15-20 menit</p>
                                    <p class="text-xs text-gray-600 mt-1">📊 Analisis lebih mendalam dan detail</p>
                                </div>
                                <div class="text-blue-600 font-bold">50+ Soal</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tips Mengerjakan</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-800 mb-2">✅ Yang Harus Dilakukan</h4>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• Jawab berdasarkan perilaku natural Anda</li>
                                <li>• Pikirkan situasi kerja pada umumnya</li>
                                <li>• Jawab dengan spontan dan jujur</li>
                                <li>• Fokus pada diri Anda, bukan ekspektasi orang lain</li>
                                <li>• Selesaikan semua pertanyaan</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">❌ Yang Harus Dihindari</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Jangan jawab berdasarkan harapan perusahaan</li>
                                <li>• Jangan berpikir terlalu lama</li>
                                <li>• Jangan ubah jawaban kecuali yakin salah</li>
                                <li>• Jangan pilih jawaban yang "terdengar baik"</li>
                                <li>• Jangan refresh halaman saat test</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Technical Requirements -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Persyaratan Teknis</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Pastikan koneksi internet stabil</li>
                            <li>• Gunakan laptop/desktop untuk pengalaman terbaik</li>
                            <li>• Jangan menutup atau refresh halaman</li>
                            <li>• Siapkan tempat yang tenang dan nyaman</li>
                            <li>• Test akan otomatis tersimpan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ready to Start -->
        <div class="instruction-card text-center">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Siap Memulai Test DISC?</h3>
            <p class="text-gray-600 mb-6">
                Pastikan Anda sudah memahami instruksi dan memilih jenis test yang diinginkan.
                <br><strong>Test tidak dapat dihentikan atau diulang setelah dimulai.</strong>
            </p>
            
            <form id="startTestForm" action="{{ route('disc.start', $candidate->candidate_code) }}" method="POST">
                @csrf
                <input type="hidden" name="test_type" id="selectedTestType" value="core_16">
                
                <button type="submit" class="btn-primary" id="startBtn">
                    🎯 Mulai Test DISC
                </button>
            </form>
            
            <p class="text-xs text-gray-500 mt-4">
                Dengan memulai test, Anda menyetujui bahwa test akan berjalan sesuai ketentuan yang diberikan.
            </p>
        </div>
    </div>

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="fixed top-4 right-4 bg-yellow-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('warning') }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testOptions = document.querySelectorAll('.test-option');
            const selectedTestTypeInput = document.getElementById('selectedTestType');
            const startBtn = document.getElementById('startBtn');
            
            // Set default selection
            testOptions[0].classList.add('selected');
            
            // Handle test option selection
            testOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    testOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Update hidden input value
                    const testType = this.dataset.type;
                    selectedTestTypeInput.value = testType;
                    
                    // Update button text
                    const questionCount = testType === 'core_16' ? '16' : '50+';
                    startBtn.innerHTML = `🎯 Mulai Test DISC (${questionCount} Soal)`;
                });
            });
            
            // Auto-hide flash messages
            setTimeout(() => {
                const alerts = document.querySelectorAll('.fixed.top-4.right-4');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(100%)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        });
    </script>
</body>
</html>