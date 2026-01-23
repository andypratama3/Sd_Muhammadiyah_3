<!DOCTYPE html>
<html>
<head>
    <style>
        table { width:100%; border-collapse: collapse; }
        th,td { border:1px solid #000; padding:5px; font-size:12px; }
    </style>
</head>
<body>
<h3 align="center">Rekap Absensi</h3>

<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Masuk</th>
            <th>Pulang</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekapAbsensi as $row)
        <tr>
            <td>{{ $row->karyawan->nama ?? '-' }}</td>
            <td>{{ $row->tanggal->format('d-m-Y') }}</td>
            <td>{{ $row->status_kehadiran }}</td>
            <td>{{ $row->jam_masuk }}</td>
            <td>{{ $row->jam_pulang }}</td>
            <td>{{ $row->keterangan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
