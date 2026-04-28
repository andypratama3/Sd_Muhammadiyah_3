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
                    <h3 class="card-title">History Rekap Absensi</h3>
                    <div class="card-tools">
                        <a href="{{ route('dashboard.rekap.absensi.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Rekap
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="alert-area"></div>
                    <div class="table-responsive">
                        <table id="historyTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Generate</th>
                                    <th>Admin</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Jumlah File</th>
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
    // Debug: Check if DataTable is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        $('#alert-area').html('<div class="alert alert-danger">Error: DataTable plugin tidak ter-load. Pastikan koneksi internet aktif.</div>');
        console.error('DataTable not loaded');
        return;
    }

    var baseUrl = "{{ url('dashboard/rekap-absensi-history') }}";

    var table = $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('dashboard.rekap-absensi-history.index') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            {
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString('id-ID');
                }
            },
            { data: 'user_name' },
            { data: 'periode' },
            { data: 'status_badge' },
            { data: 'file_count' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']]
    });

    // Publish
    $(document).on('click', '.btn-publish', function() {
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
                    table.ajax.reload();
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Unknown error', 'error');
                });
            }
        });
    });

    // Unpublish
    $(document).on('click', '.btn-unpublish', function() {
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
                    table.ajax.reload();
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Unknown error', 'error');
                });
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Hapus History?',
            text: 'File ZIP akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + '/' + id,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" }
                }).done(function(res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    table.ajax.reload();
                }).fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Unknown error', 'error');
                });
            }
        });
    });
});
</script>
@endpush
