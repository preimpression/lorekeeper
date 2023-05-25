@extends('layouts.app')

@section('title') {{ $log->title }} @endsection

@section('sidebar')
    @include('news._sidebar')
@endsection

@section('content')
    {!! breadcrumbs(['Site News' => 'news', 'Development Logs' => 'logs', $log->title => $log->url]) !!}
    @include('logs._log', ['logs' => $log, 'page' => TRUE])
<hr>
<br><br>

@comments(['model' => $log,
        'perPage' => 5
    ])

@endsection
