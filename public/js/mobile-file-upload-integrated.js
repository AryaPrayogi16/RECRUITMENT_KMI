// Mobile File Upload Integration - Standalone Enhancement
// This file provides additional mobile enhancements without breaking existing functionality

(function() {
    'use strict';

    console.log('📱 Loading mobile file upload integration...');

    // Check if we're on mobile and if base form-style.js is loaded
    const isMobile = window.isMobileDevice && window.isMobileDevice();
    const hasBaseScript = typeof window.fileValidation !== 'undefined';

    if (!hasBaseScript) {
        console.warn('⚠️ Base form-style.js not loaded yet, mobile integration may not work properly');
        return;
    }

    if (!isMobile) {
        console.log('💻 Desktop detected, mobile integration not needed');
        return;
    }

    console.log('📱 Mobile device detected, initializing mobile-specific enhancements...');

    // 🆕 MOBILE PERFORMANCE OPTIMIZATIONS
    const mobileOptimizations = {
        // Debounced file validation to prevent lag on mobile
        debounceFileValidation: function(func, delay = 300) {
            let timeoutId;
            return function(...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        },

        // Optimize file reading for mobile devices
        optimizeFileReading: function(file) {
            return new Promise((resolve, reject) => {
                // For large files on mobile, use chunk reading
                if (file.size > 1024 * 1024) { // > 1MB
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.onerror = reject;
                    // Read in smaller chunks for mobile performance
                    reader.readAsArrayBuffer(file.slice(0, Math.min(file.size, 2 * 1024 * 1024)));
                } else {
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsArrayBuffer(file);
                }
            });
        },

        // Mobile-friendly file size formatting
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + sizes[i];
        }
    };

    // 🆕 MOBILE TOUCH ENHANCEMENTS
    const touchEnhancements = {
        addTouchFeedback: function() {
            const fileLabels = document.querySelectorAll('.file-upload-label');
            
            fileLabels.forEach(label => {
                // Add touch feedback
                label.addEventListener('touchstart', function() {
                    this.style.backgroundColor = '#f3f4f6';
                    this.style.transform = 'scale(0.98)';
                }, { passive: true });

                label.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                        this.style.transform = '';
                    }, 150);
                }, { passive: true });

                // Prevent double-tap zoom on file inputs
                label.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    // Trigger file input click after touch delay
                    setTimeout(() => {
                        const input = this.previousElementSibling || this.querySelector('input[type="file"]');
                        if (input) input.click();
                    }, 50);
                });
            });
        },

        // Enhanced mobile form navigation
        improveFormNavigation: function() {
            // Add smooth scrolling behavior for mobile
            const formSections = document.querySelectorAll('.form-section');
            
            formSections.forEach((section, index) => {
                const header = section.querySelector('.section-title');
                if (header) {
                    header.style.cursor = 'pointer';
                    header.addEventListener('touchstart', function() {
                        this.style.color = '#3b82f6';
                    }, { passive: true });
                    
                    header.addEventListener('touchend', function() {
                        this.style.color = '';
                    }, { passive: true });
                }
            });
        }
    };

    // 🆕 MOBILE MEMORY MANAGEMENT
    const memoryManager = {
        // Clean up old mobile file store entries
        cleanupOldEntries: function() {
            if (window.mobileFileStore && window.mobileFileStore.files) {
                const now = Date.now();
                const maxAge = 30 * 60 * 1000; // 30 minutes
                
                for (const [key, value] of window.mobileFileStore.files.entries()) {
                    if (now - value.timestamp > maxAge) {
                        window.mobileFileStore.files.delete(key);
                        console.log(`📱 Cleaned up expired file: ${key}`);
                    }
                }
            }
        },

        // Monitor memory usage and clean up if needed
        monitorMemory: function() {
            if (navigator.deviceMemory && navigator.deviceMemory < 4) {
                console.log('📱 Low memory device detected, enabling aggressive cleanup');
                
                // More aggressive cleanup for low memory devices
                setInterval(() => {
                    this.cleanupOldEntries();
                    
                    // Force garbage collection if available
                    if (window.gc) {
                        window.gc();
                    }
                }, 5 * 60 * 1000); // Every 5 minutes
            }
        }
    };

    // 🆕 MOBILE ERROR RECOVERY
    const errorRecovery = {
        // Attempt to recover lost files from mobile store
        recoverLostFiles: function() {
            const fileInputs = ['cv', 'photo', 'transcript'];
            
            fileInputs.forEach(fieldName => {
                const input = document.getElementById(fieldName);
                if (input && input.files.length === 0 && window.mobileFileStore) {
                    const stored = window.mobileFileStore.get(fieldName);
                    if (stored && stored.file) {
                        try {
                            // Attempt to restore file to input
                            const dt = new DataTransfer();
                            dt.items.add(stored.file);
                            input.files = dt.files;
                            
                            // Update UI
                            if (window.showFilePreview) {
                                window.showFilePreview(fieldName, stored.file);
                            }
                            
                            console.log(`📱 Recovered lost file: ${fieldName}`);
                        } catch (error) {
                            console.warn(`📱 Could not recover file ${fieldName}:`, error);
                        }
                    }
                }
            });
        },

        // Set up periodic file integrity checks
        setupIntegrityChecks: function() {
            setInterval(() => {
                this.recoverLostFiles();
            }, 2 * 60 * 1000); // Every 2 minutes
        }
    };

    // 🆕 MOBILE-SPECIFIC UI ENHANCEMENTS
    const uiEnhancements = {
        // Add mobile-friendly file upload indicators
        addMobileIndicators: function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                const wrapper = input.closest('.file-upload-wrapper');
                if (wrapper) {
                    // Add mobile file status indicator
                    const statusDiv = document.createElement('div');
                    statusDiv.className = 'mobile-file-status';
                    statusDiv.style.cssText = `
                        margin-top: 8px;
                        padding: 6px 12px;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 6px;
                        font-size: 12px;
                        color: #64748b;
                        display: none;
                    `;
                    statusDiv.innerHTML = '📱 Siap untuk upload mobile';
                    wrapper.appendChild(statusDiv);
                    
                    // Show/hide based on file selection
                    input.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            statusDiv.style.display = 'block';
                            statusDiv.innerHTML = `📱 File siap: ${this.files[0].name}`;
                            statusDiv.style.backgroundColor = '#ecfdf5';
                            statusDiv.style.borderColor = '#10b981';
                            statusDiv.style.color = '#065f46';
                        } else {
                            statusDiv.style.display = 'none';
                        }
                    });
                }
            });
        },

        // Optimize form layout for mobile
        optimizeLayout: function() {
            // Add mobile-specific CSS classes
            const style = document.createElement('style');
            style.textContent = `
                @media (max-width: 768px) {
                    .file-upload-label {
                        min-height: 60px !important;
                        padding: 16px 12px !important;
                        font-size: 14px !important;
                        line-height: 1.4 !important;
                    }
                    
                    .file-preview-item {
                        flex-direction: column !important;
                        align-items: flex-start !important;
                        gap: 4px !important;
                        padding: 8px !important;
                        font-size: 12px !important;
                    }
                    
                    .mobile-file-status {
                        animation: slideIn 0.3s ease-out;
                    }
                    
                    @keyframes slideIn {
                        from {
                            opacity: 0;
                            transform: translateY(-10px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    .form-section {
                        margin-bottom: 2rem !important;
                    }
                    
                    .btn-primary {
                        width: 100% !important;
                        padding: 16px !important;
                        font-size: 16px !important;
                    }
                }
            `;
            document.head.appendChild(style);
        },

        // Add mobile progress indicators
        addProgressIndicators: function() {
            const form = document.getElementById('applicationForm');
            if (!form) return;
            
            const sections = document.querySelectorAll('.form-section');
            if (sections.length > 1) {
                const progressBar = document.createElement('div');
                progressBar.className = 'mobile-progress-bar';
                progressBar.style.cssText = `
                    position: sticky;
                    top: 0;
                    z-index: 20;
                    background: white;
                    padding: 12px 16px;
                    border-bottom: 1px solid #e2e8f0;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                `;
                
                progressBar.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                        <span>📱</span>
                        <span>Bagian <span id="current-section">1</span> dari ${sections.length}</span>
                        <div style="flex: 1; height: 4px; background: #e2e8f0; border-radius: 2px; margin-left: 8px;">
                            <div id="progress-fill" style="height: 100%; background: #10b981; border-radius: 2px; width: ${100/sections.length}%; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                `;
                
                form.insertBefore(progressBar, form.firstChild);
                
                // Update progress on scroll
                let currentSection = 1;
                const updateProgress = mobileOptimizations.debounceFileValidation(() => {
                    const scrollTop = window.pageYOffset;
                    const windowHeight = window.innerHeight;
                    
                    sections.forEach((section, index) => {
                        const rect = section.getBoundingClientRect();
                        if (rect.top <= windowHeight / 2 && rect.bottom >= windowHeight / 2) {
                            const newSection = index + 1;
                            if (newSection !== currentSection) {
                                currentSection = newSection;
                                document.getElementById('current-section').textContent = currentSection;
                                document.getElementById('progress-fill').style.width = `${(currentSection / sections.length) * 100}%`;
                            }
                        }
                    });
                }, 100);
                
                window.addEventListener('scroll', updateProgress, { passive: true });
            }
        }
    };

    // 🆕 MOBILE FORM VALIDATION ENHANCEMENTS
    const validationEnhancements = {
        // Add mobile-friendly validation messages
        enhanceValidationMessages: function() {
            const originalShowAlert = window.showAlert;
            if (originalShowAlert) {
                window.showAlert = function(message, type = 'error') {
                    // Make mobile alerts more user-friendly
                    if (isMobile) {
                        // Add mobile-specific formatting
                        const mobileMessage = message
                            .replace(/\n/g, '<br>')
                            .replace(/Error!/g, '📱 Perhatian!')
                            .replace(/Warning!/g, '📱 Peringatan!');
                        
                        return originalShowAlert(mobileMessage, type);
                    }
                    return originalShowAlert(message, type);
                };
            }
        },

        // Add mobile form validation helpers
        addMobileValidationHelpers: function() {
            // Add visual indicators for required fields on mobile
            const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            
            requiredInputs.forEach(input => {
                if (input.type !== 'file') {
                    // Add mobile-friendly focus behavior
                    input.addEventListener('focus', function() {
                        this.style.borderColor = '#3b82f6';
                        this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                    });
                    
                    input.addEventListener('blur', function() {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    });
                }
            });
        }
    };

    // 🆕 MOBILE NETWORK HANDLING
    const networkHandler = {
        // Monitor network status and show warnings
        monitorNetworkStatus: function() {
            if ('connection' in navigator) {
                const connection = navigator.connection;
                
                const checkConnection = () => {
                    if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                        this.showNetworkWarning('Koneksi lambat terdeteksi. Upload file mungkin membutuhkan waktu lebih lama.');
                    }
                };
                
                connection.addEventListener('change', checkConnection);
                checkConnection(); // Initial check
            }
            
            // Monitor online/offline status
            window.addEventListener('online', () => {
                this.hideNetworkWarning();
                console.log('📱 Network: Back online');
            });
            
            window.addEventListener('offline', () => {
                this.showNetworkWarning('Tidak ada koneksi internet. Data form akan disimpan lokal sampai koneksi kembali.');
                console.log('📱 Network: Gone offline');
            });
        },

        showNetworkWarning: function(message) {
            let warning = document.getElementById('mobile-network-warning');
            if (!warning) {
                warning = document.createElement('div');
                warning.id = 'mobile-network-warning';
                warning.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: #fbbf24;
                    color: #92400e;
                    padding: 8px 16px;
                    font-size: 12px;
                    z-index: 9999;
                    text-align: center;
                    border-bottom: 1px solid #f59e0b;
                `;
                document.body.appendChild(warning);
            }
            warning.innerHTML = `📱 ${message}`;
            warning.style.display = 'block';
        },

        hideNetworkWarning: function() {
            const warning = document.getElementById('mobile-network-warning');
            if (warning) {
                warning.style.display = 'none';
            }
        }
    };

    // 🆕 INITIALIZATION
    function initializeMobileEnhancements() {
        console.log('📱 Initializing mobile-specific enhancements...');

        try {
            // Core enhancements
            touchEnhancements.addTouchFeedback();
            touchEnhancements.improveFormNavigation();
            
            // UI enhancements
            uiEnhancements.addMobileIndicators();
            uiEnhancements.optimizeLayout();
            uiEnhancements.addProgressIndicators();
            
            // Validation enhancements
            validationEnhancements.enhanceValidationMessages();
            validationEnhancements.addMobileValidationHelpers();
            
            // Memory and performance
            memoryManager.monitorMemory();
            memoryManager.cleanupOldEntries();
            
            // Error recovery
            errorRecovery.setupIntegrityChecks();
            
            // Network handling
            networkHandler.monitorNetworkStatus();
            
            console.log('✅ Mobile enhancements initialized successfully');
            
            // Show mobile ready notification
            setTimeout(() => {
                if (window.showAlert) {
                    window.showAlert('📱 Sistem mobile siap! Form telah dioptimalkan untuk perangkat mobile Anda.', 'success');
                }
            }, 1000);
            
        } catch (error) {
            console.error('❌ Error initializing mobile enhancements:', error);
        }
    }

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMobileEnhancements);
    } else {
        // DOM is already ready
        setTimeout(initializeMobileEnhancements, 100);
    }

    // 🆕 EXPORT MOBILE UTILITIES
    window.mobileEnhancements = {
        optimizations: mobileOptimizations,
        touchEnhancements: touchEnhancements,
        memoryManager: memoryManager,
        errorRecovery: errorRecovery,
        uiEnhancements: uiEnhancements,
        validationEnhancements: validationEnhancements,
        networkHandler: networkHandler
    };

    console.log('📱 Mobile file upload integration loaded successfully');

})();