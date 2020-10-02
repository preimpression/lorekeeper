@if($research)
    {!! Form::open(['url' => 'admin/data/research/delete/'.$research->id]) !!}

    <p>
        You are about to delete the research <strong>{{ $research->name }}</strong>. This is not reversible.
        If you would like to hide the research from users, you can set it as inactive from the research settings page.
    </p>
    <p>Are you sure you want to delete <strong>{{ $research->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Research', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid research selected.
@endif