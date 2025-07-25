// Enhanced File Upload Handler untuk Mobile
(function() {
    'use strict';

    // File validation yang sudah ada tetap digunakan
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

    // 🆕 NEW: Mobile detection
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               ('ontouchstart' in window) ||
               (window.innerWidth <= 768);
    }

    // 🆕 NEW: Enhanced file storage untuk mobile
    const mobileFileStore = {
        files: new Map(),
        
        store: function(fieldName, file) {
            if (!file) return false;
            
            try {
                // Store file reference dengan metadata
                const fileData = {
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    lastModified: file.lastModified,
                    timestamp: Date.now(),
                    // Convert ke base64 untuk backup (hanya untuk file kecil)
                    dataUrl: null
                };
                
                // Untuk file kecil atau gambar, convert ke base64 sebagai backup
                if (file.size < 500 * 1024 || file.type.startsWith('image/')) {
                    this.convertToBase64(file).then(dataUrl => {
                        fileData.dataUrl = dataUrl;
                        console.log(`📱 Mobile: File ${fieldName} stored with base64 backup`);
                    }).catch(err => {
                        console.warn(`⚠️ Mobile: Could not create base64 backup for ${fieldName}:`, err);
                    });
                }
                
                this.files.set(fieldName, fileData);
                console.log(`📱 Mobile: File ${fieldName} stored in memory`, {
                    name: file.name,
                    size: file.size,
                    type: file.type
                });
                
                return true;
            } catch (error) {
                console.error(`❌ Mobile: Error storing file ${fieldName}:`, error);
                return false;
            }
        },
        
        get: function(fieldName) {
            return this.files.get(fieldName);
        },
        
        convertToBase64: function(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        },
        
        validateStored: function(fieldName) {
            const stored = this.get(fieldName);
            if (!stored) return { valid: false, error: 'File tidak ditemukan di memory' };
            
            // Check if file is still valid
            const file = stored.file;
            if (!file) return { valid: false, error: 'File reference hilang' };
            
            // Check if file properties match
            if (file.name !== stored.name || 
                file.size !== stored.size || 
                file.lastModified !== stored.lastModified) {
                return { valid: false, error: 'File telah berubah sejak dipilih' };
            }
            
            return { valid: true, file: file };
        },
        
        clear: function() {
            this.files.clear();
        }
    };

    // 🆕 ENHANCED: Mobile-optimized file validation - UPDATED FOR CHROME MOBILE
    async function validateFileForMobile(file, validation, fieldName) {
        console.log(`📱 Mobile: Validating file for ${fieldName}:`, {
            name: file.name,
            type: file.type,
            size: file.size,
            lastModified: file.lastModified
        });

        if (!file || file.size === 0) {
            return { valid: false, error: 'File tidak valid atau kosong' };
        }

        // Check file extension
        const extension = file.name.toLowerCase().split('.').pop();
        if (!validation.extensions.includes(extension)) {
            const allowedExtensions = validation.extensions.join(', ').toUpperCase();
            return { valid: false, error: `Format file harus ${allowedExtensions}. File Anda: ${extension.toUpperCase()}` };
        }

        // Check file size
        if (file.size > validation.maxSize) {
            return { valid: false, error: 'Ukuran file maksimal 2MB' };
        }

        // Enhanced mobile device detection
        const isChromeMobile = /Chrome/.test(navigator.userAgent) && /Mobile/.test(navigator.userAgent);
        const isMobile = isMobileDevice() || isChromeMobile;
        
        if (isMobile) {
            console.log(`📱 Mobile device detected (Chrome Mobile: ${isChromeMobile})`);
            
            // For image files, use mobile-optimized validation
            if (validation.extensions.includes('jpg') || validation.extensions.includes('jpeg') || validation.extensions.includes('png')) {
                return await validateImageFileForMobile(file, validation);
            }
        } else {
            // Desktop validation (existing logic)
            if (!validation.types.includes(file.type)) {
                console.warn('MIME type mismatch:', {
                    detected: file.type,
                    allowed: validation.types
                });
                const allowedExtensions = validation.extensions.join(', ').toUpperCase();
                return { valid: false, error: `Format file harus ${allowedExtensions}. Tipe file terdeteksi: ${file.type}` };
            }
        }

        return { valid: true };
    }

    // 🆕 ENHANCED: Chrome Mobile-optimized image validation dengan error handling yang lebih baik
    function validateImageFileForMobile(file, validation) {
        return new Promise((resolve) => {
            const extension = file.name.toLowerCase().split('.').pop();
            const isChromeMobile = /Chrome/.test(navigator.userAgent) && /Mobile/.test(navigator.userAgent);
            
            console.log(`📱 Validating image for Chrome Mobile:`, {
                name: file.name,
                type: file.type,
                size: file.size,
                extension: extension,
                isChromeMobile: isChromeMobile
            });
            
            // Prioritize extension validation for Chrome Mobile
            if (!validation.extensions.includes(extension)) {
                resolve({ valid: false, error: `Format file harus JPG atau PNG. File Anda: ${extension.toUpperCase()}` });
                return;
            }

            // Chrome Mobile: Skip FileReader validation if file seems problematic
            if (isChromeMobile) {
                // For Chrome Mobile, use basic validation to avoid FileReader issues
                const maxSize = validation.maxSize || (2 * 1024 * 1024);
                
                if (file.size > maxSize) {
                    resolve({ valid: false, error: 'Ukuran file terlalu besar (maksimal 2MB)' });
                    return;
                }
                
                if (file.size === 0) {
                    resolve({ valid: false, error: 'File kosong atau corrupt' });
                    return;
                }
                
                // Allow common Chrome Mobile MIME types and empty MIME types
                const allowedChromeMimeTypes = [
                    'image/jpeg', 'image/jpg', 'image/png',
                    'image/pjpeg', 'image/x-png',
                    'application/octet-stream', // Chrome Mobile sometimes returns this
                    '', // Chrome Mobile sometimes returns empty MIME type
                    'image/webp', // Chrome Mobile camera
                    undefined // Sometimes undefined
                ];
                
                if (!allowedChromeMimeTypes.includes(file.type)) {
                    console.warn('Chrome Mobile: Unexpected MIME type, but allowing based on extension:', file.type);
                }
                
                console.log(`✅ Chrome Mobile: Accepting file based on extension and basic checks`);
                resolve({ valid: true });
                return;
            }

            // For non-Chrome Mobile browsers, use standard FileReader validation
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    console.log(`✅ Standard validation successful:`, {
                        width: img.width,
                        height: img.height
                    });
                    resolve({ valid: true });
                };
                img.onerror = function() {
                    console.error(`❌ Image validation failed`);
                    resolve({ valid: false, error: 'File bukan gambar yang valid atau file rusak' });
                };
                img.src = e.target.result;
            };
            reader.onerror = function() {
                console.error(`❌ FileReader error`);
                resolve({ valid: false, error: 'Tidak dapat membaca file. File mungkin rusak.' });
            };
            reader.readAsDataURL(file);
        });
    }

    // 🆕 ENHANCED: Mobile-aware file upload handler
    async function handleFileUploadMobile(event, fieldName) {
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
            mobileFileStore.files.delete(fieldName);
            return;
        }

        // Show loading state for mobile
        if (isMobileDevice()) {
            label.innerHTML = `
                <div class="loading-spinner mr-2"></div>
                <span>📱 Memproses file mobile...</span>
            `;
        }

        try {
            console.log(`📱 Mobile: Processing file upload for ${fieldName}`);
            
            // Validate file
            const validationResult = await validateFileForMobile(file, validation, fieldName);
            
            if (!validationResult.valid) {
                showFileError(fieldName, validationResult.error);
                event.target.value = '';
                return;
            }

            // Store file in mobile store untuk mencegah hilang
            const stored = mobileFileStore.store(fieldName, file);
            if (!stored) {
                showFileError(fieldName, 'Gagal menyimpan file di memori mobile');
                event.target.value = '';
                return;
            }

            // Show preview
            showFilePreview(fieldName, file);
            
            // Log success
            console.log(`✅ Mobile: File ${fieldName} uploaded successfully:`, {
                name: file.name,
                size: file.size,
                type: file.type
            });
            
        } catch (error) {
            console.error(`❌ Mobile: File upload error for ${fieldName}:`, error);
            showFileError(fieldName, 'Terjadi kesalahan saat memproses file di perangkat mobile. Silakan coba lagi.');
            event.target.value = '';
        }
    }

    // 🆕 NEW: Mobile-specific form submission validation
    async function validateFormForMobile() {
        console.log('📱 Mobile: Validating form before submission');
        
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
                // Check if file is still valid in mobile store
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
                const validationResult = await validateFileForMobile(file, validation, fieldName);
                
                if (!validationResult.valid) {
                    hasFileErrors = true;
                    fileErrors.push(`${fieldName.toUpperCase()}: ${validationResult.error}`);
                    showFileError(fieldName, validationResult.error);
                }
            }
        }

        return { hasErrors: hasFileErrors, errors: fileErrors };
    }

    // 🆕 NEW: Enhanced error display for mobile
    function showMobileFileError(fieldName, errorMessage) {
        const label = document.getElementById(`${fieldName}-label`);
        const error = document.getElementById(`${fieldName}-error`);
        
        label.classList.add('error');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>📱 File error</span>
        `;
        
        error.innerHTML = `
            <div class="text-red-600 font-medium">📱 Mobile Error:</div>
            <div>${errorMessage}</div>
            <div class="text-xs mt-1 text-gray-600">
                <strong>Tips Mobile:</strong> ${getMobileTip(fieldName)}
            </div>
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

    // 🆕 NEW: Mobile-aware form submission interceptor
    function enhanceMobileFormSubmission() {
        const form = document.getElementById('applicationForm');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
            if (isMobileDevice()) {
                console.log('📱 Mobile: Intercepting form submission for validation');
                e.preventDefault();

                // Show mobile loading state
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> 📱 Memproses mobile...';

                try {
                    // Validate files for mobile
                    const mobileValidation = await validateFormForMobile();
                    
                    if (mobileValidation.hasErrors) {
                        // Restore submit button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        
                        // Show mobile-specific error
                        let errorMessage = '📱 Masalah file di perangkat mobile:\n\n';
                        mobileValidation.errors.forEach((error, index) => {
                            errorMessage += `${index + 1}. ${error}\n`;
                        });
                        errorMessage += '\n💡 Tips: Coba pilih file lagi atau gunakan file dengan ukuran lebih kecil.';
                        
                        showAlert(errorMessage.replace(/\n/g, '<br>'), 'error');
                        
                        // Scroll to first file error
                        const firstFileError = document.querySelector('.file-upload-label.error');
                        if (firstFileError) {
                            firstFileError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        
                        return false;
                    }
                    
                    // All mobile validations passed, continue with normal form validation
                    console.log('✅ Mobile: File validations passed, continuing with form submission');
                    
                    // Update button text
                    submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> Mengirim lamaran...';
                    
                    // Submit the form
                    this.submit();
                    
                } catch (error) {
                    console.error('❌ Mobile: Form submission error:', error);
                    
                    // Restore submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    showAlert('📱 Terjadi kesalahan pada perangkat mobile. Silakan coba lagi atau gunakan perangkat lain.', 'error');
                }
            }
            // For desktop, let the existing validation handle it
        });
    }

    // Utility functions (reuse existing ones)
    function getDefaultLabelContent(fieldName) {
        const contents = {
            cv: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                 </svg>
                 <span>📱 Pilih PDF</span>`,
            photo: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                 </svg>
                 <span>📱 Pilih Foto</span>`,
            transcript: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                     </svg>
                     <span>📱 Pilih Transkrip</span>`,
            certificates: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                       </svg>
                       <span>📱 Sertifikat (opsional)</span>`
        };
        return contents[fieldName];
    }

    function showFilePreview(fieldName, file) {
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        label.classList.add('has-file');
        label.innerHTML = `
            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>📱 ${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}</span>
        `;

        preview.innerHTML = `
            <div class="file-preview-item">
                <div class="file-preview-info">
                    <span>📱 ${file.name}</span>
                    <span class="file-size">(${formatFileSize(file.size)})</span>
                </div>
                <span class="file-remove" onclick="removeFile('${fieldName}')">×</span>
            </div>
        `;
        preview.style.display = 'block';
    }

    function showFileError(fieldName, errorMessage) {
        if (isMobileDevice()) {
            showMobileFileError(fieldName, errorMessage);
        } else {
            // Use existing desktop error display
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
            `;
            error.classList.add('show');
        }
    }

    function showAlert(message, type = 'error') {
        const alert = document.createElement('div');
        alert.className = `custom-alert ${type}`;
        alert.innerHTML = `
            <div class="font-medium">${type === 'error' ? '❌ Error!' : type === 'success' ? '✅ Berhasil!' : '⚠️ Peringatan!'}</div>
            <div class="text-sm mt-1">${message}</div>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, isMobileDevice() ? 8000 : 5000); // Longer display time for mobile
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Initialize mobile enhancements
    document.addEventListener('DOMContentLoaded', function() {
        if (isMobileDevice()) {
            console.log('📱 Mobile device detected, initializing mobile file upload enhancements');
            
            // Replace file upload handlers with mobile versions
            document.getElementById('cv').addEventListener('change', function(e) {
                handleFileUploadMobile(e, 'cv');
            });

            document.getElementById('photo').addEventListener('change', function(e) {
                handleFileUploadMobile(e, 'photo');
            });

            document.getElementById('transcript').addEventListener('change', function(e) {
                handleFileUploadMobile(e, 'transcript');
            });

            document.getElementById('certificates').addEventListener('change', function(e) {
                handleFileUploadMobile(e, 'certificates');
            });

            // Enhanced form submission for mobile
            enhanceMobileFormSubmission();

            // Mobile-specific tips
            const mobileNotice = document.createElement('div');
            mobileNotice.className = 'mobile-notice';
            mobileNotice.innerHTML = `
                <div style="background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 12px; margin: 16px 0; font-size: 14px; color: #1e40af;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span>📱</span>
                        <strong>Tips untuk Pengguna Mobile:</strong>
                    </div>
                    <ul style="margin-left: 20px; line-height: 1.5;">
                        <li>Pastikan koneksi internet stabil sebelum upload file</li>
                        <li>Gunakan file dengan ukuran di bawah 2MB</li>
                        <li>Pilih file dari galeri atau ambil foto baru untuk hasil terbaik</li>
                        <li>Jangan menutup browser saat proses upload berlangsung</li>
                    </ul>
                </div>
            `;
            
            const uploadSection = document.querySelector('[data-section="9"]');
            if (uploadSection) {
                uploadSection.insertBefore(mobileNotice, uploadSection.children[1]);
            }
        }
    });

    // Make removeFile function global
    window.removeFile = function(fieldName) {
        const input = document.getElementById(fieldName);
        const label = document.getElementById(`${fieldName}-label`);
        const preview = document.getElementById(`${fieldName}-preview`);
        
        input.value = '';
        label.classList.remove('has-file');
        label.innerHTML = getDefaultLabelContent(fieldName);
        preview.style.display = 'none';
        
        // Remove from mobile store
        mobileFileStore.files.delete(fieldName);
        
        console.log(`📱 Mobile: File ${fieldName} removed`);
    };

})();