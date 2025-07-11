/**
 * Candidate Detail Page Main Script
 * Handles core functionality and coordinates between modules
 */

class CandidateDetailPage {
    constructor() {
        this.kraeplinChart = null;
        this.discGraph = null;
        this.isInitialized = false;
        this.updateActiveNav = this.updateActiveNav.bind(this);
    }

    initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (sidebarToggle && sidebar && mainContent) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });

            // Mobile sidebar
            if (window.innerWidth <= 768) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('show');
                });
            }
        }
    }

    initModals() {
        // History Modal
        window.showHistoryModal = () => {
            const modal = document.getElementById('historyModal');
            if (modal) modal.style.display = 'block';
        };

        window.closeHistoryModal = () => {
            const modal = document.getElementById('historyModal');
            if (modal) modal.style.display = 'none';
        };

        // Status Modal
        window.showStatusModal = () => {
            const modal = document.getElementById('statusModal');
            if (modal) modal.style.display = 'block';
        };

        window.closeStatusModal = () => {
            const modal = document.getElementById('statusModal');
            if (modal) modal.style.display = 'none';
        };

        // Close modals when clicking outside
        window.addEventListener('click', (event) => {
            const historyModal = document.getElementById('historyModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target === historyModal) {
                window.closeHistoryModal();
            }
            if (event.target === statusModal) {
                window.closeStatusModal();
            }
        });
    }

    updateActiveNav() {
        const sectionNavLinks = document.querySelectorAll('.section-nav-link');
        const sections = document.querySelectorAll('.content-section');
        
        if (sectionNavLinks.length === 0 || sections.length === 0) return;

        let current = '';
        sections.forEach((section) => {
            const sectionTop = section.offsetTop;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        sectionNavLinks.forEach((link) => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }

    initSectionNavigation() {
        const sectionNavLinks = document.querySelectorAll('.section-nav-link');
        const sections = document.querySelectorAll('.content-section');

        if (sectionNavLinks.length === 0 || sections.length === 0) {
            console.warn('Section navigation elements not found');
            return;
        }

        // Scroll event listener
        window.addEventListener('scroll', this.updateActiveNav);

        // Click event listeners
        sectionNavLinks.forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Update active state
                sectionNavLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                // Smooth scroll to target
                const targetId = link.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    const header = document.querySelector('.header');
                    const nav = document.querySelector('.section-nav');
                    const headerHeight = header ? header.offsetHeight : 0;
                    const navHeight = nav ? nav.offsetHeight : 0;
                    const offsetTop = targetSection.offsetTop - headerHeight - navHeight - 20;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Initialize first active nav
        setTimeout(() => {
            if (sectionNavLinks.length > 0) {
                sectionNavLinks[0].classList.add('active');
            }
            this.updateActiveNav();
        }, 100);
    }

    initStatusUpdate() {
        const statusForm = document.getElementById('statusForm');
        if (!statusForm) return;

        statusForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const statusSelect = document.getElementById('newStatus');
            const notesTextarea = document.getElementById('statusNotes');
            
            if (!statusSelect || !notesTextarea) {
                console.error('Status form elements not found');
                return;
            }
            
            const status = statusSelect.value;
            const notes = notesTextarea.value;
            
            if (!status) {
                alert('Pilih status baru');
                return;
            }
            
            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }
                
                // Make API call
                const response = await fetch(window.candidateDetailConfig?.updateStatusUrl || '#', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content
                    },
                    body: JSON.stringify({
                        status: status,
                        notes: notes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Status berhasil diperbarui');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal update status');
                }
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Terjadi kesalahan saat update status');
            }
        });
    }

    initKraeplinChart(testResultData) {
        if (!testResultData || !window.KraeplinChart) {
            console.log('Kraeplin chart not available');
            return;
        }

        try {
            this.kraeplinChart = new window.KraeplinChart(testResultData);
            this.kraeplinChart.initialize();
            console.log('Kraeplin chart initialized successfully');
        } catch (error) {
            console.error('Error initializing Kraeplin chart:', error);
        }
    }

    initDiscGraph(testResultData) {
        if (!testResultData || !window.DiscGraph) {
            console.log('DISC graph not available');
            return;
        }

        try {
            this.discGraph = new window.DiscGraph(testResultData);
            this.discGraph.initialize();
            console.log('DISC graph initialized successfully');
        } catch (error) {
            console.error('Error initializing DISC graph:', error);
        }
    }

    initialize(config = {}) {
        if (this.isInitialized) {
            console.warn('CandidateDetailPage already initialized');
            return;
        }

        console.log('=== INITIALIZING CANDIDATE DETAIL PAGE ===');
        
        // Initialize core functionality
        this.initSidebar();
        this.initModals();
        this.initSectionNavigation();
        this.initStatusUpdate();
        
        // Initialize charts if data is provided
        if (config.kraeplinData) {
            this.initKraeplinChart(config.kraeplinData);
        }
        
        if (config.discData) {
            this.initDiscGraph(config.discData);
        }
        
        this.isInitialized = true;
        console.log('=== CANDIDATE DETAIL PAGE INITIALIZED ===');
    }

    destroy() {
        // Clean up charts
        if (this.kraeplinChart) {
            this.kraeplinChart.destroy();
            this.kraeplinChart = null;
        }
        
        if (this.discGraph) {
            this.discGraph.destroy();
            this.discGraph = null;
        }
        
        // Remove event listeners
        window.removeEventListener('scroll', this.updateActiveNav);
        
        this.isInitialized = false;
        console.log('CandidateDetailPage destroyed');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.candidateDetailPage = new CandidateDetailPage();
    
    // Configuration will be provided by PHP
    const config = window.candidateDetailConfig || {};
    window.candidateDetailPage.initialize(config);
});

// Export for external access
window.CandidateDetailPage = CandidateDetailPage;