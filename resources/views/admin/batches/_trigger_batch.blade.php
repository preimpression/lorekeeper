@if($batch)
    {!! Form::open(['url' => 'admin/data/batches/trigger/'.$batch->id]) !!}

    <p>
        You are about to manually trigger the batch <strong>{{ $batch->name }}</strong>.
        It currently has {{ $batch->targets->count() }} target{{ $batch->targets->count() == 1 ? '' : 's' }} that will be activated upon processing.
    </p>

    <h5>Targets</h5>
    @foreach($batch->targets->groupBy('target_type') as $type => $target)
        <div class="card p-2 d-block mb-2">
            <strong>{!! $type !!}</strong> :
            {{ $target->count() }}
            <div class="row">
            @foreach($target as $tar)
                <div class="col-4 col-md-3">
                    {!! $tar->target->displayName ? $tar->target->displayName : ($tar->target->name ? $tar->target->name : ($tar->target->title ? $tar->target->title : 'Huh')) !!}
                </div>
            @endforeach
            </div>
        </div>
    @endforeach

    <p>Are you sure you want to manually trigger <strong>{{ $batch->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Trigger Batch', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid batch selected.
@endif
