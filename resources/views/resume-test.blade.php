<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes - KMI Recruitment</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .logo {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .test-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .test-option {
            position: relative;
        }

        .test-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .test-option label {
            display: block;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
            background: white;
        }

        .test-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .test-option .test-name {
            font-size: 1.1em;
            margin-bottom: 5px;
        }

        .test-option .test-desc {
            font-size: 0.85em;
            opacity: 0.8;
        }

        .input-group {
            position: relative;
        }

        .input-field {
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.3s ease;
            text-transform: uppercase;
        }

        .input-field:focus {
            border-color: #667eea;
        }

        .input-field::placeholder {
            text-transform: none;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            font-size: 1.2em;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            text-align: left;
        }

        .info-box {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
            border-left: 4px solid #667eea;
        }

        .info-box h4 {
            color: #333;
            margin-bottom: 10px;
        }

        .info-box p {
            color: #666;
            font-size: 0.9em;
            line-height: 1.5;
            text-align: left;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            .test-selector {
                grid-template-columns: 1fr;
            }
            
            .logo {
                font-size: 2em;
            }
            
            .title {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📋</div>
        


        <div id="errorMessage" class="error-message" style="display: none;"></div>

        <form id="resumeTestForm">
            <div class="form-group">
                <label>Klik tombol dibawah:</label>
                <div class="test-selector">
                    <div class="test-option">
                        <input type="radio" id="kraeplin" name="testType" value="kraeplin" required>
                        <label for="kraeplin">
                            <div class="test-name">Ready</div>
                            
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="candidateCode">Kode Kandidat:</label>
                <div class="input-group">
                    <input 
                        type="text" 
                        id="candidateCode" 
                        name="candidateCode"
                        class="input-field" 
                        placeholder="Contoh: KMI12376187236812"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Mulai Tes
            </button>
        </form>

        <div class="info-box">
            <h4>❓ Bantuan</h4>
           
            <p style="margin-top: 10px;">
                <strong>Contoh format kode:</strong> KMI12376187236812
            </p>
        </div>
    </div>

    <script>
        document.getElementById('resumeTestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ambil data form
            const testType = document.querySelector('input[name="testType"]:checked')?.value;
            const candidateCode = document.getElementById('candidateCode').value.trim();
            
            // Validasi
            if (!testType) {
                showError('Silakan pilih jenis tes terlebih dahulu.');
                return;
            }
            
            if (!candidateCode) {
                showError('Silakan masukkan kode kandidat.');
                return;
            }
            
            // Buat URL dan redirect langsung
            let redirectUrl;
            if (testType === 'disc3d') {
                redirectUrl = `/disc3d/${candidateCode}/instructions`;
            } else if (testType === 'kraeplin') {
                redirectUrl = `/kraeplin/${candidateCode}/instructions`;
            }
            
            // Redirect langsung
            window.location.href = redirectUrl;
        });
        
        // Auto uppercase input
        document.getElementById('candidateCode').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    </script>
</body>
</html>