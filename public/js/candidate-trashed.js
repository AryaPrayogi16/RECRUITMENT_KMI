// Restore candidate
function restoreCandidate(id, name) {
    Swal.fire({
        title: 'Pulihkan Kandidat?',
        text: `Apakah Anda yakin ingin memulihkan kandidat "${name}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Pulihkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/candidates/${id}/restore`, {
                method: 'POST',
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

// Force delete candidate
function forceDeleteCandidate(id, name) {
    Swal.fire({
        title: 'Hapus Permanen?',
        text: `PERINGATAN: Kandidat "${name}" akan dihapus PERMANEN dan tidak dapat dipulihkan!`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal',
        footer: '<small>Tindakan ini tidak dapat dibatalkan!</small>'
    }).then((result) => {
        if (result.isConfirmed) {
            // Double confirmation
            Swal.fire({
                title: 'Konfirmasi Terakhir',
                text: 'Ketik "HAPUS" untuk konfirmasi',
                input: 'text',
                inputValidator: (value) => {
                    if (value !== 'HAPUS') {
                        return 'Ketik "HAPUS" untuk melanjutkan';
                    }
                },
                showCancelButton: true,
                confirmButtonText: 'Hapus Permanen'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    fetch(`/candidates/${id}/force`, {
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
                            Swal.fire('Terhapus!', data.message, 'success').then(() => {
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
    });
}

// Expose to global scope
window.restoreCandidate = restoreCandidate;
window.forceDeleteCandidate = forceDeleteCandidate;