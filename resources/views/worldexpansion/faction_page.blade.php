@extends('worldexpansion.layout')

@section('title') {{ $faction->name }} @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Factions' => 'world/factions', $faction->style => 'world/factions/'.$faction->id]) !!}
<h1><img src="{{$faction->thumbUrl}}" style="max-height:25px;vertical-align:inherit;"/>{!! $faction->style !!}</h1>
<h5 class="mb-0">{!! ucfirst($faction->type->displayName) !!} {!! $faction->parent ? 'inside '.$faction->parent->displayName : '' !!}</h5>

@if(($user_enabled && $faction->is_user_faction) || ($ch_enabled && $faction->is_character_faction))
    <p class="mb-0"><strong>
    Can be joined by
    {!! $faction->is_character_faction && $faction->is_user_faction ? 'both' : '' !!}
    {!! $user_enabled && $faction->is_user_faction ? 'users' : '' !!}{!! $faction->is_character_faction && $faction->is_user_faction ? ' and' : '' !!}{!! !$faction->is_character_faction && $faction->is_user_faction ? '.' : '' !!}
    {!! $ch_enabled && $faction->is_character_faction ? 'characters.' : '' !!}
    </strong></p>
@endif


@if($faction->image_extension)
    <div class="text-center"><img src="{{$faction->imageUrl}}" class="mw-100"/></div>
@endif

@isset($faction->summary)
<div class="world-entry-text px-3 text-center">{!! $faction->summary !!}</div>
@endisset

@isset($faction->parsed_description)
<div class="world-entry-text px-3">
    {!! $faction->parsed_description !!}
</div>
@endisset

<div class="row mx-0 px-0 mt-3">
    @if(count($faction->children))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Contains the following</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($faction->children as $key => $child)
                @if($child->thumb_extension)
                    <a href="{{ $child->url }}" data-toggle="tooltip" title="{{ $child->name }}"/><img src="{{$child->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $child->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($faction->children->groupBy('type_id') as $group => $children)
        <p class="mb-0">
            <strong>
                @if(count($children) == 1) {{ $loctypes->find($group)->name }}@else{{ $loctypes->find($group)->names }}@endif:
            </strong>
            @foreach($children as $key => $child) {!! $child->fullDisplayName !!}@if($key != count($children)-1), @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if(count($faction->members))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Member Figure{{ count($faction->members) == 1 ? '' : 's'}}</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($faction->members as $key => $member)
                @if($member->thumb_extension)
                    <a href="{{ $member->url }}" data-toggle="tooltip" title="{{ $member->name }}"/><img src="{{$member->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $member->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($faction->members->groupBy('category_id') as $key => $members)
        <p class="mb-0">
            <strong>
                {{ $figure_categories->find($key) ? $figure_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($members as $key => $member) <strong>{!! $member->displayName !!}</strong>@if($key != count($members)-1 && count($members)>2),@endif @if($key == count($members)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if(count($faction->figures))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Associated Figure{{ count($faction->figures) == 1 ? '' : 's'}}</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($faction->figures as $key => $figure)
                @if($figure->thumb_extension)
                    <a href="{{ $figure->url }}" data-toggle="tooltip" title="{{ $figure->name }}"/><img src="{{$figure->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $figure->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($faction->figures->groupBy('category_id') as $key => $figures)
        <p class="mb-0">
            <strong>
                {{ $figure_categories->find($key) ? $figure_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($figures as $key => $figure) <strong>{!! $figure->displayName !!}</strong>@if($key != count($figures)-1 && count($figures)>2),@endif @if($key == count($figures)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if(count($faction->events))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Associated Event{{ count($faction->events) == 1 ? '' : 's'}}</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($faction->events as $key => $event)
                @if($event->thumb_extension)
                    <a href="{{ $event->url }}" data-toggle="tooltip" title="{{ $event->name }}"/><img src="{{$event->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $event->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($faction->events->groupBy('category_id') as $key => $events)
        <p class="mb-0">
            <strong>
                {{ $event_categories->find($key) ? $event_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($events as $key => $event) <strong>{!! $event->displayName !!}</strong>@if($key != count($events)-1 && count($events)>2),@endif @if($key == count($events)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if(count($faction->locations))
    <div class="text-center col-md mb-3 fb-md-50"><div class="card h-100 py-3">
     <h5 class="mb-0">Associated Location{{ count($faction->locations) == 1 ? '' : 's'}}</h5>

        <!-- <hr>
        <p class="mb-0">
            @foreach($faction->locations as $key => $location)
                @if($location->thumb_extension)
                    <a href="{{ $location->url }}" data-toggle="tooltip" title="{{ $location->name }}"/><img src="{{$location->thumbUrl}}" class="m-1" style="max-width:100px"/> </a>
                @else
                    {!! $location->displayName !!}
                @endif
            @endforeach
        </p> -->

        <hr>
        @foreach($faction->locations->groupBy('type_id') as $key => $locations)
        <p class="mb-0">
            <strong>
                {{ $location_categories->find($key) ? $location_categories->find($key)->name : 'Miscellanous' }}:
            </strong>
            @foreach($locations as $key => $location) <strong>{!! $location->displayName !!}</strong>@if($key != count($locations)-1 && count($locations)>2),@endif @if($key == count($locations)-2) and @endif @endforeach
        </p>
        @endforeach
    </div></div>
    @endif

    @if($faction->ranks()->count())
    <div class="w-100"></div>
    <div class="text-center col-md-12"><div class="card h-100 py-3">
     <h5 class="mb-0">Faction Ranks</h5>

    <hr/>
    <div class="row">
        @if($faction->ranks()->where('is_open', 0)->count())
            <div class="col-md">
                <h4><small>Leadership</small></h4>
                @foreach($faction->ranks()->where('is_open', 0)->orderBy('sort')->get() as $rank)
                    <h6>{{ $rank->name }}{{ $rank->description ? ': '.$rank->description : '' }}</h6>
                    @if($rank->members()->count())
                        <p>
                            @foreach($rank->members as $member)
                                {!! $member->memberObject->displayName !!}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </p>
                    @endif
                @endforeach
            </div>
        @endif
        @if($faction->ranks()->where('is_open', 1)->count())
            <div class="col-md">
                <h4><small>Member Ranks</small></h4>
                @foreach($faction->ranks()->where('is_open', 1)->orderBy('sort')->get() as $rank)
                    <h6>{{ $rank->name }}{{ $rank->description ? ': '.$rank->description : '' }}{!! $currency ? ' ('.$currency->display($rank->breakpoint).')' : ' ('.$rank->breakpoint.' Standing)' !!}</h6>
                @endforeach
            </div>
        @endif
    </div>
    <hr/>
    <a href="{{ url('world/factions/'.$faction->id.'/members') }}">
        <h4><small>Members: {{ $faction->factionMembers->count() }} ・ See All</small></h4>
    </a>
    </div></div>
    @endif
</div>

@endsection
