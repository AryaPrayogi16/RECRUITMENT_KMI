<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    @stack('styles')
</head>
<body>
    <div id="app">
        @auth
            @include('layouts.navbar')
        @endauth
        
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- SweetAlert2 untuk konfirmasi logout yang lebih bagus (opsional) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Global Logout Script dengan SweetAlert2 -->
    <script>
        // Jika menggunakan SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForms = document.querySelectorAll('form[action="{{ route("logout") }}"]');
            
            logoutForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: 'Apakah Anda yakin ingin keluar dari sistem?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
        
        // Atau tanpa SweetAlert2 (simple confirm)
        function handleLogout(event) {
            if (!confirm('Apakah Anda yakin ingin logout?')) {
                event.preventDefault();
            }
        }
    </script>

@auth
    <script>
        // Global AJAX Logout Function
        function ajaxLogout() {
            fetch('{{ route("logout") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.ok) {
                    // Tambahkan loading indicator
                    document.body.style.cursor = 'wait';
                    window.location.href = '{{ route("login") }}';
                } else {
                    throw new Error('Logout failed');
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                alert('Terjadi kesalahan saat logout. Silakan coba lagi.');
                document.body.style.cursor = 'default';
            });
        }
    </script>
@endauth

    @stack('scripts')
</body>
</html>
