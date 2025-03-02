@extends('admin.layout')

@section('admin-title') Themes @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Themes' => 'admin/theme']) !!}

<h1>Themes</h1>

<p>You can create new Themes here for your users to be able to select from to view the site. </p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/themes/create') }}"><i class="fas fa-plus"></i> Create New Theme</a></div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'name_asc' => 'Sort Alphabetically (A-Z)',
                    'name_desc' => 'Sort Alphabetically (Z-A)',
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'name_asc',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}
</div>
@if(!count($themes))
    <p>No themes found.</p>
@else
    {!! $themes->render() !!}
    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-12 col-md-5 font-weight-bold">Name</div>
            <div class="col-6 col-md font-weight-bold">Creators</div>
        </div>
        @foreach($themes as $theme)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
        <div class="col-12 col-md-5">
            {!! $theme->is_default ? '<i class="fas fa-star mr-2" data-toggle="tooltip" title="This is the default theme."></i>' : '' !!}{!! $theme->is_active ? '' :  '<i class="fas fa-eye-slash mr-2"></i>' !!}{{ $theme->name }}
            {!! $theme->userCount ? '<small class="text-muted">In use by '. $theme->userCount .' user'. ($theme->userCount == 1 ? '' : 's') .'</small>' : '<small class="text-muted">Not in use</small>' !!}
        </div>
        <div class="col-6 col-md">{!! $theme->creators ? $theme->creatorDisplayName : 'N/A' !!}</div>
        <div class="col-6 col-md-1 text-right"><a href="{{ url('admin/themes/edit/'.$theme->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
        </div>
        @endforeach
    </div>
    {!! $themes->render() !!}

@endif

@endsection
