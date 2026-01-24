<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Calibri', sans-serif;
            font-size: 11px;
            color: #2c3e50;
            line-height: 1.5;
        }

        @page {
            margin: 15mm;
            size: A4 landscape;
        }

        .container {
            width: 100%;
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16px;
            color: #1e3a8a;
            margin-bottom: 5px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }

        .period-info {
            font-size: 10px;
            color: #1e3a8a;
            font-weight: bold;
            margin-top: 8px;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: #1e3a8a;
            padding: 8px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        /* ===== TABLE STYLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead {
            background-color: #1e3a8a;
            color: white;
        }

        thead th {
            padding: 10px 8px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #1e3a8a;
            letter-spacing: 0.3px;
        }

        tbody td {
            padding: 8px;
            font-size: 10px;
            border: 1px solid #e0e0e0;
            color: #333;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        /* ===== SUMMARY SECTION ===== */
        .summary-box {
            background-color: #f0f4ff;
            border-left: 4px solid #1e3a8a;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .summary-label {
            font-weight: bold;
            color: #1e3a8a;
        }

        .summary-value {
            color: #333;
        }

        /* ===== EMPLOYEE DETAIL ===== */
        .employee-header {
            background-color: #e8eaf6;
            border-bottom: 2px solid #1e3a8a;
            padding: 10px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .employee-name {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .employee-info {
            font-size: 9px;
            color: #666;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }

        .employee-info span {
            display: block;
        }

        .employee-info strong {
            color: #1e3a8a;
            font-weight: bold;
        }

        /* ===== SUMMARY CARDS ===== */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin: 10px 0 15px 0;
        }

        .summary-card {
            background-color: #f9fafb;
            border: 1px solid #e0e0e0;
            border-left: 3px solid #1e3a8a;
            padding: 8px;
            text-align: center;
            border-radius: 3px;
        }

        .summary-card.hadir {
            border-left-color: #27ae60;
        }

        .summary-card.cuti {
            border-left-color: #f39c12;
        }

        .summary-card.izin {
            border-left-color: #3498db;
        }

        .summary-card.sakit {
            border-left-color: #e74c3c;
        }

        .summary-card.alpha {
            border-left-color: #95a5a6;
        }

        .card-label {
            font-size: 9px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e0e7ff;
            font-size: 9px;
            color: #999;
            display: flex;
            justify-content: space-between;
        }

        /* ===== PAGE BREAK ===== */
        .page-break {
            page-break-after: always;
            margin-bottom: 20px;
        }

        /* ===== NO DATA ===== */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 10px;
            background-color: #f5f5f5;
            border-radius: 3px;
        }

        /* ===== TOTAL ROW ===== */
        .total-row {
            background-color: #e8eaf6;
            font-weight: bold;
            color: #1e3a8a;
        }

        .total-row td {
            border: 1px solid #1e3a8a;
        }
    </style>
</head>
<body>

<div class="container">
    @forelse($karyawans as $i => $karyawan)
        @if($i > 0)
            <div class="page-break"></div>
        @endif

        <!-- HEADER -->
        <div class="header">
            <h1>REKAP ABSENSI KARYAWAN</h1>
            <p>PT / Sekolah / Instansi XYZ</p>
            <p>Human Resources Department</p>
            <div class="period-info">
                Periode: {{ $dateRange ?? 'Semua Tanggal' }}
            </div>
        </div>

        @if($i === 0)
            <!-- RINGKASAN ABSENSI - Hanya di halaman pertama -->
            <div class="section-title">📊 Ringkasan Absensi Semua Karyawan</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 20%;">Nama Karyawan</th>
                        <th style="width: 12%;">NIP</th>
                        <th style="width: 16%;">Jabatan</th>
                        <th style="width: 8%;">Hadir</th>
                        <th style="width: 8%;">Cuti</th>
                        <th style="width: 8%;">Izin</th>
                        <th style="width: 8%;">Sakit</th>
                        <th style="width: 8%;">Alpha</th>
                        <th style="width: 8%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawans as $idx => $k)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-left"><strong>{{ $k->name }}</strong></td>
                            <td class="text-center">{{ $k->nip ?? '-' }}</td>
                            <td class="text-left">{{ $k->jabatan ?? '-' }}</td>
                            <td class="text-center">{{ $k->hadir_count ?? 0 }}</td>
                            <td class="text-center">{{ $k->cuti_count ?? 0 }}</td>
                            <td class="text-center">{{ $k->izin_count ?? 0 }}</td>
                            <td class="text-center">{{ $k->sakit_count ?? 0 }}</td>
                            <td class="text-center">{{ $k->alpha_count ?? 0 }}</td>
                            <td class="text-center"><strong>{{ ($k->hadir_count ?? 0) + ($k->cuti_count ?? 0) + ($k->izin_count ?? 0) + ($k->sakit_count ?? 0) + ($k->alpha_count ?? 0) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="page-break"></div>
        @endif

        <!-- DETAIL ABSENSI PER KARYAWAN -->
        <div class="section-title">📋 Detail Absensi - {{ $karyawan->name }}</div>

        <!-- Employee Header -->
        <div class="employee-header">
            <div class="employee-name">{{ $karyawan->name }}</div>
            <div class="employee-info">
                <span><strong>NIP:</strong> {{ $karyawan->nip ?? '-' }}</span>
                <span><strong>Jabatan:</strong> {{ $karyawan->jabatan ?? '-' }}</span>
                <span><strong>Periode:</strong> {{ $dateRange ?? 'Semua Tanggal' }}</span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card hadir">
                <div class="card-label">Hadir</div>
                <div class="card-value">{{ $karyawan->hadir_count ?? 0 }}</div>
            </div>
            <div class="summary-card cuti">
                <div class="card-label">Cuti</div>
                <div class="card-value">{{ $karyawan->cuti_count ?? 0 }}</div>
            </div>
            <div class="summary-card izin">
                <div class="card-label">Izin</div>
                <div class="card-value">{{ $karyawan->izin_count ?? 0 }}</div>
            </div>
            <div class="summary-card sakit">
                <div class="card-label">Sakit</div>
                <div class="card-value">{{ $karyawan->sakit_count ?? 0 }}</div>
            </div>
            <div class="summary-card alpha">
                <div class="card-label">Alpha</div>
                <div class="card-value">{{ $karyawan->alpha_count ?? 0 }}</div>
            </div>
        </div>

        <!-- Detail Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 10%;">Hari</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;">Jam Masuk</th>
                    <th style="width: 10%;">Jam Pulang</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawan->absensi as $idx => $absen)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($absen->tanggal)->locale('id')->translatedFormat('l') }}</td>
                        <td class="text-center">
                            <strong>{{ ucfirst($absen->status_kehadiran) }}</strong>
                        </td>
                        <td class="text-center">{{ $absen->jam_masuk ?? '-' }}</td>
                        <td class="text-center">{{ $absen->jam_pulang ?? '-' }}</td>
                        <td class="text-left">{{ $absen->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-data">
                            Tidak ada data absensi untuk periode ini
                        </td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                    <td class="text-center"><strong>{{ count($karyawan->absensi) }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div>Dicetak: {{ now()->format('d-m-Y H:i:s') }}</div>
            <div>Sistem Manajemen Absensi</div>
        </div>

    @empty
        <div class="header">
            <h1>REKAP ABSENSI KARYAWAN</h1>
            <p>PT / Sekolah / Instansi XYZ</p>
        </div>

        <div class="no-data">
            Tidak ada data karyawan untuk ditampilkan
        </div>
    @endforelse

</div>

</body>
</html>
