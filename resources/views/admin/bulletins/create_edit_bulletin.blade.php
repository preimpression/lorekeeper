@extends('admin.layout')

@section('admin-title') Bulletins @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Bulletins' => 'admin/bulletins', ($bulletins->id ? 'Edit' : 'Create').' Post' => $bulletins->id ? 'admin/bulletins/edit/'.$bulletins->id : 'admin/news/create']) !!}

<h1>{{ $bulletins->id ? 'Edit' : 'Create' }} Bulletin
    @if($bulletins->id)
        <a href="#" class="btn btn-danger float-right delete-news-button">Delete Bulletin</a>
    @endif
</h1>

{!! Form::open(['url' => $bulletins->id ? 'admin/bulletins/edit/'.$bulletins->id : 'admin/bulletins/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $bulletins->title, ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Post Time (Optional)') !!} {!! add_help('This is the time that the news post should be posted. Make sure the Is Viewable switch is off.') !!}
            {!! Form::text('post_at', $bulletins->post_at, ['class' => 'form-control', 'id' => 'datepicker']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Post Content') !!}
    {!! Form::textarea('text', $bulletins->text, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $bulletins->id ? $bulletins->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_visible', 'Is Viewable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, the post will not be visible. If the post time is set, it will automatically become visible at/after the given post time, so make sure the post time is empty if you want it to be completely hidden.') !!}
</div>

<div class="text-right">
    {{ Form::hidden('staff_bulletin', '1') }}
    {!! Form::submit($bulletins->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-news-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/bulletins/delete') }}/{{ $bulletins->id }}", 'Delete Post');
    });
    $( "#datepicker" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:ss',
    });
});

</script>
@endsection
