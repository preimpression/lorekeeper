@extends('admin.layout')

@section('admin-title') Bulletins @endsection

@section('admin-content')
{!! breadcrumbs(['Staff Bulletins' => 'admin/bulletins']) !!}
<h1>Staff Bulletins</h1>
<p class="mb-1">
  If you have the power to edit pages, you may create new staff bulletins in the same location that you create News posts.
</p>
<p>
  Click <a href="{{url('admin/bulletins/all')}}">here</a> to see a list of all bulletins.
</p>
  @if(count($newses))
    {!! $newses->render() !!}
      @foreach($newses as $news)
          @include('news._newses', ['news' => $news])
      @endforeach
    {!! $newses->render() !!}
  @else
      <div>No bulletins yet.</div>
  @endif
@endsection
