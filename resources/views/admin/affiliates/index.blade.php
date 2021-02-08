@extends('admin.layout')

@section('admin-title') Current Affiliates @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Affiliates' => 'admin/affiliates']) !!}

<h1>Current Site Affiliates</h1>

<p>This is a list of currently accepted affiliates to your website. You can automatically create an affiliate without going through the approval process by hitting "Create New Affiliate".</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/affiliates/create') }}"><i class="fas fa-plus"></i> Create New Affiliate</a></div>


@if(!count($affiliates))
    <p>No affiliates found.</p>
@else
    {!! $affiliates->render() !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-12 col-md-3 font-weight-bold">Name and Icon</div>
            <div class="col-6 col-md-3 font-weight-bold">URL</div>
            <div class="col-6 col-md font-weight-bold">Description</div>
            <div class="col-6 col-md-1 font-weight-bold">Created At</div>
            <div class="col-md-1"></div>
          </div>
          @foreach($affiliates as $affiliate)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-12 col-md-3" style="align-self:center;"> {!! $affiliate->icon !!} {{ $affiliate->name }} @if($affiliate->is_featured)<i class="fas fa-star" data-toggle="tooltip" title="Featured!"></i>@endif </div>
            <div class="col-6 col-md-3 text-truncate" style="align-self:center;"> <a href="{{$affiliate->url}}">{{$affiliate->url}}</a> </div>
            <div class="col-6 col-md text-truncate" style="align-self:center;"> {{ $affiliate->description }} </div>
            <div class="col-6 col-md-1" style="align-self:center;"> {!! pretty_date($affiliate->created_at) !!} </div>
            <div class="col col-md-1 text-right" style="align-self:center;">
              <a href="{{ url('admin/affiliates/edit/'.$affiliate->id) }}"  class="btn btn-primary py-0 px-2 w-100">Edit</a>
            </div>
          </div>
          @endforeach
        </div>

    {!! $affiliates->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection