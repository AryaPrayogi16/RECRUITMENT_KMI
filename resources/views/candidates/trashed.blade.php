<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kandidat Terhapus - HR System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/candidate.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar sama dengan index.blade.php -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <span class="logo-text">HR System</span>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="{{ route('candidates.index') }}" class="nav-link">
                        <i class="fas fa-user-tie"></i>
                        <span>Kandidat</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('candidates.trashed') }}" class="nav-link active">
                        <i class="fas fa-trash-restore"></i>
                        <span>Kandidat Terhapus</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="breadcrumb">
                            <a href="{{ route('candidates.index') }}">Kandidat</a>
                            <span>/</span>
                            <span>Kandidat Terhapus</span>
                        </div>
                        <h1 class="page-title">Kandidat Terhapus</h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('candidates.index') }}" class="btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Kandidat
                    </a>
                </div>
            </header>

            <div class="content">
                <!-- Search Filter -->
                <div class="filters-section">
                    <form class="search-form" method="GET">
                        <div class="search-input-container">
                            <input type="text" name="search" class="search-input" placeholder="Cari kandidat terhapus..." value="{{ request('search') }}">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Kandidat Terhapus</h3>
                        <span class="table-info">Total: {{ $candidates->total() }} kandidat</span>
                    </div>
                    
                    <table class="candidates-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Kandidat</th>
                                <th>Posisi</th>
                                <th>Dihapus Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $index => $candidate)
                            <tr>
                                <td>{{ $candidates->firstItem() + $index }}</td>
                                <td>{{ $candidate->candidate_code }}</td>
                                <td>
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            {{ substr($candidate->personalData->full_name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <div class="candidate-details">
                                            <div class="candidate-name">{{ $candidate->personalData->full_name ?? 'N/A' }}</div>
                                            <div class="candidate-email">{{ $candidate->personalData->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $candidate->position_applied }}</td>
                                <td>{{ $candidate->deleted_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button class="btn-small btn-success" onclick="restoreCandidate({{ $candidate->id }}, '{{ $candidate->personalData->full_name ?? 'Unknown' }}')">
                                        <i class="fas fa-undo"></i> Pulihkan
                                    </button>
                                    <button class="btn-small btn-danger" onclick="forceDeleteCandidate({{ $candidate->id }}, '{{ $candidate->personalData->full_name ?? 'Unknown' }}')">
                                        <i class="fas fa-trash"></i> Hapus Permanen
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Tidak ada kandidat terhapus</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        {{ $candidates->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/candidate-trashed.js') }}"></script>
</body>
</html>
