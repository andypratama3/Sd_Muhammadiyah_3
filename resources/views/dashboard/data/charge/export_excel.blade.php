<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekapitulasi SPP dan DPP Tahun Ajaran 2025/2026</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 30px; }
    h2 { text-align: center; margin-top: 20px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 50px; }
    th, td { border: 1px solid #aaa; padding: 6px; text-align: center; font-size: 14px; }
    th { background-color: #f0f0f0; }
    .sheet-title { background-color: #e0e0e0; padding: 8px 12px; font-weight: bold; margin-top: 40px; }
  </style>
</head>
<body>

<h2>REKAPITULASI SPP DAN DPP<br>SD MUHAMMADIYAH 3 Tahun Ajaran {{ $tahun }}/{{ $tahun + 1 }}</h2>

@foreach($rekapitulasi as $namaKelas => $siswaList)

  <div class="sheet-title">{{ $namaKelas }}</div>

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

@endforeach

</body>
</html>
