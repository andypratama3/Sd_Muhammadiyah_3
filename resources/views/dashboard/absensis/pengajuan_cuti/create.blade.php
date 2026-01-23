@extends('layouts.dashboard')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="mb-4 card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Ajukan Cuti</h6>
    </div>

    <div class="card-body">
        @include('layouts.flashmessage')
        <form action="{{ route('dashboard.pengajuan_cuti.store') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            @include('dashboard.absensis.pengajuan_cuti.form', [
                'pengajuanCuti' => null
            ])

            <div class="mt-4">
                <a href="{{ route('dashboard.pengajuan_cuti.index') }}"
                   class="btn btn-danger">
                    Kembali
                </a>
                <button class="btn btn-primary float-end">Kirim</button>
            </div>
        </form>
    </div>
</div>
@endsection
