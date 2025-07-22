// Position Transfer Handler
class PositionTransfer {
    constructor() {
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Handle delete button clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-delete-position')) {
                e.preventDefault();
                const button = e.target.closest('.btn-delete-position');
                const positionId = button.dataset.positionId;
                const positionName = button.dataset.positionName;
                
                this.handleDeletePosition(positionId, positionName);
            }
        });

        // Handle transfer form submission
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'transferForm') {
                e.preventDefault();
                this.handleTransferSubmission(e.target);
            }
        });
    }

    async handleDeletePosition(positionId, positionName) {
        try {
            const response = await fetch(`/positions/${positionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!data.success && !data.canDelete && data.transferable_positions) {
                // Show transfer modal
                this.showTransferModal(data, positionId, positionName);
            } else if (data.success) {
                // Direct delete success
                this.showSuccessMessage(data.message);
                this.removePositionFromDOM(positionId);
            } else {
                this.showErrorMessage(data.message || 'Failed to delete position');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showErrorMessage('An error occurred while processing the request');
        }
    }

    showTransferModal(data, positionId, positionName) {
        const modal = this.createTransferModal(data, positionId, positionName);
        document.body.appendChild(modal);
        
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        // Clean up modal on hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    createTransferModal(data, positionId, positionName) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'transferModal';
        modal.setAttribute('tabindex', '-1');
        
        const transferOptions = data.transferable_positions.map(pos => 
            `<option value="${pos.id}">
                ${pos.name} - ${pos.department} 
                ${pos.location ? `(${pos.location})` : ''} 
                [${pos.employment_type}] 
                (${pos.total_candidates} kandidat)
            </option>`
        ).join('');

        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Transfer Kandidat - ${positionName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Posisi</h6>
                            <ul class="mb-0">
                                <li>Total kandidat: <strong>${data.details.total_candidates}</strong></li>
                                <li>Kandidat aktif: <strong>${data.details.active_candidates}</strong></li>
                                <li>Status posisi: <strong>${data.details.position_info.status}</strong></li>
                            </ul>
                        </div>

                        <p class="text-muted">
                            Posisi ini tidak dapat dihapus karena masih memiliki kandidat yang terdaftar. 
                            Anda dapat memilih salah satu opsi berikut:
                        </p>

                        <form id="transferForm" data-position-id="${positionId}">
                            <div class="mb-3">
                                <label for="transferOption" class="form-label">Pilih Tindakan:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="actionTransfer" value="transfer" checked>
                                    <label class="form-check-label fw-bold text-primary" for="actionTransfer">
                                        <i class="fas fa-exchange-alt me-1"></i>
                                        Transfer kandidat ke posisi lain
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="actionClose" value="close">
                                    <label class="form-check-label" for="actionClose">
                                        <i class="fas fa-lock me-1"></i>
                                        Tutup posisi (kandidat tetap ada)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action" id="actionCancel" value="cancel">
                                    <label class="form-check-label" for="actionCancel">
                                        <i class="fas fa-times me-1"></i>
                                        Batalkan penghapusan
                                    </label>
                                </div>
                            </div>

                            <div id="transferSection" class="mb-3">
                                <label for="newPositionId" class="form-label">Pilih Posisi Tujuan Transfer:</label>
                                <select class="form-select" id="newPositionId" name="new_position_id" required>
                                    <option value="">-- Pilih Posisi Tujuan --</option>
                                    ${transferOptions}
                                </select>
                                <div class="form-text">
                                    Semua kandidat akan dipindahkan ke posisi yang dipilih
                                </div>
                            </div>

                            <div id="reasonSection" class="mb-3">
                                <label for="reason" class="form-label">Alasan (Opsional):</label>
                                <textarea class="form-control" id="reason" name="reason" rows="2" 
                                          placeholder="Berikan alasan transfer atau penghapusan..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Batal
                        </button>
                        <button type="submit" form="transferForm" class="btn btn-primary" id="confirmTransferBtn">
                            <i class="fas fa-check me-1"></i> Konfirmasi
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add event listeners for radio buttons
        const transferSection = modal.querySelector('#transferSection');
        const reasonSection = modal.querySelector('#reasonSection');
        const confirmBtn = modal.querySelector('#confirmTransferBtn');

        modal.querySelectorAll('input[name="action"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const selectedAction = e.target.value;
                
                if (selectedAction === 'transfer') {
                    transferSection.style.display = 'block';
                    reasonSection.style.display = 'block';
                    confirmBtn.innerHTML = '<i class="fas fa-exchange-alt me-1"></i> Transfer & Hapus';
                    confirmBtn.className = 'btn btn-warning';
                } else if (selectedAction === 'close') {
                    transferSection.style.display = 'none';
                    reasonSection.style.display = 'block';
                    confirmBtn.innerHTML = '<i class="fas fa-lock me-1"></i> Tutup Posisi';
                    confirmBtn.className = 'btn btn-secondary';
                } else if (selectedAction === 'cancel') {
                    transferSection.style.display = 'none';
                    reasonSection.style.display = 'none';
                    confirmBtn.innerHTML = '<i class="fas fa-times me-1"></i> Tutup';
                    confirmBtn.className = 'btn btn-secondary';
                }
            });
        });

        return modal;
    }

    async handleTransferSubmission(form) {
        const formData = new FormData(form);
        const positionId = form.dataset.positionId;
        const action = formData.get('action');

        if (action === 'cancel') {
            bootstrap.Modal.getInstance(form.closest('.modal')).hide();
            return;
        }

        try {
            let url, method, requestData;

            if (action === 'transfer') {
                const newPositionId = formData.get('new_position_id');
                if (!newPositionId) {
                    this.showErrorMessage('Silakan pilih posisi tujuan transfer');
                    return;
                }

                url = `/positions/${positionId}/transfer-candidates`;
                method = 'POST';
                requestData = {
                    new_position_id: newPositionId,
                    reason: formData.get('reason')
                };
            } else if (action === 'close') {
                url = `/positions/${positionId}/close`;
                method = 'POST';
                requestData = {
                    reason: formData.get('reason')
                };
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (data.success) {
                bootstrap.Modal.getInstance(form.closest('.modal')).hide();
                this.showSuccessMessage(data.message);
                
                if (action === 'transfer') {
                    this.removePositionFromDOM(positionId);
                } else if (action === 'close') {
                    this.updatePositionStatusInDOM(positionId, 'closed');
                }
            } else {
                this.showErrorMessage(data.message || 'Operation failed');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showErrorMessage('An error occurred while processing the request');
        }
    }

    removePositionFromDOM(positionId) {
        const positionElement = document.querySelector(`[data-position-id="${positionId}"]`)?.closest('.position-row, .position-card');
        if (positionElement) {
            positionElement.remove();
        }
    }

    updatePositionStatusInDOM(positionId, newStatus) {
        const statusElement = document.querySelector(`[data-position-id="${positionId}"]`)?.closest('.position-row, .position-card')?.querySelector('.status-badge');
        if (statusElement) {
            statusElement.textContent = newStatus === 'closed' ? 'Tutup' : 'Aktif';
            statusElement.className = `badge ${newStatus === 'closed' ? 'bg-secondary' : 'bg-success'}`;
        }
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type) {
        // Create and show toast notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bootstrapToast = new bootstrap.Toast(toast);
        bootstrapToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new PositionTransfer();
});