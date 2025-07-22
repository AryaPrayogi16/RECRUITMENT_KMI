{{-- resources/views/candidates/storage-management.blade.php --}}

@extends('layouts.app')

@section('title', 'Storage Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Storage Management</h6>
                        <div class="ms-auto">
                            <button class="btn btn-primary btn-sm" onclick="loadStorageStats()">
                                <i class="fa fa-refresh me-2"></i>Refresh Stats
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Storage Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Total Size</h5>
                                            <span class="h2 font-weight-bold mb-0" id="total-size">Loading...</span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                                <i class="fa fa-hdd-o text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Total Files</h5>
                                            <span class="h2 font-weight-bold mb-0" id="total-files">Loading...</span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                                <i class="fa fa-file-o text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Active Candidates</h5>
                                            <span class="h2 font-weight-bold mb-0" id="active-candidates">Loading...</span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                                <i class="fa fa-users text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Orphaned Folders</h5>
                                            <span class="h2 font-weight-bold mb-0" id="orphaned-folders">Loading...</span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                                <i class="fa fa-folder-open text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Storage Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    <i class="fa fa-trash-o fa-2x text-warning"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">Cleanup Orphaned Folders</h6>
                                                    <p class="text-muted mb-0">Remove folders that no longer have associated candidate records</p>
                                                </div>
                                                <div>
                                                    <button class="btn btn-warning btn-sm" onclick="cleanupOrphanedFolders()">
                                                        <i class="fa fa-trash me-1"></i>Cleanup
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    <i class="fa fa-users fa-2x text-info"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">Manage Trashed Candidates</h6>
                                                    <p class="text-muted mb-0">View and manage candidates in trash</p>
                                                </div>
                                                <div>
                                                    <a href="{{ route('candidates.trashed') }}" class="btn btn-info btn-sm">
                                                        <i class="fa fa-eye me-1"></i>View Trash
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Usage Chart -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Storage Usage Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Total Storage Used:</strong></td>
                                                            <td id="detail-total-size">-</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Files:</strong></td>
                                                            <td id="detail-total-files">-</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Folders:</strong></td>
                                                            <td id="detail-total-folders">-</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Active Candidates:</strong></td>
                                                            <td id="detail-active-candidates">-</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Trashed Candidates:</strong></td>
                                                            <td id="detail-trashed-candidates">-</td>
                                                        </tr>
                                                        <tr class="table-warning">
                                                            <td><strong>Orphaned Folders:</strong></td>
                                                            <td id="detail-orphaned-folders">-</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="alert alert-info">
                                                <h6><i class="fa fa-info-circle me-2"></i>Storage Management Tips</h6>
                                                <ul class="mb-0">
                                                    <li>Regularly cleanup orphaned folders to free up storage space</li>
                                                    <li>Review trashed candidates periodically and force delete if no longer needed</li>
                                                    <li>Monitor storage usage to ensure optimal performance</li>
                                                    <li>Force delete will permanently remove all associated files</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0" id="loading-message">Processing...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadStorageStats();
});

function loadStorageStats() {
    $.ajax({
        url: '{{ route('api.storageStats') }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateStorageStats(response.data);
            } else {
                showAlert('error', response.message || 'Failed to load storage stats');
            }
        },
        error: function(xhr) {
            showAlert('error', 'Error loading storage statistics');
        }
    });
}

function updateStorageStats(data) {
    // Update cards
    $('#total-size').text(data.total_size);
    $('#total-files').text(data.total_files.toLocaleString());
    $('#active-candidates').text(data.active_candidates.toLocaleString());
    $('#orphaned-folders').text(data.orphaned_folders.toLocaleString());
    
    // Update details table
    $('#detail-total-size').text(data.total_size);
    $('#detail-total-files').text(data.total_files.toLocaleString());
    $('#detail-total-folders').text(data.total_folders.toLocaleString());
    $('#detail-active-candidates').text(data.active_candidates.toLocaleString());
    $('#detail-trashed-candidates').text(data.trashed_candidates.toLocaleString());
    $('#detail-orphaned-folders').text(data.orphaned_folders.toLocaleString());
    
    // Highlight orphaned folders if any
    if (data.orphaned_folders > 0) {
        $('#orphaned-folders').parent().parent().addClass('text-warning');
    } else {
        $('#orphaned-folders').parent().parent().removeClass('text-warning');
    }
}

function cleanupOrphanedFolders() {
    if (!confirm('Are you sure you want to cleanup orphaned folders? This action cannot be undone.')) {
        return;
    }
    
    showLoading('Cleaning up orphaned folders...');
    
    $.ajax({
        url: '{{ route('api.cleanupOrphanedFolders') }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showAlert('success', response.message);
                loadStorageStats(); // Refresh stats
            } else {
                showAlert('error', response.message || 'Failed to cleanup orphaned folders');
            }
        },
        error: function(xhr) {
            hideLoading();
            showAlert('error', 'Error cleaning up orphaned folders');
        }
    });
}

function showLoading(message) {
    $('#loading-message').text(message);
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of the card body
    $('.card-body').first().prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush