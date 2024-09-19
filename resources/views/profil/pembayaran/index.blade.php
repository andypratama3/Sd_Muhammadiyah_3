@extends('layouts.user')

@section('title','Pembayaran')
@push('meta_user')
    <meta name="viewport" content="width=device-width, initial-scale=1">
@endpush
@push('css_user')
@endpush
@section('content')

<div class="section events mb-5" id="events">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h4>Pembayaran</h4>
                    <h6>Cari Nama Anak, Nomor Virtual Account</h6>
                    <div class="col-md-2"></div>
                    <form id="searchPaymentForm" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" id="kodePembayaran"
                                name="kode" placeholder="Masukan Kode Pembayaran" aria-label="Masukan Kode Pembayaran"
                                aria-describedby="button-addon2">
                            <button class="btn btn-success" type="button" id="searchPaymentButton">Cari Pembayaran</button>
                            @error('kode')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            <div id="paymentResult" class="col-lg-12 col-md-6" style="margin-top: 20px; display:none;">
                <h3 class="title mb-2">Pembayaran Ditemukan</h3>
                <div class="item">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                           <img id="siswaFoto" src="" alt="" class="img-fluid">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group mt-2">
                                                <h5>Nama : <span id="siswaName"></span></h5>
                                            </div>
                                            <div class="form-group">
                                                <h5>Kode : <span id="orderId"></span></h5>
                                            </div>
                                            <div class="form-group">
                                                <h5>Status : <span id="transactionStatus" class="badge"></span></h5>
                                            </div>
                                            <div class="form-group">
                                                <h5>Total : Rp. <span id="grossAmount"></span></h5>
                                            </div>
                                            <div class="form-group">
                                                <h5>Kategori Pembayaran : <span id="paymentCategory"></span></h5>
                                            </div>
                                            <div class="form-group float-end mt-2">
                                                <button class="btn btn-primary" id="payButton" style="display: none;">
                                                    <i class="fa fa-angle-right"> Bayar</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="paymentNotFound" class="col-lg-12 text-center mb-5" style="margin-top: 100px; display:none;">
                <span class="badge bg-danger">Pembayaran Tidak Ditemukan</span>
            </div>


            <div class="col-md-12 mt-5">
                <Button class="btn btn-primary float-start" type="button" id="modal_how_pay_button" class="btn btn-primary" data-toggle="modal" data-target="#modal_how_pay">Cara Pembayaran</Button>
            </div>

            <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="modal_how_pay" tabindex="-1" role="dialog" aria-labelledby="modal_how_payTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal_how_payTitle">Cara Melakukan Pembayaran</h5>
                            <button type="button" style="background: none; color: red; font-weight: bold;" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            
                        </div>
                        <div class="modal-footer d-flex align-items-center">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('js_user')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function () {
        // modal
        $('#modal_how_pay_button').click(function () {
            $('#modal_how_pay').modal('show');
        });

        // Close the modal when needed (optional)
        $('#closeModalButton').click(function () {
            $('#modal_how_pay').modal('hide');
        });

        $('#searchPaymentButton').click(function () {
            var kode = $('#kodePembayaran').val();
            if (kode === "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Kode pembayaran tidak boleh kosong!'
                })
                return;
            }

            $.ajax({
                url: "{{ route('pembayaran.searchOrder') }}",
                method: "GET",
                data: { kode: kode },
                success: function (response) {
                    if (response.status === "success") {
                        $('#paymentNotFound').hide();
                        $('#paymentResult').show();

                        $('#siswaFoto').attr('src', '{{ asset('storage/img/siswa/') }}' + '/' + response.data.siswa.foto);
                        $('#siswaName').text(response.data.siswa.name);
                        $('#orderId').text(response.data.order_id);
                        $('#grossAmount').text(response.data.gross_amount);
                        $('#paymentCategory').text(response.data.name);
                        if (response.data.transaction_status === 'pending') {
                            $('#transactionStatus').removeClass('bg-success').addClass('bg-warning').text('Belum Lunas');
                            $('#payButton').show();
                            $('#payButton').attr('data-snaptoken', response.snap_token);
                        } else {
                            $('#transactionStatus').removeClass('bg-warning').addClass('bg-success').text('Lunas');
                            $('#payButton').hide();
                        }
                    } else {
                        $('#paymentResult').hide();
                        $('#paymentNotFound').show();
                    }
                },
                error: function () {
                    alert('Gagal mencari pembayaran. Silakan coba lagi.');
                }
            });
        });

        $('#payButton').click(function () {
            var snapToken = $(this).attr('data-snaptoken');
            snap.pay(snapToken, {
                onSuccess: function (result) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Pembayaran Berhasil',
                    });
                },
                onPending: function (result) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pembayaran Sedang Dalam Proses',
                        text: 'Silakan lakukan pembayaran pada menu pembayaran.',
                    });
                },
                onError: function (result) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Pembayaran Gagal. Silakan coba lagi.',
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
