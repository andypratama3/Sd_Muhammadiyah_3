<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Absensi</title>
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
            size: A4 landscape;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }

        h2, h3 { margin: 0; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; font-size: 10px; }
        th { text-align: center; font-weight: bold; background-color: #f0f0f0; }

        tfoot td       { border: 1px solid #000 !important; }
        tfoot td:empty { border-left: 1px solid #000; border-right: 1px solid #000; }

        .header  { text-align: center; margin-bottom: 10px; }
        .info    { margin-bottom: 10px; }
        .info td { border: none; padding: 2px 4px; }

        .hari-merah { color: red; font-weight: bold; }
        .summary    { font-weight: bold; background-color: #f0f0f0; }

        .ttd    { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .ttd td { border: none; text-align: center; vertical-align: top;
                  font-size: 10px; width: 33.33%; padding: 10px; }

        .ttd-label { margin-bottom: 8px; }
        .ttd-space { height: 70px; }
        .ttd-img   { width: 100px; height: auto; display: block; margin: 0 auto; }
        .ttd-name  { margin-top: 8px; font-weight: bold; }

        .keterangan        { font-size: 10px; margin-top: 20px; border: 1px solid #000; padding: 10px; }
        .keterangan strong { display: block; margin-bottom: 5px; }
        .keterangan table  { border: none; }
        .keterangan td     { border: none; }

        .page-break { page-break-after: always; }
        .no-data    { text-align: center; padding: 20px; font-style: italic; color: #666; }
    </style>
</head>
<body>

@forelse($karyawans as $karyawan)

    <!-- HEADER -->
    <div class="header">
        <h2>REKAPITULASI ABSENSI</h2>
        <h3>SEKOLAH KREATIF SD MUHAMMADIYAH 3</h3>
        <h3>TAHUN {{ now()->year }}</h3>
    </div>

    <!-- INFO -->
    <table class="info">
        <tr>
            <td width="80" style="border:none;">Periode</td>
            <td width="10" style="border:none;">:</td>
            <td style="border:none;">{{ $dateRange }}</td>
        </tr>
        <tr>
            <td style="border:none;">Nama</td>
            <td style="border:none;">:</td>
            <td style="border:none;"><strong>{{ $karyawan->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td style="border:none;">Jenis Pegawai</td>
            <td style="border:none;">:</td>
            <td style="border:none;"><strong>{{ $karyawan->jenis_pegawai_from_role ?? '-' }}</strong></td>
        </tr>
    </table>

    <!-- TABEL ABSENSI -->
    @if($karyawan->absensi && $karyawan->absensi->count() > 0)

        @php
            $totalRpMasuk  = 0;
            $totalRpPulang = 0;
        @endphp

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
                @foreach($karyawan->absensi as $row)
                    @php
                        $totalRpMasuk  += floatval($row->rp_masuk  ?? 0);
                        $totalRpPulang += floatval($row->rp_pulang ?? 0);
                        $tanggal        = \Carbon\Carbon::parse($row->tanggal);
                        $hari           = $tanggal->locale('id')->translatedFormat('l');
                        $isWeekend      = ($hari === 'Minggu');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $tanggal->format('d-m-Y') }}</td>
                        <td class="text-center {{ $isWeekend ? 'hari-merah' : '' }}">{{ $hari }}</td>
                        <td class="text-center">
                            {{ $row->jam_masuk  ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i')  : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $row->jam_pulang ? \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $row->rp_masuk  ? 'Rp. ' . number_format(floatval($row->rp_masuk),  0, ',', '.') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $row->rp_pulang ? 'Rp. ' . number_format(floatval($row->rp_pulang), 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-center">
                            @switch($row->status_kehadiran)
                                @case('hadir')  <span style="color:green;">Hadir</span>    @break
                                @case('cuti')   <span style="color:orange;">Cuti</span>    @break
                                @case('izin')   <span style="color:blue;">Izin</span>      @break
                                @case('sakit')  <span style="color:red;">Sakit</span>      @break
                                @case('alpha')  <span style="color:darkred;">Alpha</span>  @break
                                @default -
                            @endswitch
                        </td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="summary">
                    <td colspan="4" class="text-center">Jumlah Total</td>
                    <td class="text-center">Rp. {{ number_format($totalRpMasuk,  0, ',', '.') }}</td>
                    <td class="text-center">Rp. {{ number_format($totalRpPulang, 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="summary">
                    <td colspan="4" class="text-center">Jumlah yang diterima</td>
                    <td colspan="2" class="text-center">
                        Rp. {{ number_format($totalRpMasuk + $totalRpPulang, 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    @else
        <div class="no-data">Tidak ada data absensi untuk periode ini.</div>
    @endif

    <!-- TTD -->
    <table class="ttd">
        <tr>
            <!-- Kolom 1: Diterima Oleh -->
            <td>
                <div class="ttd-label">Diterima Oleh</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $karyawan->name ?? '-' }}</div>
            </td>

            <!-- Kolom 2: Disetujui Oleh -->
            <td>
                <div class="ttd-label">Disetujui Oleh</div>
                <div class="ttd-space">
                    @if(!empty($ttdRusminiBase64))
                        <img src="{{ $ttdRusminiBase64 }}" class="ttd-img" alt="TTD Rusmini">
                    @endif
                </div>
                <div class="ttd-name">Rusmini S.Pd</div>
            </td>

            <!-- Kolom 3: Kepala Sekolah -->
            <td>
                <div class="ttd-label">Kepala Sekolah</div>
                <div class="ttd-space">
                    @if(!empty($ttdKepalaBase64))
                        <img src="{{ $ttdKepalaBase64 }}" class="ttd-img" alt="TTD Kepala Sekolah">
                    @endif
                </div>
                <div class="ttd-name">Ansar HS, S.Pd.M.M.Gr</div>
            </td>
        </tr>
    </table>

    <!-- RINGKASAN -->
    <div class="keterangan">
        <strong>Ringkasan Absensi :</strong>
        <table>
            <tr>
                <td width="25%">Hadir : {{ $karyawan->hadir_count ?? 0 }}x</td>
                <td width="25%">Sakit : {{ $karyawan->sakit_count ?? 0 }}x</td>
                <td width="25%">Izin  : {{ $karyawan->izin_count  ?? 0 }}x</td>
                <td width="25%">Cuti  : {{ $karyawan->cuti_count  ?? 0 }}x</td>
            </tr>
            <tr>
                <td colspan="4">Alpha : {{ $karyawan->alpha_count ?? 0 }}x</td>
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
