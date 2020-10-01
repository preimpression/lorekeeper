<ul>
    <li class="sidebar-header"><a href="{{ url('research-trees') }}" class="card-link">Research Trees</a></li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Research Trees</div>
        @foreach($trees as $tree)
            <div class="sidebar-item"><a href="{{ $tree->url }}" class="{{ set_active('research-trees/'.$tree->id) }}">{{ $tree->name }}</a></div>
        @endforeach
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Research</div>
        <div class="sidebar-item"><a href="{{ url('research') }}" class="{{ set_active('research') }}">All Research</a></div>
        @if(Auth::check())<div class="sidebar-item"><a href="{{ url('research/unlocked') }}" class="{{ set_active('research/unlocked') }}">Unlocked Research</a></div>
        <div class="sidebar-item"><a href="{{ url('research/history') }}" class="{{ set_active('research/history') }}">My Research History</a></div>@endif
    </li>
</ul>