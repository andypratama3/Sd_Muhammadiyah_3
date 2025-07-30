<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; }
    h2 { text-align: center; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #aaa; padding: 5px; text-align: center; font-size: 12px; }
    th { background-color: #eee; }
  </style>
</head>
<body>

<h2>REKAPITULASI SPP DAN DPP<br>{{ $namaKelas }}<br>Tahun Ajaran 2025/2026</h2>

<table>
  <thead>
    <tr>
      <th rowspan="2">No</th>
      <th rowspan="2">Nama</th>
      <th colspan="{{ count($bulan) }}">SPP</th>
      <th colspan="2">DPP</th>
    </tr>
    <tr>
      @foreach($bulan as $b)
        <th>{{ $b }}</th>
      @endforeach
      <th>DPP 1</th>
      <th>DPP 2</th>
    </tr>
  </thead>
  <tbody>
    @foreach($siswaList as $siswa)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $siswa['nama'] }}</td>
        @foreach($bulan as $b)
          <td>{{ $siswa['pembayaran'][$b] ?? '' }}</td>
        @endforeach
        <td>{{ $siswa['dpp_1'] }}</td>
        <td>{{ $siswa['dpp_2'] }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

</body>
</html>
