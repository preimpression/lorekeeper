<div class="col-12 col-md-3 p-1"><div class="card p-3 h-100">
    <div class="text-center">
        <a href="{{ $research->url }}" data-title="{{ $research->name }}" class="btn btn-lg btn-primary mb-2">
            <i class="{{ $research->icon_code }}"></i>
        </a>
        <h3 class="mb-0">{!! $research->displayName !!}</h3>
        <p class="mb-1"><strong>{!! $research->tree->displayName !!}</strong></p>
        
        @isset($research->prerequisite_id)
        <p class="mb-0"><strong>Prerequisite:</strong> {!! $research->prerequisite->displayName !!}</p>
        @endisset
        @isset($research->summary)
            <p class="mb-0 mt-1"> {!! $research->summary !!}</p>
        @endisset
        </div>
    </div>
</div>