<li class="mx-auto">
                
        <div class="member-view-box"  {!! !$user->hasResearch($research->id) ? 'style="opacity:0.25" title="Not Unlocked" data-toggle="tooltip"' : 'title="Unlocked: '.$research->name.'" data-toggle="tooltip"' !!} id="research-{{$research->id}}">
            <div class="member-image">
                <a href="{{ $research->url }}" class="btn btn-lg btn-secondary">
                    <h2 class="mb-0"><i class="{{ $research->icon_code }}"/></i></h2>
                </a>
                <div class="member-details text-center mb-0">
                <strong>{!! $research->displayName !!}</strong>
                </div>
            </div>
        </div>

    @if($research->children->count())
    <a href="javascript:void(0);">
        <i class="fas fa-sort-down" style="margin-left:1px"></i>
    </a>
        <ul class="{{ $user->hasResearch($research->id) ? 'active' : '' }}">
            @foreach($research->children as $child)
                @include('research._user_tree_research', ['research' => $child, 'tree' => $tree])
            @endforeach
        </ul>
    @endif
</li>