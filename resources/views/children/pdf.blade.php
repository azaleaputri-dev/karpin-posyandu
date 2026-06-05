<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Anak - {{ $child->child_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 14px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; margin: 0; }
        .subtitle { color: #475569; margin-top: 6px; }
        .grid { width: 100%; margin-top: 12px; }
        .grid td { width: 50%; vertical-align: top; padding: 8px 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .label { font-size: 10px; color: #64748b; text-transform: uppercase; }
        .value { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .section { margin-top: 24px; }
        .section h2 { font-size: 16px; margin: 0 0 8px; }
        .note { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 10px; }
        .badge { display: inline-block; padding: 5px 10px; background: #dcfce7; color: #166534; font-weight: bold; border-radius: 999px; }
        table.history { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.history th, table.history td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        table.history th { background: #e2e8f0; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Riwayat Pertumbuhan Anak</p>
        <p class="subtitle">Kartu Pintar Posyandu - Laporan ringkas untuk pemantauan anak</p>
    </div>

    <table class="grid" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="label">Nama Anak</div>
                <div class="value">{{ $child->child_name }}</div>
            </td>
            <td>
                <div class="label">Posyandu</div>
                <div class="value">{{ $child->posyandu->name }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Tanggal Lahir</div>
                <div class="value">{{ $child->birth_date->format('d M Y') }}</div>
            </td>
            <td>
                <div class="label">Usia</div>
                <div class="value">{{ $child->birth_date->age }} tahun</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Nama Ibu</div>
                <div class="value">{{ $child->mother_name }}</div>
            </td>
            <td>
                <div class="label">NIK</div>
                <div class="value">{{ $child->nik ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2>Status Gizi Sederhana</h2>
        <div class="note">
            <p><span class="badge">{{ $nutritionStatus['overall'] }}</span></p>
            <p><strong>Indikator berat:</strong> {{ $nutritionStatus['weight'] ?? '-' }}</p>
            <p><strong>Indikator tinggi:</strong> {{ $nutritionStatus['height'] ?? '-' }}</p>
            <p class="muted">{{ $nutritionStatus['note'] }}</p>
        </div>
    </div>

    <div class="section">
        <h2>Ringkasan Pengukuran</h2>
        <table class="grid" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div class="label">Berat Terakhir</div>
                    <div class="value">{{ $summary['latest_weight'] !== null ? $summary['latest_weight'] . ' kg' : '-' }}</div>
                </td>
                <td>
                    <div class="label">Tinggi Terakhir</div>
                    <div class="value">{{ $summary['latest_height'] !== null ? $summary['latest_height'] . ' cm' : '-' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Kenaikan Berat</div>
                    <div class="value">{{ $summary['weight_gain'] !== null ? number_format($summary['weight_gain'], 2) . ' kg' : '-' }}</div>
                </td>
                <td>
                    <div class="label">Kenaikan Tinggi</div>
                    <div class="value">{{ $summary['height_gain'] !== null ? number_format($summary['height_gain'], 2) . ' cm' : '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Riwayat Pengukuran</h2>
        <table class="history">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Berat</th>
                    <th>Tinggi</th>
                    <th>Suhu</th>
                    <th>Sumber</th>
                    <th>Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($measurements as $measurement)
                    <tr>
                        <td>{{ $measurement->measured_at->format('d M Y H:i') }}</td>
                        <td>{{ $measurement->weight_kg }} kg</td>
                        <td>{{ $measurement->height_cm }} cm</td>
                        <td>{{ $measurement->temperature_c !== null ? $measurement->temperature_c . ' C' : '-' }}</td>
                        <td>{{ strtoupper($measurement->source) }}</td>
                        <td>{{ optional($measurement->device)->device_name ?? 'Manual input' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada riwayat pengukuran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
