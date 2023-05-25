@extends('layouts.app')

@section('title') Development Logs @endsection

@section('sidebar')
    @include('news._sidebar')
@endsection

@section('content')
{!! breadcrumbs(['Site News' => 'news', 'Development Logs' => 'logs']) !!}
<h1>Dev Logs</h1>
@if(count($logs))
    {!! $logs->render() !!}
    @foreach($logs as $log)
        @include('logs._log', ['dev-logs' => $log, 'page' => FALSE])
    @endforeach
    {!! $logs->render() !!}
@else
    <div>No development logs yet.</div>
@endif
@endsection
