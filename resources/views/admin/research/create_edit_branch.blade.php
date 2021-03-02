@extends('admin.layout')

@section('admin-title') Research @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Research' => 'admin/data/research', ($research->id ? 'Edit' : 'Create').' Research' => $research->id ? 'admin/data/research/edit/'.$research->id : 'admin/data/research/create']) !!}

<h1>{{ $research->id ? 'Edit' : 'Create' }} Research
    @if($research->id)
        ({!! $research->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-research-button">Delete Research</a>
    @endif
</h1>

@if(!$trees->count())
    <div class="alert alert-danger " role="alert">
        There must be an existing Tree in order to create any research.
    </div>
@else

{!! Form::open(['url' => $research->id ? 'admin/data/research/edit/'.$research->id : 'admin/data/research/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $research->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Summary (Optional)') !!}
    {!! Form::text('summary', $research->summary, ['class' => 'form-control']) !!}
</div>

<h3>Hierarchy</h3>
<div class="row mx-0 px-0">
    <div class="form-group col-12 col-md-3 px-0 pr-md-1">
        {!! Form::label('Tree') !!} {!! add_help('This tree will determine where the research is kept under.') !!}
        {!! Form::select('tree_id', $trees, $research->tree_id, ['class' => 'form-control selectize', 'placeholder' => 'Choose a Research Tree', 'id' => 'tree']) !!}
    </div>

    <div class="form-group col-12 col-md-3 px-0 px-md-1" id="parentSpot">
        @if(!isset($research->tree_id))
            {!! Form::label('Parent (Optional)') !!} {!! add_help('This is the branch under which the research is. <br><strong>If left blank, this will be \'top level.\'</strong>""') !!}
            {!! Form::select('parent_id', [0=>'Select a Tree First'], null, ['class' => 'form-control selectize', 'disabled' => true]) !!}
        @else
            {!! Form::label('Parent (Optional)') !!} {!! add_help('This is the branch under which the research is. <br><strong>If left blank, this will be \'top level.\'</strong>""') !!}
            {!! Form::select('parent_id', [0=>'Choose a Parent'] + $prereq_branches, isset($research) ? $research->parent_id : null, ['class' => 'form-control selectize']) !!}
        @endif
    </div>
    <div class="form-group col-12 row col-md-6 px-0 pl-md-1 pr-md-0">
        <div class="col-12">{!! Form::label('Prerequisite (Optional)') !!} {!! add_help('This prerequisite is required in order to purchase this research. Generally is the same as Parent. If it is the same, hit the toggle and don\'t worry about the field.') !!}</div>
        <div class="col-6 col-md-8">{!! Form::select('prerequisite_id',[0=>'Choose a Prerequisite'] + $branches, $research->prerequisite_id, ['class' => 'form-control selectize']) !!}</div>
        <div class="col-6 col-md-4 text-right">{!! Form::checkbox('prereq_is_same', 1, isset($research->id) && ($research->parent_id == $research->prerequisite_id) ? 1 : 0 , ['class' => 'form-check-input',  'data-toggle' => 'toggle', 'data-on' => 'Parent is Prerequisite', 'data-off' => 'Different Prerequisite']) !!}</div>
    </div>
</div>

<h3>Purchase Price + Icon</h3>
<div class="row mx-0 px-0">
    <div class="form-group col-12 row col-md-6 px-0 pl-md-2 pr-md-0">
        <div class="col-12">{!! Form::label('Fontawesome Icon (Optional)') !!} - <a href="https://fontawesome.com/icons?d=gallery&s=solid&m=free" data-toggle="tooltip" title="If some free icons aren't loading, you may need to manually install an updated version of FontAwesome on your site.">Examples</a></div>
        <div class="col-1 my-auto pr-2 text-right"><i id="fontawesome" class="{{ isset($research->icon_code) ? $research->icon_code : 'fas fa-sitemap' }}"/></i></div>
        <div class="col">{!! Form::text('icon', isset($research->icon_code) ? $research->icon_code : 'fas fa-sitemap', ['class' => 'form-control', 'id' => 'iconic']) !!}</div>
    </div>
    <div class="form-group col-12 row col-md-6 px-0 pl-md-2 pr-md-0">
        <div class="col-12">{!! Form::label('Price') !!}</div>
        <div class="col">{!! Form::text('price', $research->price, ['class' => 'form-control']) !!}</div>
        <div class="col-2 my-auto">{{ isset($research->tree) ? $research->tree->currency->name : '' }} {!! add_help('The currency type is set by the Tree. This will update upon save and reload if you change the tree.')!!} </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $research->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="float-left">
    <h3>Rewards</h3>
    <p>These rewards will be claimable after purchase. This allows for rewards to be claimed by users who purchased this research in the past.</p>
</div>
@include('widgets._loot_select', ['loots' => $research->rewards, 'showLootTables' => true, 'showRaffles' => true])



<div class="form-group float-left">
    {!! Form::checkbox('is_active', 1, $research->id ? $research->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the research will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($research->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary px-5']) !!}
</div>

{!! Form::close() !!}

@include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles, 'showLootTables' => true, 'showRaffles' => true])


@endif

@endsection

@section('scripts')
@parent
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
<script>
$( document ).ready(function() {
    $('.delete-research-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/research/delete') }}/{{ $research->id }}", 'Delete Research Branch');
    });

    $("#iconic").change(function(){
             var text = $('#iconic').val();
            $("#fontawesome").removeClass();
            $("#fontawesome").addClass(text);
          });

    $( "#tree" ).change(function() {
        var tree = $('#tree').val();
        var id = '<?php echo($research->id); ?>';
        if(!id) id = null;
        debugger;
        $.ajax({
        type: "GET", url: "{{ url('admin/data/research/parent') }}?tree="+tree+"&id="+id, dataType: "text"
        }).done(function (res) { $("#parentSpot").html(res); }).fail(function (jqXHR, textStatus, errorThrown) { alert("AJAX call failed: " + textStatus + ", " + errorThrown); });
    });


});

</script>
@endsection
