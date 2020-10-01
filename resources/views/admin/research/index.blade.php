@extends('admin.layout')

@section('admin-title') Research @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Research' => 'admin/data/research']) !!}

<h1>Research Branches</h1>

<p class="mb-1">This is a list of research branches that users can use currency to purchase. The currency is set via the tree - which is required for every research.</p> 
<p>If you want more icons than fontAwesome free provides, I recommend looking up <a href="https://icomoon.io/">Icomoon</a> and pulling in custom svg icons.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/research/create') }}"><i class="fas fa-plus"></i> Create New Research</a></div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('tree_id', $trees, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>


@if(!count($researches))
    <p>No research found.</p>
@else 
    {!! $researches->render() !!}
    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Tree</th>
                <th>Parent</th>
                <th>Prerequisite</th>
                <th>Price</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($researches as $research)
                <tr class="sort-research" data-id="{{ $research->id }}">
                    <td class="font-weight-bold">
                        <i class=" mr-1 {!! $research->icon_code !!}"/></i>
                        {!! $research->displayName !!}
                    </td>
                    <td>{!! $research->tree ? $research->tree->displayName : '' !!}</td>
                    <td>{!! $research->parent ? $research->parent->displayName : '' !!}</td>
                    <td>{!! $research->prerequisite ? $research->prerequisite->displayName : '' !!}</td>
                    <td>
                        {!! $research->price ? $research->price : '' !!}
                        {!! $research->tree->currency->abbreviation ? $research->tree->currency->abbreviation : $research->tree->currency->displayName !!}
                    
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/research/edit/'.$research->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $researches->render() !!}
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $( "#sortable" ).disableSelection();
});
</script>
@endsection