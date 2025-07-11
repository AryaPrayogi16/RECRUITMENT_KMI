// public/js/candidates.js

// Sidebar toggle
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

// Dropdown menu
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(d => {
        if (d !== dropdown) {
            d.classList.remove('show');
        }
    });
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                d.classList.remove('show');
            });
        }
    });
}

// Search, filter, and reset
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const positionFilter = document.getElementById('positionFilter');
let searchTimeout;

function applyFilters() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (positionFilter.value) params.append('position', positionFilter.value);
        window.location.href = window.candidatesIndexUrl + '?' + params.toString();
    }, 500);
}

if (searchInput) searchInput.addEventListener('input', applyFilters);
if (statusFilter) statusFilter.addEventListener('change', applyFilters);
if (positionFilter) positionFilter.addEventListener('change', applyFilters);

function resetFilters() {
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (positionFilter) positionFilter.value = '';
    window.location.href = window.candidatesIndexUrl;
}

// Update status
function updateStatus(candidateId, status) {
    const statusLabels = window.candidateStatusLabels || {};
    const statusLabel = statusLabels[status] || status;
    if (confirm(`Apakah Anda yakin ingin mengubah status kandidat ini menjadi "${statusLabel}"?`)) {
        document.getElementById('loadingOverlay').style.display = 'flex';
        fetch(`/candidates/${candidateId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Gagal mengubah status kandidat: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengubah status');
        })
        .finally(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
        });
    }
}

// Schedule interview
function scheduleInterview(candidateId) {
    window.location.href = `/candidates/${candidateId}/schedule-interview`;
}

// Export functionality
const exportBtn = document.querySelector('.btn-export');
if (exportBtn) {
    exportBtn.addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        const selectedIds = [];
        document.querySelectorAll('input[name="candidate_ids[]"]:checked').forEach(cb => {
            selectedIds.push(cb.value);
        });
        if (selectedIds.length > 0) {
            params.append('selected_ids', selectedIds.join(','));
        }
        window.location.href = window.candidatesExportUrl + '?' + params.toString();
    });
}

// Initialize filter values on page load
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('search') && searchInput) {
            searchInput.value = urlParams.get('search');
        }
        if (urlParams.get('status') && statusFilter) {
            statusFilter.value = urlParams.get('status');
        }
        if (urlParams.get('position') && positionFilter) {
            positionFilter.value = urlParams.get('position');
        }
    });
}

// Bulk selection
// Requires: checkbox with id="selectAll", checkboxes with class="candidate-checkbox", toolbar with id="bulkActionToolbar", span with id="selectedCount"
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const candidateCheckboxes = document.querySelectorAll('.candidate-checkbox');
    const bulkToolbar = document.getElementById('bulkActionToolbar');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            candidateCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkToolbar();
        });
    }

    candidateCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkToolbar);
    });

    function updateBulkToolbar() {
        const selectedCount = document.querySelectorAll('.candidate-checkbox:checked').length;
        if (bulkToolbar && selectedCountSpan) {
            if (selectedCount > 0) {
                bulkToolbar.style.display = 'block';
                selectedCountSpan.textContent = selectedCount;
            } else {
                bulkToolbar.style.display = 'none';
            }
        }
        // Update select all checkbox state
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount === candidateCheckboxes.length;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < candidateCheckboxes.length;
        }
    }
});

// Delete single candidate (SweetAlert2)
function deleteCandidate(id, name) {
    Swal.fire({
        title: 'Hapus Kandidat?',
        text: `Apakah Anda yakin ingin menghapus kandidat "${name}"? Data dapat dipulihkan dari menu kandidat terhapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/candidates/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
}

// Bulk delete
function bulkDelete() {
    const selectedCheckboxes = document.querySelectorAll('.candidate-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        Swal.fire('Peringatan', 'Pilih kandidat yang ingin dihapus', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Hapus Kandidat Terpilih?',
        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} kandidat? Data dapat dipulihkan dari menu kandidat terhapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/candidates/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    candidate_ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
}

// Expose to global scope
window.deleteCandidate = deleteCandidate;
window.bulkDelete = bulkDelete;
// Expose functions to global scope if needed
window.toggleDropdown = toggleDropdown;
window.resetFilters = resetFilters;
window.updateStatus = updateStatus;
window.scheduleInterview = scheduleInterview;
window.deleteCandidate = deleteCandidate;
