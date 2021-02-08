@extends('admin.layout')

@section('admin-title') Affiliate Requests @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Affiliates' => 'admin/affiliates', 'Affiliate Requests' => 'admin/affiliates/pending']) !!}


<h1>
    Affiliate Requests
</h1>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/affiliates/pending*') }}" href="{{ url('admin/affiliates/pending') }}">Pending</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/affiliates/accepted*') }}" href="{{ url('admin/affiliates/accepted') }}">Accepted</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ set_active('admin/affiliates/rejected*') }}" href="{{ url('admin/affiliates/rejected') }}">Rejected</a>
  </li>
</ul>

{!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('sort', [
                'newest'         => 'Newest First',
                'oldest'         => 'Oldest First',
            ], Request::get('sort') ? : 'oldest', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
{!! Form::close() !!}

{!! $affiliates->render() !!}

<div class="row ml-md-2">
  <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
    <div class="col-6 col-md-3 font-weight-bold">Site</div>
    <div class="col-6 col-md-4 font-weight-bold">Url</div>
    <div class="col-6 col-md-3 font-weight-bold">Submitted</div>
    <div class="col-6 col-md-1 font-weight-bold">Status</div>
  </div>

  @foreach($affiliates as $affiliate)
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
        <div class="col-12 col-md-3" style="align-self:center;"> {!! $affiliate->icon !!} {{ $affiliate->name }} @if($affiliate->is_featured)<i class="fas fa-star" data-toggle="tooltip" title="Featured!"></i>@endif </div>    
        <div class="col col-md-4 text-truncate" style="align-self:center;"><a href="{{ $affiliate->url }}">{{ $affiliate->url }}</a></div>
        <div class="col col-md-3" style="align-self:center;">{!! pretty_date($affiliate->created_at) !!}</div>
        <div class="col-1 d-none d-md-block" style="align-self:center;"><span class="btn btn-{{ $affiliate->status == 'Pending' ? 'secondary' : ($affiliate->status == 'Accepted' ? 'success' : 'danger') }} ">{{ $affiliate->status }}</span></div>
        <div class="col col-md-1" style="align-self:center;"><a href="{{ url('admin/affiliates/edit/'.$affiliate->id) }}" class="btn btn-primary btn-block ">Details</a></div>
    </div>
  @endforeach

</div>

{!! $affiliates->render() !!}
<div class="text-center mt-4 small text-muted">{{ $affiliates->total() }} result{{ $affiliates->total() == 1 ? '' : 's' }} found.</div>


@endsection
