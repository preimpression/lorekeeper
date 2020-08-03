@extends('admin.layout')

@section('admin-title') Bulletins @endsection

@section('admin-content')
{!! breadcrumbs(['Staff Bulletins' => 'bulletins']) !!}
<h1>Staff Bulletins</h1>
<p>
  If you have the power to edit pages, you may create new staff bulletins in the same location that you create News posts.
</p>
@if(count($newses))
    {!! $newses->render() !!}
    <div class="row">
      <div class="col-12 col-md-9 pl-0">
        @foreach($newses as $news)
            @include('news._news', ['news' => $news])
        @endforeach
      </div>

      <div class="col-3 card p-3 h-100">
        <h5>Other Bulletins</h5>
        @foreach($newses as $news)
          <div>
            {!! $news->adminDisplayName !!}
          </div>
        @endforeach
      </div>
    </div>

        {!! $newses->render() !!}
@else
    <div>No news posts yet.</div>
@endif
@endsection
