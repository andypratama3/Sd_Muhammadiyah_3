{{-- @foreach ($activitys as $activity)
    <p>{{ $activity->log_name }}</p>
@endforeach --}}
@extends('layouts.dashboard')
@section('title','All Activity')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-4 text-center">Activity</h4>

                <div class="table-responsive">
                    <table class="table mt-4" id="siswa_table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Log</th>
                                <th>Description</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activitys as $activity)
                            <tr>
                                <td>{{ ++$no }}</td>
                                <td>{{ $activity->log_name }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ $activity->causer_id }}</td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
