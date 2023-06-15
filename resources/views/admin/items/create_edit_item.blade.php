@extends('admin.layout')

@section('admin-title') Items @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', ($item->id ? 'Edit '.$item->name : 'Create Item') => $item->id ? 'admin/data/items/edit/'.$item->id : 'admin/data/items/create']) !!}

<h1>{!! $item->id ? 'Edit '.$item->displayName : 'Create Item' !!}
    @if($item->id)
        <a href="#" class="btn btn-outline-danger float-right delete-item-button">Delete {{ $item->name }}</a>
    @endif
</h1>

{!! Form::open(['url' => $item->id ? 'admin/data/items/edit/'.$item->id : 'admin/data/items/create', 'files' => true]) !!}

<div class="card mb-2"><div class="card-body">
    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $item->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if($item->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Item Category (Optional)') !!}
                {!! Form::select('item_category_id', $categories, $item->item_category_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        @if(Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('Item Rarity (Optional)') !!} {!! add_help('This should be a number.') !!}
                    {!! Form::number('rarity', $item && $item->rarity ? $item->rarity : '', ['class' => 'form-control']) !!}
                </div>
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $item->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

</div></div>

<div class="card mb-2"><div class="card-body">
    <h3>
        <a href="#" class="btn btn-primary mr-2 float-right btn-sm" id="add-credit-button">Add Credit</a>
        Credits
    </h3>
    <div class="card-body">
        <div class="row no-gutters form-group" id="creditList" style="clear:both;">
            @if($item->id && isset($item->credits)) @foreach($item->credits as $id => $credit)
                <div class="col-md-3 align-items-center mb-2">
                    <a href="#" class="remove-credit-button btn btn-danger btn-sm mr-2"><i class="fas fa-trash"></i></a>
                    <a href="{{ isset($credit['url']) ? $credit['url'] :  (isset($credit['id']) ? url('/user').'/'.$userOptions[$credit['id']] : 'unknown') }}" target="_blank">
                        {{ isset($credit['name']) ? $credit['name'] :  (isset($credit['id']) ? $userOptions[$credit['id']] : (isset($credit['url']) ? $credit['url'] : 'artist')) }}
                    </a>
                    {{ isset($credit['role']) ? '('.$credit['role'].')' : '' }}
                    @foreach($credit as $type => $info)
                        {!! Form::hidden('credit-'.$type.'['.$id.']', $info ) !!}
                    @endforeach
                </div>
            @endforeach @endif
        </div>
    </div>
</div></div>

<div class="card mb-2"><div class="card-body">
    @if(Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <div class="form-group">
            {!! Form::label('Uses (Optional)') !!} {!! add_help('A short description of the item\'s use(s). Supports raw HTML if need be, but keep it brief.') !!}
            {!! Form::text('uses', $item && $item->uses ? $item->uses : '', ['class' => 'form-control']) !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md form-group">
            {!! Form::checkbox('allow_transfer', 1, $item->id ? $item->allow_transfer : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('allow_transfer', 'Allow User → User Transfer', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to transfer this item to other users. Non-account-bound items can be account-bound when granted to users directly.') !!}
        </div>
        @if(Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
            <div class="col-md form-group">
                {!! Form::checkbox('is_released', 1, $item->id ? $item->is_released : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_released', 'Is Released', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, users will not be able to view information for the item/it will be hidden from view. This is overridden by the item being owned at any point by anyone on the site.') !!}
            </div>
        @endif
    </div>

    @if(Config::get('lorekeeper.extensions.item_entry_expansion.extra_fields'))
        <h3>Availability Information</h3>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('release', 'Source (Optional)') !!} {!! add_help('The original and/or general source of the item. Should be brief.') !!}
                    {!! Form::text('release', $item && $item->source ? $item->source : '', ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('prompts[]', 'Drop Location(s) (Optional)') !!} {!! add_help('You can select up to 10 prompts at once.') !!}
                    {!! Form::select('prompts[]', $prompts, $item && isset($item->data['prompts']) ? $item->data['prompts'] : '', ['id' => 'promptsList', 'class' => 'form-control', 'multiple']) !!}
                </div>
            </div>
        </div>
    @endif

    @if(Config::get('lorekeeper.extensions.item_entry_expansion.resale_function'))
        <h3>Resale Information</h3>
        <p>The currency and amount users will be able to sell this item from their inventory for. If quantity is not set, the item will be unable to be sold.</p>
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('currency_id', 'Currency') !!}
                    {!! Form::select('currency_id', $userCurrencies, isset($item->data['resell']) && App\Models\Currency\Currency::where('id', $item->resell->flip()->pop())->first() ? $item->resell->flip()->pop() : null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('currency_quantity', 'Quantity') !!}
                    {!! Form::text('currency_quantity', isset($item->data['resell']) ? $item->resell->pop() : null, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    @endif
</div></div>

<div class="text-right mb-2">
    {!! Form::submit($item->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>


{!! Form::close() !!}

@if($item->id)
    <div class="card mb-2"><div class="card-body">
        <h3>Item Tags</h3>
        <p>Item tags indicate extra functionality for the item. Click on the edit button to edit the specific item tag's data.</p>
        @if(count($item->tags))
            <table class="table">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Active?</th>
                        <th></th>
                    </tr>
                </thead>
                @foreach($item->tags as $tag)
                    <tr>
                        <td>{!! $tag->displayTag !!}</td>
                        <td class="{{ $tag->is_active ? 'text-success' : 'text-danger' }}">{{ $tag->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right"><a href="{{ url('admin/data/items/tag/'.$item->id.'/'.$tag->tag) }}" class="btn btn-outline-primary">Edit</a></td>
                    </tr>
                @endforeach
            </table>
        @else
            <p>No item tags attached to this item.</p>
        @endif
        <div class="text-right">
            <a href="{{ url('admin/data/items/tag/'.$item->id) }}" class="btn btn-outline-primary">Add a Tag</a>
        </div>
    </div></div>

    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._item_entry', ['imageUrl' => $item->imageUrl, 'name' => $item->displayName, 'description' => $item->parsed_description, 'searchUrl' => $item->searchUrl])
        </div>
    </div>
@endif


<div class="row hide credit-row col-12 mb-1">
    <div class="col-md-2">
        {!! Form::select('credit-type[]', ['onsite' => 'Onsite Credit', 'offsite' => 'Offsite Credit'], null, ['class'=> 'form-control mr-2 credit-type']) !!}
    </div>
    <div class="col-md-6 credit-info d-flex">
        {!! Form::select('credit-id[]', $userOptions, null, ['class'=> 'form-control credit-select', 'placeholder' => 'Select a User']) !!}
        {!! Form::hidden('credit-name[]', null) !!}
        {!! Form::hidden('credit-url[]', null) !!}
    </div>
    <div class="col-md">
        {!! Form::text('credit-role[]', null, ['class' => 'ml-md-2 form-control', 'placeholder' => 'Credit Role (Optional)']) !!}
    </div>
    <a href="#" class="col-md-auto remove-credit-button btn btn-danger" style="height:fit-content;"><i class="fas fa-trash"></i></a>
</div>

<div id="credit-info-onsite" class="hide credit-info ">
    {!! Form::select('credit-id[]', $userOptions, null, ['class'=> 'form-control mr-2 credit-select', 'placeholder' => 'Select a User']) !!}
    {!! Form::hidden('credit-name[]', null) !!}
    {!! Form::hidden('credit-url[]', null) !!}
</div>

<div id="credit-info-offsite" class="hide credit-info ">
    {!! Form::text('credit-name[]', null, ['class' => 'form-control col', 'placeholder' => 'Name']) !!}
    {!! Form::text('credit-url[]', null, ['class' => 'form-control col ml-1', 'placeholder' => 'Url']) !!}
    {!! Form::hidden('credit-id[]', null) !!}
</div>


@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    var $credits = $('#creditsTable');
    var $value = 1000;
    $('.selectize').selectize();

    $('#promptsList').selectize({
        maxItems: 10
    });

    $('.delete-item-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/items/delete') }}/{{ $item->id }}", 'Delete Item');
    });


    $('#add-credit-button').on('click', function(e) {
        e.preventDefault();
        addCreditRow();
    });
    $('.remove-credit-button').on('click', function(e) {
        e.preventDefault();
        removeCreditRow($(this));
    })

    function addCreditRow() {
        var $clone = $('.credit-row').clone();
        $('#creditList').append($clone);
        $clone.removeClass('hide credit-row');
        $clone.attr('name', $value++);
        $clone.find('.remove-credit-button').on('click', function(e) {
            e.preventDefault();
            removeCreditRow($(this));
        })
        $clone.find('.credit-type').on('change', function(e){
            $val = $clone.find('.credit-type').val();
            if($val == "onsite") {
                addOnsite($clone.find('.credit-info'));
                $clone.find('.credit-select').selectize();
            }
            else if($val == "offsite") addOffsite($clone.find('.credit-info'));
        });
        $clone.find('.credit-select').selectize();
    }
    function removeCreditRow($trigger) {
        $trigger.parent().remove();
    }


    function addOnsite($info)  {
        $clone = $('#credit-info-onsite').children().clone();
        $clone.removeClass('hide').addClass('col-md-12');
        $info.html($clone);
    }
    function addOffsite($info)  {
        $clone = $('#credit-info-offsite').children().clone();
        $clone.removeClass('hide').addClass('col-md');
        $info.html($clone);
    }




});

</script>
@endsection
