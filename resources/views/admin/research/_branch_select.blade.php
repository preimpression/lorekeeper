@if(!isset($tree))
    {!! Form::label('Parent (Optional)') !!} {!! add_help('This is the branch under which the research is. <br><strong>If left blank, this will be \'top level.\'</strong>""') !!}
    {!! Form::select('parent_id', [0=>'Select a Tree First'], null, ['class' => 'form-control selectize', 'disabled' => true]) !!}
@else
    {!! Form::label('Parent (Optional)') !!} {!! add_help('This is the branch under which the research is. <br><strong>If left blank, this will be \'top level.\'</strong>""') !!}
    {!! Form::select('parent_id', [0=>'Choose a Parent'] + $prereq_branches, isset($research) ? $research->parent_id : null, ['class' => 'form-control selectize']) !!}
@endif