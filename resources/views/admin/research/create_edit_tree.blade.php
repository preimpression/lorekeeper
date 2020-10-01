@extends('admin.layout')

@section('admin-title') Research Trees @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Research Trees' => 'admin/data/trees', ($tree->id ? 'Edit' : 'Create').' Research Tree' => $tree->id ? 'admin/data/trees/edit/'.$tree->id : 'admin/data/trees/create']) !!}

<h1>{{ $tree->id ? 'Edit' : 'Create' }} Research Tree
    @if($tree->id)
        ({!! $tree->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-tree-button">Delete Research Tree</a>
    @endif
</h1>

{!! Form::open(['url' => $tree->id ? 'admin/data/trees/edit/'.$tree->id : 'admin/data/trees/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="row mx-0 px-0">
    <div class="form-group col-12 col-md-6 pl-0 pr-0 pr-md-1">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $tree->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-12 col-md-6 pl-0 pr-0 pl-md-1">
        {!! Form::label('Currency') !!} {!! add_help('This currency will be used for all research in this tree.') !!}
        {!! Form::select('currency_id', $currencies, $tree->currency_id, ['class' => 'form-control selectize', 'placeholder' => 'Choose a Currency']) !!}
    </div>
</div>


<div class="form-group">
    {!! Form::label('Summary (Optional)') !!}
    {!! Form::text('summary', $tree->summary, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Tree Image (Optional)') !!} {!! add_help('This image is used on the tree index and on the tree page as a header.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100x100 (Choose a standard size for all tree images)</div>
    @if(isset($tree->image_url))
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $tree->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $tree->id ? $tree->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the tree will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($tree->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-tree-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/trees/delete') }}/{{ $tree->id }}", 'Delete Research Tree');
    });
    $('.selectize').selectize();
});
    
</script>
@endsection