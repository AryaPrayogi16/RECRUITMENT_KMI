<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes 1 - Instruksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .instruction-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .example-calculation {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 8px;
            padding: 16px;
            margin: 12px 0;
        }
        .number-pair {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            margin: 0 8px;
            padding: 8px;
            background: #e0f2fe;
            border-radius: 6px;
            min-width: 50px;
        }
        .number {
            font-size: 24px;
            font-weight: bold;
            color: #0369a1;
        }
        .plus-sign {
            font-size: 16px;
            color: #64748b;
            margin: 2px 0;
        }
        .result {
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 4px;
        }
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
        .timer-demo {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin: 12px 0;
        }
        .timer-display {
            font-size: 32px;
            font-weight: bold;
            color: #d97706;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tes 1</h1>
            <p class="text-lg text-gray-600">PT Kayu Mebel Indonesia Group</p>
            <p class="text-sm text-gray-500 mt-2">Kandidat: <strong>{{ $candidate->candidate_code }}</strong></p>
        </div>

        <!-- Instructions -->
        <div class="instruction-card">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Petunjuk Pengerjaan</h2>
            
            <div class="space-y-6">
                <!-- How to do the test -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Cara Mengerjakan</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-600">
                        <li>Setiap kolom berisi angka-angka yang disusun secara vertikal</li>
                        <li>Tugas Anda adalah menjumlahkan 2 angka yang berurutan (atas + bawah)</li>
                        <li>Tulis hanya <strong>digit terakhir</strong> dari hasil penjumlahan</li>
                        <li>Setiap kolom diberi waktu <strong>15 detik</strong></li>
                        <li>Kerjakan sebanyak mungkin dalam waktu yang tersedia</li>
                        <li>Jika waktu habis, otomatis akan pindah ke kolom berikutnya</li>
                    </ol>
                </div>

                <!-- Example -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Contoh Pengerjaan</h3>
                    <div class="example-calculation">
                        <p class="text-sm text-gray-600 mb-4">Jika ada kolom dengan angka: 2, 7, 9, 4, 6</p>
                        
                        <div class="flex justify-center items-center flex-wrap gap-4">
                            <div class="number-pair">
                                <div class="number">2</div>
                                <div class="plus-sign">+</div>
                                <div class="number">7</div>
                                <div class="result">9</div>
                            </div>
                            
                            <div class="number-pair">
                                <div class="number">7</div>
                                <div class="plus-sign">+</div>
                                <div class="number">9</div>
                                <div class="result">6</div>
                            </div>
                            
                            <div class="number-pair">
                                <div class="number">9</div>
                                <div class="plus-sign">+</div>
                                <div class="number">4</div>
                                <div class="result">3</div>
                            </div>
                            
                            <div class="number-pair">
                                <div class="number">4</div>
                                <div class="plus-sign">+</div>
                                <div class="number">6</div>
                                <div class="result">0</div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-700">
                            <strong>Penjelasan:</strong>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>2 + 7 = 9 → tulis <strong>9</strong></li>
                                <li>7 + 9 = 16 → tulis <strong>6</strong> (digit terakhir)</li>
                                <li>9 + 4 = 13 → tulis <strong>3</strong> (digit terakhir)</li>
                                <li>4 + 6 = 10 → tulis <strong>0</strong> (digit terakhir)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Timer Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Waktu Tes</h3>
                    <div class="timer-demo">
                        <div class="timer-display">00:15</div>
                        <p class="text-sm text-amber-700 mt-2">Setiap kolom memiliki waktu 15 detik</p>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 mt-3">
                        <li>Total 32 kolom = maksimal 8 menit</li>
                        <li>Timer akan terlihat di bagian atas setiap kolom</li>
                        <li>Ketika waktu habis, otomatis pindah ke kolom berikutnya</li>
                        <li>Anda bisa pindah kolom lebih awal jika sudah selesai</li>
                    </ul>
                </div>

                <!-- Tips -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tips Mengerjakan</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-800 mb-2">✅ Yang Harus Dilakukan</h4>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• Kerjakan dengan cepat dan teliti</li>
                                <li>• Fokus pada kecepatan dan akurasi</li>
                                <li>• Jangan terlalu lama mikir satu soal</li>
                                <li>• Tetap tenang dan konsentrasi</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">❌ Yang Harus Dihindari</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Jangan panik jika tertinggal</li>
                                <li>• Jangan fokus pada kesempurnaan</li>
                                <li>• Jangan berhenti di tengah kolom</li>
                                <li>• Jangan melihat hasil sebelumnya</li>
                                <li>• Jangan refresh halaman</li>
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
                            <li>• Jangan menutup halaman ketika tes berlangsung</li>
                            <li>• Siapkan tempat yang tenang dan bebas gangguan</li>
                            <li>• Tes otomatis tersimpan ketika selesai</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ready to Start -->
        <div class="instruction-card text-center">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Apakah Anda Siap Memulai Tes?</h3>
            <p class="text-gray-600 mb-6">
                Pastikan Anda sudah memahami instruksi di atas. Tes akan dimulai segera setelah Anda menekan tombol mulai.
                <br><strong>Tes tidak dapat dihentikan atau diulang setelah dimulai.</strong>
            </p>
            
            <form action="{{ route('kraeplin.start', $candidate->candidate_code) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">
                    🚀 Mulai Tes 1
                </button>
            </form>
            
            <p class="text-xs text-gray-500 mt-4">
                Dengan memulai tes, Anda menyetujui bahwa tes akan berjalan sesuai ketentuan yang diberikan.
            </p>
        </div>
    </div>

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</body>
</html>