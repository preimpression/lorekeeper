@if($tree)
    {!! Form::open(['url' => 'admin/data/trees/delete/'.$tree->id]) !!}

    <p>
        You are about to delete the research tree <strong>{{ $tree->name }}</strong>. This is not reversible.
        If you would like to hide the tree from users, you can set it as inactive from the research tree settings page.
    </p>
    <p>Are you sure you want to delete <strong>{{ $tree->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Research Tree', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid research tree selected.
@endif