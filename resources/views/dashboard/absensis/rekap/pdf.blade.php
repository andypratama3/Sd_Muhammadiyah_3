<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Karyawan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Calibri', sans-serif;
            font-size: 11px;
            color: #2c3e50;
            line-height: 1.4;
            background: #f5f6fa;
        }

        @page {
            margin: 20mm 15mm;
        }

        .container {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .logo-space {
            width: 60px;
            height: 60px;
            background: #e8eaf6;
            border-radius: 4px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #1e3a8a;
        }

        .header-text h1 {
            font-size: 20px;
            color: #1e3a8a;
            margin-bottom: 4px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header-text p {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }

        .period-info {
            background: #f0f4ff;
            padding: 12px 20px;
            border-radius: 4px;
            margin-top: 15px;
            display: inline-block;
            border-left: 4px solid #1e3a8a;
        }

        .period-info strong {
            color: #1e3a8a;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-top: 35px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e7ff;
            letter-spacing: 0.5px;
        }

        /* ===== TABLE STYLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: #1e3a8a;
            color: white;
        }

        th {
            padding: 12px 8px;
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.3px;
            border: none;
        }

        td {
            padding: 10px 8px;
            font-size: 10.5px;
            border-bottom: 1px solid #e8eef7;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:hover {
            background: #f0f4ff;
            transition: background 0.2s ease;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* ===== STATUS BADGES ===== */
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 10px;
            display: inline-block;
            min-width: 50px;
            text-align: center;
        }

        .badge-hadir { background: #d4edda; color: #155724; }
        .badge-cuti { background: #fff3cd; color: #856404; }
        .badge-izin { background: #d1ecf1; color: #0c5460; }
        .badge-sakit { background: #f8d7da; color: #721c24; }
        .badge-alpha { background: #e2e3e5; color: #383d41; }

        /* ===== SUMMARY BOX ===== */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4ff 100%);
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border-left: 4px solid #1e3a8a;
        }

        .summary-card.hadir { border-left-color: #27ae60; }
        .summary-card.cuti { border-left-color: #f39c12; }
        .summary-card.izin { border-left-color: #3498db; }
        .summary-card.sakit { border-left-color: #e74c3c; }
        .summary-card.alpha { border-left-color: #95a5a6; }

        .summary-label {
            font-size: 10px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 5px;
        }

        /* ===== EMPLOYEE SECTION ===== */
        .employee-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .employee-header {
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 15px;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }

        .employee-name {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 10px;
        }

        .employee-info span {
            display: block;
            margin: 3px 0;
        }

        .employee-info strong {
            color: #fff;
        }

        .employee-body {
            background: #f9fafb;
            padding: 15px;
            border-radius: 0 0 6px 6px;
            border: 1px solid #e8eef7;
            border-top: none;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e7ff;
            font-size: 9px;
            color: #999;
            display: flex;
            justify-content: space-between;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            body {
                background: white;
            }

            .container {
                box-shadow: none;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        <div class="header-top">
            <div class="logo-space">Logo</div>
            <div class="header-text">
                <h1>REKAP ABSENSI KARYAWAN</h1>
                <p>PT / Sekolah / Instansi XYZ</p>
                <p>Human Resources Department</p>
            </div>
        </div>
        <div class="period-info">
            Periode: <strong>{{ request('date') ?? 'Semua Tanggal' }}</strong>
        </div>
    </div>

    <!-- SUMMARY TABLE -->
    <div class="section-title">Ringkasan Absensi</div>
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama Karyawan</th>
                <th width="12%">NIP</th>
                <th width="16%">Jabatan</th>
                <th width="8%">Hadir</th>
                <th width="8%">Cuti</th>
                <th width="8%">Izin</th>
                <th width="8%">Sakit</th>
                <th width="8%">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawans as $i => $karyawan)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $karyawan->name }}</strong></td>
                    <td class="text-center">{{ $karyawan->nip ?? '-' }}</td>
                    <td>{{ $karyawan->jabatan ?? '-' }}</td>
                    <td class="text-center"><span class="badge badge-hadir">{{ $karyawan->hadir_count }}</span></td>
                    <td class="text-center"><span class="badge badge-cuti">{{ $karyawan->cuti_count }}</span></td>
                    <td class="text-center"><span class="badge badge-izin">{{ $karyawan->izin_count }}</span></td>
                    <td class="text-center"><span class="badge badge-sakit">{{ $karyawan->sakit_count }}</span></td>
                    <td class="text-center"><span class="badge badge-alpha">{{ $karyawan->alpha_count }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- DETAIL PER KARYAWAN -->
    <div class="section-title">Detail Absensi Per Karyawan</div>

    @foreach($karyawans as $karyawan)
        <div class="employee-section">
            <!-- Employee Header -->
            <div class="employee-header">
                <div class="employee-name">{{ $karyawan->name }}</div>
                <div class="employee-info">
                    <span><strong>NIP:</strong> {{ $karyawan->nip ?? '-' }}</span>
                    <span><strong>Jabatan:</strong> {{ $karyawan->jabatan ?? '-' }}</span>
                </div>
            </div>

            <!-- Employee Body -->
            <div class="employee-body">
                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card hadir">
                        <div class="summary-label">Hadir</div>
                        <div class="summary-value">{{ $karyawan->hadir_count }}</div>
                    </div>
                    <div class="summary-card cuti">
                        <div class="summary-label">Cuti</div>
                        <div class="summary-value">{{ $karyawan->cuti_count }}</div>
                    </div>
                    <div class="summary-card izin">
                        <div class="summary-label">Izin</div>
                        <div class="summary-value">{{ $karyawan->izin_count }}</div>
                    </div>
                    <div class="summary-card sakit">
                        <div class="summary-label">Sakit</div>
                        <div class="summary-value">{{ $karyawan->sakit_count }}</div>
                    </div>
                    <div class="summary-card alpha">
                        <div class="summary-label">Alpha</div>
                        <div class="summary-value">{{ $karyawan->alpha_count }}</div>
                    </div>
                </div>

                <!-- Detail Table -->
                <table>
                    <thead>
                        <tr>
                            <th width="12%">Tanggal</th>
                            <th width="12%">Hari</th>
                            <th width="14%">Status</th>
                            <th width="12%">Jam Masuk</th>
                            <th width="12%">Jam Pulang</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawan->absensi as $absen)
                            <tr>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }}
                                </td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($absen->tanggal)->locale('id')->translatedFormat('l') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ strtolower($absen->status_kehadiran) }}">
                                        {{ ucfirst($absen->status_kehadiran) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $absen->jam_masuk ?? '-' }}</td>
                                <td class="text-center">{{ $absen->jam_pulang ?? '-' }}</td>
                                <td>{{ $absen->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px; color: #999;">
                                    Tidak ada data absensi untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="page-break"></div>
    @endforeach

    <!-- FOOTER -->
    <div class="footer">
        <div>Dicetak oleh: Sistem Manajemen Absensi</div>
        <div>Tanggal Cetak: {{ now()->format('d-m-Y H:i:s') }}</div>
    </div>
</div>

</body>
</html>
