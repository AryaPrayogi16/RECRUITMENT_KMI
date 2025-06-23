<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kandidat Summary - {{ date('d M Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #333;
        }
        
        .container {
            padding: 15px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
        }
        
        .header h1 {
            font-size: 16pt;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .header-info {
            font-size: 9pt;
            color: #6c757d;
        }
        
        /* Summary Stats */
        .summary-stats {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .stat-item {
            display: inline-block;
            margin: 0 15px;
            font-size: 9pt;
        }
        
        .stat-value {
            font-weight: bold;
            color: #4f46e5;
            font-size: 12pt;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 15px;
        }
        
        thead {
            background: #2d3748;
            color: white;
        }
        
        th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2d3748;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tbody tr:hover {
            background: #e9ecef;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-reviewing { background: #dbeafe; color: #1e40af; }
        .status-interview { background: #e0e7ff; color: #3730a3; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-screening { background: #fef3c7; color: #92400e; }
        .status-offered { background: #d1fae5; color: #065f46; }
        .status-withdrawn { background: #f3f4f6; color: #374151; }
        
        /* Compact Info */
        .compact-info {
            font-size: 7pt;
            line-height: 1.2;
        }
        
        .info-line {
            margin-bottom: 2px;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 7pt;
            color: #6c757d;
            text-align: center;
        }
        
        /* Page info */
        .page-info {
            text-align: right;
            font-size: 7pt;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        /* Truncate text */
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Ringkasan Data Kandidat</h1>
            <div class="header-info">
                Generated: {{ now()->format('d F Y H:i') }} | Total: {{ $candidates->count() }} kandidat
            </div>
        </div>
        
        <!-- Summary Statistics -->
        <div class="summary-stats">
            @php
                $statusCounts = $candidates->groupBy('application_status')->map->count();
            @endphp
            <div class="stat-item">
                <div class="stat-value">{{ $candidates->count() }}</div>
                <div>Total Kandidat</div>
            </div>
            @foreach($statusCounts as $status => $count)
                <div class="stat-item">
                    <div class="stat-value">{{ $count }}</div>
                    <div>{{ ucfirst($status) }}</div>
                </div>
            @endforeach
        </div>
        
        <!-- Candidates Table -->
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="8%">Kode</th>
                    <th width="15%">Nama</th>
                    <th width="12%">Kontak</th>
                    <th width="12%">Posisi</th>
                    <th width="8%">Status</th>
                    <th width="8%">Tgl Apply</th>
                    <th width="12%">Pendidikan</th>
                    <th width="12%">Pengalaman</th>
                    <th width="10%">Gaji Harapan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidates as $index => $candidate)
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>{{ $candidate->candidate_code }}</td>
                        <td>
                            <strong>{{ $candidate->personalData->full_name ?? 'N/A' }}</strong><br>
                            <span style="font-size: 7pt; color: #6c757d;">
                                {{ $candidate->personalData->birth_place ?? '' }}
                                @if($candidate->personalData->birth_date)
                                    ({{ \Carbon\Carbon::parse($candidate->personalData->birth_date)->age }} thn)
                                @endif
                            </span>
                        </td>
                        <td class="compact-info">
                            <div class="info-line">📧 {{ Str::limit($candidate->personalData->email ?? 'N/A', 20) }}</div>
                            <div class="info-line">📱 {{ $candidate->personalData->phone_number ?? 'N/A' }}</div>
                        </td>
                        <td>{{ Str::limit($candidate->position_applied, 25) }}</td>
                        <td align="center">
                            <span class="status-badge status-{{ $candidate->application_status }}">
                                {{ ucfirst($candidate->application_status) }}
                            </span>
                        </td>
                        <td align="center">{{ $candidate->created_at->format('d/m/y') }}</td>
                        <td class="compact-info">
                            @php
                                $latestEducation = $candidate->formalEducation->sortByDesc('end_year')->first();
                            @endphp
                            @if($latestEducation)
                                <strong>{{ $latestEducation->education_level }}</strong><br>
                                {{ Str::limit($latestEducation->institution_name, 20) }}<br>
                                {{ $latestEducation->major ? Str::limit($latestEducation->major, 15) : '' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="compact-info">
                            @php
                                $latestWork = $candidate->workExperiences->sortByDesc('end_year')->first();
                                $totalYears = 0;
                                foreach($candidate->workExperiences as $exp) {
                                    $start = $exp->start_year;
                                    $end = $exp->end_year ?? date('Y');
                                    $totalYears += ($end - $start);
                                }
                            @endphp
                            @if($latestWork)
                                <strong>{{ Str::limit($latestWork->position, 20) }}</strong><br>
                                {{ Str::limit($latestWork->company_name, 20) }}<br>
                                Total: {{ $totalYears }} thn
                            @else
                                Fresh Graduate
                            @endif
                        </td>
                        <td align="right">
                            @if($candidate->expected_salary)
                                {{ number_format($candidate->expected_salary / 1000000, 1) }}jt
                            @else
                                Nego
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Page Info -->
        <div class="page-info">
            Halaman 1 dari 1
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>{{ config('app.name') }} - Dokumen Rahasia | Generated by: {{ Auth::user()->full_name }}</p>
        </div>
    </div>
</body>
</html>