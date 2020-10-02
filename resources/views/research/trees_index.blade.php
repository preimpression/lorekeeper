@extends('research.layout')

@section('research-title') Tree Index @endsection

@section('research-content')
{!! breadcrumbs(['Research Trees' => 'trees']) !!}

<h1>
    Research Trees
</h1>

<p>
    These are the available research trees
</p>

<div class="row trees-row">
    @foreach($trees as $tree)
        <div class="col-md-3 col-6 mb-3 text-center row  mx-0 px-0 align-items-end">
            <div class="tree-image col-12">
                <a href="{{ $tree->url }}"><img src="{{ $tree->treeImageUrl }}" class="mw-100"/></a>
            </div>
            <div class=" mt-1 col-12">
                <a href="{{ $tree->url }}" class="h5 mb-0">{{ $tree->name }}</a>
                <p>{{ $tree->summary }}</p>
            </div>
        </div>
    @endforeach
</div>

@endsection
