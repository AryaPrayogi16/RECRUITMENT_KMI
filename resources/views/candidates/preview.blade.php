<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Preview CV - {{ $candidate->full_name ?? 'Kandidat' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1a202c;
        }

        .preview-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #4a5568;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #f7fafc;
            color: #2d3748;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
        }

        .candidate-name {
            font-size: 1rem;
            color: #6b7280;
            font-weight: normal;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4);
        }

        .preview-content {
            flex: 1;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .pdf-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: calc(100vh - 180px);
            min-height: 600px;
        }

        .pdf-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        object[type="application/pdf"] {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        embed {
            width: 100%;
            height: 100%;
        }

        .loading-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: white;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: white;
            color: #6b7280;
        }

        .error-container i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #e2e8f0;
        }

        .error-container p {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .preview-header {
                padding: 15px 20px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .header-left {
                width: 100%;
                justify-content: space-between;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .candidate-name {
                display: none;
            }

            .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .pdf-container {
                height: calc(100vh - 250px);
            }
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <!-- Header -->
        <header class="preview-header">
            <div class="header-content">
                <div class="header-left">
                    <a href="{{ route('candidates.show', $candidate->id) }}" class="back-btn" title="Kembali ke Detail Kandidat">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="page-title">
                            Preview CV
                            <span class="candidate-name">- {{ $candidate->personalData->full_name ?? 'Kandidat' }}</span>
                        </h1>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                    
                    <a href="{{ route('candidates.show', $candidate->id) }}" class="btn btn-primary">
                        <i class="fas fa-user"></i>
                        Lihat Detail
                    </a>
                </div>
            </div>
        </header>

        <!-- HTML Preview -->
        <main class="preview-content">
            <div class="pdf-container" id="pdfContainer">
                <iframe 
                    id="htmlFrame"
                    src="{{ route('candidates.preview.html', $candidate->id) }}"
                    class="pdf-iframe">
                </iframe>
            </div>
        </main>
    </div>

    <script>
        // Show PDF after loading
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingContainer').style.display = 'none';
                document.getElementById('pdfObject').style.display = 'block';
            }, 1000);
        });

        // Print function
        window.print = function() {
            const pdfObject = document.getElementById('pdfObject');
            if (pdfObject && pdfObject.contentWindow) {
                pdfObject.contentWindow.print();
            } else {
                window.frames[0].print();
            }
        };
    </script>
</body>
</html>