// Enhanced KTP OCR Script - Minimal Enhancement (Keep Working Base)
(function() {
    'use strict';

    console.log('🚀 Loading KTP OCR Script - Minimal Enhancement...');

    // ✅ KEEP: Original working OCR Configuration
    const OCR_CONFIG = {
        language: 'eng',
        logger: m => {
            console.log('OCR Logger:', m);
            if (m.status === 'recognizing text') {
                updateOCRProgress(Math.round(m.progress * 100));
            }
        },
        tessedit_pageseg_mode: 6,
        tessedit_ocr_engine_mode: 1,
        preserve_interword_spaces: 1,
        tessedit_char_whitelist: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz .:/-()%',
    };

    // 🆕 ENHANCED: Better NIK patterns (keeping simple approach)
    const NIK_PATTERNS = [
        // Original working patterns first
        /(?:NIK|N\s*I\s*K|N1K|NlK)[\s:]*(\d{16})/gi,
        /\b(\d{16})\b/g,
        /(\d{4})\s*(\d{4})\s*(\d{4})\s*(\d{4})/g,
        
        // Additional patterns for better recognition
        /NIK\s*:?\s*(\d{16})/gi,
        /N\s*I\s*K\s*:?\s*(\d{16})/gi,
        /NlK\s*:?\s*(\d{16})/gi,
        /N1K\s*:?\s*(\d{16})/gi,
        
        // Spaced versions
        /NIK\s*:?\s*(\d{4}\s+\d{4}\s+\d{4}\s+\d{4})/gi,
        /N\s*I\s*K\s*:?\s*(\d{4}\s+\d{4}\s+\d{4}\s+\d{4})/gi,
        
        // Line-based patterns
        /^.*NIK.*?(\d{16}).*$/gmi,
    ];

    let ocrWorker = null;
    let isOcrProcessing = false;
    let currentImageFile = null;
    let processingAttempts = 0;
    const maxAttempts = 3;
    let nikLocked = true;

    // ✅ KEEP: Original working OCR initialization
    async function initializeOCR() {
        try {
            console.log('🔄 Initializing OCR (optimized for NIK)...');
            
            if (typeof Tesseract === 'undefined') {
                throw new Error('Tesseract.js tidak tersedia');
            }

            const worker = await Tesseract.createWorker('ind', 1, {
                logger: m => {
                    console.log(m);
                    if (m.status === 'recognizing text') {
                        const progress = Math.round(m.progress * 100);
                        updateOCRProgress(progress, `Mengenali teks... ${progress}%`);
                    } else if (m.status === 'loading tesseract core') {
                        updateOCRProgress(10, "Memuat Tesseract Core...");
                    } else if (m.status === 'initializing tesseract') {
                        updateOCRProgress(20, "Menginisialisasi Tesseract...");
                    } else if (m.status === 'loading language traineddata') {
                        updateOCRProgress(30, "Memuat model bahasa Indonesia...");
                    }
                },
            });

            updateOCRProgress(40, "Mengkonfigurasi OCR...");
            
            await worker.setParameters({
                'tessedit_char_whitelist': 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 .:/-(),%',
                'tessedit_pageseg_mode': Tesseract.PSM.AUTO,
                'preserve_interword_spaces': '1',
                'tessedit_ocr_engine_mode': Tesseract.OEM.LSTM_ONLY
            });

            console.log('✅ OCR initialized successfully');
            return worker;

        } catch (error) {
            console.error('❌ Error initializing OCR:', error);
            throw error;
        }
    }

    function lockNikFieldDefault() {
        const nikField = document.getElementById('nik');
        if (nikField) {
            nikField.value = '';
            nikField.readOnly = true;
            nikField.style.backgroundColor = '#f9fafb';
            nikField.style.borderColor = '#d1d5db';
            nikField.style.color = '#9ca3af';
            nikField.placeholder = 'Gunakan scan KTP untuk mengisi NIK';
            nikField.classList.add('nik-locked');
            
            const existingIndicators = nikField.parentNode.querySelectorAll('.nik-instruction, .ocr-indicator, .nik-locked-indicator');
            existingIndicators.forEach(indicator => indicator.remove());
            
            addScanInstructions(nikField);
            
            nikLocked = true;
            console.log('🔒 NIK field locked - scan required for input');
        }
    }

    function addScanInstructions(nikField) {
        const instructions = document.createElement('div');
        instructions.className = 'nik-scan-instructions';
        nikField.parentNode.appendChild(instructions);
    }

    function startScanProcess() {
        const fileInput = document.getElementById('ktp-image-input');
        if (fileInput) {
            fileInput.click();
        } else {
            showOCRMessage('⚠️ Fitur scan KTP belum siap. Silakan refresh halaman dan coba lagi.', 'warning');
        }
    }

    function lockNikFieldWithOCR(nik) {
        const nikField = document.getElementById('nik');
        if (nikField) {
            const existingInstructions = nikField.parentNode.querySelectorAll('.nik-scan-instructions, .nik-success-indicator');
            existingInstructions.forEach(instruction => instruction.remove());
            
            nikField.value = nik;
            nikField.readOnly = true;
            nikField.style.backgroundColor = '#ecfdf5';
            nikField.style.borderColor = '#10b981';
            nikField.style.color = '#065f46';
            nikField.classList.remove('nik-locked');
            nikField.classList.add('ocr-filled');
            
            addOCRSuccessIndicator(nikField, nik);
            
            nikLocked = true;
            console.log('🔒 NIK field locked with OCR value:', nik);
            
            sessionStorage.setItem('nik_locked', 'true');
            sessionStorage.setItem('extracted_nik', nik);
            
            nikField.dispatchEvent(new Event('input', { bubbles: true }));
            nikField.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function addOCRSuccessIndicator(nikField, nik) {
        const indicator = document.createElement('div');
        indicator.className = 'nik-success-indicator';
        indicator.innerHTML = `
            <div style="margin-top: 4px; padding: 8px 12px; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); 
                        border: 1px solid #10b981; border-radius: 6px; font-size: 12px; color: #065f46;">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><strong>✅ Success!</strong></span>
                </div>
                <div style="background: rgba(255, 255, 255, 0.7); padding: 6px 8px; border-radius: 4px; margin-bottom: 6px;">
                    <strong>NIK:</strong> ${nik}
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" onclick="window.KTPOcr.retryScan()" 
                            style="flex: 1; padding: 4px 8px; background: #3b82f6; color: white; border: none; 
                                   border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 500;">
                        🔄 Scan Ulang
                    </button>
                    <button type="button" onclick="window.KTPOcr.resetScan()" 
                            style="flex: 1; padding: 4px 8px; background: #6b7280; color: white; border: none; 
                                   border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 500;">
                        🔄 Reset
                    </button>
                </div>
            </div>
        `;
        nikField.parentNode.appendChild(indicator);
    }

    function retryScan() {
        const userConfirmed = confirm(
            'Scan KTP ulang?\n\n' +
            'NIK yang sudah tersimpan akan diganti dengan hasil scan yang baru.\n\n' +
            'Pastikan foto KTP Anda jelas dan fokus.'
        );
        
        if (!userConfirmed) return;
        
        processingAttempts = 0;
        currentImageFile = null;
        startScanProcess();
        showOCRMessage('💡 Silakan pilih foto KTP yang baru atau ambil foto ulang.', 'info');
    }

    function resetScan() {
        const userConfirmed = confirm(
            'Reset scan KTP?\n\n' +
            'NIK yang sudah tersimpan akan dihapus dan Anda perlu scan ulang dari awal.'
        );
        
        if (!userConfirmed) return;
        
        processingAttempts = 0;
        currentImageFile = null;
        isOcrProcessing = false;
        
        sessionStorage.removeItem('nik_locked');
        sessionStorage.removeItem('extracted_nik');
        
        lockNikFieldDefault();
    }

    // ✅ KEEP: Original working validation
    function isValidNIKPattern(nik) {
        if (!nik || nik.length !== 16 || !/^\d{16}$/.test(nik)) {
            return false;
        }
        
        if (/^(\d)\1{15}$/.test(nik)) {
            return false;
        }
        
        if (nik.startsWith('00')) {
            return false;
        }
        
        return true;
    }

    // ✅ KEEP: Original working process function (with minimal enhancement)
    async function processKTPImage(imageFile) {
        if (isOcrProcessing) {
            showOCRWarning('Scan sedang berlangsung. Mohon tunggu...');
            return;
        }

        if (!imageFile) {
            showOCRError('File gambar tidak valid');
            return;
        }

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!validTypes.includes(imageFile.type)) {
            showOCRError('Format file harus JPG, PNG, atau WebP');
            return;
        }

        const maxSize = 10 * 1024 * 1024;
        if (imageFile.size > maxSize) {
            showOCRError('Ukuran file terlalu besar (maksimal 10MB)');
            return;
        }

        try {
            isOcrProcessing = true;
            processingAttempts++;
            currentImageFile = imageFile;
            
            showOCRProgress('Memproses foto KTP...', 0);
            updateProcessingState(true);

            const worker = await initializeOCR();
            if (!worker) {
                throw new Error('OCR worker tidak tersedia');
            }

            updateProgress(50, "Memulai pengenalan teks...");
            
            const { data: { text, confidence } } = await worker.recognize(imageFile);
            
            updateProgress(90, "Memproses hasil...");
            
            console.log('OCR Confidence:', confidence);
            console.log('Raw OCR Text:', text);
            
            const cleanedText = preprocessText(text);
            console.log('Cleaned Text:', cleanedText);

            const extractedNIK = extractNIKFromText(cleanedText, text);
            
            if (extractedNIK) {
                console.log('🎯 NIK Found:', extractedNIK);
                
                lockNikFieldWithOCR(extractedNIK);
                await storeNIKInSession(extractedNIK);
                
                showOCRSuccess(`✅ NIK berhasil diekstrak: ${extractedNIK}`);
                resetProcessingAttempts();
                
            } else {
                if (processingAttempts < maxAttempts) {
                    showOCRWarning(`⚠️ NIK tidak terdeteksi (Percobaan ${processingAttempts}/${maxAttempts}). Mencoba dengan metode lain...`);
                    setTimeout(() => {
                        retryWithSimpleNIK();
                    }, 2000);
                } else {
                    showOCRError(`❌ NIK tidak dapat terdeteksi setelah ${maxAttempts} percobaan. Silakan coba dengan foto KTP yang lebih jelas.`);
                    resetProcessingAttempts();
                    setTimeout(() => showScanTips(), 2000);
                }
            }

            await worker.terminate();
            ocrWorker = null;

        } catch (error) {
            console.error('❌ OCR Error:', error);
            
            if (processingAttempts < maxAttempts) {
                showOCRWarning(`⚠️ Error (${processingAttempts}/${maxAttempts}): ${error.message}`);
                setTimeout(() => {
                    retryWithSimpleNIK();
                }, 3000);
            } else {
                showOCRError(`❌ Scan gagal: ${error.message}. Silakan coba dengan foto yang lebih jelas.`);
                resetProcessingAttempts();
                setTimeout(() => showScanTips(), 2000);
            }
        } finally {
            if (processingAttempts >= maxAttempts) {
                isOcrProcessing = false;
                updateProcessingState(false);
                hideOCRProgress();
            }
        }
    }

    function showScanTips() {
        showOCRMessage(`
            💡 <strong>Tips untuk foto KTP yang lebih baik:</strong><br>
            • Pastikan NIK terlihat jelas dan tidak buram<br>
            • Hindari bayangan atau pantulan cahaya<br>
            • Ambil foto dari jarak yang tepat<br>
            • Pastikan pencahayaan cukup dan merata
        `, 'info');
    }

    async function storeNIKInSession(extractedNIK) {
        try {
            console.log('📝 Storing NIK in session only', { nik: extractedNIK });
            
            const response = await fetch('/ktp-ocr/upload', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    extracted_nik: extractedNIK,
                    ktp_image: null
                })
            });
            
            const result = await response.json();
            console.log('📝 Server response for NIK storage:', result);
            
            if (result.success) {
                console.log('✅ NIK stored in session successfully');
            } else {
                console.error('❌ NIK storage failed:', result.message);
                showOCRError('Gagal menyimpan NIK: ' + result.message);
            }
            
        } catch (error) {
            console.error('❌ Error storing NIK:', error);
        }
    }

    // ✅ KEEP: Original working text preprocessing
    function preprocessText(text) {
        let cleaned = text
            .replace(/[^\w\s\/:.,()-]/g, ' ')
            .replace(/\s+/g, ' ')
            .replace(/\n\s*\n/g, '\n')
            .trim();
        
        const corrections = {
            'NlK': 'NIK', 'Nl K': 'NIK', 'N I K': 'NIK', 'N1K': 'NIK',
            'JenIs': 'Jenis', 'Jen is': 'Jenis', 'JENIS': 'Jenis',
            'KeIamin': 'Kelamin', 'Ke lamin': 'Kelamin', 'KELAMIN': 'Kelamin'
        };
        
        for (const [wrong, correct] of Object.entries(corrections)) {
            const regex = new RegExp(wrong, 'gi');
            cleaned = cleaned.replace(regex, correct);
        }
        
        return cleaned;
    }

    // 🆕 ENHANCED: Better NIK extraction (keeping original logic but more patterns)
    function extractNIKFromText(cleanedText, originalText) {
        const fullText = cleanedText.replace(/\n/g, ' ').replace(/\s+/g, ' ');
        
        console.log('🔍 Processing text for NIK extraction:', fullText);

        // Try all patterns
        for (const pattern of NIK_PATTERNS) {
            try {
                if (pattern.global) {
                    const matches = [...fullText.matchAll(pattern)];
                    for (const match of matches) {
                        let nik;
                        if (match[1]) {
                            // Has capture group
                            nik = match[1].replace(/[\s\-\.]/g, '');
                        } else {
                            // No capture group, use full match
                            nik = match[0].replace(/[\s\-\.]/g, '').replace(/[^\d]/g, '');
                        }
                        
                        if (nik.length === 16 && /^\d{16}$/.test(nik) && isValidNIKPattern(nik)) {
                            console.log('✅ NIK found via pattern:', nik);
                            return nik;
                        }
                    }
                } else {
                    const match = fullText.match(pattern);
                    if (match) {
                        let nik;
                        if (match[1]) {
                            nik = match[1].replace(/[\s\-\.]/g, '');
                        } else {
                            nik = match[0].replace(/[\s\-\.]/g, '').replace(/[^\d]/g, '');
                        }
                        
                        if (nik.length === 16 && /^\d{16}$/.test(nik) && isValidNIKPattern(nik)) {
                            console.log('✅ NIK found via pattern:', nik);
                            return nik;
                        }
                    }
                }
            } catch (e) {
                console.warn('Pattern error:', e);
                continue;
            }
        }

        console.log('❌ No NIK found in text');
        return null;
    }

    // ✅ KEEP: Original working retry function
    async function retryWithSimpleNIK() {
        if (!currentImageFile || processingAttempts >= maxAttempts) {
            return;
        }

        try {
            showOCRProgress(`Percobaan ${processingAttempts + 1}/${maxAttempts} (mode angka saja)...`, 0);
            
            const worker = await Tesseract.createWorker('eng', 1, {
                logger: m => console.log('Simple NIK OCR:', m.status)
            });
            
            await worker.setParameters({
                'tessedit_char_whitelist': '0123456789 ',
                'tessedit_pageseg_mode': Tesseract.PSM.AUTO
            });
            
            showOCRProgress('Mencari NIK dengan fokus angka...', 50);
            
            const { data: { text } } = await worker.recognize(currentImageFile);
            console.log('Simple NIK OCR result:', text);
            
            const extractedNIK = extractNIKFromText('', text);
            
            if (extractedNIK) {
                lockNikFieldWithOCR(extractedNIK);
                await storeNIKInSession(extractedNIK);
                
                showOCRSuccess(`✅ NIK berhasil diambil (Mode angka): ${extractedNIK}`);
                resetProcessingAttempts();
            } else {
                processingAttempts++;
                if (processingAttempts < maxAttempts) {
                    setTimeout(() => retryWithSimpleNIK(), 2000);
                } else {
                    showOCRError(`❌ NIK tidak terdeteksi setelah ${maxAttempts} percobaan. Silakan coba dengan foto yang lebih jelas.`);
                    resetProcessingAttempts();
                    setTimeout(() => showScanTips(), 2000);
                }
            }
            
            await worker.terminate();
            
        } catch (error) {
            console.error(`❌ Simple NIK Error:`, error);
            processingAttempts++;
            if (processingAttempts < maxAttempts) {
                setTimeout(() => retryWithSimpleNIK(), 3000);
            } else {
                showOCRError('❌ Scan gagal. Silakan coba dengan foto KTP yang lebih jelas.');
                resetProcessingAttempts();
                setTimeout(() => showScanTips(), 2000);
            }
        }
    }

    // ✅ KEEP: All original UI functions unchanged
    function updateProgress(progress, status) {
        const progressBar = document.getElementById('ocr-progress')?.querySelector('.ocr-progress-bar');
        const statusText = document.getElementById('ocr-progress')?.querySelector('.ocr-progress-text');
        
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (statusText) statusText.textContent = status;
    }

    function resetProcessingAttempts() {
        processingAttempts = 0;
        isOcrProcessing = false;
        updateProcessingState(false);
        hideOCRProgress();
    }

    function updateProcessingState(processing) {
        const uploadArea = document.querySelector('.ocr-upload-area');
        const tryAgainBtn = document.getElementById('ocr-try-again-btn');
        
        if (uploadArea) {
            if (processing) {
                uploadArea.classList.add('processing');
            } else {
                uploadArea.classList.remove('processing');
            }
        }
        
        if (tryAgainBtn) {
            tryAgainBtn.disabled = processing;
        }
    }

    function showOCRProgress(message, percentage) {
        percentage = percentage || 0;
        let progressElement = document.getElementById('ocr-progress');
        if (!progressElement) {
            progressElement = createOCRProgressElement();
        }
        
        const progressText = progressElement.querySelector('.ocr-progress-text');
        const progressBar = progressElement.querySelector('.ocr-progress-bar');
        
        if (progressText) progressText.textContent = message;
        if (progressBar) progressBar.style.width = `${percentage}%`;
        
        progressElement.style.display = 'block';
    }

    function updateOCRProgress(percentage, message) {
        const progressElement = document.getElementById('ocr-progress');
        if (progressElement) {
            const progressBar = progressElement.querySelector('.ocr-progress-bar');
            const progressText = progressElement.querySelector('.ocr-progress-text');
            
            if (progressBar) progressBar.style.width = `${percentage}%`;
            if (progressText && message) progressText.textContent = message;
        }
    }

    function hideOCRProgress() {
        const progressElement = document.getElementById('ocr-progress');
        if (progressElement) {
            setTimeout(() => {
                progressElement.style.display = 'none';
            }, 1000);
        }
    }

    function createOCRProgressElement() {
        const progressElement = document.createElement('div');
        progressElement.id = 'ocr-progress';
        progressElement.className = 'ocr-progress-container';
        progressElement.innerHTML = `
            <div class="ocr-progress-content">
                <div class="ocr-progress-spinner"></div>
                <div class="ocr-progress-text">Memproses foto KTP...</div>
                <div class="ocr-progress-track">
                    <div class="ocr-progress-bar"></div>
                </div>
            </div>
        `;
        
        const nikField = document.getElementById('nik');
        if (nikField && nikField.parentNode) {
            nikField.parentNode.appendChild(progressElement);
        }
        
        return progressElement;
    }

    function showOCRMessage(message, type) {
        type = type || 'info';
        const existingMessages = document.querySelectorAll('.ocr-message');
        existingMessages.forEach(msg => msg.remove());

        const messageElement = document.createElement('div');
        messageElement.className = `ocr-message ocr-message-${type}`;
        messageElement.innerHTML = `
            <div class="ocr-message-content">
                <span class="ocr-message-icon">${getMessageIcon(type)}</span>
                <span class="ocr-message-text">${message}</span>
                <button class="ocr-message-close" type="button">&times;</button>
            </div>
        `;

        const nikField = document.getElementById('nik');
        if (nikField && nikField.parentNode) {
            nikField.parentNode.appendChild(messageElement);
        }

        const closeBtn = messageElement.querySelector('.ocr-message-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                messageElement.remove();
            });
        }

        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, 8000);
    }

    function showOCRSuccess(message) {
        showOCRMessage(message, 'success');
    }

    function showOCRError(message) {
        showOCRMessage(message, 'error');
    }

    function showOCRWarning(message) {
        showOCRMessage(message, 'warning');
    }

    function getMessageIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    function initializeKTPOCR() {
        console.log('🚀 Initializing KTP OCR - Minimal Enhancement...');

        if (typeof Tesseract === 'undefined') {
            console.error('❌ Tesseract.js not loaded');
            showOCRError('Library OCR tidak tersedia. Silakan refresh halaman.');
            return;
        }

        createOCRUploadArea();
        
        const savedNikLocked = sessionStorage.getItem('nik_locked');
        const savedNikValue = sessionStorage.getItem('extracted_nik');
        
        if (savedNikLocked === 'true' && savedNikValue) {
            lockNikFieldWithOCR(savedNikValue);
            showOCRMessage(`✅ NIK dari sesi sebelumnya: ${savedNikValue}`, 'info');
        } else {
            lockNikFieldDefault();
        }
        
        console.log('✅ KTP OCR initialized - Minimal Enhancement ready');
    }

    function createOCRUploadArea() {
        console.log('🔧 Creating OCR upload area...');
        const nikField = document.getElementById('nik');
        
        if (!nikField) {
            console.error('❌ NIK field not found');
            return;
        }

        const ocrContainer = document.createElement('div');
        ocrContainer.className = 'ocr-upload-container';
        ocrContainer.innerHTML = `
            <div class="ocr-upload-area">
                <input type="file" id="ktp-image-input" class="ocr-file-input" accept="image/*" capture="environment">
                <label for="ktp-image-input" class="ocr-upload-label">
                    <svg class="ocr-camera-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div class="ocr-upload-text">
                        <strong>📷 Scan KTP untuk NIK</strong>
                        <small>Tap untuk mengambil foto atau pilih dari galeri</small>
                    </div>
                </label>
                <div class="ocr-tips">
                    💡 <strong>Tips:</strong> Pastikan foto KTP jelas, pencahayaan cukup, dan NIK terlihat dengan jelas untuk hasil scan yang optimal.
                </div>
            </div>
        `;

        nikField.parentNode.insertBefore(ocrContainer, nikField.nextSibling);

        const fileInput = document.getElementById('ktp-image-input');
        const tryAgainBtn = document.getElementById('ocr-try-again-btn');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    processingAttempts = 0;
                    processKTPImage(file);
                    if (tryAgainBtn) {
                        tryAgainBtn.style.display = 'inline-block';
                    }
                }
            });
        }

        if (tryAgainBtn) {
            tryAgainBtn.addEventListener('click', function() {
                if (currentImageFile && !isOcrProcessing) {
                    processingAttempts = 0;
                    processKTPImage(currentImageFile);
                } else {
                    showOCRMessage('Tidak ada foto untuk diproses ulang. Silakan pilih foto KTP terlebih dahulu.', 'warning');
                }
            });
        }
        
        console.log('✅ OCR upload area created successfully');
    }

    function cleanupEnhancedOCR() {
        if (ocrWorker) {
            ocrWorker.terminate();
            ocrWorker = null;
        }
        isOcrProcessing = false;
        processingAttempts = 0;
        currentImageFile = null;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeKTPOCR);
    } else {
        initializeKTPOCR();
    }

    window.addEventListener('beforeunload', cleanupEnhancedOCR);

    // Exports
    window.KTPOcr = {
        processImage: processKTPImage,
        cleanup: cleanupEnhancedOCR,
        isProcessing: () => isOcrProcessing,
        isLocked: () => nikLocked,
        startScanProcess: startScanProcess,
        retryScan: retryScan,
        resetScan: resetScan,
        retryLast: () => {
            if (currentImageFile && !isOcrProcessing) {
                processingAttempts = 0;
                processKTPImage(currentImageFile);
            } else {
                showOCRMessage('Tidak ada foto untuk diproses ulang. Silakan pilih foto KTP terlebih dahulu.', 'warning');
            }
        },
        getNikSource: () => 'minimal_enhanced',
        getCurrentNik: () => {
            const nikField = document.getElementById('nik');
            return nikField ? nikField.value : '';
        },
        resetOCR: () => {
            processingAttempts = 0;
            isOcrProcessing = false;
            currentImageFile = null;
            hideOCRProgress();
            updateProcessingState(false);
            
            const messages = document.querySelectorAll('.ocr-message');
            messages.forEach(msg => msg.remove());
            
            lockNikFieldDefault();
            
            console.log('OCR reset - Minimal Enhancement');
        }
    };

})();