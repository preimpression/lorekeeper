@if($figure)
    {!! Form::open(['url' => 'admin/world/figures/delete/'.$figure->id]) !!}

    <p>
        You are about to delete <strong>{!! $figure->name !!}</strong>? This is not reversible.
        If you would like to hide the figure from users, you can set it as inactive from the figure settings page.
    </p>
    <p>Are you sure you want to delete <strong>{{ $figure->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Figure', ['class' => 'btn btn-danger w-100']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid figure selected.
@endif
