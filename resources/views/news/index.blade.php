@extends('layouts.app')

@section('title') Site News @endsection

@section('content')
{!! breadcrumbs(['Site News' => 'news']) !!}
<h1>Site News</h1>
@if(count($newses))
{!! $newses->render() !!}


    <div class="d-flex p-0 m-0">
      <div class="col-12 col-md-9 pl-0">
        @foreach($newses as $news)
            @include('news._news', ['news' => $news])
        @endforeach
      </div>

      <div class="col-3 card p-3 h-100">
        <h5>Other News</h5>

        <div class="mb-4">
          @foreach($newses as $news)
            <div>
              {!! $news->displayName !!}
            </div>
          @endforeach
        </div>

        {!! $newses->render() !!}

      </div>
    </div>

    {!! $newses->render() !!}



@else
    <div>No news posts yet.</div>
@endif
@endsection
