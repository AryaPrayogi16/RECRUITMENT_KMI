<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamaran Berhasil Dikirim - PT Kayu Mebel Indonesia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .success-animation {
            animation: checkmark 0.6s ease-in-out;
        }
        
        @keyframes checkmark {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen">
    <div class="max-w-2xl mx-auto py-16 px-4">
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="success-animation inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4 fade-in">
                Lamaran Berhasil Dikirim! 🎉
            </h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in" style="animation-delay: 0.2s">
                <div class="text-center">
                    <p class="text-lg text-gray-700 mb-4">
                        Terima kasih telah melamar di <strong>PT Kayu Mebel Indonesia</strong>
                    </p>
                    
                    @if($candidateCode)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600 mb-2">Kode Kandidat Anda:</p>
                            <p class="text-2xl font-bold text-green-700 tracking-wider">
                                {{ $candidateCode }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Simpan kode ini untuk referensi Anda
                            </p>
                        </div>
                    @endif
                    
                    <p class="text-gray-600 leading-relaxed">
                        Lamaran Anda telah berhasil diterima dan akan diproses oleh tim HR kami. 
                        Kami akan menghubungi Anda dalam 3-5 hari kerja untuk tahap selanjutnya.
                    </p>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in" style="animation-delay: 0.4s">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Langkah Selanjutnya</h2>
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-semibold text-sm">1</span>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Verifikasi Data</h3>
                        <p class="text-gray-600 text-sm">Tim HR akan meninjau dan memverifikasi data aplikasi Anda</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-semibold text-sm">2</span>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Penjadwalan Interview</h3>
                        <p class="text-gray-600 text-sm">Jika lolos seleksi awal, kami akan menghubungi Anda untuk wawancara</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-semibold text-sm">3</span>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Hasil Final</h3>
                        <p class="text-gray-600 text-sm">Pemberitahuan hasil akhir akan disampaikan melalui email/telepon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in" style="animation-delay: 0.6s">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Kontak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Email</h3>
                    <p class="text-blue-600">hr4@pawindo.com</p>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Telepon</h3>
                    <p class="text-gray-700">+62 857-4544-9692</p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami dengan menyertakan kode kandidat Anda.
                </p>
            </div>
        </div>

        <!-- Tips -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 fade-in" style="animation-delay: 0.8s">
            <h2 class="text-lg font-semibold text-blue-900 mb-3">💡 Tips Persiapan Interview</h2>
            <ul class="text-blue-800 text-sm space-y-2">
                <li>• Pelajari lebih lanjut tentang perusahaan dan budaya kerja kami</li>
                <li>• Siapkan contoh konkret dari pengalaman dan pencapaian Anda</li>
                <li>• Pastikan koneksi internet stabil jika interview dilakukan secara online</li>
                <li>• Siapkan pertanyaan tentang posisi dan perusahaan</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="text-center space-y-4 fade-in" style="animation-delay: 1s">
            <a href="{{ route('job.application.form') }}" 
               class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Lamar Posisi Lain
            </a>
            
            <div class="text-center">
                <a href="https://kmifilebox.com/recruitment" target="_blank" 
                   class="text-blue-600 hover:text-blue-800 text-sm underline">
                    Kunjungi Website Perusahaan
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 pt-8 border-t border-gray-200 fade-in" style="animation-delay: 1.2s">
            <p class="text-gray-500 text-sm">
                © {{ date('Y') }} PT Kayu Mebel Indonesia. Semua hak dilindungi.
            </p>
        </div>
    </div>

    <!-- Copy to clipboard functionality -->


    <script>
         // Clear localStorage after successful submission
        document.addEventListener('DOMContentLoaded', function() {
            // Clear the saved form data from localStorage
            if (localStorage.getItem('jobApplicationFormData')) {
                localStorage.removeItem('jobApplicationFormData');
                console.log('Form data cleared from localStorage');
            }
        });
        // Auto-select candidate code when clicked
        @if($candidateCode)
        document.addEventListener('DOMContentLoaded', function() {
            const candidateCode = document.querySelector('.text-2xl.font-bold.text-green-700');
            if (candidateCode) {
                candidateCode.addEventListener('click', function() {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText('{{ $candidateCode }}').then(function() {
                            // Show temporary feedback
                            const originalText = candidateCode.textContent;
                            candidateCode.textContent = 'Copied!';
                            candidateCode.classList.add('text-blue-600');
                            
                            setTimeout(function() {
                                candidateCode.textContent = originalText;
                                candidateCode.classList.remove('text-blue-600');
                            }, 1000);
                        });
                    }
                });
                
                // Add cursor pointer to indicate clickable
                candidateCode.style.cursor = 'pointer';
                candidateCode.title = 'Klik untuk copy';
            }
        });
        @endif
    </script>
</body>
</html>