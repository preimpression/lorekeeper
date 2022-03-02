@extends('admin.layout')

@section('admin-title') Batches @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Batches' => 'admin/data/batches']) !!}

<h1>Batches</h1>

<p>This is a list of batches in the game.</p>


<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        <div class="text-right mb-3">
            <a class="btn btn-primary" href="{{ url('admin/data/batches/create') }}"><i class="fas fa-plus"></i> Create New Batch</a>
        </div>
    {!! Form::close() !!}
</div>

@if(!count($batches))
    <p>No batches found.</p>
@else
    {!! $batches->render() !!}

        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom font-weight-bold">
                <div class="col-5 col-md-5">Name</div>
                <div class="col-5 col-md">Target Count</div>
            </div>
            @foreach($batches as $batch)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-5 col-md-5"> {{ $batch->name }} </div>
                    <div class="col-5 col-md">
                        {{ $batch->targets->count() }}
                        @if($batch->targets->count() > 0)<a href="#batch-{{$batch->id}}-targets" data-toggle="collapse" role="button"  aria-expanded="false" aria-controls="batch-{{$batch->id}}-targets">
                            <i class="fas fa-caret-down"></i>
                        </a>@endif
                    </div>
                    <div class="col-2 col-md-auto text-right">
                    <a href="{{ url('admin/data/batches/edit/'.$batch->id) }}"  class="btn btn-primary py-0 px-2">Edit</a>
                    </div>

                    <div class="col-12 collapse" id="batch-{{ $batch->id }}-targets">
                        <div class="row ml-2 no-gutters">
                            <strong class="col-12">Targets:</strong>
                            @foreach($batch->targets->groupBy('target_type') as $type => $target)
                                <div class="col-6 col-md-3 p-1">
                                    <div class="card-header">
                                        <strong>{!! $type !!}</strong> :
                                        {{ $target->count() }}
                                        <a href="#batch-{{ $batch->id }}-{{ $type }}" data-toggle="collapse" role="button"  aria-expanded="false" aria-controls="batch-{{$batch->id}}-targets">
                                            <i class="fas fa-caret-down"></i>
                                        </a>
                                    </div>
                                    <div class="collapse" id="batch-{{ $batch->id }}-{{ $type }}">
                                        @foreach($target as $tar)
                                            <span class="py-1 px-3 d-inline-block">
                                                {!! $tar->target->displayName ? $tar->target->displayName : ($tar->target->name ? $tar->target->name : ($tar->target->title ? $tar->target->title : 'Invalid Name')) !!} <br>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    {!! $batches->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
