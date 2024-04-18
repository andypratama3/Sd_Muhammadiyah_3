@extends('layouts.user')

@section('title','Pembayaran')

@section('content')

<section id="posts" class="posts mt-5">
    <div class="container" data-aos="fade-up">
        <div class="card w-100 d-lg-grid mt-5">
            <div class="card-header text-center mb-2">
                <h2>Halaman Pembayaran</h2>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Kode Pembayaran</label>
                        <input type="text" name="kode_pembayaran" id="kode_pembayaran" class="form-control"
                            placeholder="Kode Pembayaran" aria-label="kode pembayaran">
                    </div>
                </div>
                <div class="form-group mt-4 justify-center">
                    <button class="btn btn-primary" id="button_search">Cari Pembayaran</button>
                </div>

            </div>
        </div>
        {{-- result for search code_payment --}}
        <div class="card w-100 mt-2 mb-3" id="result_order" style="display: none">
            <div class="card-header text-center mb-2">
                <h5 id="status_result"></h5>
            </div>
            <div class="card-body">
                <div class="col-sm-12">
                    <div class="row" id="content-result">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Pembayaran</label>
                                <h5 id="name"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Kode Pembayaran</label>
                                <h5 id="order_id"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Nama Siswa</label>
                                <h5 id="nama_siswa"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Kelas</label>
                                <h5 id="kelas"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Category Kelas</label>
                                <h5 id="category_kelas"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Total Pembayaran</label>
                                <h5 id="gross_amount"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="">Status Pembayaran</label>
                                <h5 id="status"></h5>
                                <hr>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary" id="pay">Bayar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> <!-- End Post Grid Section -->
@push('js_user')
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function () {
        //property
        let name = $('#name');
        let gross_amount = $('#gross_amount');
        let result_card = $('#result_order');
        let status_result = $('#status_result');
        let content_result = $('#content-result');
        let nama_siswa = $('#nama_siswa');
        let kelas = $('#kelas');
        let category_kelas = $('#category_kelas');
        let order_id = $('#order_id');
        let status = $('#status');

        $('#posts').on('click', '#button_search', function () {
            let kode_pembayaran = $('#kode_pembayaran').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: "{{ route('pembayaran.search') }}",
                data: {
                    kode_pembayaran: kode_pembayaran,
                },
                success: function (response) {
                    if(response.status == 'success'){
                        content_result.css('display', '');
                        result_card.css('display', '');
                        name.text(response.data.name);
                        nama_siswa.text(response.siswa);
                        kelas.text(response.kelas);
                        category_kelas.text(response.data.category_kelas);
                        status_result.text(response.message);
                        order_id.text(response.data.order_id);
                        gross_amount.text(response.data.gross_amount);
                        status.text(response.data.status);
                        if(response.data.status == 'Berhasil'){
                            $('#pay').css('display', 'none');
                        }

                    }else  {
                        content_result.css('display', 'none');
                        result_card.css('display', '');
                        status_result.text(response.message);
                    }
                },
            });
        });
        $('#posts').on('click', '#pay', function () {
            let payment_id = $('#order_id').text();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: "{{ route('pembayaran.pay') }}",
                data: {
                    order_id : payment_id,
                },
                success: function (response) {

                    window.location.href = response.redirect;
                }

            });
        });
    });
</script>
@endpush
@endsection
