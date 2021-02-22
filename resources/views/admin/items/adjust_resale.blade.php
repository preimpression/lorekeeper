@extends('admin.layout')

@section('admin-title') Items @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Items' => 'admin/data/items', 'Adjust Resale' => 'admin/data/items/resale']) !!}

<h1>Adjust Resale Values of Items</h1>

<p>
    This is a tool to adjust the resale values of items en masse.
</p>


<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('item_category_id', $categories, Request::get('item_category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::label('sort', 'Sort: ', ['class' => 'mr-2']) !!}
            {!! Form::select('sort', ['name_asc' => 'Alphabetically Ascending', 'name_desc' => 'Alphabetically Descending', 'value_asc' => 'Value-less First', 'newest' => 'Newest First', 'oldest' => 'Oldest First',], Request::get('sort'), ['class' => 'form-control']) !!}
        </div>
        <!-- If you are running MariaDB 10.1.x, remove "Value-less First" as an option. -->
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($items))
    <p>No items found.</p>
@else

    {!! $items->render() !!}

    {!! Form::open(['url' => 'admin/data/items/resale']) !!}

        <div class="row ml-md-2 mb-4">
          <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-12 col-md-5 font-weight-bold">Name</div>
            <div class="col-6 col-md font-weight-bold">Value</div>
            <div class="col-6 col-md font-weight-bold">Currency</div>
          </div>
          @foreach($items as $item)
          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
            <div class="col-12 col-md-5 my-auto"> {!! $item->displayName !!} {{ $item->category ? ' - [' . $item->category->name . ']' : '' }} </div>
            {{ Form::hidden('id[]', $item->id) }}
            <div class="col-6 col-md">{!! Form::text('currency_quantity[]', isset($item->data['resell']) ? $item->resell->pop() : null, ['class' => 'form-control', 'placeholder' => 'Quantity']) !!}</div>
            <div class="col-6 col-md">{!! Form::select('currency_id[]', $userCurrencies, (isset($item->data['resell']) && App\Models\Currency\Currency::where('id', $item->resell->flip()->pop())->first() ? $item->resell->flip()->pop() : 0), ['class' => 'form-control', 'placeholder' => 'Pick a Currency']) !!}</div>
          </div>
          @endforeach
        </div>

    <div class="text-center">
        {!! Form::submit('Adjust', ['class' => 'btn btn-block btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    {!! $items->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
