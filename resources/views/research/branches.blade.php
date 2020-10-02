@extends('research.layout')

@section('research-title') Index @endsection

@section('research-content')
{!! breadcrumbs(['Research' => 'research']) !!}
<h1>Research</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('tree_id', $researchTrees, Request::get('name'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select('sort', [
                    'alpha'          => 'Sort Alphabetically (A-Z)',
                    'alpha-reverse'  => 'Sort Alphabetically (Z-A)',
                    'tree'           => 'Sort by Tree',
                    'newest'         => 'Newest First',
                    'oldest'         => 'Oldest First'    
                ], Request::get('sort') ? : 'tree', ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
</div>

{!! $researches->render() !!}
<div class="row">
    @foreach($researches as $research)
        @include('research._branch_entry', ['research' => $research])
    @endforeach
</div>
{!! $researches->render() !!}

<div class="text-center mt-4 small text-muted">{{ $researches->total() }} result{{ $researches->total() == 1 ? '' : 's' }} found.</div>

@endsection
