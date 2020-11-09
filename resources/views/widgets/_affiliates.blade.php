<div class="card mt-2 text-center">
    <div class="row mx-2 my-1" style="font-size:0.9em">
        <div class="col-6 text-left mx-0 px-0">@if($open)<a href="{{ url('affiliates/apply')}}">Apply to Affiliates</a>@endif</div>
        <div class="col-6 text-right mx-0 px-0"><a href="{{ url('affiliates')}}">See All Affiliates</a></div>
    </div>
        @if($featured->count() > 0)
            <hr class="my-0">
            <div class="card-body py-2" style="background-color:rgba(0,0,0,.01)">
                @foreach($featured as $feat)
                    {!! $feat->icon !!} 
                @endforeach
            </div>
        @endif
        @if($affiliates->count() > 0)
            <hr class="my-0">
            <div class="card-body py-2">
                    @foreach($affiliates as $affiliate)
                        {!! $affiliate->icon !!} 
                    @endforeach
            </div>
        @endif
</div>

<style>
    /* You can edit this as you like for hover effects on affiliate icons! */
    .avatar { opacity:0.5; filter:grayscale(.75);transition: 0.3s; }
    .avatar:hover{ opacity:1;filter:unset;transition: 0.3s; }
</style>