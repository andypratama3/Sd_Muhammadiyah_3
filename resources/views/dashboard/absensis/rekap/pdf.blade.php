<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Fingerscan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 15mm;
            size: A4;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        h2, h3 { margin: 0; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; font-size: 10px; }

        th { text-align: center; font-weight: bold; background-color: #f0f0f0; }

        .header { text-align: center; margin-bottom: 10px; }

        .info { margin-bottom: 10px; }
        .info td { border: none; padding: 2px 4px; }

        .hari-merah { color: red; font-weight: bold; }

        .summary {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .ttd { margin-top: 30px; }
        .ttd td {
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
            padding-top: 40px;
            width: 33.33%;
        }

        .keterangan {
            font-size: 10px;
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }

        .keterangan strong { display: block; margin-bottom: 5px; }

        .page-break { page-break-after: always; margin-top: 20px; }

        .no-data { text-align: center; padding: 20px; font-style: italic; color: #666; }
    </style>
</head>
<body>

@forelse($karyawans as $karyawan)

    <!-- HEADER -->
    <div class="header">
        <h2>REKAPITULASI FINGERSCAN</h2>
        <h3>SEKOLAH KREATIF SD MUHAMMADIYAH 3</h3>
        <h3>TAHUN {{ now()->year }}</h3>
    </div>

    <!-- INFO -->
    <table class="info">
        <tr>
            <td width="60" style="border: none;">Periode</td>
            <td width="10" style="border: none;">:</td>
            <td style="border: none;">
                @if(request('date'))
                    {{ request('date') }}
                @else
                    {{ now()->translatedFormat('F Y') }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="border: none;">Nama</td>
            <td style="border: none;">:</td>
            <td style="border: none;"><strong>{{ $karyawan->name ?? '-' }}</strong></td>
        </tr>
    </table>

    <!-- TABEL ABSENSI -->
    @if($karyawan->absensi && $karyawan->absensi->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="10%">Tanggal</th>
                <th width="9%">Hari</th>
                <th width="10%">Jam Datang</th>
                <th width="10%">Jam Pulang</th>
                <th width="10%">RP Datang</th>
                <th width="10%">RP Pulang</th>
                <th width="8%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRpMasuk = 0;
                $totalRpPulang = 0;
            @endphp

            @foreach($karyawan->absensi as $row)
                @php
                    $totalRpMasuk += floatval($row->rp_masuk ?? 0);
                    $totalRpPulang += floatval($row->rp_pulang ?? 0);
                    $tanggal = \Carbon\Carbon::parse($row->tanggal);
                    $hari = $tanggal->locale('id')->translatedFormat('l');
                    $isWeekend = in_array($hari, ['Minggu']);
                @endphp
                <tr>
                    <td class="text-center">{{ $tanggal->format('d-m-Y') }}</td>
                    <td class="text-center {{ $isWeekend ? 'hari-merah' : '' }}">
                        {{ $hari }}
                    </td>
                    <td class="text-center">
                        @if($row->jam_masuk)
                            {{ \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->jam_pulang)
                            {{ \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->rp_masuk)
                            Rp. {{ number_format(floatval($row->rp_masuk), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->rp_pulang)
                            Rp. {{ number_format(floatval($row->rp_pulang), 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @switch($row->status_kehadiran)
                            @case('hadir')
                                <span style="color: green;">Hadir</span>
                                @break
                            @case('cuti')
                                <span style="color: orange;">Cuti</span>
                                @break
                            @case('izin')
                                <span style="color: blue;">Izin</span>
                                @break
                            @case('sakit')
                                <span style="color: red;">Sakit</span>
                                @break
                            @case('alpha')
                                <span style="color: darkred;">Alpha</span>
                                @break
                            @default
                                -
                        @endswitch
                    </td>
                    <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>

        <!-- TOTAL -->
        <tfoot>
            <tr class="summary">
                <td colspan="5" class="text-center">Jumlah Total</td>
                <td class="text-center">Rp. {{ number_format($totalRpMasuk, 0, ',', '.') }}</td>
                <td class="text-center">Rp. {{ number_format($totalRpPulang, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr class="summary">
                <td colspan="6" class="text-center">Jumlah yang di terima</td>
                <td class="text-center">Rp. {{ number_format($totalRpMasuk + $totalRpPulang, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @else
    <div class="no-data">
        Tidak ada data absensi untuk periode ini.
    </div>
    @endif

    <!-- TTD -->
    <table class="ttd" width="100%">
        <tr>
            <td>
                Diterima Oleh<br><br><br>
                <strong>{{ $karyawan->name }}</strong>
            </td>
            <td>
                Disetujui Oleh<br><br><br>
                <strong></strong>
            </td>
            <td>
                Kepala Sekolah<br><br><br>
                <strong>Ansar HS, S.Pd.M.M.Gr</strong>
            </td>
        </tr>
    </table>

    <!-- KETERANGAN -->
    <div class="keterangan">
        <strong>Ringkasan Absensi :</strong>
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; width: 25%;">Hadir : {{ $karyawan->hadir_count ?? 0 }}x</td>
                <td style="border: none; width: 25%;">Sakit : {{ $karyawan->sakit_count ?? 0 }}x</td>
                <td style="border: none; width: 25%;">Izin : {{ $karyawan->izin_count ?? 0 }}x</td>
                <td style="border: none; width: 25%;">Cuti : {{ $karyawan->cuti_count ?? 0 }}x</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;" colspan="4">Alpha : {{ $karyawan->alpha_count ?? 0 }}x</td>
            </tr>
        </table>
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

@empty
    <div class="no-data">
        <h3>Tidak ada data karyawan untuk ditampilkan</h3>
    </div>
@endforelse

</body>
</html>
