@if($affiliate)
    {!! Form::open(['url' => 'admin/affiliates/delete/'.$affiliate->id]) !!}

<div class="text-center">
    <span style="font-size:4em;vertical-align: middle"><i class="fas fa-skull-crossbones"></i></span>
    {!! $affiliate->icon !!}
    <span style="font-size:4em;vertical-align: middle"><i class="fas fa-skull-crossbones"></i></span>
</div>

    <p>You are about to delete the affiliate with <strong>{{ $affiliate->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $affiliate->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Affiliate', ['class' => 'btn btn-danger btn-block']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid affiliate selected.
@endif