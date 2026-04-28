@extends('layouts.dashboard')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rekap Absensi Saya</h3>
                    <div class="card-tools">
                        <small class="text-muted">Hanya menampilkan yang sudah dipublish admin</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Berikut adalah rekap absensi yang telah dipublish oleh admin. Silakan download file Anda masing-masing.
                    </div>
                    <div id="alert-area"></div>
                    <div class="table-responsive">
                        <table id="karyawanTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Generate</th>
                                    <th>Periode</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Check if DataTable is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        $('#alert-area').html('<div class="alert alert-danger">Error: DataTable plugin tidak ter-load. Pastikan koneksi internet aktif.</div>');
        console.error('DataTable not loaded');
        return;
    }

    var table = $('#karyawanTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('dashboard.rekap-absensi-history.karyawan-index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { 
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString('id-ID');
                }
            },
            { data: 'periode' },
            { data: 'dibuat_oleh' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']]
    });
});
</script>
@endpush
