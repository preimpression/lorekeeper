@extends('admin.layout')

@section('admin-title') Research trees @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Research trees' => 'admin/data/trees']) !!}

<h1>Research trees</h1>

<p>This is a list of research trees that users can use currency to purchase research from.</p> 
<p>The sorting order reflects the order in which the trees will be listed on the tree index.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/trees/create') }}"><i class="fas fa-plus"></i> Create New Research Tree</a></div>
@if(!count($trees))
    <p>No research trees found.</p>
@else 
    <table class="table table-sm tree-table">
        <tbody id="sortable" class="sortable">
            @foreach($trees as $tree)
                <tr class="sort-item" data-id="{{ $tree->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $tree->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/trees/edit/'.$tree->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/trees/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection