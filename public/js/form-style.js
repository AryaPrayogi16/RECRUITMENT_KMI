// Form Application Script - Updated with Mobile Integration
(function() {
    'use strict';

    // Check if form was successfully submitted (for clearing localStorage)
    if (typeof formSubmitted !== 'undefined' && formSubmitted) {
        localStorage.removeItem('jobApplicationFormData');
    }

    // 🆕 MOBILE DETECTION
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               ('ontouchstart' in window) ||
               (window.innerWidth <= 768);
    }

    // Enhanced File Upload System dengan Mobile Support
    const fileValidation = {
        cv: {
            types: ['application/pdf'],
            extensions: ['pdf'],
            maxSize: 2 * 1024 * 1024,
            required: true
        },
        photo: {
            types: ['image/jpeg', 'image/jpg', 'image/png', 'image/pjpeg', 'image/x-png'],
            extensions: ['jpg', 'jpeg', 'png'],
            maxSize: 2 * 1024 * 1024,
            required: true
        },
        transcript: {
            types: ['application/pdf'],
            extensions: ['pdf'],
            maxSize: 2 * 1024 * 1024,
            required: true
        },
        certificates: {
            types: ['application/pdf'],
            extensions: ['pdf'],
            maxSize: 2 * 1024 * 1024,
            required: false
        }
    };

    // 🆕 MOBILE FILE STORE for preventing file loss
    const mobileFileStore = {
        files: new Map(),
        
        store: function(fieldName, file) {
            if (!file) return false;
            
            try {
                const fileData = {
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    lastModified: file.lastModified,
                    timestamp: Date.now(),
                    buffer: null
                };
                
                // Convert to ArrayBuffer for mobile stability
                if (isMobileDevice() && file.size < 5 * 1024 * 1024) { // Max 5MB for buffer
                    this.convertToArrayBuffer(file).then(buffer => {
                        fileData.buffer = buffer;
                        console.log(`📱 Mobile: File ${fieldName} stored with ArrayBuffer backup`);
                    }).catch(err => {
                        console.warn(`⚠️ Mobile: Could not create ArrayBuffer backup for ${fieldName}:`, err);
                    });
                }
                
                this.files.set(fieldName, fileData);
                console.log(`📱 Mobile: File ${fieldName} stored successfully`);
                
                return true;
            } catch (error) {
                console.error(`❌ Mobile: Error storing file ${fieldName}:`, error);
                return false;
            }
        },
        
        get: function(fieldName) {
            return this.files.get(fieldName);
        },
        
        convertToArrayBuffer: function(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        },
        
        validateStored: function(fieldName) {
            const stored = this.get(fieldName);
            if (!stored) return { valid: false, error: 'File tidak ditemukan di memory' };
            
            // Check timestamp (expire after 30 minutes)
            const maxAge = 30 * 60 * 1000;
            if (Date.now() - stored.timestamp > maxAge) {
                return { valid: false, error: 'File sudah expire, silakan pilih ulang' };
            }
            
            return { valid: true, file: stored.file };
        },
        
        clear: function() {
            this.files.clear();
        }
    };

    // 🆕 ENHANCED FILE VALIDATION with Mobile Support
    async function validateFile(file, validation) {
        console.log(`🔍 Validating file (${isMobileDevice() ? 'Mobile' : 'Desktop'}):`, {
            name: file.name,
            type: file.type,
            size: file.size,
            lastModified: file.lastModified
        });

        // Check if file is valid
        if (!file || file.size === 0) {
            return { valid: false, error: 'File tidak valid atau kosong' };
        }

        // Get file extension
        const extension = file.name.toLowerCase().split('.').pop();
        
        // Check file extension first
        if (!validation.extensions.includes(extension)) {
            const allowedExtensions = validation.extensions.join(', ').toUpperCase();
            return { valid: false, error: `Format file harus ${allowedExtensions}. File Anda: ${extension.toUpperCase()}` };
        }

        // Check file size
        if (file.size > validation.maxSize) {
            return { valid: false, error: 'Ukuran file maksimal 2MB' };
        }

        // 🆕 MOBILE-FRIENDLY MIME TYPE VALIDATION
        if (isMobileDevice()) {
            // Mobile: More lenient with MIME types, focus on extension
            if (validation.extensions.includes('jpg') || validation.extensions.includes('jpeg') || validation.extensions.includes('png')) {
                return await validateImageFile(file, validation);
            }
            
            // For PDF on mobile, just check extension
            if (extension === 'pdf') {
                return { valid: true };
            }
        } else {
            // Desktop: Check MIME type as usual
            if (!validation.types.includes(file.type)) {
                console.warn('MIME type mismatch:', {
                    detected: file.type,
                    allowed: validation.types
                });
                
                // If extension is correct but MIME is wrong, still accept for mobile compatibility
                if (validation.extensions.includes(extension)) {
                    console.log('Accepting file based on extension despite MIME type mismatch');
                    return { valid: true };
                }
                
                const allowedExtensions = validation.extensions.join(', ').toUpperCase();
                return { valid: false, error: `Format file harus ${allowedExtensions}. Tipe file terdeteksi: ${file.type}` };
            }
        }

        // For photo files, do additional image validation
        if (validation.extensions.includes('jpg') || validation.extensions.includes('jpeg') || validation.extensions.includes('png')) {
            return await validateImageFile(file, validation);
        }

        return { valid: true };
    }

    // Enhanced image validation function
    function validateImageFile(file, validation) {
        return new Promise((resolve) => {
            // Check MIME type first, but be more lenient for images
            const extension = file.name.toLowerCase().split('.').pop();
            
            if (!validation.types.includes(file.type) && !isMobileDevice()) {
                console.warn('MIME type mismatch for image:', {
                    detected: file.type,
                    allowed: validation.types,
                    extension: extension
                });
                
                // If extension is correct but MIME type is wrong, try to validate as image anyway
                if (!validation.extensions.includes(extension)) {
                    resolve({ valid: false, error: `Format file harus JPG atau PNG. Tipe file terdeteksi: ${file.type}` });
                    return;
                }
            }

            // Try to load as image to verify it's actually a valid image
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = new Image();
                
                img.onload = function() {
                    console.log(`✅ Image validation successful (${isMobileDevice() ? 'Mobile' : 'Desktop'}):`, {
                        width: img.width,
                        height: img.height,
                        size: file.size,
                        type: file.type
                    });
                    resolve({ valid: true });
                };
                
                img.onerror = function() {
                    console.error('Image validation failed - not a valid image');
                    resolve({ valid: false, error: 'File bukan gambar yang valid atau file rusak' });
                };
                
                img.src = e.target.result;
            };
            
            reader.onerror = function() {
                console.error('FileReader error');
                resolve({ valid: false, error: 'Tidak dapat membaca file. File mungkin rusak.' });
            };
            
            // Read as data URL to validate image
            reader.readAsDataURL(file);
        });
    }

    // 🆕 UNIFIED FILE UPLOAD HANDLER - Works for both mobile and desktop
    async function handleFileUpload(event, fieldName) {
        const file = event.target.files[0];
        const validation = fileValidation[fieldName];
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        const error = document.getElementById(`${fieldName}-error`);

        // Reset states
        label.classList.remove('has-file', 'error');
        preview.style.display = 'none';
        error.textContent = '';
        error.classList.remove('show');

        if (!file) {
            label.innerHTML = getDefaultLabelContent(fieldName);
            if (isMobileDevice()) {
                mobileFileStore.files.delete(fieldName);
            }
            return;
        }

        // Show loading state
        if (isMobileDevice()) {
            label.innerHTML = `
                <div class="loading-spinner mr-2"></div>
                <span>📱 Memproses file...</span>
            `;
        } else {
            label.innerHTML = `
                <div class="loading-spinner mr-2"></div>
                <span>Memvalidasi file...</span>
            `;
        }

        try {
            console.log(`🔄 Processing file upload for ${fieldName} (${isMobileDevice() ? 'Mobile' : 'Desktop'})`);
            
            // Validate file
            const validationResult = await validateFile(file, validation);
            
            if (!validationResult.valid) {
                showFileError(fieldName, validationResult.error);
                event.target.value = '';
                return;
            }

            // Store file in mobile store for mobile devices
            if (isMobileDevice()) {
                const stored = mobileFileStore.store(fieldName, file);
                if (!stored) {
                    showFileError(fieldName, 'Gagal menyimpan file di memori mobile. Silakan coba lagi.');
                    event.target.value = '';
                    return;
                }
            }

            // Show preview
            showFilePreview(fieldName, file);
            
            // Log successful validation
            console.log(`✅ File ${fieldName} uploaded successfully (${isMobileDevice() ? 'Mobile' : 'Desktop'}):`, {
                name: file.name,
                type: file.type,
                size: file.size
            });
            
        } catch (error) {
            console.error(`❌ File upload error for ${fieldName}:`, error);
            const errorMsg = isMobileDevice() ? 
                'Terjadi kesalahan saat memproses file di perangkat mobile. Silakan coba lagi.' :
                'Terjadi kesalahan saat memvalidasi file. Silakan coba lagi.';
            showFileError(fieldName, errorMsg);
            event.target.value = '';
        }
    }

    // Enhanced photo upload handler (kept for compatibility)
    async function handlePhotoUpload(event, fieldName) {
        return handleFileUpload(event, fieldName);
    }

    function handleMultipleFileUpload(event, fieldName) {
        const files = Array.from(event.target.files);
        const validation = fileValidation[fieldName];
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        const error = document.getElementById(`${fieldName}-error`);

        // Reset states
        label.classList.remove('has-file', 'error');
        preview.style.display = 'none';
        preview.innerHTML = '';
        error.textContent = '';
        error.classList.remove('show');

        if (files.length === 0) {
            label.innerHTML = getDefaultLabelContent(fieldName);
            return;
        }

        let validFiles = [];
        let errors = [];

        // Process files sequentially to avoid Promise issues
        Promise.all(files.map(async (file, index) => {
            try {
                const validationResult = await validateFile(file, validation);
                if (validationResult.valid) {
                    validFiles.push(file);
                } else {
                    errors.push(`File ${index + 1} (${file.name}): ${validationResult.error}`);
                }
            } catch (error) {
                errors.push(`File ${index + 1} (${file.name}): Gagal memvalidasi`);
            }
        })).then(() => {
            if (errors.length > 0) {
                showFileError(fieldName, errors.join('<br>'));
                event.target.value = '';
                return;
            }

            // Show preview for multiple files
            showMultipleFilePreview(fieldName, validFiles);
        });
    }

    // Enhanced error display with mobile support
    function showFileError(fieldName, errorMessage) {
        const label = document.getElementById(`${fieldName}-label`);
        const error = document.getElementById(`${fieldName}-error`);
        
        label.classList.add('error');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>${isMobileDevice() ? '📱 ' : ''}File tidak valid</span>
        `;
        
        error.innerHTML = `
            <div class="text-red-600 font-medium">${isMobileDevice() ? '📱 Mobile ' : ''}Error:</div>
            <div>${errorMessage}</div>
            ${isMobileDevice() ? `
                <div class="text-xs mt-1 text-blue-600">
                    <strong>💡 Tips Mobile:</strong> ${getMobileTip(fieldName)}
                </div>
            ` : `
                <div class="text-xs mt-1 text-gray-600">
                    ${fieldName === 'photo' ? 
                        'Pastikan file yang Anda upload adalah foto dengan format JPG atau PNG dan ukuran maksimal 2MB.' :
                        'Pastikan file sesuai dengan format yang diminta dan ukuran maksimal 2MB.'
                    }
                </div>
            `}
        `;
        error.classList.add('show');
    }

    function getMobileTip(fieldName) {
        const tips = {
            photo: 'Ambil foto baru menggunakan kamera atau pilih dari galeri. Pastikan file tidak terlalu besar.',
            cv: 'Pastikan file PDF tidak corrupt dan ukuran di bawah 2MB.',
            transcript: 'Scan dokumen dengan jelas dan simpan sebagai PDF.',
            certificates: 'File opsional - abaikan jika tidak ada sertifikat.'
        };
        return tips[fieldName] || 'Pastikan file valid dan tidak corrupt.';
    }

    function showFilePreview(fieldName, file) {
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        label.classList.add('has-file');
        const fileName = isMobileDevice() && file.name.length > 20 ? 
                        file.name.substring(0, 20) + '...' : file.name;
        
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>${isMobileDevice() ? '📱 ' : ''}${fileName}</span>
        `;

        preview.innerHTML = `
            <div class="file-preview-item">
                <div class="file-preview-info">
                    ${getFileIcon(file.type)}
                    <span>${fileName}</span>
                    <span class="file-size">(${formatFileSize(file.size)})</span>
                </div>
                <span class="file-remove" onclick="removeFile('${fieldName}')">×</span>
            </div>
        `;
        preview.style.display = 'block';
    }

    function showMultipleFilePreview(fieldName, files) {
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        label.classList.add('has-file');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>${isMobileDevice() ? '📱 ' : ''}${files.length} file dipilih</span>
        `;

        let previewHtml = '';
        files.forEach((file, index) => {
            const fileName = isMobileDevice() && file.name.length > 15 ? 
                            file.name.substring(0, 15) + '...' : file.name;
            previewHtml += `
                <div class="file-preview-item">
                    <div class="file-preview-info">
                        ${getFileIcon(file.type)}
                        <span>${fileName}</span>
                        <span class="file-size">(${formatFileSize(file.size)})</span>
                    </div>
                    <span class="file-remove" onclick="removeMultipleFile('${fieldName}', ${index})">×</span>
                </div>
            `;
        });
        
        preview.innerHTML = previewHtml;
        preview.style.display = 'block';
    }

    function removeFile(fieldName) {
        const input = document.getElementById(fieldName);
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        input.value = '';
        label.classList.remove('has-file');
        label.innerHTML = getDefaultLabelContent(fieldName);
        preview.style.display = 'none';
        
        // Remove from mobile store
        if (isMobileDevice()) {
            mobileFileStore.files.delete(fieldName);
        }
        
        console.log(`🗑️ File ${fieldName} removed (${isMobileDevice() ? 'Mobile' : 'Desktop'})`);
    }

    function removeMultipleFile(fieldName, indexToRemove) {
        const input = document.getElementById(fieldName);
        const dt = new DataTransfer();
        const files = input.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== indexToRemove) {
                dt.items.add(files[i]);
            }
        }
        
        input.files = dt.files;
        
        if (dt.files.length === 0) {
            removeFile(fieldName);
        } else {
            handleMultipleFileUpload({ target: input }, fieldName);
        }
    }

    function getDefaultLabelContent(fieldName) {
        const mobilePrefix = isMobileDevice() ? '📱 ' : '';
        const contents = {
            cv: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                 </svg>
                 <span>${mobilePrefix}Pilih file PDF</span>`,
            photo: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                 </svg>
                 <span>${mobilePrefix}Pilih file JPG/PNG</span>`,
            transcript: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                     </svg>
                     <span>${mobilePrefix}Pilih file PDF</span>`,
            certificates: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                       </svg>
                       <span>${mobilePrefix}Pilih file PDF (dapat lebih dari 1)</span>`
        };
        return contents[fieldName];
    }

    function getFileIcon(fileType) {
        if (fileType.includes('image')) {
            return `<svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>`;
        } else {
            return `<svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>`;
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form State Preservation (existing code kept unchanged)
    const STORAGE_KEY = 'jobApplicationFormData';
    const form = document.getElementById('applicationForm');
    const saveIndicator = document.getElementById('saveIndicator');
    
    // Required field IDs for validation
    const requiredFields = [
        'position_applied', 'expected_salary', 'full_name', 'email', 'nik',
        'phone_number', 'phone_alternative', 'birth_place', 'birth_date', 'gender', 
        'religion', 'marital_status', 'ethnicity', 'current_address', 
        'current_address_status', 'ktp_address', 'height_cm', 'weight_kg', 
        'motivation', 'strengths', 'weaknesses', 'start_work_date', 
        'information_source', 'cv', 'photo', 'transcript'
    ];
    
    // Save form data dengan OCR status preservation (existing code kept)
    function saveFormData() {
        const formData = new FormData(form);
        const data = {};
        
        // Handle regular inputs (existing code tetap sama)
        for (let [key, value] of formData.entries()) {
            if (!key.includes('cv') && !key.includes('photo') && !key.includes('transcript') && !key.includes('certificates')) {
                if (data[key]) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            }
        }

        // Handle salary formatting (existing code tetap sama)
        const salaryInput = document.getElementById('expected_salary');
        if (salaryInput && data.expected_salary) {
            data.expected_salary = getRawSalaryValue(salaryInput);
        }
        
        // PRESERVE OCR status di localStorage
        const nikField = document.getElementById('nik');
        if (nikField && nikField.readOnly && nikField.classList.contains('ocr-filled')) {
            data.ocr_nik_locked = 'true';
            data.ocr_nik_value = nikField.value;
            console.log('Preserving OCR NIK status in localStorage');
        }

        // Handle checkboxes (existing code tetap sama)
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (!checkbox.name.includes('[]')) {
                data[checkbox.name] = checkbox.checked ? '1' : '0';
            }
        });
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        showSaveIndicator();
    }
    
    // Load form data dengan OCR state restoration (existing code kept)
    function loadFormData() {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (!savedData) return;
        
        try {
            const data = JSON.parse(savedData);
            
            // Restore regular inputs (existing code tetap sama)
            Object.keys(data).forEach(key => {
                const elements = form.querySelectorAll(`[name="${key}"]`);
                
                elements.forEach((element, index) => {
                    if (element.type === 'checkbox') {
                        element.checked = data[key] === '1' || data[key] === true;
                    } else if (element.type === 'radio') {
                        if (Array.isArray(data[key])) {
                            element.checked = data[key].includes(element.value);
                        } else {
                            element.checked = element.value === data[key];
                        }
                    } else if (element.tagName === 'SELECT' || element.type === 'text' || element.type === 'number' || element.type === 'date' || element.type === 'email' || element.tagName === 'TEXTAREA') {
                        if (Array.isArray(data[key])) {
                            element.value = data[key][index] || '';
                        } else {
                            element.value = data[key] || '';
                        }
                    }
                });
            });

            // Handle salary formatting (existing code tetap sama)
            const salaryInput = document.getElementById('expected_salary');
            if (salaryInput && salaryInput.value) {
                const rawValue = salaryInput.value.replace(/\./g, '');
                salaryInput.value = rawValue;
                formatSalary(salaryInput);
            }

            // RESTORE OCR NIK state jika ada
            if (data.ocr_nik_locked === 'true' && data.ocr_nik_value) {
                const nikField = document.getElementById('nik');
                if (nikField) {
                    nikField.value = data.ocr_nik_value;
                    nikField.readOnly = true;
                    nikField.style.backgroundColor = '#ecfdf5';
                    nikField.style.borderColor = '#10b981';
                    nikField.style.color = '#065f46';
                    nikField.classList.add('ocr-filled');
                    
                    console.log('Restored OCR NIK from localStorage:', data.ocr_nik_value);
                    
                    // Remove instruction if exists
                    const existingInstruction = nikField.parentNode.querySelector('.nik-instruction');
                    if (existingInstruction) {
                        existingInstruction.remove();
                    }

                    // Add OCR indicator
                    addOcrIndicator(nikField);
                }
            }

            // Handle checkbox arrays (existing code tetap sama)
            const checkboxArrays = ['driving_licenses'];
            checkboxArrays.forEach(name => {
                if (data[name + '[]'] && Array.isArray(data[name + '[]'])) {
                    data[name + '[]'].forEach(value => {
                        const checkbox = form.querySelector(`input[name="${name}[]"][value="${value}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            });
            
        } catch (e) {
            console.error('Error loading form data:', e);
        }
    }

    // Add OCR indicator to NIK field (existing code kept)
    function addOcrIndicator(nikField) {
        // Remove existing indicator
        const existing = nikField.parentNode.querySelector('.ocr-indicator');
        if (existing) {
            existing.remove();
        }
        
        const indicator = document.createElement('div');
        indicator.className = 'ocr-indicator';
        indicator.innerHTML = `
            <div style="margin-top: 4px; padding: 4px 8px; background: #ecfdf5; border: 1px solid #10b981; 
                        border-radius: 4px; font-size: 12px; color: #065f46; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>NIK terisi otomatis dari scan KTP</span>
            </div>
        `;
        nikField.parentNode.appendChild(indicator);
    }
    
    // Show save indicator (existing code kept)
    function showSaveIndicator() {
        saveIndicator.classList.add('show');
        setTimeout(() => {
            saveIndicator.classList.remove('show');
        }, 2000);
    }
    
    // Show custom alert (enhanced for mobile)
    function showAlert(message, type = 'error') {
        // Use global showAlert if available (defined in blade template)
        if (window.showAlert) {
            window.showAlert(message, type);
            return;
        }
        
        // Fallback implementation
        const alert = document.createElement('div');
        alert.className = `custom-alert ${type}`;
        alert.innerHTML = `
            <div class="font-medium">${type === 'error' ? (isMobileDevice() ? '📱❌ Error!' : 'Error!') : type === 'success' ? '✅ Berhasil!' : '⚠️ Peringatan!'}</div>
            <div class="text-sm mt-1">${message}</div>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, isMobileDevice() ? 8000 : 5000);
    }
    
    // Debounce function (existing code kept)
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Dynamic form functions (existing code kept unchanged)
    let familyIndex = 0;
    let educationIndex = 0;
    let nonFormalEducationIndex = 0;
    let workIndex = 0;
    let languageIndex = 0;
    let socialActivityIndex = 0;
    let achievementIndex = 0;

    // Get default templates (existing code kept unchanged)
    function getDefaultFamilyMember(index) {
        return `
            <div class="dynamic-group" data-index="${index}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                        <select name="family_members[${index}][relationship]" class="form-input" required>
                            <option value="">Pilih Hubungan</option>
                            <option value="Pasangan">Pasangan</option>
                            <option value="Anak">Anak</option>
                            <option value="Ayah">Ayah</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Saudara">Saudara</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama <span class="required-star">*</span></label>
                        <input type="text" name="family_members[${index}][name]" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Usia <span class="required-star">*</span></label>
                        <input type="number" name="family_members[${index}][age]" class="form-input" min="0" max="120" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                        <input type="text" name="family_members[${index}][education]" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                        <input type="text" name="family_members[${index}][occupation]" class="form-input" required>
                    </div>
                    <div class="form-group flex items-end">
                        <button type="button" class="btn-remove" onclick="removeFamilyMember(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus</button>
                    </div>
                </div>
            </div>
        `;
    }

    function getDefaultEducation(index) {
        return `
            <div class="dynamic-group" data-index="${index}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="form-label">Jenjang Pendidikan <span class="required-star">*</span></label>
                        <select name="formal_education[${index}][education_level]" class="form-input" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="SMA/SMK">SMA/SMK</option>
                            <option value="Diploma">Diploma</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Institusi <span class="required-star">*</span></label>
                        <input type="text" name="formal_education[${index}][institution_name]" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan <span class="required-star">*</span></label>
                        <input type="text" name="formal_education[${index}][major]" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun Mulai <span class="required-star">*</span></label>
                        <input type="number" name="formal_education[${index}][start_year]" class="form-input" min="1950" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun Selesai <span class="required-star">*</span></label>
                        <input type="number" name="formal_education[${index}][end_year]" class="form-input" min="1950" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IPK/Nilai <span class="required-star">*</span></label>
                        <input type="number" name="formal_education[${index}][gpa]" class="form-input" step="0.01" min="0" max="4" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeEducation(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus Pendidikan</button>
                </div>
            </div>
        `;
    }

    function getDefaultLanguageSkill(index) {
        return `
            <div class="dynamic-group" data-index="${index}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="form-label">Bahasa <span class="required-star">*</span></label>
                        <select name="language_skills[${index}][language]" class="form-input" required>
                            <option value="">Pilih Bahasa</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="Bahasa Mandarin">Bahasa Mandarin</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kemampuan Berbicara <span class="required-star">*</span></label>
                        <select name="language_skills[${index}][speaking_level]" class="form-input" required>
                            <option value="">Pilih Level</option>
                            <option value="Pemula">Pemula</option>
                            <option value="Menengah">Menengah</option>
                            <option value="Mahir">Mahir</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kemampuan Menulis <span class="required-star">*</span></label>
                        <select name="language_skills[${index}][writing_level]" class="form-input" required>
                            <option value="">Pilih Level</option>
                            <option value="Pemula">Pemula</option>
                            <option value="Menengah">Menengah</option>
                            <option value="Mahir">Mahir</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeLanguageSkill(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus Bahasa</button>
                </div>
            </div>
        `;
    }

    // Make functions global for onclick handlers (existing code kept)
    window.addFamilyMember = function() {
        familyIndex++;
        const container = document.getElementById('familyMembers');
        container.insertAdjacentHTML('beforeend', getDefaultFamilyMember(familyIndex));
        attachEventListeners();
        updateRemoveButtons('familyMembers');
    };

    window.removeFamilyMember = function(button) {
        const container = document.getElementById('familyMembers');
        if (container.children.length > 1) {
            button.closest('.dynamic-group').remove();
            updateRemoveButtons('familyMembers');
            saveFormData();
        } else {
            showAlert('Minimal harus ada 1 anggota keluarga.', 'warning');
        }
    };

    window.addEducation = function() {
        educationIndex++;
        const container = document.getElementById('formalEducation');
        container.insertAdjacentHTML('beforeend', getDefaultEducation(educationIndex));
        attachEventListeners();
        updateRemoveButtons('formalEducation');
    };

    window.removeEducation = function(button) {
        const container = document.getElementById('formalEducation');
        if (container.children.length > 1) {
            button.closest('.dynamic-group').remove();
            updateRemoveButtons('formalEducation');
            saveFormData();
        } else {
            showAlert('Minimal harus ada 1 pendidikan formal.', 'warning');
        }
    };

    window.addLanguageSkill = function() {
        languageIndex++;
        const container = document.getElementById('languageSkills');
        container.insertAdjacentHTML('beforeend', getDefaultLanguageSkill(languageIndex));
        attachEventListeners();
        updateRemoveButtons('languageSkills');
    };

    window.removeLanguageSkill = function(button) {
        const container = document.getElementById('languageSkills');
        if (container.children.length > 1) {
            button.closest('.dynamic-group').remove();
            updateRemoveButtons('languageSkills');
            saveFormData();
        } else {
            showAlert('Minimal harus ada 1 kemampuan bahasa.', 'warning');
        }
    };

    // Optional dynamic functions (existing code kept)
    window.addNonFormalEducation = function() {
        nonFormalEducationIndex++;
        const container = document.getElementById('nonFormalEducation');
        const template = `
            <div class="dynamic-group" data-index="${nonFormalEducationIndex}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nama Kursus/Pelatihan</label>
                        <input type="text" name="non_formal_education[${nonFormalEducationIndex}][course_name]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Penyelenggara</label>
                        <input type="text" name="non_formal_education[${nonFormalEducationIndex}][organizer]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="non_formal_education[${nonFormalEducationIndex}][date]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="non_formal_education[${nonFormalEducationIndex}][description]" class="form-input">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeNonFormalEducation(this)">Hapus</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        attachEventListeners();
    };

    window.removeNonFormalEducation = function(button) {
        button.closest('.dynamic-group').remove();
        saveFormData();
    };

    window.addWorkExperience = function() {
        workIndex++;
        const container = document.getElementById('workExperiences');
        const template = `
            <div class="dynamic-group" data-index="${workIndex}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nama Perusahaan</label>
                        <input type="text" name="work_experiences[${workIndex}][company_name]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Perusahaan</label>
                        <input type="text" name="work_experiences[${workIndex}][company_address]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bergerak di Bidang</label>
                        <input type="text" name="work_experiences[${workIndex}][company_field]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Posisi/Jabatan</label>
                        <input type="text" name="work_experiences[${workIndex}][position]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun Mulai</label>
                        <input type="number" name="work_experiences[${workIndex}][start_year]" class="form-input" min="1950" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun Selesai</label>
                        <input type="number" name="work_experiences[${workIndex}][end_year]" class="form-input" min="1950" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gaji Terakhir</label>
                        <input type="number" name="work_experiences[${workIndex}][salary]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alasan Berhenti</label>
                        <input type="text" name="work_experiences[${workIndex}][reason_for_leaving]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama & No Telp Atasan Langsung</label>
                        <input type="text" name="work_experiences[${workIndex}][supervisor_contact]" class="form-input" 
                               placeholder="contoh: Bpk. Ahmad - 081234567890">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeWorkExperience(this)">Hapus Pengalaman</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        attachEventListeners();
    };

    window.removeWorkExperience = function(button) {
        button.closest('.dynamic-group').remove();
        saveFormData();
    };

    window.addSocialActivity = function() {
        socialActivityIndex++;
        const container = document.getElementById('socialActivities');
        const template = `
            <div class="dynamic-group" data-index="${socialActivityIndex}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nama Organisasi</label>
                        <input type="text" name="social_activities[${socialActivityIndex}][organization_name]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bidang</label>
                        <input type="text" name="social_activities[${socialActivityIndex}][field]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Periode Kepesertaan</label>
                        <input type="text" name="social_activities[${socialActivityIndex}][period]" class="form-input" 
                               placeholder="contoh: 2020-2022">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="social_activities[${socialActivityIndex}][description]" class="form-input">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeSocialActivity(this)">Hapus</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        attachEventListeners();
    };

    window.removeSocialActivity = function(button) {
        button.closest('.dynamic-group').remove();
        saveFormData();
    };

    window.addAchievement = function() {
        achievementIndex++;
        const container = document.getElementById('achievements');
        const template = `
            <div class="dynamic-group" data-index="${achievementIndex}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="form-label">Prestasi</label>
                        <input type="text" name="achievements[${achievementIndex}][achievement]" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tahun</label>
                        <input type="number" name="achievements[${achievementIndex}][year]" class="form-input" min="1950" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="achievements[${achievementIndex}][description]" class="form-input">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn-remove" onclick="removeAchievement(this)">Hapus</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        attachEventListeners();
    };

    window.removeAchievement = function(button) {
        button.closest('.dynamic-group').remove();
        saveFormData();
    };

    // File removal functions (make global)
    window.removeFile = removeFile;
    window.removeMultipleFile = removeMultipleFile;

    // Address copy functionality (existing code kept)
    function initializeAddressCopy() {
        const copyCheckbox = document.getElementById('copy_ktp_address');
        const currentAddressField = document.getElementById('current_address');
        const ktpAddressField = document.getElementById('ktp_address');

        if (copyCheckbox && currentAddressField && ktpAddressField) {
            copyCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    currentAddressField.value = ktpAddressField.value;
                    currentAddressField.setAttribute('readonly', true);
                    currentAddressField.style.backgroundColor = '#f3f4f6';
                    currentAddressField.style.color = '#6b7280';
                    saveFormData();
                } else {
                    currentAddressField.removeAttribute('readonly');
                    currentAddressField.style.backgroundColor = '';
                    currentAddressField.style.color = '';
                    currentAddressField.value = '';
                    currentAddressField.focus();
                    saveFormData();
                }
            });

            ktpAddressField.addEventListener('input', function() {
                if (copyCheckbox.checked) {
                    currentAddressField.value = this.value;
                    saveFormData();
                }
            });
        }
    }

    // Salary formatting functions (existing code kept)
    function formatSalary(input) {
        const cursorPosition = input.selectionStart;
        const oldValue = input.value;
        
        let value = input.value.replace(/\D/g, '');
        
        if (value) {
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        input.value = value;
        
        const newDots = (value.match(/\./g) || []).length;
        const oldDots = (oldValue.match(/\./g) || []).length;
        const dotDifference = newDots - oldDots;
        
        const newCursorPosition = cursorPosition + dotDifference;
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }

    function unformatSalary(input) {
        input.value = input.value.replace(/\./g, '');
    }

    function getRawSalaryValue(input) {
        return input.value.replace(/\./g, '');
    }

    // Enhanced duplicate checking system (existing code kept)
    const duplicateChecker = {
        email: {
            timeout: null,
            isChecking: false,
            lastChecked: null
        },
        nik: {
            timeout: null,
            isChecking: false,
            lastChecked: null
        }
    };

    function checkDuplicate(fieldType, value, callback) {
        const checker = duplicateChecker[fieldType];
        if (checker.timeout) clearTimeout(checker.timeout);
        if (checker.lastChecked === value) return;
        checker.timeout = setTimeout(async () => {
            if (checker.isChecking) return;
            checker.isChecking = true;
            checker.lastChecked = value;
            try {
                const response = await fetch(`/check-${fieldType}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ [fieldType]: value })
                });
                const result = await response.json();
                callback(result);
            } catch (error) {
                console.error(`Error checking ${fieldType}:`, error);
                callback({ exists: false, message: 'Duplikasi status: 0' });
            } finally {
                checker.isChecking = false;
            }
        }, 1000);
    }

    function showDuplicateStatus(fieldId, result, isValid = true) {
        const input = document.getElementById(fieldId);
        const existingStatus = input.parentNode.querySelector('.duplicate-status');
        if (existingStatus) existingStatus.remove();
        const statusElement = document.createElement('div');
        statusElement.className = 'duplicate-status text-xs mt-1 flex items-center';
        if (result.exists) {
            statusElement.className += ' text-red-600';
            statusElement.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                ${result.message}
            `;
            input.classList.add('error');
        } else if (isValid && result.message !== 'Email tidak valid' && result.message !== 'NIK harus 16 digit angka') {
            statusElement.className += ' text-green-600';
            statusElement.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                ${result.message}
            `;
            input.classList.remove('error');
        } else {
            statusElement.className += ' text-gray-500';
            statusElement.innerHTML = result.message;
            input.classList.remove('error');
        }
        input.parentNode.appendChild(statusElement);
    }

    function enhanceEmailValidation() {
        const emailInput = document.getElementById('email');
        if (!emailInput) return;
        emailInput.addEventListener('input', function(e) {
            const email = e.target.value.trim();
            e.target.classList.remove('error');
            if (email.length === 0) {
                const status = e.target.parentNode.querySelector('.duplicate-status');
                if (status) status.remove();
                return;
            }
            if (!isValidEmail(email)) {
                showDuplicateStatus('email', { exists: false, message: 'Format email tidak valid' }, false);
                return;
            }
            checkDuplicate('email', email, (result) => {
                showDuplicateStatus('email', result, true);
            });
        });
    }

    function enhanceNikValidation() {
        const nikInput = document.getElementById('nik');
        if (!nikInput) return;

        nikInput.addEventListener('input', function(e) {
            const nik = e.target.value.trim();
            e.target.classList.remove('error');
            const existingError = e.target.parentNode.querySelector('.nik-error');
            if (existingError) existingError.remove();

            if (nik.length === 0) {
                const status = e.target.parentNode.querySelector('.duplicate-status');
                if (status) status.remove();
                return;
            }

            if (nik.length !== 16 || !/^[0-9]{16}$/.test(nik)) {
                showDuplicateStatus('nik', { exists: false, message: 'NIK harus 16 digit angka' }, false);
                return;
            }

            checkDuplicate('nik', nik, (result) => {
                showDuplicateStatus('nik', result, true);
            });
        });
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function enhanceFormSubmissionValidation() {
        const form = document.getElementById('applicationForm');
        if (!form) return;
        const originalHandler = form.onsubmit;
        form.addEventListener('submit', async function(e) {
            const emailDuplicateStatus = document.querySelector('#email').parentNode.querySelector('.duplicate-status');
            const nikDuplicateStatus = document.querySelector('#nik').parentNode.querySelector('.duplicate-status');
            let hasDuplicates = false;
            let duplicateErrors = [];
            if (emailDuplicateStatus && emailDuplicateStatus.classList.contains('text-red-600')) {
                hasDuplicates = true;
                duplicateErrors.push('Email sudah terdaftar dalam sistem');
            }
            if (nikDuplicateStatus && nikDuplicateStatus.classList.contains('text-red-600')) {
                hasDuplicates = true;
                duplicateErrors.push('NIK sudah terdaftar dalam sistem');
            }
            if (hasDuplicates) {
                e.preventDefault();
                showAlert(
                    'Tidak dapat mengirim lamaran:<br>' + duplicateErrors.join('<br>'),
                    'error'
                );
                const firstError = document.querySelector('.duplicate-status.text-red-600');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            if (originalHandler) {
                return originalHandler.call(this, e);
            }
        });
    }

    function attachEventListeners() {
        const newInputs = form.querySelectorAll('input:not([type="file"]):not(.listener-attached), select:not(.listener-attached), textarea:not(.listener-attached)');
        
        newInputs.forEach(input => {
            if (!input.classList.contains('listener-attached')) {
                input.addEventListener('change', function() {
                    saveFormData();
                });
                input.addEventListener('input', debounce(function() {
                    saveFormData();
                }, 1000));
                input.classList.add('listener-attached');
            }
        });
    }

    function updateRemoveButtons(containerId) {
        const container = document.getElementById(containerId);
        const removeButtons = container.querySelectorAll('.btn-remove');
        
        removeButtons.forEach((button, index) => {
            if (index === 0 && container.children.length > 1) {
                button.style.display = 'none';
            } else if (index > 0) {
                button.style.display = 'inline-block';
            }
        });
    }

    function cleanEmptyOptionalFields() {
        const optionalContainers = [
            'nonFormalEducation', 
            'workExperiences', 
            'socialActivities', 
            'achievements'
        ];
        
        optionalContainers.forEach(containerId => {
            const container = document.getElementById(containerId);
            if (container) {
                const groups = container.querySelectorAll('.dynamic-group');
                groups.forEach(group => {
                    const inputs = group.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], select, textarea');
                    const isEmpty = Array.from(inputs).every(input => !input.value || input.value.trim() === '');
                    
                    if (isEmpty) {
                        group.remove();
                    }
                });
            }
        });
    }

    function initializeNikField() {
        const nikField = document.getElementById('nik');
        if (!nikField) return;

        const ocrValidated = sessionStorage.getItem('nik_locked') === 'true';
        const savedNikValue = sessionStorage.getItem('extracted_nik');
        
        if (ocrValidated && savedNikValue) {
            nikField.value = savedNikValue;
            nikField.readOnly = true;
            nikField.style.backgroundColor = '#ecfdf5';
            nikField.style.borderColor = '#10b981';
            nikField.style.color = '#065f46';
            nikField.classList.add('ocr-filled');
            addOcrIndicator(nikField);
            console.log('NIK field restored from OCR session:', savedNikValue);
        } else {
            nikField.readOnly = false;
            nikField.style.backgroundColor = '';
            nikField.style.color = '';
            nikField.placeholder = 'Masukkan NIK 16 digit atau gunakan scan KTP';
            
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
    }

    // 🆕 ENHANCED MOBILE FILE VALIDATION FOR FORM SUBMISSION
    async function validateMobileFilesForSubmission() {
        if (!isMobileDevice()) return { hasErrors: false, errors: [] };
        
        console.log('📱 Mobile: Validating files for form submission');
        
        const fileFields = ['cv', 'photo', 'transcript'];
        let hasFileErrors = false;
        let fileErrors = [];

        for (const fieldName of fileFields) {
            const input = document.getElementById(fieldName);
            const validation = fileValidation[fieldName];
            
            if (!input.files.length && validation.required) {
                hasFileErrors = true;
                fileErrors.push(`${fieldName.toUpperCase()}: File harus diupload`);
                continue;
            }
            
            if (input.files.length > 0) {
                // Check mobile store validation
                const storedValidation = mobileFileStore.validateStored(fieldName);
                
                if (!storedValidation.valid) {
                    console.error(`📱 Mobile: File ${fieldName} validation failed:`, storedValidation.error);
                    hasFileErrors = true;
                    fileErrors.push(`${fieldName.toUpperCase()}: ${storedValidation.error}. Silakan pilih file lagi.`);
                    
                    // Reset the file input
                    const label = document.getElementById(`${fieldName}-label`);
                    const preview = document.getElementById(`${fieldName}-preview`);
                    label.classList.remove('has-file');
                    label.classList.add('error');
                    preview.style.display = 'none';
                    input.value = '';
                    
                    continue;
                }
                
                // Re-validate the file
                const file = storedValidation.file;
                const validationResult = await validateFile(file, validation);
                
                if (!validationResult.valid) {
                    hasFileErrors = true;
                    fileErrors.push(`${fieldName.toUpperCase()}: ${validationResult.error}`);
                    showFileError(fieldName, validationResult.error);
                }
            }
        }

        return { hasErrors: hasFileErrors, errors: fileErrors };
    }

    // 🆕 MAKE FUNCTIONS GLOBALLY AVAILABLE
    window.fileValidation = fileValidation;
    window.handleMultipleFileUpload = handleMultipleFileUpload;
    window.isMobileDevice = isMobileDevice;
    window.mobileFileStore = mobileFileStore;

    // MAIN INITIALIZATION
    document.addEventListener('DOMContentLoaded', function() {
        console.log(`🚀 Initializing form system (${isMobileDevice() ? 'Mobile' : 'Desktop'})`);
        
        loadFormData();
        initializeAddressCopy();
        initializeNikField();
        
        // Add event listeners for auto-save
        const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                saveFormData();
            });
            input.addEventListener('input', debounce(function() {
                saveFormData();
            }, 1000));
        });

        // Initialize file upload handlers with mobile support
        document.getElementById('cv').addEventListener('change', function(e) {
            handleFileUpload(e, 'cv');
        });

        document.getElementById('photo').addEventListener('change', function(e) {
            handlePhotoUpload(e, 'photo');
        });

        document.getElementById('transcript').addEventListener('change', function(e) {
            handleFileUpload(e, 'transcript');
        });

        document.getElementById('certificates').addEventListener('change', function(e) {
            handleMultipleFileUpload(e, 'certificates');
        });

        // Initialize salary formatting
        const salaryInput = document.getElementById('expected_salary');
        if (salaryInput) {
            salaryInput.addEventListener('input', function(e) {
                formatSalary(e.target);
                
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    saveFormData();
                }, 1000);
            });
            
            if (salaryInput.value) {
                formatSalary(salaryInput);
            }
            
            salaryInput.form.addEventListener('submit', function(e) {
                const rawValue = getRawSalaryValue(salaryInput);
                salaryInput.value = rawValue;
            });
        }

        // Enhanced validation
        enhanceNikValidation();
        enhanceEmailValidation();
        enhanceFormSubmissionValidation();

        // Initialize remove button states
        updateRemoveButtons('familyMembers');
        updateRemoveButtons('formalEducation');
        updateRemoveButtons('languageSkills');

        // 🆕 ENHANCED FORM VALIDATION with Mobile Support
        document.getElementById('applicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Unformat salary before validation and submission
            const salaryInput = document.getElementById('expected_salary');
            if (salaryInput) {
                const rawValue = getRawSalaryValue(salaryInput);
                salaryInput.value = rawValue;
                console.log('Raw salary value for submission:', rawValue);
            }

            let errors = [];
            let hasError = false;

            // Clean empty optional fields first
            cleanEmptyOptionalFields();

            // Reset all field styles
            form.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Check required basic fields
            requiredFields.forEach(fieldId => {
                const input = document.getElementById(fieldId);
                if (input && (!input.value || input.value.trim() === '')) {
                    hasError = true;
                    input.classList.add('error');
                    if (input.type === 'file') {
                        document.getElementById(`${fieldId}-label`).classList.add('error');
                    }
                    errors.push(`${input.previousElementSibling.textContent.replace(' *', '')} harus diisi`);
                }
            });
            
            // Enhanced date validation
            const startWorkDate = document.getElementById('start_work_date');
            if (startWorkDate && startWorkDate.value) {
                console.log('Validating start_work_date:', startWorkDate.value);
                
                const selectedDateParts = startWorkDate.value.split('-');
                if (selectedDateParts.length === 3) {
                    const selectedDate = new Date(
                        parseInt(selectedDateParts[0]),
                        parseInt(selectedDateParts[1]) - 1,
                        parseInt(selectedDateParts[2])
                    );
                    
                    const today = new Date();
                    today.setHours(23, 59, 59, 999);
                    
                    if (selectedDate <= today) {
                        hasError = true;
                        startWorkDate.classList.add('error');
                        const todayStr = new Date().toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit', 
                            year: 'numeric'
                        });
                        errors.push(`Tanggal mulai kerja harus setelah ${todayStr}`);
                    }
                } else {
                    hasError = true;
                    startWorkDate.classList.add('error');
                    errors.push('Format tanggal mulai kerja tidak valid');
                }
            }

            // Validate dynamic sections
            const familyContainer = document.getElementById('familyMembers');
            const educationContainer = document.getElementById('formalEducation');
            const languageContainer = document.getElementById('languageSkills');
            
            if (familyContainer.children.length === 0) {
                hasError = true;
                errors.push('Data keluarga minimal harus diisi 1 anggota');
            }
            
            if (educationContainer.children.length === 0) {
                hasError = true;
                errors.push('Pendidikan formal minimal harus diisi 1 pendidikan');
            }
            
            if (languageContainer.children.length === 0) {
                hasError = true;
                errors.push('Kemampuan bahasa minimal harus diisi 1 bahasa');
            }
            
            // Validate required fields in dynamic sections
            [
                {container: familyContainer, name: 'Data Keluarga'},
                {container: educationContainer, name: 'Pendidikan Formal'},
                {container: languageContainer, name: 'Kemampuan Bahasa'}
            ].forEach(section => {
                Array.from(section.container.children).forEach((group, index) => {
                    const requiredInputs = group.querySelectorAll('input[required], select[required]');
                    requiredInputs.forEach(input => {
                        if (!input.value || input.value.trim() === '') {
                            hasError = true;
                            input.classList.add('error');
                            errors.push(`${section.name} #${index + 1}: ${input.previousElementSibling.textContent.replace(' *', '')} harus diisi`);
                        }
                    });
                });
            });

            // Check agreement
            const agreementCheckbox = document.querySelector('input[name="agreement"]');
            if (!agreementCheckbox.checked) {
                hasError = true;
                errors.push('Anda harus menyetujui pernyataan untuk melanjutkan');
            }

            // 🆕 ENHANCED FILE VALIDATION with Mobile Support
            const fileInputs = ['cv', 'photo', 'transcript'];
            
            // Mobile-specific file validation
            if (isMobileDevice()) {
                const mobileFileValidation = await validateMobileFilesForSubmission();
                if (mobileFileValidation.hasErrors) {
                    hasError = true;
                    errors.push(...mobileFileValidation.errors);
                }
            } else {
                // Desktop file validation
                for (const fieldName of fileInputs) {
                    const input = document.getElementById(fieldName);
                    if (input && input.files.length > 0) {
                        const file = input.files[0];
                        const validation = fileValidation[fieldName];
                        
                        try {
                            const validationResult = await validateFile(file, validation);
                            
                            if (!validationResult.valid) {
                                hasError = true;
                                errors.push(`${fieldName.toUpperCase()}: ${validationResult.error}`);
                                showFileError(fieldName, validationResult.error);
                            }
                        } catch (error) {
                            hasError = true;
                            errors.push(`${fieldName.toUpperCase()}: Gagal memvalidasi file`);
                            console.error(`Validation error for ${fieldName}:`, error);
                        }
                    } else if (fileValidation[fieldName].required) {
                        hasError = true;
                        errors.push(`${fieldName.toUpperCase()}: File harus diupload`);
                    }
                }
            }
            
            if (hasError) {
                // Re-format salary if there are errors for user experience
                if (salaryInput && salaryInput.value) {
                    formatSalary(salaryInput);
                }
                
                let errorMessage = `${isMobileDevice() ? '📱 ' : ''}Harap lengkapi data berikut:\n\n`;
                errors.slice(0, 10).forEach((error, index) => {
                    errorMessage += `${index + 1}. ${error}\n`;
                });
                if (errors.length > 10) {
                    errorMessage += `\n... dan ${errors.length - 10} field lainnya`;
                }
                
                if (isMobileDevice()) {
                    errorMessage += '\n\n💡 Tips Mobile: Pastikan semua file telah dipilih dengan benar dan tidak hilang dari memori perangkat.';
                }
                
                showAlert(errorMessage.replace(/\n/g, '<br>'), 'error');
                
                // Scroll to first error
                const firstError = form.querySelector('.form-input.error, .file-upload-label.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (firstError.classList.contains('form-input')) {
                        firstError.focus();
                    }
                }
            } else {
                // Disable submit button to prevent double submission
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="loading-spinner mr-2"></span> ${isMobileDevice() ? '📱 Mengirim...' : 'Mengirim...'}`;
                
                console.log(`✅ All validations passed, submitting form (${isMobileDevice() ? 'Mobile' : 'Desktop'})`);
                this.submit();
            }
        });

        // 🆕 ADD MOBILE NOTICE if mobile device detected
        if (isMobileDevice()) {
            const mobileNotice = document.createElement('div');
            mobileNotice.className = 'mobile-notice';
            mobileNotice.innerHTML = `
                <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 14px; color: #065f46;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span>📱</span>
                        <strong>Mode Mobile Terdeteksi - Sistem Dioptimalkan</strong>
                    </div>
                    <ul style="margin-left: 20px; line-height: 1.5;">
                        <li>✅ Upload file dioptimalkan untuk mobile</li>
                        <li>📁 File disimpan aman di memori perangkat</li>
                        <li>🔄 Sistem backup otomatis untuk mencegah file hilang</li>
                        <li>💡 Validasi file dipermudah untuk kompatibilitas mobile</li>
                    </ul>
                </div>
            `;
            
            const uploadSection = document.querySelector('[data-section="9"]');
            if (uploadSection) {
                uploadSection.insertBefore(mobileNotice, uploadSection.children[1]);
            }
        }

        console.log(`✅ Form system initialized successfully (${isMobileDevice() ? 'Mobile' : 'Desktop'})`);
    });

})();