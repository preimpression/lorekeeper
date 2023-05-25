<ul>
    <li class="sidebar-header"><a href="{{ url('news') }}" class="card-link">Recent Posts</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">News</div>
        @foreach($newses->take(5) as $news)
            <div class="sidebar-item"><a href="{{ $news->url }}" class="{{ set_active('news/'.$news->id.'*') }}">{{ $news->title }}</a></div>
        @endforeach
            <div class="sidebar-item"><a href="{{ url('news') }}" class="{{ set_active('news') }}">All News >></a></div>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Dev Logs</div>
        @foreach($logs->take(5) as $log)
            <div class="sidebar-item"><a href="{{ $log->url }}" class="{{ set_active('logs/'.$log->id.'*') }}">{{ $log->title }}</a></div>
        @endforeach
            <div class="sidebar-item"><a href="{{ url('logs') }}" class="{{ set_active('logs') }}">All Logs >></a></div>
</ul>
