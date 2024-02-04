@extends('layouts.user')
@section('title','Pembayaran')

@section('content')

<section id="posts" class="posts">
    <div class="container" data-aos="fade-up">
        <div class="card w-100 d-lg-grid">
            <div class="card-header text-center mb-2">
                <h2>Halaman Pembayaran</h2>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Kode Pembayaran</label>
                        <input type="text" name="kode_pembayaran" id="kode_pembayaran" class="form-control" placeholder="Kode Pembayaran" aria-label="kode pembayaran" >
                    </div>
                </div>
                <div class="form-group mt-4 justify-center">
                    <button class="btn btn-primary" id="button_search">Cari Pembayaran</button>
                </div>

            </div>
        </div>
        <div class="card w-100 d-lg-grid mt-2" id="result_order" style="display: none;">
            <div class="card-header text-center mb-2">
                <h2>Order Di Temukan</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group row">
                        <label for="">Kode Pembayaran</label>
                        <h2>Akkkkkk</h2>
                    </div>
                </div>
                <div class="form-group text-center">
                    <button class="btn btn-primary">Bayar</button>
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
        $('#posts').on('click','#button_search',function () {
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
                    let result = response.data.order_id;
                    $('#result_order').removeAttr('style');
                    $('#name').textContent(response.data.);
                },
                error: function (err){

                }
            });
        });

    });
</script>
@endpush
@endsection
