@if($bulletins)
    {!! Form::open(['url' => 'admin/bulletins/delete/'.$bulletins->id]) !!}

    <p>You are about to delete the  bulletin <strong>{{ $bulletins->title }}</strong>. This is not reversible. If you would like to preserve the content while preventing other staff from accessing the post, you can use the viewable setting instead to hide the post.</p>
    <p>Are you sure you want to delete <strong>{{ $bulletins->title }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Post', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid post selected.
@endif
