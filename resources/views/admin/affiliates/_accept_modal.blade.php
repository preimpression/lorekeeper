<p>This will accept the affiliate request.</p>
{!! Form::open(['url' => 'admin/affiliates/edit/'.$affiliate->id.'/accept']) !!}

    <div class="form-group">
        {!! Form::label('staff_comment', 'Comment') !!} {!! add_help('Enter a comment for the user. They will see this on their request page.') !!}
        {!! Form::textarea('staff_comment', $affiliate->staff_comment, ['class' => 'form-control']) !!}
    </div>
    <div class="row">
        <div class="col-auto">
            {!! Form::label('is_featured', 'Feature this Affiliate? ') !!} &nbsp;
            {!! Form::checkbox('is_featured', 1, $affiliate->id ? $affiliate->is_featured : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        </div>
        <div class="col text-right">
            {!! Form::submit('Accept Affiliate Request', ['class' => 'btn btn-success btn-block']) !!}
        </div>
    </div>
{!! Form::close() !!}