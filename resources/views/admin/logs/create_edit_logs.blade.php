@extends('admin.layout')

@section('admin-title') Dev Logs @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Devlogs' => 'admin/logs', ($log->id ? 'Edit' : 'Create').' Devlog' => $log->id ? 'admin/logs/edit/'.$log->id : 'admin/logs/create']) !!}

<h1>{{ $log->id ? 'Edit' : 'Create' }} Devlog
    @if($log->id)
        <a href="#" class="btn btn-danger float-right delete-logs-button">Delete Devlog</a>
    @endif
</h1>

{!! Form::open(['url' => $log->id ? 'admin/logs/edit/'.$log->id : 'admin/logs/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $log->title, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Devlog Time (Optional)') !!} {!! add_help('This is the time that the devlog should be posted. Make sure the Is Viewable switch is off.') !!}
            {!! Form::text('post_at', $log->post_at, ['class' => 'form-control', 'id' => 'datepicker']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Devlog Content') !!}
    {!! Form::textarea('text', $log->text, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="row">
    <div class="col-md">
        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, $log->id ? $log->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Viewable', ['class' => 'form-check-label ml-3']) !!}
            {!! add_help('If this is turned off, the devlog will not be visible. If the post time is set, it will automatically become visible at/after the given post time, so make sure the post time is empty if you want it to be completely hidden.') !!}
        </div>
    </div>
    @if($log->id && $log->is_visible)
        <div class="col-md">
            <div class="form-group">
                {!! Form::checkbox('bump', 1, null, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('bump', 'Bump devlogs', ['class' => 'form-check-label ml-3']) !!}
                {!! add_help('If toggled on, this will alert users that there is new devlog. Best in conjunction with a clear notification of changes!') !!}
            </div>
        </div>
    @endif
</div>

<div class="text-right">
    {!! Form::submit($log->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-logs-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/logs/delete') }}/{{ $log->id }}", 'Delete Post');
    });
    $( "#datepicker" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:ss',
    });
});

</script>
@endsection
