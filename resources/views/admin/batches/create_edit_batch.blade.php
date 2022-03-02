@extends('admin.layout')

@section('admin-title') Batches @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Batches' => 'admin/data/batches', ($batch->id ? 'Edit' : 'Create').' Batch' => $batch->id ? 'admin/data/batches/edit/'.$batch->id : 'admin/data/batches/create']) !!}

<h1>{{ $batch->id ? 'Edit' : 'Create' }} Batch
    @if($batch->id)
        <a href="#" class="btn btn-outline-danger float-right delete-batch-button">Delete Batch</a>
        <a href="#" class="btn btn-outline-success mr-2 float-right trigger-batch-button">Trigger Batch</a>
    @endif
</h1>

{!! Form::open(['url' => $batch->id ? 'admin/data/batches/edit/'.$batch->id : 'admin/data/batches/create']) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="form-group col-md d-flex align-items-center justify-content-between">
        {!! Form::label('name', 'Name', ['class' => 'mb-0 mr-2']) !!}
        {!! Form::text('name', $batch->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group col-md d-flex align-items-center justify-content-between">
        {!! Form::label('trigger_at', 'Trigger At (Optional)', ['class' => 'mb-0 mr-2']) !!}
        {!! Form::text('trigger_at', $batch->trigger_at, ['class' => 'form-control', 'id' => 'datepicker']) !!}
    </div>
</div>

<h3>Targets</h3>
<p>
    Targets are elements of {!! Config::get('lorekeeper.settings.site_name') !!} that are <b>currently disabled</b> or set to invisible explicitly in some way. <br>
    This does not include intricacies of whether an "unreleased" item is owned by a user or not or whether a prompt has dates in its visibility.
</p>
<p>
    When this batch is triggered, all of these targets will have their visibilities or release status set to true.
</p>

<div class="text-right mb-3">
    <a href="#" class="btn btn-info" id="addTarget">Add Target</a>
</div>
<div class="table table-sm" id="targetTable">
    <div class="row col-12 no-gutters pb-1 px-0 ubt-bottom font-weight-bold">
        <div class="col-6">Target Type</div>
        <div class="col-6">Target</div>
    </div>
    <div id="targetTableBody">
        @if($batch->id)
            @foreach($batch->targets as $target)
                <div class="target-row row ubt-top py-1 no-gutters">
                    <div class="col-6">{!! Form::select('target_type[]', [
                        'Item' => 'Item', 'Shop' => 'Shop', 'Prompt' => 'Prompt', 'News' => 'News', 'Sales' => 'Sale', 'SitePage' => 'Site Page', 'Raffle' => 'Raffle', 'Character' => 'Character',
                        ] + (isset($world_expanded)) ? [
                            'Location' => 'Location', 'Event' => 'Event', 'Concept' => 'Concept', 'Fauna' => 'Fauna', 'Flora' => 'Flora', 'Figure' => 'Figure', 'Faction' => 'Faction',
                        ] : [])
                        , $target->target_type, ['class' => 'form-control reward-type', 'placeholder' => 'Select Target Type']) !!}</div>
                    <div class="target-row-select col px-1">
                        @if($target->target_type == 'Item')
                            {!! Form::select('target_id[]', $items, $target->target_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($target->target_type == 'Shop')
                            {!! Form::select('target_id[]', $shops, $target->target_id, ['class' => 'form-control shop-select selectize', 'placeholder' => 'Select Shop']) !!}
                        @elseif($target->target_type == 'Prompt')
                            {!! Form::select('target_id[]', $prompts, $target->target_id, ['class' => 'form-control prompt-select selectize', 'placeholder' => 'Select Prompt']) !!}
                        @elseif($target->target_type == 'News')
                            {!! Form::select('target_id[]', $newses, $target->target_id, ['class' => 'form-control news-select selectize', 'placeholder' => 'Select News']) !!}
                        @elseif($target->target_type == 'Sales')
                            {!! Form::select('target_id[]', $sales, $target->target_id, ['class' => 'form-control sale-select selectize', 'placeholder' => 'Select Sale']) !!}
                        @elseif($target->target_type == 'SitePage')
                            {!! Form::select('target_id[]', $sitepages, $target->target_id, ['class' => 'form-control sitepage-select selectize', 'placeholder' => 'Select Site Page']) !!}
                        @elseif($target->target_type == 'Raffle')
                            {!! Form::select('target_id[]', $raffles, $target->target_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                        @elseif($target->target_type == 'Character')
                            {!! Form::select('target_id[]', $characters, $target->target_id, ['class' => 'form-control character-select selectize', 'placeholder' => 'Select Character']) !!}
                        @elseif($target->target_type == 'Location')
                            {!! Form::select('target_id[]', $locations, $target->target_id, ['class' => 'form-control location-select selectize', 'placeholder' => 'Select Location']) !!}
                        @elseif($target->target_type == 'Event')
                            {!! Form::select('target_id[]', $events, $target->target_id, ['class' => 'form-control event-select selectize', 'placeholder' => 'Select Event']) !!}
                        @elseif($target->target_type == 'Concept')
                            {!! Form::select('target_id[]', $concepts, $target->target_id, ['class' => 'form-control concept-select selectize', 'placeholder' => 'Select Concept']) !!}
                        @elseif($target->target_type == 'Fauna')
                            {!! Form::select('target_id[]', $faunas, $target->target_id, ['class' => 'form-control fauna-select selectize', 'placeholder' => 'Select Fauna']) !!}
                        @elseif($target->target_type == 'Flora')
                            {!! Form::select('target_id[]', $floras, $target->target_id, ['class' => 'form-control flora-select selectize', 'placeholder' => 'Select Flora']) !!}
                        @elseif($target->target_type == 'Figure')
                            {!! Form::select('target_id[]', $figures, $target->target_id, ['class' => 'form-control figure-select selectize', 'placeholder' => 'Select Figure']) !!}
                        @elseif($target->target_type == 'Faction')
                            {!! Form::select('target_id[]', $factions, $target->target_id, ['class' => 'form-control faction-select selectize', 'placeholder' => 'Select Faction']) !!}
                        @endif
                    </div>
                    <div class="text-right col-auto"><a href="#" class="btn btn-danger remove-target-button">Remove</a></div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="text-right">
    {!! Form::submit($batch->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<div id="targetRowData" class="hide">
        <div id="targetRow">
            <div class="target-row row ubt-top  no-gutters py-1">
                <div class="col-6">{!! Form::select('target_type[]', [
                    'Item' => 'Item', 'Shop' => 'Shop', 'Prompt' => 'Prompt', 'News' => 'News', 'Sales' => 'Sale', 'SitePage' => 'Site Page', 'Raffle' => 'Raffle', 'Character' => 'Character',
                    ] + (isset($world_expanded)) ? [
                        'Location' => 'Location', 'Event' => 'Event', 'Concept' => 'Concept', 'Fauna' => 'Fauna', 'Flora' => 'Flora', 'Figure' => 'Figure', 'Faction' => 'Faction',
                    ] : [])
                    , null, ['class' => 'form-control reward-type', 'placeholder' => 'Select Target Type']) !!}</div>
                <div class="target-row-select col px-1"></div>
                <div class="text-right col-auto"><a href="#" class="btn btn-danger remove-target-button">Remove</a></div>
            </div>
        </div>
    {!! Form::select('target_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('target_id[]', $shops, null, ['class' => 'form-control shop-select', 'placeholder' => 'Select Shop']) !!}
    {!! Form::select('target_id[]', $prompts, null, ['class' => 'form-control prompt-select', 'placeholder' => 'Select Prompt']) !!}
    {!! Form::select('target_id[]', $newses, null, ['class' => 'form-control news-select', 'placeholder' => 'Select News']) !!}
    {!! Form::select('target_id[]', $sales, null, ['class' => 'form-control sale-select', 'placeholder' => 'Select Sale']) !!}
    {!! Form::select('target_id[]', $sitepages, null, ['class' => 'form-control sitepage-select', 'placeholder' => 'Select Site Page']) !!}
    {!! Form::select('target_id[]', $raffles, null, ['class' => 'form-control raffle-select', 'placeholder' => 'Select Raffle']) !!}
    {!! Form::select('target_id[]', $characters, null, ['class' => 'form-control character-select', 'placeholder' => 'Select Character']) !!}
    @if(isset($world_expanded)))
        {!! Form::select('target_id[]', $locations, null, ['class' => 'form-control location-select', 'placeholder' => 'Select Location']) !!}
        {!! Form::select('target_id[]', $events, null, ['class' => 'form-control event-select', 'placeholder' => 'Select Event']) !!}
        {!! Form::select('target_id[]', $concepts, null, ['class' => 'form-control concept-select', 'placeholder' => 'Select Concept']) !!}
        {!! Form::select('target_id[]', $faunas, null, ['class' => 'form-control fauna-select', 'placeholder' => 'Select Fauna']) !!}
        {!! Form::select('target_id[]', $floras, null, ['class' => 'form-control flora-select', 'placeholder' => 'Select Flora']) !!}
        {!! Form::select('target_id[]', $figures, null, ['class' => 'form-control figure-select', 'placeholder' => 'Select Figure']) !!}
        {!! Form::select('target_id[]', $factions, null, ['class' => 'form-control faction-select', 'placeholder' => 'Select Faction']) !!}
    @endif
</div>


@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {

    $('.delete-batch-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/batches/delete') }}/{{ $batch->id }}", 'Delete Batch');
    });

    $('.trigger-batch-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/batches/trigger') }}/{{ $batch->id }}", 'Manually Trigger Batch');
    });

    $( "#datepicker" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH:mm:ss',
    });

    var $targetTable        = $('#targetTableBody');
    var $targetRow          = $('#targetRow').find('.target-row');
    var $itemSelect         = $('#targetRowData').find('.item-select');
    var $shopSelect         = $('#targetRowData').find('.shop-select');
    var $promptSelect       = $('#targetRowData').find('.prompt-select');
    var $newsSelect         = $('#targetRowData').find('.news-select');
    var $saleSelect         = $('#targetRowData').find('.sale-select');
    var $sitepageSelect     = $('#targetRowData').find('.sitepage-select');
    var $raffleSelect       = $('#targetRowData').find('.raffle-select');
    var $characterSelect    = $('#targetRowData').find('.character-select');
    var $locationSelect     = $('#targetRowData').find('.location-select');
    var $eventSelect        = $('#targetRowData').find('.event-select');
    var $conceptSelect      = $('#targetRowData').find('.concept-select');
    var $faunaSelect        = $('#targetRowData').find('.fauna-select');
    var $floraSelect        = $('#targetRowData').find('.flora-select');
    var $figureSelect       = $('#targetRowData').find('.figure-select');
    var $factionSelect      = $('#targetRowData').find('.faction-select');

    $('#targetTableBody .selectize').selectize();
    attachRemoveListener($('#targetTableBody .remove-target-button'));

    $('#addTarget').on('click', function(e) {
        e.preventDefault();
        var $clone = $targetRow.clone();
        $targetTable.append($clone);
        attachRewardTypeListener($clone.find('.reward-type'));
        attachRemoveListener($clone.find('.remove-target-button'));
    });

    $('.reward-type').on('change', function(e) {
        var val     = $(this).val();
        var $cell   = $(this).parent().find('.target-row-select');

        var $clone = null;
        if(val == 'Item') $clone = $itemSelect.clone();
        else if (val == 'Shop') $clone = $shopSelect.clone();
        else if (val == 'Prompt') $clone = $promptSelect.clone();
        else if (val == 'News') $clone = $newsSelect.clone();
        else if (val == 'Sales') $clone = $saleSelect.clone();
        else if (val == 'SitePage') $clone = $sitepageSelect.clone();
        else if (val == 'Raffle') $clone = $raffleSelect.clone();
        else if (val == 'Character') $clone = $characterSelect.clone();
        else if (val == 'Location') $clone = $locationSelect.clone();
        else if (val == 'Event') $clone = $eventSelect.clone();
        else if (val == 'Concept') $clone = $conceptSelect.clone();
        else if (val == 'Fauna') $clone = $faunaSelect.clone();
        else if (val == 'Flora') $clone = $floraSelect.clone();
        else if (val == 'Figure') $clone = $figureSelect.clone();
        else if (val == 'Faction') $clone = $factionSelect.clone();

        $cell.html('');
        $cell.append($clone);
    });

    function attachRewardTypeListener(node) {
        node.on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.target-row-select');

            var $clone = null;
            if(val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Shop') $clone = $shopSelect.clone();
            else if (val == 'Prompt') $clone = $promptSelect.clone();
            else if (val == 'News') $clone = $newsSelect.clone();
            else if (val == 'Sales') $clone = $saleSelect.clone();
            else if (val == 'SitePage') $clone = $sitepageSelect.clone();
            else if (val == 'Raffle') $clone = $raffleSelect.clone();
            else if (val == 'Character') $clone = $characterSelect.clone();
            else if (val == 'Location') $clone = $locationSelect.clone();
            else if (val == 'Event') $clone = $eventSelect.clone();
            else if (val == 'Concept') $clone = $conceptSelect.clone();
            else if (val == 'Fauna') $clone = $faunaSelect.clone();
            else if (val == 'Flora') $clone = $floraSelect.clone();
            else if (val == 'Figure') $clone = $figureSelect.clone();
            else if (val == 'Faction') $clone = $factionSelect.clone();

            $cell.html('');
            $cell.append($clone);
            $clone.selectize();
        });
    }

    function attachRemoveListener(node) {
        node.on('click', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }


});


















</script>
@endsection
