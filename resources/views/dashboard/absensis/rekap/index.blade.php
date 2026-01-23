@extends('layouts.dashboard')

@section('title','Rekap Absensi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Rekap Absensi</h5>
        <div>
            <a href="{{ route('dashboard.rekap.absensi.export.pdf', request()->all()) }}"
               class="btn btn-danger btn-sm">PDF</a>

            <a href="{{ route('dashboard.rekap.absensi.export.excel', request()->all()) }}"
               class="btn btn-success btn-sm">Excel</a>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered">
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
                    <td>{{ $row->karyawan->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->formatLocalized('%A, %d %B %Y', null, 'id_ID') }}</td>
                    <td>{{ ucfirst($row->status_kehadiran) }}</td>
                    <td>{{ $row->jam_masuk }}</td>
                    <td>{{ $row->jam_pulang }}</td>
                    <td>{{ $row->keterangan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $rekapAbsensi->links() }}
    </div>
</div>
@endsection
