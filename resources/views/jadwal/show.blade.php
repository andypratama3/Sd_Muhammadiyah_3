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
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.9);
    }
    .modal-content {
        margin: auto;
        display: flex;
        width: 80%;
        max-width: 500px;
    }
    .modal-content .caption{
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
            <div class="col-12 text-center mb-5 position-relative">
                <a href="{{ route('jadwal.index') }}" class="btn btn-primary" style="position: absolute; left: 0;"><i class="bi bi-arrow-left-circle"></i> Kembali</a>
                <div class="row justify-content-center">
                    <div class="col-lg-6" >
                        <h4 class="display-4">Jadwal {{ $kelass->name }}</h2>
                        <p>Jadwal Sekolah Kini Bisa Di Lihat Secara Online</p>
                    </div>
                </div>
            </div>
            @foreach ($category_kelas as $category)
            <div class="col-lg-4 text-center mb-5">
                <img src="assets/img/person-1.jpg" alt="" class="img-fluid w-50 mb-4">
                <h4>Kelas {{ $category }}</h4>
                <div class="form-group mb-3">
                    <input type="hidden" class="kelas" id="kelas" name="kelas" value="{{ $kelass->id }}">
                    <input type="hidden" class="category_kelas" name="category_kelas" value="{{ $category }}">
                    <label for="">Tahun</label>
                    <select name="tahun_ajaran" class="form-control text-center" id="tahun_ajaran_{{ $category }}">
                        <option selected disabled>Pilih Tahun</option>
                        <?php $years = range(2010, strftime("%Y", time())); ?>
                        <?php foreach($years as $year) : ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                      <?php endforeach; ?>a>
                    </select>
                </div>
                <span class="d-block mb-3 text-uppercase" id="button_reload">
                    <button class="btn btn-primary btn-sm preview-image" type="button" data-foto="{{ $kelass->jadwal }}" data-category="{{ $category }}">Lihat Jadwal</button>
                </span>
            </div>
            @endforeach
        </div>
    </div>
</section>
<div id="myModal" class="modal">

    <!-- The Close Button -->
    <span class="closeheader">&times;</span>

    <!-- Modal Content (The Image) -->
    <img class="modal-content" id="foto" src="">

    <!-- Modal Caption (Image Text) -->
    <div id="caption" class="caption justify-content-center">
        <div class="col-12" >
          <a href="" class="btn btn-danger d-flex justify-content-center">Download</a>
        </div>
    </div>
</div>
@push('js_user')
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function () {
        function preview(data) {
            $('#preview-image').removeClass('display-none');
            let modal = document.getElementById("myModal");

            let imageUrl = '{{ asset("storage/file/jadwal/") }}'+ '/' + data;

            modal.style.display = "block";
            $('#foto').attr('src', imageUrl);

            // Get the <span> element that closes the modal
            let span = document.getElementsByClassName("closeheader")[0];
            // When the user clicks on <span> (x), close the modal
            span.onclick = function () {
                modal.style.display = "none";
            }
        }

        $('.preview-image').on('click', function () {
            let category = $(this).data('category');
            let kelas = $('#kelas').val();
            let tahun_ajaran = $('#tahun_ajaran_' + category).val();


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
                    let foto = response.jadwal;
                    if (tahun_ajaran) {
                        preview(foto);
                    } else {
                        $('#preview-image').addClass('display-none');
                        swal({
                            title: 'Error',
                            text: response.message,
                            icon: 'warning',
                            buttons: {
                                    confirm: 'Yes',
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
@endsection
