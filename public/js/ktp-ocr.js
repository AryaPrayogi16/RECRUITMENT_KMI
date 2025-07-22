// Enhanced KTP OCR Script - Complete Version with Flexible NIK Management
(function() {
    'use strict';

    console.log('🚀 Loading Enhanced KTP OCR Script with Flexible NIK Management...');

    // ✅ KEEP: Original OCR Configuration (TIDAK DIUBAH)
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

    // ✅ KEEP: Original NIK patterns
    const NIK_PATTERNS = [
        /(?:NIK|N\s*I\s*K|N1K|NlK)[\s:]*(\d{16})/gi,
        /\b(\d{16})\b/g,
        /(\d{4})\s*(\d{4})\s*(\d{4})\s*(\d{4})/g
    ];

    let ocrWorker = null;
    let isOcrProcessing = false;
    let currentImageFile = null;
    let processingAttempts = 0;
    const maxAttempts = 3;
    let nikLocked = false; // Track NIK lock status

    // ✅ KEEP: Original OCR initialization
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

    // 🆕 UPDATED: Enhanced NIK field locking - more user-friendly
    function lockNikField(nik) {
        const nikField = document.getElementById('nik');
        if (nikField) {
            // Remove any existing instruction
            const existingInstruction = nikField.parentNode.querySelector('.nik-instruction');
            if (existingInstruction) {
                existingInstruction.remove();
            }
            
            nikField.value = nik;
            nikField.readOnly = true;
            nikField.style.backgroundColor = '#ecfdf5';
            nikField.style.borderColor = '#10b981';
            nikField.style.color = '#065f46';
            nikField.classList.add('ocr-filled');
            
            // Add enhanced lock icon with more options
            addEnhancedLockIcon(nikField);
            
            nikLocked = true;
            console.log('🔒 NIK field locked with value:', nik);
            
            // Trigger form events untuk integration dengan form validation
            nikField.dispatchEvent(new Event('input', { bubbles: true }));
            nikField.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // 🆕 UPDATED: More user-friendly unlock function
    function unlockNikField() {
        const nikField = document.getElementById('nik');
        if (!nikField) return;

        // Show confirmation before unlocking
        const userConfirmed = confirm(
            'Apakah Anda yakin ingin membatalkan hasil scan KTP?\n\n' +
            'Setelah dibatalkan, Anda harus mengisi NIK secara manual atau scan ulang KTP.'
        );
        
        if (!userConfirmed) return;

        nikField.readOnly = false;
        nikField.style.backgroundColor = '';
        nikField.style.borderColor = '';
        nikField.style.color = '';
        nikField.classList.remove('ocr-filled');
        nikField.value = '';
        nikField.placeholder = 'Masukkan NIK 16 digit atau gunakan scan KTP';
        
        // Remove lock icon
        removeLockIcon(nikField);
        
        // Add back helpful instruction
        addHelpfulInstruction(nikField);
        
        nikLocked = false;
        console.log('🔓 NIK field unlocked by user request');
        
        // Clear OCR session data
        sessionStorage.removeItem('nik_locked');
        sessionStorage.removeItem('extracted_nik');
        sessionStorage.removeItem('ktpFileData');
        
        // Focus on the field for immediate editing
        nikField.focus();
        
        showOCRMessage('✅ NIK field telah dibuka untuk pengeditan manual. Anda dapat mengisi NIK secara manual atau scan ulang KTP.', 'info');
    }

    // 🆕 NEW: Add helpful instruction to NIK field
    function addHelpfulInstruction(nikField) {
        // Remove existing instruction first
        const existing = nikField.parentNode.querySelector('.nik-instruction');
        if (existing) existing.remove();
        
        const instructionDiv = document.createElement('div');
        instructionDiv.className = 'nik-instruction';
        instructionDiv.innerHTML = `
            <div style="margin-top: 4px; padding: 6px 8px; background: #eff6ff; border: 1px solid #3b82f6; 
                        border-radius: 4px; font-size: 12px; color: #1e40af; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>💡 <strong>Tips:</strong> Gunakan fitur scan KTP untuk pengisian NIK otomatis yang lebih mudah dan akurat</span>
            </div>
        `;
        nikField.parentNode.appendChild(instructionDiv);
    }

    // 🆕 UPDATED: Enhanced lock icon with better UX
    function addEnhancedLockIcon(nikField) {
        // Remove existing icon
        removeLockIcon(nikField);
        
        const lockIcon = document.createElement('div');
        lockIcon.className = 'nik-lock-icon';
        lockIcon.innerHTML = `
            <div style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); 
                        display: flex; align-items: center; gap: 6px; color: #10b981; font-size: 12px; font-weight: 500; z-index: 10;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
                </svg>
                <span style="font-weight: 600;">KTP Scan</span>
                <button type="button" onclick="window.KTPOcr.unlockField()" 
                        style="margin-left: 4px; padding: 2px 6px; background: #ef4444; color: white; 
                               border: none; border-radius: 3px; font-size: 10px; cursor: pointer; font-weight: 500;"
                        title="Batal dan isi manual">
                    ✕ Batal
                </button>
            </div>
        `;
        
        nikField.parentNode.style.position = 'relative';
        nikField.parentNode.appendChild(lockIcon);
    }

    function removeLockIcon(nikField) {
        const existingIcon = nikField.parentNode.querySelector('.nik-lock-icon');
        if (existingIcon) {
            existingIcon.remove();
        }
    }

    // ✅ KEEP: Original validation functions
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

    // 🆕 UPDATED: Enhanced image processing function
    async function processKTPImage(imageFile) {
        if (isOcrProcessing) {
            showOCRWarning('OCR sedang berjalan. Mohon tunggu...');
            return;
        }

        if (!imageFile) {
            showOCRError('File gambar tidak valid');
            return;
        }

        // Enhanced validation
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
            
            showOCRProgress('Memproses gambar KTP...', 0);
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
                
                // Lock the NIK field with extracted value
                lockNikField(extractedNIK);
                
                // Store with OCR session
                storeKTPFileForUpload(imageFile, extractedNIK);
                
                showOCRSuccess(`✅ NIK berhasil terdeteksi dan diisi otomatis: ${extractedNIK}`);
                resetProcessingAttempts();
                
            } else {
                if (processingAttempts < maxAttempts) {
                    showOCRWarning(`⚠️ NIK tidak terdeteksi (Percobaan ${processingAttempts}/${maxAttempts}). Mencoba lagi...`);
                    setTimeout(() => {
                        retryWithSimpleNIK();
                    }, 2000);
                } else {
                    showOCRError('❌ NIK tidak dapat terdeteksi setelah 3 percobaan. Anda dapat mengisi NIK secara manual.');
                    resetProcessingAttempts();
                    
                    // 🆕 NEW: Automatically guide to manual input after failed OCR
                    setTimeout(() => {
                        const nikField = document.getElementById('nik');
                        if (nikField && !nikField.readOnly) {
                            nikField.focus();
                            addHelpfulInstruction(nikField);
                            showOCRMessage('💡 Silakan isi NIK secara manual pada field yang tersedia.', 'info');
                        }
                    }, 2000);
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
                showOCRError(`❌ OCR gagal: ${error.message}. Anda dapat mengisi NIK secara manual.`);
                resetProcessingAttempts();
                
                // 🆕 NEW: Focus on manual input after error
                setTimeout(() => {
                    const nikField = document.getElementById('nik');
                    if (nikField && !nikField.readOnly) {
                        nikField.focus();
                        addHelpfulInstruction(nikField);
                    }
                }, 2000);
            }
        } finally {
            if (processingAttempts >= maxAttempts) {
                isOcrProcessing = false;
                updateProcessingState(false);
                hideOCRProgress();
            }
        }
    }

    // Enhanced file storage function
    function storeKTPFileForUpload(imageFile, extractedNIK) {
        try {
            // Create FormData for server upload
            const formData = new FormData();
            formData.append('ktp_image', imageFile);
            formData.append('extracted_nik', extractedNIK);
            
            // Upload to server
            uploadKtpToServer(formData);
            
            console.log('📄 KTP file uploading to server', {
                size: imageFile.size,
                nik: extractedNIK,
                name: imageFile.name
            });
        } catch (error) {
            console.error('Error storing KTP file:', error);
            showOCRError('Gagal menyimpan file KTP: ' + error.message);
        }
    }

    // Enhanced server upload function
    async function uploadKtpToServer(formData) {
        try {
            console.log('📤 Uploading KTP to server...', {
                has_ktp_image: formData.has('ktp_image'),
                has_extracted_nik: formData.has('extracted_nik'),
                nik_value: formData.get('extracted_nik')
            });

            const response = await fetch('/upload-ktp-ocr', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const result = await response.json();
            
            console.log('📤 Server response:', result);
            
            if (result.success) {
                console.log('✅ KTP uploaded to server successfully', {
                    nik: result.data?.nik
                });
                
                showOCRMessage('✅ File KTP berhasil diproses dan tersimpan.', 'success');
            } else {
                console.error('❌ KTP upload failed:', result.message);
                showOCRError('Gagal memproses file KTP: ' + result.message);
            }
            
        } catch (error) {
            console.error('❌ Error uploading KTP:', error);
            showOCRError('Gagal memproses file KTP. Silakan coba lagi.');
        }
    }

    // Enhanced text processing functions
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

    function extractNIKFromText(cleanedText, originalText) {
        const fullText = cleanedText.replace(/\n/g, ' ').replace(/\s+/g, ' ');
        
        console.log('Processing text for NIK extraction:', fullText);

        const nikPatterns = [
            /(?:NIK|N\s*I\s*K|N1K)[\s:]*(\d{16})/i,
            /\b(\d{16})\b/g,
            /(?:NIK|N\s*I\s*K)[\s:]*(\d{4}\s*\d{4}\s*\d{4}\s*\d{4})/i
        ];
        
        for (const pattern of nikPatterns) {
            if (pattern.global) {
                const matches = [...fullText.matchAll(pattern)];
                for (const match of matches) {
                    const nik = match[0].replace(/\s/g, '');
                    if (nik.length === 16 && /^\d{16}$/.test(nik)) {
                        console.log('✅ NIK found via global pattern:', nik);
                        return nik;
                    }
                }
            } else {
                const match = fullText.match(pattern);
                if (match) {
                    const nik = match[1].replace(/\s/g, '');
                    if (nik.length === 16) {
                        console.log('✅ NIK found via pattern:', nik);
                        return nik;
                    }
                }
            }
        }

        console.log('❌ No NIK found in text');
        return null;
    }

    // Enhanced retry function
    async function retryWithSimpleNIK() {
        if (!currentImageFile || processingAttempts >= maxAttempts) {
            return;
        }

        try {
            showOCRProgress(`Percobaan ${processingAttempts + 1}/${maxAttempts} (fokus NIK)...`, 0);
            
            const worker = await Tesseract.createWorker('eng', 1, {
                logger: m => console.log('Simple NIK OCR:', m.status)
            });
            
            await worker.setParameters({
                'tessedit_char_whitelist': '0123456789 ',
                'tessedit_pageseg_mode': Tesseract.PSM.AUTO
            });
            
            showOCRProgress('Mencari NIK dengan mode khusus angka...', 50);
            
            const { data: { text } } = await worker.recognize(currentImageFile);
            console.log('Simple NIK OCR result:', text);
            
            const extractedNIK = extractNIKFromText('', text);
            
            if (extractedNIK) {
                lockNikField(extractedNIK);
                storeKTPFileForUpload(currentImageFile, extractedNIK);
                
                showOCRSuccess(`✅ NIK terdeteksi dan diisi otomatis (Mode angka): ${extractedNIK}`);
                resetProcessingAttempts();
            } else {
                processingAttempts++;
                if (processingAttempts < maxAttempts) {
                    setTimeout(() => retryWithSimpleNIK(), 2000);
                } else {
                    showOCRError(`❌ NIK tidak terdeteksi setelah ${maxAttempts} percobaan. Anda dapat mengisi NIK secara manual.`);
                    resetProcessingAttempts();
                    
                    // Guide user to manual input
                    setTimeout(() => {
                        const nikField = document.getElementById('nik');
                        if (nikField && !nikField.readOnly) {
                            nikField.focus();
                            addHelpfulInstruction(nikField);
                        }
                    }, 2000);
                }
            }
            
            await worker.terminate();
            
        } catch (error) {
            console.error(`❌ Simple NIK Error:`, error);
            processingAttempts++;
            if (processingAttempts < maxAttempts) {
                setTimeout(() => retryWithSimpleNIK(), 3000);
            } else {
                showOCRError('❌ OCR gagal. Anda dapat mengisi NIK secara manual.');
                resetProcessingAttempts();
                
                // Guide to manual input
                setTimeout(() => {
                    const nikField = document.getElementById('nik');
                    if (nikField && !nikField.readOnly) {
                        nikField.focus();
                        addHelpfulInstruction(nikField);
                    }
                }, 2000);
            }
        }
    }

    // UI Progress and State Functions
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

    // Message and Progress UI Functions
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
                <div class="ocr-progress-text">Memproses gambar KTP...</div>
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

    // 🆕 UPDATED: Enhanced initialization with flexible NIK field management
    function initializeKTPOCR() {
        console.log('🚀 Initializing Enhanced KTP OCR functionality...');

        if (typeof Tesseract === 'undefined') {
            console.error('❌ Tesseract.js not loaded');
            showOCRError('Library OCR tidak tersedia. Silakan refresh halaman.');
            return;
        }

        createOCRUploadArea();
        
        // Check for previous OCR session but don't force lock
        const savedNikLocked = sessionStorage.getItem('nik_locked');
        const savedNikValue = sessionStorage.getItem('extracted_nik');
        
        if (savedNikLocked === 'true' && savedNikValue) {
            lockNikField(savedNikValue);
            showOCRMessage(`✅ NIK dari sesi sebelumnya: ${savedNikValue}`, 'info');
        } else {
            // 🆕 NEW: Don't lock NIK field by default - make it user-friendly
            const nikField = document.getElementById('nik');
            if (nikField) {
                nikField.readOnly = false;
                nikField.style.backgroundColor = '';
                nikField.style.color = '';
                nikField.placeholder = 'Masukkan NIK 16 digit atau gunakan scan KTP';
                
                // Add helpful instruction instead of warning
                addHelpfulInstruction(nikField);
            }
        }
        
        console.log('✅ Enhanced KTP OCR ready with flexible NIK management');
    }

    // 🆕 UPDATED: More user-friendly upload area
    function createOCRUploadArea() {
        console.log('🔧 Creating user-friendly OCR upload area...');
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
                    <span class="ocr-upload-text">
                        <strong>📱 Scan KTP untuk NIK Otomatis</strong><br>
                        <small>Klik untuk ambil foto atau pilih file gambar KTP</small>
                    </span>
                </label>
                <div class="ocr-tips">
                    💡 <strong>Tips:</strong> Pastikan foto KTP jelas dan fokus pada bagian NIK. Jika scan gagal, Anda tetap dapat mengisi NIK secara manual.
                </div>
            </div>
            <div class="ocr-controls" style="margin-top: 8px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" id="ocr-try-again-btn" class="btn-secondary" style="display: none;">
                    🔄 Coba Lagi
                </button>
                <small class="text-gray-500">Scan KTP untuk kemudahan, atau isi manual jika diperlukan</small>
            </div>
        `;

        nikField.parentNode.insertBefore(ocrContainer, nikField.nextSibling);

        // Event listeners
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
                    showOCRMessage('Tidak ada gambar untuk diproses ulang. Silakan pilih gambar KTP terlebih dahulu.', 'warning');
                }
            });
        }
        
        console.log('✅ User-friendly OCR upload area created successfully');
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

    // 🆕 UPDATED: Enhanced exports with comprehensive functionality
    window.KTPOcr = {
        processImage: processKTPImage,
        cleanup: cleanupEnhancedOCR,
        isProcessing: () => isOcrProcessing,
        isLocked: () => nikLocked,
        unlockField: unlockNikField,
        retryLast: () => {
            if (currentImageFile && !isOcrProcessing) {
                processingAttempts = 0;
                processKTPImage(currentImageFile);
            } else {
                showOCRMessage('Tidak ada gambar untuk diproses ulang. Silakan pilih gambar KTP terlebih dahulu.', 'warning');
            }
        },
        // 🆕 NEW: Additional helper functions
        canManualInput: () => !nikLocked,
        getNikSource: () => nikLocked ? 'ocr' : 'manual',
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
            
            // Clear any existing messages
            const messages = document.querySelectorAll('.ocr-message');
            messages.forEach(msg => msg.remove());
            
            console.log('OCR state reset');
        }
    };

})();