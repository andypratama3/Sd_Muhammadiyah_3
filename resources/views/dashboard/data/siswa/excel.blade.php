<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Export Excel</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td>Nama</td>
                <td>Jenis Kelamin</td>
                <td>Tempat Lahir</td>
                <td>Tanggal Lahir</td>
                <td>Nisn</td>
                <td>Agama</td>
                {{-- data sekolah --}}
                <td>Kelas/tahun</td>
                <td>Tanggal Masuk</td>
                <td>Beasiswa</td>
                {{-- data orang tua --}}
                <td>Nama Ayah</td>
                <td>Nama Ibu</td>
                <td>Pendidikan Ayah</td>
                <td>Pendidikan Ibu</td>
                {{-- pekerjaan --}}
                <td>Pekerjaan_Ayah</td>
                <td>Pekerjaan Ibu</td>
                {{-- wali --}}
                <td>Nama wali</td>
                <td>Pekerjaan wali</td>
                <td>Alamat wali</td>
                {{-- alamat --}}
                <td>Rt</td>
                <td>Rw</td>
                <td>provinsi</td>
                <td>kabupaten</td>
                <td>kecamatan</td>
                <td>kelurahan</td>
                <td>nama_jalan</td>
                <td>Jenis Tinggal</td>
                <td>No HP</td>
            </tr>
        </thead>
        @php
            $no = 0;
        @endphp
        <tbody>
            @foreach ($siswas as $siswa)
            <tr>
                <td>{{ ++$no }}</td>
                <td>{{ $siswa->name}}</td>
                <td>{{ $siswa->jk}}</td>
                <td>{{ $siswa->tmpt_lahir}}</td>
                <td>{{ $siswa->tgl_lahir}}</td>
                <td>{{ $siswa->nisn}}</td>
                <td>{{ $siswa->agama}}</td>
                <td>{{ $siswa->kelas_tahun}}</td>
                <td>{{ $siswa->tanggal_masuk}}</td>
                <td>{{ $siswa->beasiswa}}</td>
                <td>{{ $siswa->foto}}</td>

                <td>{{ $siswa->nama_ayah}}</td>
                <td>{{ $siswa->nama_ibu}}</td>
                <td>{{ $siswa->pendidikan_ayah}}</td>
                <td>{{ $siswa->pendidikan_ibu}}</td>

                <td>{{ $siswa->pekerjaan_ayah}}</td>
                <td>{{ $siswa->pekerjaan_ibu}}</td>
                <td>{{ $siswa->nama_wali}}</td>
                <td>{{ $siswa->pekerjaan_wali}}</td>
                <td>{{ $siswa->alamat_wali}}</td>
                <td>{{ $siswa->rt}}</td>
                <td>{{ $siswa->rw}}</td>
                <td>{{ $siswa->provinsi}}</td>
                <td>{{ $siswa->kabupaten}}</td>
                <td>{{ $siswa->kecamatan}}</td>
                <td>{{ $siswa->kelurahan}}</td>
                <td>{{ $siswa->nama_jalan}}</td>
                <td>{{ $siswa->jenis_tinggal}}</td>
                <td>{{ $siswa->no_hp}}</td>
            </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>
