<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Training</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 0 0 16px; color: #636363; }
        .meta { font-size: 10px; color: #636363; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #c9e0fc; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #636363; border-bottom: 1px solid #e8e8e8; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #f9d4d2; color: #b3262b; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-info { background: #c9e0fc; color: #024ad8; }
        .badge-neutral { background: #e8e8e8; color: #3d3d3d; }
        .footer { margin-top: 24px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #e8e8e8; padding-top: 12px; }
    </style>
</head>
<body>
    <h1>Laporan Training</h1>
    <h2>Sistem E-Learning Training Karyawan</h2>
    <p class="meta">Tanggal Ekspor: {{ $exportDate }}</p>

    @if ($reportRows->isEmpty())
        <p style="text-align:center; padding:40px 0; color:#999;">Tidak ada data untuk diekspor.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Training</th>
                    <th>Ditugaskan</th>
                    <th>Belum Mulai</th>
                    <th>Berjalan</th>
                    <th>Selesai</th>
                    <th>Lulus</th>
                    <th>Tidak Lulus</th>
                    <th>Menunggu</th>
                    <th>Avg Pre</th>
                    <th>Avg Post</th>
                    <th>% Selesai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportRows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row['training']->title }}</td>
                        <td>{{ $row['total_employees'] }}</td>
                        <td>{{ $row['not_started'] }}</td>
                        <td>{{ $row['in_progress'] }}</td>
                        <td>{{ $row['completed'] }}</td>
                        <td>{{ $row['passed'] }}</td>
                        <td>{{ $row['failed'] }}</td>
                        <td>{{ $row['waiting_review'] }}</td>
                        <td>{{ $row['avg_pre_test'] > 0 ? number_format((float) $row['avg_pre_test'], 1) : '-' }}</td>
                        <td>{{ $row['avg_post_test'] > 0 ? number_format((float) $row['avg_post_test'], 1) : '-' }}</td>
                        <td>{{ $row['completion_pct'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem E-Learning Training Karyawan.</p>
    </div>
</body>
</html>
