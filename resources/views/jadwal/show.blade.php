@extends('layouts.user')
@section('title','Jadwal')

@push('css_user')
<style>
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {
        opacity: 0.7;
    }

    .modal {
        display: none;
        overflow-y: initial !important;
        position: fixed;
        z-index: 1;
        padding-top: 80px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: auto;
        display: flex;
        flex-direction: column;
        width: 80%;
        max-width: 800px;
    }

    .modal-content .caption {
        border: 2px solid white;
    }

    @keyframes zoom {
        from {
            transform: scale(0)
        }

        to {
            transform: scale(1)
        }
    }

    /* The Close Button */
    .closeheader {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }

    .closeheader:hover,
    .closeheader:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    @media only screen and (max-width: 700px) {
        .modal-content {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section>
    <div class="container" data-aos="fade-up">
        <div class="row">
            <div class="mb-5 text-center col-12 position-relative">
                <a href="{{ route('jadwal.index') }}" class="btn btn-primary" style="position: absolute; left: 0;">
                    <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <h4 class="display-4">Jadwal {{ $kelass->name }}</h4>
                        <p>Jadwal Sekolah Kini Bisa Di Lihat Secara Online</p>
                    </div>
                </div>
            </div>

            @foreach ($category_kelas as $category)
            <div class="mb-5 text-center col-lg-4">
                <img src="{{ asset('asset_new/images/SD3_logo1.png') }}" alt="" class="mb-4 img-fluid w-50">
                <h4>Kelas {{ $category }}</h4>
                <div class="mb-3 form-group">
                    <input type="hidden" class="kelas" id="kelas" name="kelas" value="{{ $kelass->id }}">
                    <input type="hidden" class="category_kelas" name="category_kelas" value="{{ $category }}">
                    <label for="">Tahun</label>
                    <select name="tahun_ajaran" class="text-center form-control" id="tahun_ajaran_{{ $category }}">
                        <option selected disabled>Pilih Tahun</option>
                        <?php $years = range(2010, strftime("%Y", time())); ?>
                        <?php foreach($years as $year) : ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <span class="mb-3 d-block text-uppercase" id="button_reload">
                    <button class="btn btn-primary btn-sm preview-image" type="button"
                        data-foto="{{ $kelass->jadwal }}" data-category="{{ $category }}">
                        Lihat Jadwal
                    </button>
                </span>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Modal -->
<div id="myModal" class="mt-4 modal show" data-aos="zoom-in-down" data-duration="200">
    <!-- The Close Button -->
    <span class="closeheader">&times;</span>

    <!-- Modal Content -->
    <div class="text-center modal-content">
        <img id="foto" src="" class="w-100 d-none" style="max-height: 600px;" />
        <iframe id="pdf_frame" src="" class="w-100 d-none" style="height: 600px;" frameborder="0"></iframe>
    </div>

    <!-- Modal Caption -->
    <div id="caption" class="text-center">
        <a href="" id="download_link" class="mt-2 btn btn-danger btn-sm" style="display: inline-block;" download>Download</a>
    </div>
</div>
@endsection

@push('js_user')
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function () {
        function preview(data) {
            let modal = document.getElementById("myModal");
            let fileUrl = '{{ asset("storage/file/jadwal/") }}' + '/' + data;

            modal.style.display = "block";

            // Reset
            $('#foto').addClass('d-none').attr('src', '');
            $('#pdf_frame').addClass('d-none').attr('src', '');

            // Cek ekstensi file
            let ext = data.split('.').pop().toLowerCase();

            if (ext === 'pdf') {
                $('#pdf_frame').removeClass('d-none').attr('src', fileUrl);
            } else {
                $('#foto').removeClass('d-none').attr('src', fileUrl);
            }

            // Set link download
            $('#download_link').attr('href', fileUrl);

            // Close modal
            let span = document.getElementsByClassName("closeheader")[0];
            span.onclick = function () {
                modal.style.display = "none";
            }
        }

        $('.preview-image').on('click', function () {
            let category = $(this).data('category');
            let kelas = $('#kelas').val();
            let tahun_ajaran = $('#tahun_ajaran_' + category).val();

            if (!tahun_ajaran) {
                swal({
                    title: 'Error',
                    text: 'Silakan pilih tahun ajaran terlebih dahulu',
                    icon: 'warning',
                    buttons: {
                        confirm: 'OK',
                    },
                    dangerMode: true,
                });
                return;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: "{{ route('jadwal.tahun.ajaran') }}",
                data: {
                    kelas: kelas,
                    tahun_ajaran: tahun_ajaran,
                    category_kelas: category
                },
                success: function (response) {
                    let tahun_ajaran = response.tahun_ajaran;
                    let foto = response.file;
                    if (tahun_ajaran && foto) {
                        preview(foto);
                    } else {
                        swal({
                            title: 'Error',
                            text: response.message,
                            icon: 'warning',
                            buttons: {
                                confirm: 'OK',
                            },
                            dangerMode: true,
                        });
                    }
                }
            });
        });
    });
</script>
@endpush
