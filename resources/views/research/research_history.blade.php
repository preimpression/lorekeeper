@extends('research.layout')

@section('research-title') My Research History @endsection

@section('research-content')
{!! breadcrumbs(['Research' => 'research', 'My Research History' => 'history']) !!}

<h1>
    My Research History
</h1>

{!! $logs->render() !!}
    <table class="table table-sm">
        <thead>
            <th style="width:25%;">Research</th>
            <th>Tree</th>
            <th>Type</th>
            <th>Message</th>
            <th>Cost</th>
            <th class="text-right">Date</th>
        </thead>
        <tbody>
            @foreach($logs as $log)
                @include('research._research_history_row', ['log' => $log])
            @endforeach
        </tbody>
    </table>
{!! $logs->render() !!}

@endsection