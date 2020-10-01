@extends('admin.layout')

@section('admin-title') Grant Research @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Research' => 'admin/grants/research']) !!}

<h1>Grant Research</h1>

{!! Form::open(['url' => 'admin/grants/research']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('users[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('users[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Research(es)') !!} {!! add_help('Must have at least 1 research.') !!}
    <div id="researchList">
        <div class="d-flex mb-2">
            {!! Form::select('research_ids[]', $research, null, ['class' => 'form-control mr-2 default research-select', 'placeholder' => 'Select Research']) !!}
            <a href="#" class="remove-research btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div class="text-right"><a href="#" class="btn btn-primary" id="add-research">Add Research</a></div>
    <div class="research-row hide mb-2">
        {!! Form::select('research_ids[]', $research, null, ['class' => 'form-control mr-2 research-select', 'placeholder' => 'Select Research']) !!}
        <a href="#" class="remove-research btn btn-danger mb-2">×</a>
    </div>
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('message', 'Notes (Optional)') !!} {!! add_help('Additional message for the research. This will appear in the log.') !!}
    {!! Form::text('message', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>


<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxResearch: 10
        });
        $('.default.research-select').selectize();
        $('#add-research').on('click', function(e) {
            e.preventDefault();
            addItemRow();
        });
        $('.remove-research').on('click', function(e) {
            e.preventDefault();
            removeItemRow($(this));
        })
        function addItemRow() {
            var $rows = $("#researchList > div")
            if($rows.length === 1) {
                $rows.find('.remove-research').removeClass('disabled')
            }
            var $clone = $('.research-row').clone();
            $('#researchList').append($clone);
            $clone.removeClass('hide research-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-research').on('click', function(e) {
                e.preventDefault();
                removeItemRow($(this));
            })
            $clone.find('.research-select').selectize();
        }
        function removeItemRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#researchList > div")
            if($rows.length === 1) {
                $rows.find('.remove-research').addClass('disabled')
            }
        }
    });
</script>

@endsection