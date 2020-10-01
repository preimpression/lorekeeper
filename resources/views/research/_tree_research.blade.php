<li class="mx-auto">
        <div class="member-view-box"  id="research-{{$research->id}}">
            <div class="member-image">
                <a href="{{ $research->url }}" class="btn btn-lg btn-secondary">
                    <h2 class="mb-0"><i class="{{ $research->icon_code }}"/></i></h2>
                </a>
                <div class="member-details text-center">
                <strong>{!! $research->displayName !!}</strong>
                
                <p class="mb-0">
                    {!! $research->price !!} 
                    <span data-toggle="tooltip" title="{{ $tree->currency->name }}">
                        {!! isset($tree->currency->abbreviation) ? $tree->currency->abbreviation : $tree->currency->displayName !!}
                    </span>
                </p>
                <p class="mb-0">
                   <small data-toggle="tooltip" title="Prerequisite/Requires. <br>This research is required.">R: {!! isset($research->prerequisite) ? $research->prerequisite->displayName : 'None' !!}</small>
                </p>
                </div>
            </div>
        </div>


    @if($research->children->count())
    <a href="javascript:void(0);">
        <i class="fas fa-sort-down" style="margin-left:1px"></i>
    </a>
        <ul class="active">
            @foreach($research->children as $child)
                @include('research._tree_research', ['research' => $child, 'tree' => $tree])
            
            @endforeach
        </ul>
    @endif
</li>