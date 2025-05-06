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
        background-color: #198754;
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
        <div class="row" id="row">
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
                                <h4 class="mt-2 text-white">Biodata Siswa</h4>
                            </div>

                            <div class="box" style="padding: 10px; border-radius: 4px;">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-2">

                                        {{-- <img src="https://ui-avatars.com/api/?name={{ $siswa->name }}" alt="" class="img-fluid" style="border-radius: 10px;"> --}}
                                        <img src="{{ $siswa->foto ? url('storage/img/siswa/' . $siswa->foto) : asset('asset_dashboard/img/default.jpg') }}"
                                        onerror="this.onerror=null; this.src='{{ asset('asset_dashboard/img/default.jpg') }}';"
                                        alt=""
                                        class="img-fluid"
                                        style="border-radius: 10px;">

                                        {{-- <img src="{{ $siswa->foto ? url('storage/img/siswa/' . $siswa->foto) : asset('asset_dashboard/img/default.jpg') }}" alt="" class="img-fluid" style="border-radius: 10px;"> --}}
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="info-grid">
                                            <span class="text-field">Nama</span><span >:</span><span>{{ $siswa->name }}</span>
                                            <span class="text-field">Kelas</span><span>:</span><span>{{ $siswa->kelas->first()->name ?? '-' }}</span>
                                            <span class="text-field">NISN</span><span>:</span><span>{{ $siswa->nisn }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            <div class="card-header">
                                <h4 class="mt-2 text-white">List Pembayaran Siswa</h4>
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
                                                                                    <input type="hidden" name="charge_id" value="{{ $charge->id }}">
                                                                                    <hr>

                                                                                    <div class="col-md-4">
                                                                                        <label class="fw-bold">{{ $charge->kategori_pembayaran->name }} </label>
                                                                                        <br>
                                                                                        <label class="fw-bold">Tanggal :</label>
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
                                                                                        @elseif($charge->transaction_status == 'free')
                                                                                            <span class="badge bg-success">Gratis <i class="align-center bi bi-check"></i> </span>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-2 text-center">
                                                                                        @if($charge->transaction_status == 'settlement')
                                                                                            <button class="btn btn-primary btn-sm" style="font-size: 10px;" id="detailButton" data-id="{{ $charge->id }}">Detail Pembayaran</button>
                                                                                            @elseif($charge->transaction_status == 'free')
                                                                                            <button class="btn btn-primary btn-sm" style="font-size: 10px;">Gratis</button>
                                                                                            @else
                                                                                            <button class="btn btn-success btn-sm" data-id="{{ $charge->id }}" id="payButton">Bayar</button>
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



            <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="modal_detail" tabindex="-1" role="dialog" aria-labelledby="modal_detailTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Transaksi Pembayaran</h5>
                            <button type="button" style="background: none; color: red; font-weight: bold;" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table">
                                <tr>
                                    <th>Nama</th>
                                    <td id="detail_name"></td>
                                </tr>
                                <tr>
                                    <th>Order ID</th>
                                    <td id="detail_order_id"></td>
                                </tr>
                                <tr>
                                    <th>Total Bayar</th>
                                    <td id="detail_gross_amount"></td>
                                </tr>
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <td id="detail_payment_type"></td>
                                </tr>
                                <tr>
                                    <th>Bank / Provider</th>
                                    <td id="detail_bank"></td>
                                </tr>
                                <tr>
                                    <th>VA Number / Kode Bayar</th>
                                    <td id="detail_va_number"></td>
                                </tr>
                                <tr>
                                    <th>Status Transaksi</th>
                                    <td id="detail_transaction_status"></td>
                                </tr>
                                <tr>
                                    <th>Waktu Transaksi</th>
                                    <td id="detail_transaction_time"></td>
                                </tr>
                            </table>
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
<script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
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

        // set time out
    //     setInterval(() => {
    //     $('.row, .accordion').each(function () {
    //         $(this).load(location.href + " " + $(this).attr('class'));
    //     });
    // }, 5000);

        $('#modal_how_pay_button').click(function () {
            $('#modal_how_pay').modal('show');
        });

        // Close the modal when needed (optional)
        $('#closeModalButton').click(function () {
            $('#modal_how_pay').modal('hide');
        });

        // button pay
        $('.accordion-body').on('click', '#payButton', function () {
            let charge_id = $(this).data('id');

            if (!charge_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'ID transaksi tidak ditemukan!',
                });
                return;
            }

            // Cegah klik berulang
            $(this).prop('disabled', true);

            // console.log("Mengambil snap_token untuk charge_id:", charge_id);

            $.ajax({
                type: "GET",
                url: "{{ route('pembayaran.searchOrder') }}",
                data: { charge_id: charge_id },
                cache: false,
                success: function (response) {
                    if (response.status === 'success' && response.snap_token) {
                        let snapToken = response.snap_token;


                        snap.pay(snapToken, {
                            onSuccess: function (result) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Pembayaran Berhasil',
                                });
                                // $('#payButton').prop('disabled', false);
                            },
                            onPending: function (result) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Pembayaran Sedang Dalam Proses',
                                    text: 'Silakan lakukan pembayaran pada menu pembayaran.',
                                });
                                $('#payButton').prop('disabled', false);
                            },
                            onError: function (result) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Pembayaran Gagal. Silakan coba lagi.',
                                });
                                // $('#payButton').prop('disabled', false);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message || 'Terjadi kesalahan dalam mendapatkan snap_token',
                        });
                        // $('#payButton').prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    console.error("Kesalahan Ajax:", xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data transaksi',
                    });
                    // $('#payButton').prop('disabled', false);
                }
            });
        });

        $('.accordion-body').on('click', '#detailButton', function () {
            let charge_id = $(this).data('id');

            if (!charge_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'ID transaksi tidak ditemukan!',
                });
                return;
            }

            $.ajax({
                type: "GET",
                url: "{{ route('pembayaran.searchOrderDetail') }}",
                data: {
                    charge_id: charge_id
                },
                cache: false,
                success: function(response) {
                    if (response.status === "success") {
                        showTransactionDetail(response.data);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Data tidak ditemukan!',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan, coba lagi.',
                    });
                }
            });
        });

        function showTransactionDetail(data) {
            // Set nilai umum
            $("#detail_name").text(data.name ?? "-");
            $("#detail_order_id").text(data.order_id ?? "-");
            $("#detail_gross_amount").text(formatCurrency(data.gross_amount ?? 0));
            $("#detail_payment_type").text(data.payment_type ?? "-");
            $("#detail_transaction_status").text(data.transaction_status ?? "-");
            $("#detail_transaction_time").text(data.transaction_time ?? "-");

            // Tampilkan status transaksi dalam bentuk badge
            let statusBadge = "";
            if (data.transaction_status === 'settlement' || data.transaction_status === 'pay_offline' || data.transaction_status === 'capture' ) {
                statusBadge = `<span class="badge bg-success">Lunas</span>`;
            } else if (data.transaction_status === 'pending') {
                statusBadge = `<span class="badge bg-warning">Belum Lunas</span>`;
            } else if(data.transaction_status === 'free') {
                statusBadge = `<span class="badge bg-success">Gratis</span>`;

            } else {
                statusBadge = data.transaction_status;
            }
            $("#detail_transaction_status").html(statusBadge);

            // Reset semua elemen agar tidak ada data yang tertinggal dari transaksi sebelumnya
            $("#detail_bank, #detail_va_number").text("-");

            // Menentukan informasi tambahan berdasarkan payment_type
            switch (data.payment_type) {
                case "bank_transfer":
                    $("#detail_bank").text(data.bank ?? "-");
                    $("#detail_va_number").text(data.va_number ?? "-");
                    break;
                case "credit_card":
                    $("#detail_bank").text(data.bank ?? "-");
                    $("#detail_va_number").text(data.card_number ? "**** **** **** " + data.card_number.slice(-4) : "-");
                    break;
                case "qris":
                    $("#detail_bank").text("QRIS");
                    $("#detail_va_number").text("Tersedia (Silakan scan)");
                    break;
                case "cstore":
                    $("#detail_bank").text(data.store ?? "-");
                    $("#detail_va_number").text(data.payment_code ?? "-");
                    break;
                case "e_wallet":
                    $("#detail_bank").text(data.e_wallet ?? "-");
                    $("#detail_va_number").text("Tersedia (Lihat aplikasi e-wallet)");
                    break;
                default:
                    $("#detail_payment_type").text("Tipe pembayaran tidak dikenal");
                    break;
            }

            // Tampilkan modal
            $("#modal_detail").modal("show");
        }

        // Fungsi untuk format angka ke mata uang (Rupiah)
        function formatCurrency(amount) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0
            }).format(amount);
        }
    });


</script>
@endpush
@endsection
