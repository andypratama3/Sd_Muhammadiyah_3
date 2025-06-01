@extends('layouts.dashboard')
@section('title', 'Pengunjung Halaman')
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css" />
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="card-title"> Data Pengunjung Halaman</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table mt-4 w-100" id="visitor_table" >
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Url</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@push('js')
    {{-- <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script> --}}
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            function reloadTable(id) {
                var table = $(id).DataTable();
                table.cleanData;
                table.ajax.reload();
            }
            $('#visitor_table').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    'url': '{{ route('dashboard.url.visitor.index') }}',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        ordering: false
                    },
                    {
                        data: 'url',
                        name: 'url',
                    },
                    {
                        data: 'count',
                        name: 'count'
                    },
                ],
            });
        });
    </script>
@endpush
@endsection
