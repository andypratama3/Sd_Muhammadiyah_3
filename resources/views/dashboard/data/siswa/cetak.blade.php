<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<style>
    tr {
        margin: 0 !important;
        text-align: start;

    }

    tr td {
        font-size: 12px;
        font-weight: bold;
    }
    @media print{
        @page{
            margin-top: 30px;
            margin-bottom: 30px;
        }
    }
</style>

<body>
    <div class="container">
        <!-- <div class="row"> -->
        <div class="align-items-center justify-items-center text-center">
            <h6>IDENTITAS PESERTA DIDIK</h6>
            <!-- <table class="table d-table-row m-0 d-flex" > -->
            <table class="d-table-cell">
                <tr>
                    <td>A.</td>
                    <td>Nama Peseta Didik</td>
                    <th>:</th>
                    <td>{{ $siswa->name }}</td>
                </tr>
                <tr>
                    <td>B.</td>
                    <td>NIS/NISN</td>
                    <th>:</th>
                    <td>{{ $siswa->nisn }}</td>
                </tr>
                <tr>
                    <td>C.</td>
                    <td>Tempat, Tanggal Lahir</td>
                    <th>:</th>
                    <td>{{ $siswa->tmpt_lahir }}, {{ $siswa->tgl_lahir }}</td>
                </tr>
                <tr>
                    <td>D.</td>
                    <td>Jenis Kelamin</td>
                    <th>:</th>
                    <td>{{ $siswa->jk }}</td>
                </tr>
                <tr>
                    <td>E.</td>
                    <td>Agama</td>
                    <th>:</th>
                    <td>{{ $siswa->agama }}</td>
                </tr>
                <tr>
                    <td>F.</td>
                    <td>Pendidikan Sebelumnya</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - TK/PAUD/SD</td>
                    <th>:</th>
                    @if ($siswa->nama_pendidikan == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->nama_pendidikan }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Alamat</td>
                    <th>:</th>
                    @if ($siswa->nama_jalan_pendidikan == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->nama_jalan_pendidikan }}</td>
                    @endif
                </tr>

                <tr>
                    <td>G.</td>
                    <td>Masuk Di Sekolah</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - Kelas/Tahun/Ajaran</td>
                    <th>:</th>
                    <td>{{ $siswa->kelas_tahun }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - Pada Smester</td>
                    <th>:</th>
                    <td>1</td>
                </tr>
                <tr>
                    <td>H.</td>
                    <td>Alamat Peserta Didik</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - Jalan</td>
                    <th>:</th>
                    <td>{{ $siswa->nama_jalan }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - Kelurahan/Desa</td>
                    <th>:</th>
                    <td>{{ $kelurahan_take['name'] }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - Kecamatan</td>
                    <th>:</th>
                    <td>{{ $kecamatan_take['name'] }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - Provinsi</td>
                    <th>:</th>
                    <td>{{ $provinsi_take['name'] }}</td>

                </tr>
                <tr>
                    <td>I.</td>
                    <td>Nama Orang Tua</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - Ayah</td>
                    <th>:</th>
                    @if ($siswa->nama_ayah == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->nama_ayah }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Ibu</td>
                    <th>:</th>
                    @if ($siswa->nama_ibu == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->nama_ibu }}</td>
                    @endif
                </tr>

                <tr>
                    <td>J.</td>
                    <td>Pendidikan Orang Tua</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - Ayah</td>
                    <th>:</th>
                    @if ($siswa->pendidikan_ayah == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->pendidikan_ayah }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Ibu</td>
                    <th>:</th>
                    @if ($siswa->pendidikan_ibu == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->pendidikan_ibu }}</td>
                    @endif
                </tr>
                <tr>
                    <td>K.</td>
                    <td>Pekerjaan Orang Tua</td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"> - Ayah</td>
                    <th>:</th>
                    @if ($siswa->pekerjaan_ayah == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->pekerjaan_ayah }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Ibu</td>
                    <th>:</th>
                    @if ($siswa->pekerjaan_ibu == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->pekerjaan_ibu }}</td>
                    @endif
                </tr>
                <r>
                    <td>L.</td>
                    <td>Alamat Orang Tua</td>
                    <th></th>
                    <td></td>
                <tr>
                    <td colspan="2"> - Nama Jalan</td>
                    <th>:</th>
                    <td>{{ $siswa->nama_jalan }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - RT</td>
                    <th>:</th>
                    <td>{{ $siswa->rt }}</td>
                </tr>
                <tr>
                    <td colspan="2"> - RW</td>
                    <th>:</th>
                    <td>{{ $siswa->rw }}</td>
                </tr>
                <tr>
                    <td>M.</td>
                    <td>Wali Peserta Didik</td>
                    <th></th>
                </tr>
                <tr>
                    <td colspan="2"> - Nama</td>
                    <th>:</th>
                    @if ($siswa->nama_wali == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->nama_wali }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Pekerjaan</td>
                    <th>:</th>
                    @if ($siswa->pekerjaan_wali == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->pekerjaan_wali }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="2"> - Alamat</td>
                    <th>:</th>
                    @if ($siswa->alamat_wali == null)
                        <td>-</td>
                    @else
                        <td>{{ $siswa->alamat_wali }}</td>
                    @endif
                </tr>
            </table>
            <div class="row">
                <!-- <div class="col-12" style="border: 2px solid blue;"> -->
                <h6 class="text-end">Samarinda,
                    @php
                        setlocale(LC_TIME, 'id_ID');
                        $timestamp = time();
                        $currentDate = strftime('%d %B %y', $timestamp);
                        echo $currentDate;
                    @endphp
                </h6>
                <h6 class="text-end">Kepala Sekolah</h6>
                <img src="{{ asset('storage/img/siswa/'. $siswa->foto) }}" alt="" style="width: 100px; margin-left: 290px;">
                <h6 class="text-end" style="text-decoration: underline;">
                    <strong>Ansar HS, S.Pd M.M</strong></h6>
                <h6 class="text-end"><strong>NBM </strong> : 1144131</h6>
                <!-- </div> -->
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        window.print();
    </script>
</body>

</html>
