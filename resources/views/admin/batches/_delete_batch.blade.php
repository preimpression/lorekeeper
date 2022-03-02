@if($batch)
    {!! Form::open(['url' => 'admin/data/batches/delete/'.$batch->id]) !!}

    <p>
        You are about to delete the batch <strong>{{ $batch->name }}</strong>.
        It currently has {{ $batch->targets->count() }} target{{ $batch->targets->count() == 1 ? '' : 's' }}. They will not be activated upon this deletion!
    </p>
    <p>Are you sure you want to delete <strong>{{ $batch->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Batch', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid batch selected.
@endif
