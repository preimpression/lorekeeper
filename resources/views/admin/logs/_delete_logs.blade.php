@if($log)
    {!! Form::open(['url' => 'admin/logs/delete/'.$log->id]) !!}

        <p>
            You are about to delete the devlog <strong>{{ $log->title }}</strong>. This is not reversible.
            If you would like to preserve the content while preventing users from accessing the devlog,
            you can use the viewable setting instead to hide the devlog.
        </p>
        <p>Are you sure you want to delete <strong>{{ $log->title }}</strong>?</p>

        <div class="text-right">
            {!! Form::submit('Delete Devlog', ['class' => 'btn btn-danger']) !!}
        </div>

    {!! Form::close() !!}
@else
    Invalid devlog selected.
@endif
