@extends('layouts.dashboard')
@section('title', 'WhatsApp Message Status')
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-1.4.2/datatables.min.css" />
@endpush

@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title">WhatsApp Message Status</h4>
        </div>
        <div class="card-body">
            <div class="mt-4 table-responsive">
                <table class="table mt-4 w-100" id="statusTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Message ID</th>
                            <th>Recipient</th>
                            <th>Status</th>
                            <th>Timestamp</th>
                            <th>Errors</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for showing WhatsApp status details -->
<div class="modal fade" id="statusDetailModal" tabindex="-1" role="dialog" aria-labelledby="statusDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusDetailModalLabel">WhatsApp Status Detail</h5>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div id="modalContent" style="display: none;">
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <strong>ID:</strong>
                            <p id="detailId"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Message ID:</strong>
                            <p id="detailMessageId"></p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <strong>Recipient:</strong>
                            <p id="detailRecipient"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p id="detailStatus"></p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6">
                            <strong>Timestamp:</strong>
                            <p id="detailTimestamp"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Created At:</strong>
                            <p id="detailCreatedAt"></p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-12">
                            <strong>Errors:</strong>
                            <pre id="detailErrors" style="background-color: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-1.4.2/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#statusTable').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    'url': "{{ route('dashboard.monitoring.whatsapp-error.data') }}",
                    'data': function(d) {}
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'message_id',
                        name: 'message_id'
                    },
                    {
                        data: 'recipient',
                        name: 'recipient'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'timestamp',
                        name: 'timestamp'
                    },
                    {
                        data: 'has_errors',
                        name: 'has_errors',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Handle show data button click
            $('#statusTable').on('click', '.btn-show-data', function(e) {
                e.preventDefault();
                const dataUrl = $(this).attr('data-url');

                // Show loading state
                $('#modalLoading').show();
                $('#modalContent').hide();
                $('#statusDetailModal').modal('show');

                // Fetch the data
                $.ajax({
                    url: dataUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const data = response.data;

                        // Populate modal with data
                        $('#detailId').text(data.id || '-');
                        $('#detailMessageId').text(data.message_id || '-');
                        $('#detailRecipient').text(data.recipient || '-');
                        $('#detailStatus').html('<span class="badge badge-primary">' + (data.status || '-') + '</span>');
                        $('#detailTimestamp').text(data.timestamp || '-');
                        $('#detailCreatedAt').text(data.created_at || '-');

                        // Parse and format errors JSON
                        if (data.errors) {
                            try {
                                const errors = typeof data.errors === 'string' ? JSON.parse(data.errors) : data.errors;
                                $('#detailErrors').text(JSON.stringify(errors, null, 2));
                            } catch (e) {
                                $('#detailErrors').text(data.errors);
                            }
                        } else {
                            $('#detailErrors').text('No errors');
                        }

                        // Hide loading and show content
                        $('#modalLoading').hide();
                        $('#modalContent').show();
                    },
                    error: function(error) {
                        console.error('Error loading data:', error);
                        Swal.fire('Error', 'Failed to load status details', 'error');
                        $('#statusDetailModal').modal('hide');
                    }
                });
            });
        });
    </script>
@endpush
@endsection
