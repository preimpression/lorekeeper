@extends('admin.layout')

@section('admin-title') Glossary Term @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Glossary' => 'admin/world/glossary', ($term->id ? 'Edit' : 'Create').' Glossary Term' => $term->id ? 'admin/world/glossary/edit/'.$term->id : 'admin/world/glossary/create']) !!}

<h1>{{ $term->id ? 'Edit' : 'Create' }} Glossary Term
    @if($term->id)
        ({!! $term->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-term-button">Delete Term</a>
    @endif
</h1>

{!! Form::open(['url' => $term->id ? 'admin/world/glossary/edit/'.$term->id : 'admin/world/glossary/create']) !!}

<div class="card mb-3">
    <div class="card-header h3">Basic Information</div>
    <div class="card-body">
        <div class="form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $term->name, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Description (Optional)') !!}
            {!! Form::textarea('description', $term->description, ['class' => 'form-control wysiwyg']) !!}
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h3">
        Linked Attachment
    </div>
    <div class="card-body">
        <p>The linked item here will be displayed as the link for the glossary item</p>
        <div class="row no-gutters mb-4" id="attachments">
            <div class="row col-12 logs-table-header align-items-center ">
                <div class="col-6 py-1 h-100 font-weight-bold"><div class="logs-table-cell">Attachment Type</div></div>
                <div class="col-6 py-1 h-100 font-weight-bold"><div class="logs-table-cell">Attachment</div></div>
            </div>
            <div id="attachmentsBody" class="row col-12 px-0">
                @php
                    // $items               = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
                    $locations           = \App\Models\WorldExpansion\Location::getLocationsByType();
                    $figures             = \App\Models\WorldExpansion\Figure::getFiguresByCategory();
                    $faunas              = \App\Models\WorldExpansion\Fauna::getFaunasByCategory();
                    $floras              = \App\Models\WorldExpansion\Flora::getFlorasByCategory();
                    $concepts            = \App\Models\WorldExpansion\Concept::getConceptsByCategory();
                    $factions            = \App\Models\WorldExpansion\Faction::getFactionsByType();
                    $events              = \App\Models\WorldExpansion\Event::getEventsByCategory();
                @endphp
                <div class="attachment-row col-12 row py-1 logs-table-row">
                    <div class="col-6">
                        {!! Form::select('attachment_type[]', [
                            'Item'      => 'Item',      'Event' => 'Event',
                            'Figure'    => 'Figure',    'Fauna'     => 'Fauna',     'Flora'     => 'Flora',
                            'Faction'   => 'Faction',   'Concept'   => 'Concept',   'Location'  => 'Location'
                            ], $term->link_type, ['class' => 'form-control attachment-type', 'placeholder' => 'Select Attachment Type']) !!}
                    </div>
                    <div class="col-6 attachment-row-select">
                        {{-- @if($term->link_type == 'Item')
                            {!! Form::select('attachment_id[]', $items, $term->link_id, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!} --}}
                        @if($term->link_type == 'Figure')
                            {!! Form::select('attachment_id[]', $figures, $term->link_id, ['class' => 'form-control figure-select', 'placeholder' => 'Select Figure']) !!}
                        @elseif($term->link_type == 'Fauna')
                            {!! Form::select('attachment_id[]', $faunas, $term->link_id, ['class' => 'form-control fauna-select', 'placeholder' => 'Select Fauna']) !!}
                        @elseif($term->link_type == 'Flora')
                            {!! Form::select('attachment_id[]', $floras, $term->link_id, ['class' => 'form-control flora-select', 'placeholder' => 'Select Flora']) !!}
                        @elseif($term->link_type == 'Faction')
                            {!! Form::select('attachment_id[]', $factions, $term->link_id, ['class' => 'form-control faction-select', 'placeholder' => 'Select Faction']) !!}
                        @elseif($term->link_type == 'Concept')
                            {!! Form::select('attachment_id[]', $concepts, $term->link_id, ['class' => 'form-control concept-select', 'placeholder' => 'Select Concept']) !!}
                        @elseif($term->link_type == 'Location')
                            {!! Form::select('attachment_id[]', $locations, $term->link_id, ['class' => 'form-control location-select', 'placeholder' => 'Select Location']) !!}
                        @elseif($term->link_type == 'Event')
                            {!! Form::select('attachment_id[]', $events, $term->link_id, ['class' => 'form-control event-select', 'placeholder' => 'Select Event']) !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $term->id ? $term->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the term will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($term->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@include('widgets._attachment_select_row')
@endsection

@section('scripts')
@parent
@include('js._attachment_js')
<script>
$( document ).ready(function() {
    $('.delete-term-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/world/glossary/delete') }}/{{ $term->id }}", 'Delete Term');
    });

});

</script>
@endsection
