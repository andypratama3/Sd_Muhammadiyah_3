<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulir PPDB - SD Kreatif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('ppdb_asset/css/style.css') }}">
    <style>
        code {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="form-container">
            <img src="{{ asset('ppdb_asset/img/SD3_logo1.png') }}" alt="" class="logo-header img-fluid img-bordered">
            <h1>🎓 Formulir Lengkap Pendaftaran Siswa Baru</h1>
            <h4>Sekolah Kreatif SD Muhammadiyah 3 Samarinda<br>Tahun Ajaran 2025/2026</h4>


            <div class="step active">
               <div class="section-title">🧒 Data Calon Siswa</div>
               <div class="row">
                {!! Form::open(['route' => 'formDetail.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                @csrf
                @include('spmb.field_detail')
                {!! Form::close() !!}
               </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>

