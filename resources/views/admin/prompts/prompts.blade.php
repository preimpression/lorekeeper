@extends('admin.layout')

@section('admin-title') Prompts @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Prompts' => 'admin/data/prompts']) !!}

<h1>Prompts</h1>

<p>This is a list of prompts users can submit to.</p>
<p>If you want to see this list using the vanilla Lorekeeper style, <a href="/admin/data/prompts/old" class="font-weight-bold">go here.</a></p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/prompts/create') }}"><i class="fas fa-plus"></i> Create New Prompt</a></div>

<div>
  {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-group mr-3 mb-3"> {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!} </div>
    <div class="form-group mr-3 mb-3"> {!! Form::select('prompt_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!} </div>
    <div class="form-group mb-3">{!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}</div>
  {!! Form::close() !!}
</div>

@if(!count($prompts))
<p>No prompts found.</p>
@else

<div class="accordion" id="accordionExample">
  @foreach($promptCategories as $promptCategory=>$promptItems)

    @if( (count($prompts->where('prompt_category_id','=',$promptCategory))) > 0 )

      <h5 class="card-header inventory-header border mt-2">
        <a data-toggle="collapse" href="#collapse{{$promptCategory}}" role="button" aria-expanded="false" aria-controls="collapse{{$promptCategory}}">
        {{ $promptCategory > 0 ? $promptCategories[$promptCategory]->name : 'Miscellaneous' }} - {{ count($prompts->where('prompt_category_id','=',$promptCategory)) }}
        </a>
      </h5>

      <div class="card card-body p-0 border-bottom">
        <div id="collapse{{$promptCategory}}"  class="row ml-md-2 collapse collapsed px-2"  data-parent="#accordionExample" aria-labelledby="#collapse{{$promptCategory}}">
          <div class="d-flex row flex-wrap col-12 py-2 pb-2 px-0 ubt-bottom mw-100">
            <div class="col-4 col-md-1 font-weight-bold">Active</div>
            <div class="col-4 col-md-3 font-weight-bold">Name</div>
            <div class="col-4 col-md-3 font-weight-bold">Category</div>
            <div class="col-4 col-md-2 font-weight-bold">Starts</div>
            <div class="col-4 col-md-2 font-weight-bold">Ends</div>
          </div>

        @foreach($pickPrompts as $pickPrompt)

          @foreach($pickPrompt->where('prompt_category_id','=',$promptCategory)  as $finalPrompt)

          <div class="d-flex row flex-wrap col-12 mt-1 pt-2 pb-1 px-0 ubt-top">
            <div class="col-2 col-md-1"> {!! $finalPrompt->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!} </div>
            <div class="col-5 col-md-3 text-truncate" title="{{ $finalPrompt->summary }}"> {{ $finalPrompt->name }}</div>
            <div class="col-5 col-md-3"> {{ $finalPrompt->category ? $finalPrompt->category->name : '-' }} </div>
            <div class="col-4 col-md-2">{!! $finalPrompt->start_at ? pretty_date($finalPrompt->start_at) : '-' !!}</div>
            <div class="col-4 col-md-2">{!! $finalPrompt->end_at ? pretty_date($finalPrompt->end_at) : '-' !!}</div>
            <div class="col-3 col-md-1 text-right"> <a href="{{ url('admin/data/prompts/edit/'.$finalPrompt->id) }}"  class="btn btn-primary py-0 px-2">Edit</a> </div>
          </div>

          @endforeach

        @endforeach

      </div><br />
    </div>
    @endif

  @endforEach
</div>
@endif

@endsection

@section('scripts')
@parent
@endsection
