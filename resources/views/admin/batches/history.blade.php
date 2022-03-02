@extends('admin.layout')

@section('admin-title') Batch Trigger History @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Batches' => 'admin/data/batches', 'Batch Trigger History' => 'admin/data/batches/history']) !!}

<h1>Batch Trigger History</h1>

<p>This is a list of batches that have been triggered in the past.</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        <div class="text-right mb-3">
            <a class="btn btn-primary" href="{{ url('admin/data/logs/create') }}"><i class="fas fa-plus"></i> Create New Batch</a>
        </div>
    {!! Form::close() !!}
</div>


@if(!count($logs))
    <p class="text-center"><small>No logs found.</small></p>
@else
    {!! $logs->render() !!}

        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom font-weight-bold">
                <div class="col-6 col-md-2">Name</div>
                <div class="col-6 col-md-2">Trigger</div>
                <div class="col-6 col-md">Targets</div>
                <div class="col-6 col-md-2">Date</div>
            </div>
            @foreach($logs->sortByDesc('created_at') as $batch)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-6 col-md-2"> #{{ $batch->batch_id }} - {{ $batch->batch_name }} </div>
                    <div class="col-6 col-md-2"> {!! $batch->staff ? $batch->staff->displayName : 'Automated' !!}</div>
                    <div class="col-12 col-md ">
                        @foreach($batch->data as $type => $targets)
                            <span class="pr-2"><strong>{!! $type !!}</strong>: {!! implode(', ', $targets) !!}</span>
                        @endforeach
                    </div>
                    <div class="col-12 col-md-2"> {!! format_date($batch->created_at) !!}</div>

                </div>
            @endforeach
        </div>

    {!! $logs->render() !!}
@endif

@endsection
