@extends('admin.layout')

@section('admin-title') Research @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Research' => 'admin/data/research', 'User Research' => 'admin/data/research/users']) !!}

<h1>Recent User Research Log</h1>



@if(!count($logs))
    <p>No research found.</p>
@else 
    {!! $logs->render() !!}
    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Research</th>
                <th>Tree</th>
                <th>Origin</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{!! $log->recipient->displayName !!}</td>
                    <td>{!! $log->research->displayName !!}</td>
                    <td>{!! $log->research->tree->displayName !!}</td>
                    <td>
                        <strong>{!! json_decode($log->data,1)['log_type'] !!}</strong>
                        @if(json_decode($log->data,1)['log_type'] == 'Staff Grant') from {!! $log->sender->displayName !!} @endif
                    </td>
                    <td>{!! format_date($log->created_at) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $logs->render() !!}
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $( "#sortable" ).disableSelection();
});
</script>
@endsection