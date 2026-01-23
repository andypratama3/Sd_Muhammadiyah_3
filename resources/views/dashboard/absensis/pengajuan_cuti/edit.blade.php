@extends('layouts.dashboard')

@section('title', 'Edit Pengajuan Cuti')

@section('content')
<div class="mb-4 card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Edit Pengajuan Cuti</h6>
    </div>

    @include('layouts.flashmessage')
    <div class="card-body">
        <form action="{{ route('dashboard.pengajuan_cuti.update', $pengajuanCuti->id) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('dashboard.absensis.pengajuan_cuti.form', [
                'pengajuanCuti' => $pengajuanCuti
            ])

            <div class="mt-4">
                <a href="{{ route('dashboard.pengajuan_cuti.index') }}"
                   class="btn btn-danger">
                    Kembali
                </a>
                <button class="btn btn-primary float-end">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
