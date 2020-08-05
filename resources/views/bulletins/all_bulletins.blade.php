@extends('admin.layout')

@section('admin-title') All Bulletins @endsection

@section('admin-content')
{!! breadcrumbs(['Staff Bulletins' => 'admin/bulletins','All Bulletins' => 'admin/bulletins/all']) !!}
<h1>Staff Bulletins</h1>
<p>
  Here is a list of all staff bulletins.
</p>
  @if(count($newses))
  {!! $newses->render() !!}
      @foreach($newses as $news)
         <div class="mb-1 ml-0 ml-md-3">
           <h5 class="d-inline-block">{!! $news->displayName !!}</h5> <span class="d-none d-md-inline-block">- Posted on {!! format_date($news->updated_at, false) !!} by {!! $news->user->displayName !!}</span>
         </div>
      @endforeach
  @else
      <div>No bulletins yet.</div>
  @endif
@endsection
