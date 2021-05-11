@extends('admin.layout')

@section('admin-title') Location Types @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Location Types' => 'admin/world/location-types', ($type->id ? 'Edit' : 'Create').' Location Type' => $type->id ? 'admin/world/location-types/edit/'.$type->id : 'admin/world/location-types/create']) !!}

<h1>{{ $type->id ? 'Edit' : 'Create' }} Location Type
    @if($type->id)
        ({!! $type->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-type-button">Delete Location Type</a>
    @endif
</h1>

{!! Form::open(['url' => $type->id ? 'admin/world/location-types/edit/'.$type->id : 'admin/world/location-types/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row mx-0 px-0 ">
    <div class="form-group col-md-6 px-0 pr-md-1">
        {!! Form::label('Name - Singular') !!}
        {!! Form::text('name', $type->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group col-md-6 px-0 pl-md-1">
        {!! Form::label('Name - Plural') !!}
        {!! Form::text('names', $type->names, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('Summary (Optional)') !!}
    {!! Form::text('summary', $type->summary, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    @if($type->thumb_extension)
        <a href="{{$type->thumbUrl}}"  data-lightbox="entry" data-title="{{ $type->name }}"><img src="{{$type->thumbUrl}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
    @endif
    {!! Form::label('Thumbnail Image (Optional)') !!} {!! add_help('This thumbnail is used on the location type index.') !!}
    <div>{!! Form::file('image_th') !!}</div>
    <div class="text-muted">Recommended size: 200x200</div>
    @if(isset($type->thumb_extension))
        <div class="form-check">
            {!! Form::checkbox('remove_image_th', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Thumbnail As-Is', 'data-on' => 'Remove Thumbnail Image']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    @if($type->image_extension)
        <a href="{{$type->imageUrl}}"  data-lightbox="entry" data-title="{{ $type->name }}"><img src="{{$type->imageUrl}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
    @endif
    {!! Form::label('Location Type Image (Optional)') !!} {!! add_help('This image is used on the location type page as a header.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: None (Choose a standard size for all location type header images.)</div>
    @if(isset($type->image_extension))
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Header Image As-Is', 'data-on' => 'Remove Current Header Image']) !!}
        </div>
    @endif
</div>

<div class="form-group" style="clear:both">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $type->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="text-right">
    {!! Form::submit($type->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-type-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/world/location-types/delete') }}/{{ $type->id }}", 'Delete Location Type');
    });
    $('.selectize').selectize();
});

</script>
@endsection
