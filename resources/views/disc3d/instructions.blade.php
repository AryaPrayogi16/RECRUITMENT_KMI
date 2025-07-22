<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes 2 - Instruksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .instruction-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .example-question {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 8px;
            padding: 16px;
            margin: 12px 0;
        }
        .choice-example {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 4px 0;
            background: #e0f2fe;
            border-radius: 6px;
            font-weight: 500;
        }
        .choice-selected {
            background: #10b981;
            color: white;
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tes 2</h1>
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
                        <li>Anda akan disajikan serangkaian pernyataan tentang perilaku dan sikap</li>
                        <li>Setiap kelompok berisi <strong>4 pilihan pernyataan</strong></li>
                        <li>Pilih <strong>1 pernyataan yang PALING</strong> menggambarkan diri Anda</li>
                        <li>Pilih <strong>1 pernyataan yang PALING TIDAK</strong> menggambarkan diri Anda</li>
                        <li>Tidak ada jawaban benar atau salah, jawablah dengan jujur</li>
                        <li>Total ada <strong>28 kelompok pernyataan</strong> yang harus dijawab</li>
                    </ol>
                </div>

                <!-- Example -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Contoh Pengerjaan</h3>
                    <div class="example-question">
                        <p class="text-sm text-gray-600 mb-4">Contoh kelompok pernyataan:</p>
                        
                        <div class="space-y-2">
                            <div class="choice-example">
                                A. Saya suka bekerja dengan orang lain
                            </div>
                            <div class="choice-example choice-selected">
                                B. Saya selalu menyelesaikan tugas tepat waktu ⭐ (PALING)
                            </div>
                            <div class="choice-example">
                                C. Saya suka mencoba hal-hal baru
                            </div>
                            <div class="choice-example" style="background: #fee2e2; color: #dc2626;">
                                D. Saya mudah tersinggung ❌ (PALING TIDAK)
                            </div>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-700">
                            <strong>Cara memilih:</strong>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Pilih <strong>satu yang PALING</strong> menggambarkan Anda (ditandai ⭐)</li>
                                <li>Pilih <strong>satu yang PALING TIDAK</strong> menggambarkan Anda (ditandai ❌)</li>
                                <li>Dua pilihan lainnya dibiarkan kosong</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Timer Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Waktu Tes</h3>
                    
                    <ul class="list-disc list-inside space-y-1 text-gray-600 mt-3">
                        <li>Anda dapat mengerjakan sesuai kecepatan Anda sendiri</li>
                        <li>Pikirkan dengan baik sebelum memilih jawaban</li>
                        <li>Pastikan setiap kelompok sudah dipilih 2 jawaban (PALING dan PALING TIDAK)</li>
                        <li>Periksa kembali jawaban sebelum menyelesaikan tes</li>
                    </ul>
                </div>

                <!-- Tips -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Tips Mengerjakan</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-800 mb-2">✅ Yang Harus Dilakukan</h4>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• Jawab dengan jujur sesuai diri Anda</li>
                                <li>• Baca setiap pernyataan dengan teliti</li>
                                <li>• Pilih berdasarkan kondisi kerja sehari-hari</li>
                                <li>• Pastikan setiap kelompok sudah lengkap</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">❌ Yang Harus Dihindari</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Jangan menjawab berdasarkan ekspektasi</li>
                                <li>• Jangan memilih jawaban yang sama terus</li>
                                <li>• Jangan terburu-buru dalam menjawab</li>
                                <li>• Jangan melewati soal yang belum lengkap</li>
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
                            <li>• Siapkan tempat yang tenang untuk berkonsentrasi</li>
                            <li>• Jawaban otomatis tersimpan saat Anda memilih</li>
                            <li>• Pastikan semua kelompok sudah dijawab sebelum submit</li>
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
            
            <form action="{{ route('disc3d.start', $candidate->candidate_code) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">
                    🚀 Mulai Tes 2
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