@extends('worldexpansion.layout')

@section('title') Figure :: {{ $figure->name }} @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Figure' => 'world/figures', $figure->name => 'world/figures/'.$figure->id]) !!}
<h1 ><img src="{{$figure->thumbUrl}}" style="max-height:25px;vertical-align:inherit;"/>{!! $figure->displayName !!}</h1>
<h5>{!! $figure->category ? ucfirst($figure->category->displayName) : 'Miscellaneous' !!}
    {!! $figure->faction ? '・ Part of '.ucfirst($figure->faction->displayName) : '' !!}{!! $figure->factionRank ? ' ('.$figure->factionRank->name.')' : null !!}

@if($figure->birth_date || $figure->death_date)
    <span class="ml-4 text-muted">{!! $figure->birth_date ? 'Born: '.format_date($figure->birth_date, false) : 'Born: Unknown' !!} {!! $figure->death_date ? '- Died: '.format_date($figure->death_date, false) : '- Died: Unknown' !!}</span>
@endif
</h5>

@if($figure->image_extension)
    <div class="text-center"><img src="{{$figure->imageUrl}}" class="mw-100 mb-3"/></div>
@endif

@isset($figure->summary)
<div class="world-entry-text px-3 text-center">{!! $figure->summary !!}</div>
@endisset

@isset($figure->parsed_description)
<div class="world-entry-text px-3">
    {!! $figure->parsed_description !!}
</div>
@endisset

<div class="row mx-0 px-0 mt-3">
    @if(count($figure->items))
    <div class="text-center col-md mb-3"><div class="card h-100 py-3">
        <h5 class="mb-0">Associated Item{{ count($figure->items) == 1 ? '' : 's' }}</h5>
        <!-- <hr>
        <p class="mb-0">
            @foreach($figure->items as $key => $item)
                @if($item->has_image)
                    <a href="{{ $item->url }}" data-toggle="tooltip" title="{{ $item->name }}"/><img src="{{$item->imageUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $item->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($figure->items->groupBy('item_category_id') as $key => $items)
        <p class="mb-0">
            <strong>
                {{ $item_categories->find($key) ? $item_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($items as $key => $item) <strong>{!! $item->displayName !!}</strong>@if($key != count($items)-1 && count($items)>2),@endif @if($key == count($items)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif
    @if(count($figure->events))
    <div class="text-center col-md mb-3"><div class="card h-100 py-3">
        <h5 class="mb-0">Associated Event{{ count($figure->events) == 1 ? '' : 's' }}</h5>
        <!-- <hr>
        <p class="mb-0">
            @foreach($figure->events as $key => $event)
                @if($event->thumb_extension)
                    <a href="{{ $event->url }}" data-toggle="tooltip" title="{{ $event->name }}"/><img src="{{$event->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $event->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($figure->events->groupBy('category_id') as $key => $events)
        <p class="mb-0">
            <strong>
                {{ $event_categories->find($key) ? $event_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($events as $key => $event) <strong>{!! $event->displayName !!}</strong>@if($key != count($events)-1 && count($events)>2),@endif @if($key == count($events)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if(count($figure->factions))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Associated Faction{{ count($figure->factions) == 1 ? '' : 's'}}</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($figure->factions as $key => $faction)
                @if($faction->thumb_extension)
                    <a href="{{ $faction->url }}" data-toggle="tooltip" title="{{ $faction->name }}"/><img src="{{$faction->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $faction->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($figure->factions->groupBy('type_id') as $key => $factions)
        <p class="mb-0">
            <strong>
                {{ $faction_categories->find($key) ? $faction_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($factions as $key => $faction) <strong>{!! $faction->displayName !!}</strong>@if($key != count($factions)-1 && count($factions)>2),@endif @if($key == count($factions)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif
</div>




@endsection
