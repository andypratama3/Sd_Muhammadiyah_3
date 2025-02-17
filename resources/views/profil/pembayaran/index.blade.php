@extends('layouts.user')

@section('title','Pembayaran')
@push('meta_user')
    <meta name="viewport" content="width=device-width, initial-scale=1">
@endpush
@push('css_user')
<style>
   .info-grid {
        display: grid;
        grid-template-columns: auto 20px auto;
        gap: 7px;
        align-items: center;
    }


    .info-grid span {
        font-family: Arial, Helvetica, sans-serif;
        font-weight: bold;
        color: black;
        font-size: 15px;
    }
    .card-header {
        /* border: 2px solid red; */
        background-color: var(--bs-primary);
        /* make have ronded in end */
        border-top-left-radius: 40px !important; /* Adjust the value as needed */
        border-top-right-radius: 150px !important; /* Adjust the value as needed */


    }

    .accordion-button:not(.collapsed) {
    color: var(--bs-primary) !important;
}

</style>
@endpush
@section('content')

<div class="section events mb-5" id="events">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h4 >Pembayaran</h4>
                    <h6>Cari Nisn Anak</h6>
                    <div class="col-md-2"></div>
                    <form action="{{ route('pembayaran.index') }}" method="GET" >
                        <div class="input-group wow fadeInUp" data-wow-delay="0.2s">
                            <input type="text" class="form-control @error('nisn') is-invalid @enderror" id="nisnPembayaran"
                                name="nisn" placeholder="Nisn" aria-label="Masukan Nisn Siswa"
                                aria-describedby="button-addon2" value="{{ old('nisn', request()->get('nisn')) }}">
                            <button class="btn btn-success" type="submit">Cari Siswa</button>
                            @error('nisn')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </form>
                    {{-- <form id="searchPaymentForm" method="GET" >
                        <div class="input-group">
                            <input type="text" class="form-control @error('nisn') is-invalid @enderror" id="nisnPembayaran"
                                name="nisn" placeholder="Masukan nisn Pembayaran" aria-label="Masukan Nisn Siswa"
                                aria-describedby="button-addon2">
                            <button class="btn btn-success" type="button" id="searchPaymentButton">Cari Pembayaran</button>
                            @error('nisn')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </form> --}}
                </div>
            </div>

            <div class="row mt-2 mb-4 wow fadeInUp" data-wow-delay="0.2s">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if(request()->filled('nisn'))
                            @if($siswa != null)
                            <div class="card-header">
                                <h4>Biodata Siswa</h4>
                            </div>

                            <div class="box" style="padding: 10px; border-radius: 4px; background-color: #c8ffa8">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-2">
                                        <img src="{{ asset('storage/img/siswa/' . $siswa->foto) }}" alt="" class="img-fluid" style="border-radius: 10px;">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="info-grid">
                                            <span class="text-field">Nama</span><span >:</span><span>{{ $siswa->name }}</span>
                                            <span class="text-field">Kelas</span><span>:</span><span>{{ $siswa->kelas->first()->name }}</span>
                                            <span class="text-field">NISN</span><span>:</span><span>{{ $siswa->nisn }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            <div class="card-header">
                                <h4>List Pembayaran Siswa</h4>
                            </div>

                            @forelse ($list_pembayaran as $year => $categories)
                                <div class="accordion mt-2" id="paymentAccordion{{ $loop->index }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                            <button class="accordion-button collapsed text-black" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ $loop->index }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapse{{ $loop->index }}">
                                                    <i class="bi bi-folder2-open d-none" id="folderOpenIcon{{ $loop->index }}" style="margin-right: 10px;"></i>
                                                    <i class="bi bi-folder" id="folderIcon{{ $loop->index }}" style="margin-right: 10px;"></i> Tahun {{ $year }}
                                            </button>
                                        </h2>


                                        <div id="collapse{{ $loop->index }}"
                                             class="accordion-collapse collapse"
                                             aria-labelledby="heading{{ $loop->index }}"
                                             data-bs-parent="#paymentAccordion{{ $loop->index }}">
                                            <div class="accordion-body">
                                                @foreach($categories as $category_id => $charges)
                                                    <div class="accordion" id="categoryAccordion{{ $year.$category_id }}">
                                                        <div class="accordion-item mt-2">
                                                            <h2 class="accordion-header" id="categoryHeading{{ $year.$category_id }}">
                                                                <button class="accordion-button collapsed fw-bold" type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#categoryCollapse{{ $year.$category_id }}"
                                                                        aria-expanded="false"
                                                                        aria-controls="categoryCollapse{{ $year.$category_id }}">
                                                                    {{ $charges->first()->kategori_pembayaran->name }}
                                                                </button>
                                                            </h2>
                                                            <div id="categoryCollapse{{ $year.$category_id }}"
                                                                 class="accordion-collapse collapse"
                                                                 aria-labelledby="categoryHeading{{ $year.$category_id }}"
                                                                 data-bs-parent="#categoryAccordion{{ $year.$category_id }}">
                                                                 <div class="accordion-body">
                                                                    <h5 class="mb-3">Daftar Pembayaran</h5>
                                                                    <ul style="list-style: none !important;">
                                                                        @foreach($charges as $charge)
                                                                            <li>
                                                                                <div class="row align-items-center">
                                                                                    <div class="col-md-4">
                                                                                        <label class="fw-bold">Tanggal Pembayaran:</label>
                                                                                        {{ \Carbon\Carbon::parse($charge->created_at)->translatedFormat('d F Y') }}
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <label class="fw-bold">Total :</label>
                                                                                        <strong>Rp. {{ number_format($charge->gross_amount, 0, ',', '.') }}</strong>
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <label class="fw-bold">Status:</label>
                                                                                        @if($charge->transaction_status == 'pending')
                                                                                            <span class="badge bg-warning">Belum Lunas <i class="align-center bi bi-clock" style="color: black !important;"></i> </span>
                                                                                        @elseif($charge->transaction_status == 'settlement')
                                                                                            <span class="badge bg-primary">Lunas <i class="align-center bi bi-check"></i> </span>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-2 text-center">
                                                                                        @if($charge->transaction_status == 'settlement')
                                                                                            <button class="btn btn-primary btn-sm" style="font-size: 10px;">Detail Pembayaran</button>
                                                                                        @else
                                                                                            <button class="btn btn-success btn-sm" id="payButton">Bayar</button>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </li>
                                                                            <hr>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="card-body mt-2 text-center">
                                    <h2>Tidak Ada Pembayaran</h2>
                                </div>
                            @endforelse
                            @else
                            <div class="card-body mt-2 text-center">
                                <h2>Tidak Ada Data Siswa / Anak</h2>
                            </div>
                            @endif
                        @else
                            <div class="card-body mt-2 text-center pt-2">
                                <h4>Cari Siswa Dengan Memasukkan NISN Siswa</h4>
                            </div>
                        @endif
                        </div>
                    </div>
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


            <div class="col-md-12 mt-5 wow fadeInLeft" data-wow-delay="0.2s">
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
{{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // Select all collapse elements
        const collapses = document.querySelectorAll('.accordion-collapse');

        collapses.forEach(function (collapse) {
            // Listen for when the collapse is shown
            collapse.addEventListener('show.bs.collapse', function () {
                const button = collapse.previousElementSibling.querySelector('.accordion-button');
                const folderIcon = button.querySelector('.bi-folder');
                const folderOpenIcon = button.querySelector('.bi-folder2-open');

                if (folderIcon) folderIcon.classList.add('d-none');  // Hide folder icon
                if (folderOpenIcon) folderOpenIcon.classList.remove('d-none');  // Show open folder icon
            });

            // Listen for when the collapse is hidden
            collapse.addEventListener('hide.bs.collapse', function () {
                const button = collapse.previousElementSibling.querySelector('.accordion-button');
                const folderIcon = button.querySelector('.bi-folder');
                const folderOpenIcon = button.querySelector('.bi-folder2-open');

                if (folderIcon) folderIcon.classList.remove('d-none');  // Show folder icon
                if (folderOpenIcon) folderOpenIcon.classList.add('d-none');  // Hide open folder icon
            });
        });
    });

    $(document).ready(function () {
        // modal
        $('#modal_how_pay_button').click(function () {
            $('#modal_how_pay').modal('show');
        });

        // Close the modal when needed (optional)
        $('#closeModalButton').click(function () {
            $('#modal_how_pay').modal('hide');
        });

        // search payment use keyboard when press eneter
        $('#kodePembayaran').keypress(function (e) {
            if(e.which == 13) {

                // $.ajax({
                //     url: "{{ route('pembayaran.searchOrder') }}",
                //     method: "GET",
                //     data: { kode: kode },
                //     success: function (response) {
                //         if (response.status === "success") {
                //             $('#paymentNotFound').hide();
                //             $('#paymentResult').show();

                //             $('#siswaFoto').attr('src', '{{ asset('storage/img/siswa/') }}' + '/' + response.data.siswa.foto);
                //             $('#siswaName').text(response.data.siswa.name);
                //             $('#orderId').text(response.data.order_id);
                //             $('#grossAmount').text(response.data.gross_amount);
                //             $('#paymentCategory').text(response.data.name);
                //             if (response.data.transaction_status === 'pending') {
                //                 $('#transactionStatus').removeClass('bg-success').addClass('bg-warning').text('Belum Lunas');
                //                 $('#payButton').show();
                //                 $('#payButton').attr('data-snaptoken', response.snap_token);
                //             } else {
                //                 $('#transactionStatus').removeClass('bg-warning').addClass('bg-success').text('Lunas');
                //                 $('#payButton').hide();
                //             }
                //         } else {
                //             $('#paymentResult').hide();
                //             $('#paymentNotFound').show();
                //         }
                //     },
                //     error: function () {
                //         alert('Gagal mencari pembayaran. Silakan coba lagi.');
                //     }
                // });
            }

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
