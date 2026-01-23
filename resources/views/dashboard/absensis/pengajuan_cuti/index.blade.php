@extends('layouts.dashboard')

@section('title', 'Pengajuan Cuti')

@section('content')
<div class="mb-4 card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Pengajuan Cuti</h6>
        <a href="{{ route('dashboard.pengajuan_cuti.create') }}"
           class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Ajukan Cuti
        </a>
    </div>

    <div class="card-body">
       @include('layouts.flashmessage')
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Jumlah Hari</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuanCuti as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->karyawan->name ?? '-' }}</td>
                            <td>{{ ucfirst($item->jenis) }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->formatLocalized('%d %B %Y') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->formatLocalized('%d %B %Y') }}
                            </td>
                            <td>{{ $item->jumlah_hari }} hari</td>
                            <td>
                                @if ($item->status == 'menunggu')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif ($item->status == 'disetujui')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            @if($item->status == 'menunggu' || $item->status == 'ditolak')
                            <td>
                                <a href="{{ route('dashboard.pengajuan_cuti.edit', $item->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Belum ada pengajuan cuti
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
