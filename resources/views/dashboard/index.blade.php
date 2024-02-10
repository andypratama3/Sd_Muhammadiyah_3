@extends('layouts.dashboard')
@section('title', 'Dashboard')
@push('css')
<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

@endpush
@section('content')
    <!-- Container Fluid-->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                {{ Breadcrumbs::render() }}
            </ol>
          </nav>
    </div>
    <div class="col-lg-12">
        <div class="card">
          <div class="card-body">

            <!-- Bar Chart -->
            {!! $ArtikelChart->container() !!}
            <!-- End Bar CHart -->

          </div>
        </div>
      </div>
@push('js')
<script src="{{ $ArtikelChart->cdn() }}"></script>

{{ $ArtikelChart->script() }}


@endpush
@endsection
