@extends('layouts.dashboard')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Rekap Absensi</h3>
                    <div class="card-tools">
                        <a href="{{ route('dashboard.rekap-absensi-history.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('dashboard.rekap-absensi-history.download', $rekap->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Download ZIP
                        </a>
                        @if($rekap->status === 'draft')
                        <button class="btn btn-sm btn-success btn-publish" data-id="{{ $rekap->id }}">
                            <i class="fas fa-paper-plane"></i> Publish ke Karyawan
                        </button>
                        @else
                        <button class="btn btn-sm btn-warning btn-unpublish" data-id="{{ $rekap->id }}">
                            <i class="fas fa-undo"></i> Unpublish
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Tanggal Generate</th>
                                    <td>{{ $rekap->created_at->locale('id')->translatedFormat('d F Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Admin</th>
                                    <td>{{ $rekap->user->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Periode</th>
                                    <td>{{ $rekap->date_range_label }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($rekap->status === 'published')
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td>{{ $rekap->keterangan ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="mt-2 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>File per Karyawan ({{ count($rekap->file_per_karyawan) }} file)</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        @foreach($rekap->file_per_karyawan as $karyawanId => $filename)
                                            @php $karyawan = \App\Models\Karyawan::find($karyawanId) @endphp
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $karyawan->name ?? 'Karyawan #' . $karyawanId }}
                                                <span class="badge bg-info">{{ $filename }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var baseUrl = "{{ url('dashboard/rekap-absensi-history') }}";

    // Publish
    $('.btn-publish').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Publish Rekap?',
            text: 'Rekap ini akan dipublish ke karyawan',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Publish!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + '/' + id + '/publish',
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}" }
                }).done(function(res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    location.reload();
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Unknown error', 'error');
                });
            }
        });
    });

    // Unpublish
    $('.btn-unpublish').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Batalkan Publish?',
            text: 'Rekap ini akan dikembalikan ke draft',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + '/' + id + '/unpublish',
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}" }
                }).done(function(res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    location.reload();
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Unknown error', 'error');
                });
            }
        });
    });
});
</script>
@endpush
