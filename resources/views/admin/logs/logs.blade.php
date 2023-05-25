@extends('admin.layout')

@section('admin-title') Dev Logs @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'News' => 'admin/news', 'Development Logs' => 'admin/logs']) !!}

<h1>Dev Logs</h1>

<p>You can create new devlog here. Creating a devlog alerts every user that there is a new post, unless the devlog is marked as not viewable (see the devlog creation page for details).</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/logs/create') }}"><i class="fas fa-plus"></i> Create New Devlog</a></div>
@if(!count($logs))
    <p>No devlogs found.</p>
@else
    {!! $logs->render() !!}
      <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
          <div class="col-12 col-md-5 font-weight-bold">Title</div>
          <div class="col-6 col-md-3 font-weight-bold">Posted At</div>
          <div class="col-6 col-md-3 font-weight-bold">Last Edited</div>
        </div>
        @foreach($logs as $log)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
          <div class="col-12 col-md-5">
              @if(!$log->is_visible)
                  @if($log->post_at)
                      <i class="fas fa-clock mr-1" data-toggle="tooltip" title="This post is scheduled to be posted in the future."></i>
                  @else
                      <i class="fas fa-eye-slash mr-1" data-toggle="tooltip" title="This post is hidden."></i>
                  @endif
              @endif
              <a href="{{ $log->url }}">{{ $log->title }}</a>
          </div>
          <div class="col-6 col-md-3">{!! pretty_date($log->post_at ? : $log->created_at) !!}</div>
          <div class="col-6 col-md-3">{!! pretty_date($log->updated_at) !!}</div>
          <div class="col-12 col-md-1 text-right"><a href="{{ url('admin/logs/edit/'.$log->id) }}" class="btn btn-primary py-0 px-2 w-100">Edit</a></div>
        </div>
        @endforeach
      </div>
    {!! $logs->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $logs->total() }} result{{ $logs->total() == 1 ? '' : 's' }} found.</div>

@endif

@endsection
