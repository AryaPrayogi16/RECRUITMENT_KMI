// Form Application Script - Enhanced with Default NIK Lock Support
(function() {
    'use strict';

    // Check if form was successfully submitted (for clearing localStorage)
    if (typeof formSubmitted !== 'undefined' && formSubmitted) {
        localStorage.removeItem('jobApplicationFormData');
    }

    // Enhanced File Upload System dengan validasi foto yang diperbaiki
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

    // 🆕 NEW: Browser detection function
    function getBrowserInfo() {
        const userAgent = navigator.userAgent;
        
        const isChrome = /Chrome/.test(userAgent) && !/Edge|Edg/.test(userAgent);
        const isSafari = /Safari/.test(userAgent) && !/Chrome/.test(userAgent);
        const isEdge = /Edge|Edg/.test(userAgent);
        const isFirefox = /Firefox/.test(userAgent);
        const isMobile = /Mobile|Android|iPhone|iPad/.test(userAgent);
        
        let browserName = 'Unknown';
        if (isChrome) browserName = 'Chrome';
        else if (isSafari) browserName = 'Safari';
        else if (isEdge) browserName = 'Edge';
        else if (isFirefox) browserName = 'Firefox';
        
        return {
            name: browserName,
            isChrome,
            isSafari,
            isEdge,
            isFirefox,
            isMobile,
            userAgent
        };
    }

    // 🔧 CRITICAL FIX: Enhanced file validation function - CHROME/SAFARI OPTIMIZED
    async function validateFile(file, validation) {
        console.log('🔍 Validating file for Chrome/Safari:', {
            name: file.name,
            type: file.type,
            size: file.size,
            lastModified: file.lastModified,
            browser: getBrowserInfo()
        });

        // Check if file is valid
        if (!file || file.size === 0) {
            return { valid: false, error: 'File tidak valid atau kosong' };
        }

        // Get file extension
        const extension = file.name.toLowerCase().split('.').pop();
        
        // Check file extension first (PRIORITY for Chrome/Safari)
        if (!validation.extensions.includes(extension)) {
            const allowedExtensions = validation.extensions.join(', ').toUpperCase();
            return { valid: false, error: `Format file harus ${allowedExtensions}. File Anda: ${extension.toUpperCase()}` };
        }

        // Check file size
        if (file.size > validation.maxSize) {
            return { valid: false, error: 'Ukuran file maksimal 2MB' };
        }

        // 🆕 CRITICAL: For photo files, use Chrome/Safari optimized validation
        if (validation.extensions.includes('jpg') || validation.extensions.includes('jpeg') || validation.extensions.includes('png')) {
            return await validateImageFileForBrowsers(file, validation);
        }

        // 🆕 RELAXED: For non-image files, be more flexible with MIME types
        const browserInfo = getBrowserInfo();
        if (browserInfo.isChrome || browserInfo.isSafari) {
            console.log(`📝 ${browserInfo.name}: Using relaxed MIME validation for non-image files`);
            
            // For Chrome/Safari, if extension is correct, don't fail on MIME type
            if (validation.extensions.includes(extension)) {
                console.log(`✅ ${browserInfo.name}: Accepting file based on extension:`, extension);
                return { valid: true };
            }
        }

        // Standard MIME type check for other browsers
        if (!validation.types.includes(file.type)) {
            console.warn('MIME type mismatch:', {
                detected: file.type,
                allowed: validation.types
            });
            const allowedExtensions = validation.extensions.join(', ').toUpperCase();
            return { valid: false, error: `Format file harus ${allowedExtensions}. Tipe file terdeteksi: ${file.type}` };
        }

        return { valid: true };
    }

    // 🔧 CRITICAL FIX: Enhanced image validation for Chrome/Safari
    function validateImageFileForBrowsers(file, validation) {
        return new Promise((resolve) => {
            const extension = file.name.toLowerCase().split('.').pop();
            const browserInfo = getBrowserInfo();
            
            console.log(`📷 Image validation for ${browserInfo.name}:`, {
                name: file.name,
                type: file.type,
                size: file.size,
                extension: extension,
                browser: browserInfo.name,
                is_mobile: browserInfo.isMobile
            });
            
            // Extension check first (CRITICAL for Chrome/Safari)
            if (!validation.extensions.includes(extension)) {
                resolve({ valid: false, error: `Format file harus JPG atau PNG. File Anda: ${extension.toUpperCase()}` });
                return;
            }

            // 🆕 CHROME/SAFARI SPECIFIC: Very relaxed MIME type handling
            if (browserInfo.isChrome || browserInfo.isSafari) {
                console.log(`📷 ${browserInfo.name}: Using browser-optimized image validation`);
                
                // Size check
                const maxSize = validation.maxSize || (2 * 1024 * 1024);
                if (file.size > maxSize) {
                    resolve({ valid: false, error: 'Ukuran file terlalu besar (maksimal 2MB)' });
                    return;
                }
                
                if (file.size === 0) {
                    resolve({ valid: false, error: 'File kosong atau corrupt' });
                    return;
                }
                
                // 🔧 CRITICAL: Accept ANY MIME type for Chrome/Safari if extension is correct
                const chromeAllowedMimeTypes = [
                    'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                    'image/pjpeg', 'image/x-png', 'image/heic', 'image/heif',
                    'application/octet-stream', // Chrome sometimes uses this
                    'binary/octet-stream',     // Safari sometimes uses this
                    '', // Empty MIME type
                    null, // Null MIME type
                    undefined // Undefined MIME type
                ];
                
                // For Chrome/Safari, MIME type is not critical if extension is correct
                if (file.type && !chromeAllowedMimeTypes.includes(file.type)) {
                    console.warn(`📷 ${browserInfo.name}: Unexpected MIME type (${file.type}), but accepting based on extension`);
                }
                
                // 🆕 OPTIONAL: Try image validation, but don't fail if it doesn't work
                if (window.FileReader && !browserInfo.isMobile) {
                    try {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = new Image();
                            img.onload = function() {
                                console.log(`✅ ${browserInfo.name}: Image validation successful`, {
                                    width: img.width,
                                    height: img.height,
                                    file_size: file.size
                                });
                                resolve({ valid: true });
                            };
                            img.onerror = function() {
                                console.warn(`📷 ${browserInfo.name}: Image load failed, but accepting based on extension and size`);
                                // Don't fail - accept based on extension for Chrome/Safari
                                resolve({ valid: true });
                            };
                            img.src = e.target.result;
                        };
                        reader.onerror = function() {
                            console.warn(`📷 ${browserInfo.name}: FileReader failed, but accepting based on extension and size`);
                            // Don't fail - accept based on extension for Chrome/Safari
                            resolve({ valid: true });
                        };
                        reader.readAsDataURL(file);
                    } catch (error) {
                        console.warn(`📷 ${browserInfo.name}: Image validation exception, but accepting:`, error.message);
                        resolve({ valid: true });
                    }
                } else {
                    // For mobile or when FileReader is not available
                    console.log(`📷 ${browserInfo.name}: Skipping image validation, accepting based on extension and size`);
                    resolve({ valid: true });
                }
                
                return;
            }

            // 🔧 STANDARD: For other browsers (Firefox, Edge, etc.)
            console.log(`📷 ${browserInfo.name}: Using standard image validation`);
            
            // Check MIME type for non-Chrome/Safari browsers
            if (!validation.types.includes(file.type)) {
                console.warn(`📷 ${browserInfo.name}: MIME type mismatch:`, {
                    detected: file.type,
                    allowed: validation.types
                });
                
                // If extension is correct but MIME type is wrong, try to validate anyway
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
                    console.log(`✅ ${browserInfo.name}: Standard image validation successful:`, {
                        width: img.width,
                        height: img.height,
                        size: file.size,
                        type: file.type
                    });
                    resolve({ valid: true });
                };
                
                img.onerror = function() {
                    console.error(`❌ ${browserInfo.name}: Image validation failed - not a valid image`);
                    resolve({ valid: false, error: 'File bukan gambar yang valid atau file rusak' });
                };
                
                img.src = e.target.result;
            };
            
            reader.onerror = function() {
                console.error(`❌ ${browserInfo.name}: FileReader error`);
                resolve({ valid: false, error: 'Tidak dapat membaca file. File mungkin rusak.' });
            };
            
            // Read as data URL to validate image
            reader.readAsDataURL(file);
        });
    }

    // Standard file upload handler
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
            return;
        }

        try {
            // Validate file
            const validationResult = await validateFile(file, validation);
            
            if (!validationResult.valid) {
                showFileError(fieldName, validationResult.error);
                event.target.value = '';
                return;
            }

            // Show preview
            showFilePreview(fieldName, file);
        } catch (error) {
            console.error('File validation error:', error);
            showFileError(fieldName, 'Terjadi kesalahan saat memvalidasi file. Silakan coba lagi.');
            event.target.value = '';
        }
    }

    // Enhanced photo upload handler
    async function handlePhotoUpload(event, fieldName) {
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
            return;
        }

        // Show loading state
        label.innerHTML = `
            <div class="loading-spinner mr-2"></div>
            <span>Memvalidasi foto...</span>
        `;

        try {
            // Debug file info
            console.log('Photo upload debug:', {
                name: file.name,
                type: file.type,
                size: file.size,
                lastModified: new Date(file.lastModified).toISOString()
            });

            // Validate file (this returns a Promise for images)
            const validationResult = await validateFile(file, validation);
            
            if (!validationResult.valid) {
                showFileError(fieldName, validationResult.error);
                event.target.value = '';
                return;
            }

            // Show preview
            showFilePreview(fieldName, file);
            
            // Log successful validation
            console.log('Photo validation successful:', {
                name: file.name,
                type: file.type,
                size: file.size
            });
            
        } catch (error) {
            console.error('Photo validation error:', error);
            showFileError(fieldName, 'Terjadi kesalahan saat memvalidasi file. Silakan coba lagi.');
            event.target.value = '';
        }
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

    // Enhanced error display
    function showFileError(fieldName, errorMessage) {
        const label = document.getElementById(`${fieldName}-label`);
        const error = document.getElementById(`${fieldName}-error`);
        
        label.classList.add('error');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>File tidak valid</span>
        `;
        
        error.innerHTML = `
            <div class="text-red-600 font-medium">Error:</div>
            <div>${errorMessage}</div>
            <div class="text-xs mt-1 text-gray-600">
                ${fieldName === 'photo' ? 
                    'Pastikan file yang Anda upload adalah foto dengan format JPG atau PNG dan ukuran maksimal 2MB.' :
                    'Pastikan file sesuai dengan format yang diminta dan ukuran maksimal 2MB.'
                }
            </div>
        `;
        error.classList.add('show');
    }

    function showFilePreview(fieldName, file) {
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        label.classList.add('has-file');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>File dipilih</span>
        `;

        preview.innerHTML = `
            <div class="file-preview-item">
                <div class="file-preview-info">
                    ${getFileIcon(file.type)}
                    <span>${file.name}</span>
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
            <span>${files.length} file dipilih</span>
        `;

        let previewHtml = '';
        files.forEach((file, index) => {
            previewHtml += `
                <div class="file-preview-item">
                    <div class="file-preview-info">
                        ${getFileIcon(file.type)}
                        <span>${file.name}</span>
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
        const contents = {
            cv: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                 </svg>
                 <span>Pilih file PDF</span>`,
            photo: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                 </svg>
                 <span>Pilih file JPG/PNG</span>`,
            transcript: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                     </svg>
                     <span>Pilih file PDF</span>`,
            certificates: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                       </svg>
                       <span>Pilih file PDF (dapat lebih dari 1)</span>`
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

    // Form State Preservation
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
    
    // 🆕 UPDATED: Save form data with improved NIK handling
    function saveFormData() {
        const formData = new FormData(form);
        const data = {};
        
        // Handle regular inputs
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

        // Handle salary formatting
        const salaryInput = document.getElementById('expected_salary');
        if (salaryInput && data.expected_salary) {
            data.expected_salary = getRawSalaryValue(salaryInput);
        }
        
        // 🆕 NEW: Preserve NIK field state for form consistency
        const nikField = document.getElementById('nik');
        if (nikField) {
            // Check if field is locked (any type)
            if (nikField.readOnly) {
                data.nik_field_locked = 'true';
                data.nik_field_source = 'locked';
                
                // Check if it's OCR or manual lock
                if (nikField.classList.contains('ocr-filled')) {
                    data.nik_field_source = 'ocr';
                } else if (nikField.classList.contains('manual-input')) {
                    data.nik_field_source = 'manual';
                }
                
                console.log('Preserving NIK locked state in localStorage:', {
                    locked: true,
                    source: data.nik_field_source,
                    value: nikField.value
                });
            } else {
                data.nik_field_locked = 'false';
                data.nik_field_source = 'open';
            }
        }

        // Handle checkboxes
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (!checkbox.name.includes('[]')) {
                data[checkbox.name] = checkbox.checked ? '1' : '0';
            }
        });
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        showSaveIndicator();
    }
    
    // 🆕 UPDATED: Load form data with NIK field state restoration
    function loadFormData() {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (!savedData) return;
        
        try {
            const data = JSON.parse(savedData);
            
            // Restore regular inputs
            Object.keys(data).forEach(key => {
                // Skip NIK field state keys during regular restoration
                if (key.startsWith('nik_field_')) return;
                
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

            // Handle salary formatting
            const salaryInput = document.getElementById('expected_salary');
            if (salaryInput && salaryInput.value) {
                const rawValue = salaryInput.value.replace(/\./g, '');
                salaryInput.value = rawValue;
                formatSalary(salaryInput);
            }

            // 🆕 NEW: Handle NIK field state restoration (but respect current OCR state)
            const nikField = document.getElementById('nik');
            if (nikField && data.nik_field_locked && data.nik_field_source) {
                // Only restore if no active OCR session exists
                const hasActiveOCRSession = sessionStorage.getItem('nik_locked') === 'true' || 
                                          sessionStorage.getItem('extracted_nik');
                
                if (!hasActiveOCRSession) {
                    console.log('Restoring NIK field state from localStorage:', {
                        locked: data.nik_field_locked,
                        source: data.nik_field_source
                    });
                    
                    // The NIK field will be handled by KTP OCR initialization
                    // We don't override OCR state here
                } else {
                    console.log('OCR session active - skipping localStorage NIK state restoration');
                }
            }

            // Handle checkbox arrays
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
    
    // Show save indicator
    function showSaveIndicator() {
        saveIndicator.classList.add('show');
        setTimeout(() => {
            saveIndicator.classList.remove('show');
        }, 2000);
    }
    
    // Show custom alert
    function showAlert(message, type = 'error') {
        const alert = document.createElement('div');
        alert.className = `custom-alert ${type}`;
        alert.innerHTML = `
            <div class="font-medium">${type === 'error' ? 'Error!' : type === 'success' ? 'Berhasil!' : 'Peringatan!'}</div>
            <div class="text-sm mt-1">${message}</div>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // Debounce function
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
    
    // Dynamic form functions
    let familyIndex = 3;
    let educationIndex = 0;
    let nonFormalEducationIndex = 0;
    let workIndex = 0;
    let languageIndex = 0;
    let socialActivityIndex = 0;
    let achievementIndex = 0;

    // Get default templates
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
                        <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
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

    // Make functions global for onclick handlers
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

    // Optional dynamic functions
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

    // Address copy functionality
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
                    currentAddressField.value = ''; // Clear the field when unchecked
                    currentAddressField.focus();
                    saveFormData();
                }
            });

            // Update current address when KTP address changes (if copy is checked)
            ktpAddressField.addEventListener('input', function() {
                if (copyCheckbox.checked) {
                    currentAddressField.value = this.value;
                    saveFormData();
                }
            });
        }
    }

    // Salary formatting functions yang diperbaiki
    function formatSalary(input) {
        // Simpan posisi cursor
        const cursorPosition = input.selectionStart;
        const oldValue = input.value;
        
        // Remove all non-digits
        let value = input.value.replace(/\D/g, '');
        
        // Add thousand separators
        if (value) {
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        input.value = value;
        
        // Restore cursor position (adjust for added dots)
        const newDots = (value.match(/\./g) || []).length;
        const oldDots = (oldValue.match(/\./g) || []).length;
        const dotDifference = newDots - oldDots;
        
        const newCursorPosition = cursorPosition + dotDifference;
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }

    function unformatSalary(input) {
        // Remove dots for form submission - hanya menghilangkan titik
        input.value = input.value.replace(/\./g, '');
    }

    // Function untuk mendapatkan nilai raw salary (tanpa titik)
    function getRawSalaryValue(input) {
        return input.value.replace(/\./g, '');
    }

    // 🆕 ENHANCED: Duplicate checking system with real-time validation
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

    // 🆕 ENHANCED: Better duplicate check function with error handling
    function checkDuplicate(fieldType, value, callback) {
        const checker = duplicateChecker[fieldType];
        if (checker.timeout) clearTimeout(checker.timeout);
        if (checker.lastChecked === value) return;
        
        checker.timeout = setTimeout(async () => {
            if (checker.isChecking) return;
            
            checker.isChecking = true;
            checker.lastChecked = value;
            
            // Show checking state
            const input = document.getElementById(fieldType);
            if (input) {
                input.classList.add('checking-duplicate');
                showDuplicateStatus(fieldType, { exists: false, message: 'Memeriksa...' }, false, 'checking');
            }
            
            try {
                const response = await fetch(`/check-${fieldType}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ [fieldType]: value })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                // Remove checking state
                if (input) {
                    input.classList.remove('checking-duplicate');
                }
                
                callback(result);
                
            } catch (error) {
                console.error(`Error checking ${fieldType}:`, error);
                
                // Remove checking state
                if (input) {
                    input.classList.remove('checking-duplicate');
                }
                
                // Show connection error
                callback({ 
                    exists: false, 
                    message: 'Error koneksi. Pastikan internet stabil dan coba lagi.',
                    error: true
                });
            } finally {
                checker.isChecking = false;
            }
        }, 800); // Reduced delay for better UX
    }

    // 🆕 ENHANCED: Better status display with different states
    function showDuplicateStatus(fieldId, result, isValid = true, state = 'normal') {
        const input = document.getElementById(fieldId);
        const existingStatus = input.parentNode.querySelector('.duplicate-status');
        if (existingStatus) existingStatus.remove();
        
        const statusElement = document.createElement('div');
        statusElement.className = 'duplicate-status text-xs mt-1 flex items-center';
        
        if (state === 'checking') {
            statusElement.className += ' text-yellow-600';
            statusElement.innerHTML = `
                <div class="loading-spinner mr-1" style="width: 12px; height: 12px;"></div>
                ${result.message}
            `;
        } else if (result.error) {
            statusElement.className += ' text-red-600';
            statusElement.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                ${result.message}
            `;
            input.classList.add('error');
        } else if (result.exists) {
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

    // 🆕 UPDATED: NIK validation with support for locked fields
    function enhanceNikValidation() {
        const nikInput = document.getElementById('nik');
        if (!nikInput) return;

        nikInput.addEventListener('input', function(e) {
            const nik = e.target.value.trim();
            e.target.classList.remove('error');
            const existingError = e.target.parentNode.querySelector('.nik-error');
            if (existingError) existingError.remove();

            // Skip validation if field is locked (OCR or manual lock)
            if (nikInput.readOnly) {
                console.log('NIK field is locked, skipping input validation');
                return;
            }

            if (nik.length === 0) {
                const status = e.target.parentNode.querySelector('.duplicate-status');
                if (status) status.remove();
                return;
            }

            // Enhanced NIK validation
            if (nik.length !== 16) {
                showDuplicateStatus('nik', { exists: false, message: `NIK harus 16 digit (saat ini: ${nik.length} digit)` }, false);
                return;
            }
            
            if (!/^[0-9]{16}$/.test(nik)) {
                showDuplicateStatus('nik', { exists: false, message: 'NIK hanya boleh berisi angka' }, false);
                return;
            }

            // Check for obvious invalid patterns
            if (/^(\d)\1{15}$/.test(nik)) {
                showDuplicateStatus('nik', { exists: false, message: 'NIK tidak valid (semua digit sama)' }, false);
                return;
            }
            
            if (nik.startsWith('00')) {
                showDuplicateStatus('nik', { exists: false, message: 'NIK tidak valid (dimulai dengan 00)' }, false);
                return;
            }

            // Basic province code validation
            const provinceCode = parseInt(nik.substring(0, 2));
            if (provinceCode < 11 || provinceCode > 94) {
                showDuplicateStatus('nik', { exists: false, message: 'Kode provinsi NIK tidak valid' }, false);
                return;
            }

            // Check for duplicates
            checkDuplicate('nik', nik, (result) => {
                showDuplicateStatus('nik', result, true);
            });
        });
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // 🆕 ENHANCED: Better form submission validation
    function enhanceFormSubmissionValidation() {
        const form = document.getElementById('applicationForm');
        if (!form) return;
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Check for duplicate validation errors
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
            
            // Check if still checking duplicates
            const isStillChecking = document.querySelector('.checking-duplicate');
            if (isStillChecking) {
                showAlert('Mohon tunggu, sistem sedang memeriksa duplikasi data...', 'warning');
                return false;
            }
            
            if (hasDuplicates) {
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
            
            // Continue with normal form submission
            this.submit();
        });
    }

    // ✅ KEEP: Existing attachEventListeners function
    function attachEventListeners() {
        // Attach save event listeners for dynamically added elements
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

    // ✅ KEEP: Existing updateRemoveButtons function  
    function updateRemoveButtons(containerId) {
        const container = document.getElementById(containerId);
        const removeButtons = container.querySelectorAll('.btn-remove');
        
        // For family members, always show remove button since user can delete default fields if not needed
        if (containerId === 'familyMembers') {
            removeButtons.forEach(button => {
                button.style.display = 'inline-block';
            });
        } else {
            // Original logic for other containers
            removeButtons.forEach((button, index) => {
                if (index === 0 && container.children.length > 1) {
                    button.style.display = 'none';
                } else if (index > 0) {
                    button.style.display = 'inline-block';
                }
            });
        }
    }

    // ✅ KEEP: Existing cleanEmptyOptionalFields function
    function cleanEmptyOptionalFields() {
        // Remove empty optional dynamic sections
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

    // 🆕 UPDATED: NIK field initialization - respects OCR lock state
    function initializeNikField() {
        const nikField = document.getElementById('nik');
        if (!nikField) return;

        // Check if there's an active OCR session
        const ocrValidated = sessionStorage.getItem('nik_locked') === 'true';
        const savedNikValue = sessionStorage.getItem('extracted_nik');
        
        // Let the KTP OCR system handle the initial state
        // This function will be called after OCR initialization
        console.log('NIK field initialization - waiting for OCR system...', {
            ocrValidated,
            savedNikValue,
            fieldState: nikField.readOnly ? 'locked' : 'open'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Load form data first
        loadFormData();
        
        // Initialize other components
        initializeAddressCopy();
        
        // Initialize NIK field (will be overridden by OCR if needed)
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

        // Initialize file upload handlers
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
            // Event listener untuk formatting real-time
            salaryInput.addEventListener('input', function(e) {
                formatSalary(e.target);
                
                // Debounced save dengan nilai yang sudah diformat
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    saveFormData();
                }, 1000);
            });
            
            // Format on load if there's a value
            if (salaryInput.value) {
                formatSalary(salaryInput);
            }
            
            // Event listener untuk form submission
            salaryInput.form.addEventListener('submit', function(e) {
                // Unformat salary sebelum submit
                const rawValue = getRawSalaryValue(salaryInput);
                salaryInput.value = rawValue;
            });
        }

        // Enhanced NIK validation
        enhanceNikValidation();

        // Enhanced email validation
        enhanceEmailValidation();

        // Enhanced form submission validation
        enhanceFormSubmissionValidation();

        // Initialize remove button states
        updateRemoveButtons('familyMembers');
        updateRemoveButtons('formalEducation');
        updateRemoveButtons('languageSkills');

        // Enhanced form validation dengan async file validation
        document.getElementById('applicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Unformat salary sebelum validation dan submission
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

            // 🆕 UPDATED: Enhanced NIK validation for locked/unlocked states
            const nikField = document.getElementById('nik');
            if (nikField) {
                const nikValue = nikField.value.trim();
                
                // Basic NIK presence check
                if (!nikValue) {
                    hasError = true;
                    nikField.classList.add('error');
                    errors.push('NIK harus diisi');
                } else {
                    // Additional validation for unlocked fields (manual input)
                    if (!nikField.readOnly) {
                        // More strict validation for manual input
                        if (nikValue.length !== 16) {
                            hasError = true;
                            nikField.classList.add('error');
                            errors.push(`NIK harus 16 digit (saat ini: ${nikValue.length} digit)`);
                        } else if (!/^[0-9]{16}$/.test(nikValue)) {
                            hasError = true;
                            nikField.classList.add('error');
                            errors.push('NIK harus berupa 16 digit angka');
                        }
                    }
                    
                    console.log('NIK validation in form submission:', {
                        value: nikValue,
                        readOnly: nikField.readOnly,
                        source: nikField.classList.contains('ocr-filled') ? 'OCR' : 
                               nikField.classList.contains('manual-input') ? 'manual' : 'unknown'
                    });
                }
            }
            
            // Enhanced date validation dengan explicit parsing
            const startWorkDate = document.getElementById('start_work_date');
            if (startWorkDate && startWorkDate.value) {
                console.log('Validating start_work_date:', startWorkDate.value);
                
                // Parse date using explicit format (YYYY-MM-DD)
                const selectedDateParts = startWorkDate.value.split('-');
                if (selectedDateParts.length === 3) {
                    const selectedDate = new Date(
                        parseInt(selectedDateParts[0]), // year
                        parseInt(selectedDateParts[1]) - 1, // month (0-based)
                        parseInt(selectedDateParts[2]) // day
                    );
                    
                    const today = new Date();
                    today.setHours(23, 59, 59, 999); // Set to end of today
                    
                    console.log('Selected date:', selectedDate);
                    console.log('Today (end of day):', today);
                    console.log('Is selected date after today?', selectedDate > today);
                    
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

            const agreementCheckbox = document.querySelector('input[name="agreement"]');
            if (!agreementCheckbox.checked) {
                hasError = true;
                errors.push('Anda harus menyetujui pernyataan untuk melanjutkan');
            }

            const fileInputs = ['cv', 'photo', 'transcript'];
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
            
            if (hasError) {
                // Re-format salary jika ada error untuk user experience
                if (salaryInput && salaryInput.value) {
                    formatSalary(salaryInput);
                }
                
                let errorMessage = 'Harap lengkapi data berikut:\n\n';
                errors.slice(0, 10).forEach((error, index) => {
                    errorMessage += `${index + 1}. ${error}\n`;
                });
                if (errors.length > 10) {
                    errorMessage += `\n... dan ${errors.length - 10} field lainnya`;
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
                submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> Mengirim...';
                
                // Submit form dengan nilai raw
                console.log('All validations passed, submitting form with raw salary value...');
                this.submit();
            }
        });
    });

})();