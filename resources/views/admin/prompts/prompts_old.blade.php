@extends('admin.layout')

@section('admin-title') Prompts @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Prompts' => 'admin/data/prompts', 'Prompts Original Style' => 'admin/data/prompts/old']) !!}

<h1>Prompts</h1>

<p>This is a list of prompts users can submit to.</p>
<p>If you want to see this list using the accordion style, <a href="/admin/data/prompts" class="font-weight-bold">go here.</a></p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/prompts/create') }}"><i class="fas fa-plus"></i> Create New Prompt</a></div>

<div>
  {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
  <div class="form-group mr-3 mb-3">
    {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
  </div>
  <div class="form-group mr-3 mb-3">
    {!! Form::select('prompt_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
  </div>
  <div class="form-group mb-3">
    {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
  </div>
  {!! Form::close() !!}
</div>

@if(!count($prompts))
<p>No prompts found.</p>
@else
{!! $prompts->render() !!}

<div class="row ml-md-2">
  <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
    <div class="col-4 col-md-1 font-weight-bold">Active</div>
    <div class="col-4 col-md-3 font-weight-bold">Name</div>
    <div class="col-4 col-md-3 font-weight-bold">Category</div>
    <div class="col-4 col-md-2 font-weight-bold">Starts</div>
    <div class="col-4 col-md-2 font-weight-bold">Ends</div>
  </div>

  @foreach($prompts as $prompt)
  <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
    <div class="col-2 col-md-1"> {!! $prompt->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!} </div>
    <div class="col-5 col-md-3 text-truncate" title="{{ $prompt->summary }}"> {{ $prompt->name }}</div>
    <div class="col-5 col-md-3"> {{ $prompt->category ? $prompt->category->name : '-' }} </div>
    <div class="col-4 col-md-2">{!! $prompt->start_at ? format_date($prompt->start_at) : '-' !!}</div>
    <div class="col-4 col-md-2">{!! $prompt->end_at ? format_date($prompt->end_at) : '-' !!}</div>
    <div class="col-3 col-md-1 text-right"> <a href="{{ url('admin/data/prompts/edit/'.$prompt->id) }}"  class="btn btn-primary py-0 px-2">Edit</a> </div>
  </div>
  @endforeach
</div>
{!! $prompts->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
