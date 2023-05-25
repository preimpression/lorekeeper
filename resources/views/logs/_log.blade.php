<div class="card mb-3">
    <div class="card-header">
        <h2 class="card-title mb-0">{!! $log->displayName !!}</h2>
        <small>
            Posted {!! $log->post_at ? pretty_date($log->post_at) : pretty_date($log->created_at) !!} :: Last edited {!! pretty_date($log->updated_at) !!} by {!! $log->user->displayName !!}
        </small>
    </div>
    <div class="card-body">
        <div class="parsed-text">
            {!! $log->parsed_text !!}
        </div>
    </div>
    <?php $commentCount = App\Models\Comment::where('commentable_type', 'App\Models\DevLog')->where('commentable_id', $log->id)->count(); ?>
    @if(!$page)
        <div class="text-right mb-2 mr-2">
            <a class="btn" href="{{ $log->url }}"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : ''}}</a>
        </div>
    @else
        <div class="text-right mb-2 mr-2">
            <span class="btn"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : ''}}</span>
        </div>
    @endif
</div>
