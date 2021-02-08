<p>This will reject the affiliate request.</p>
{!! Form::open(['url' => 'admin/affiliates/edit/'.$affiliate->id.'/reject']) !!}
    <div class="form-group">
        {!! Form::label('staff_comment', 'Comment') !!} {!! add_help('Enter a comment for the user. They will see this on their request page.') !!}
        {!! Form::textarea('staff_comment', $affiliate->staff_comment, ['class' => 'form-control']) !!}
    </div>
    <div class="text-right">
        {!! Form::submit('Reject Affiliate Request', ['class' => 'btn btn-danger btn-block']) !!}
    </div>
{!! Form::close() !!}