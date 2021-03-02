@extends('research.layout')

@section('research-title') {{ $research->name }} @endsection

@section('research-content')
{!! breadcrumbs(['Research' => 'research', $research->tree->name => $research->tree->url, $research->name => $research->url]) !!}

<h1>
    {!! $research->displayName !!} ({!! $research->tree->displayName !!})
</h1>

<div class="text-center">
    <h1 style="font-size:5em;"><i class="my-2 mx-1 {{ $research->icon_code }}"></i></h1>

    @isset($research->prerequisite_id)
        <p class="mb-0"><strong>Prerequisite:</strong> {!! $research->prerequisite->displayName !!}</p>
    @endisset

    @if(count($research->subrequisites))
        <p class="mb-0">
            <strong>Required by:</strong>
            @foreach($research->subrequisites as $subrequisite)
                <span class="mr-1">{!! $subrequisite->displayName !!}</span>
            @endforeach
        </p>
    @endif

    @isset($research->parent_id)
        <p class="mb-0"><strong>Child of:</strong> {!! $research->parent->displayName !!}</p>
    @endisset

    @if(count($research->children))
        <p class="mb-0">
            <strong>Children:</strong>
            @foreach($research->children as $child)
                <span class="mr-1">{!! $child->displayName !!}</span>
            @endforeach
        </p>
    @endif

    @isset($research->summary)
        <p class="mb-0 mt-3"> {!! $research->summary !!}</p>
    @endisset

</div>

{!! $research->parsed_description !!}


@auth

    @if(!$research->is_active)
        <h5 class="text-center">
            <a  class="btn btn-outline-danger mt-3 purchase-research-button disabled">
                <strong>{{ $research->name }} is not currently available for purchase</strong>
            </a>
        </h5>
    @elseif(Auth::user()->hasResearch($research->id))
        <h5 class="text-center">
            <a  class="btn btn-outline-danger mt-3 purchase-research-button disabled">
                <strong>You already have {{ $research->name }}!</strong>
            </a>
        </h5>
    @elseif($research->prerequisite && !Auth::user()->hasResearch($research->prerequisite->id))

        <h5 class="text-center mt-3 mb-0 pb-0 font-weight-bold text-danger"> {{ $research->name }} requires {{ $research->prerequisite->name }}</h5>

        <h5 class="text-center">
            <a href="{{ $research->prerequisite->url }}" class="btn btn-danger mt-3">Go to {{ $research->prerequisite->name }}</a>
        </h5>

    @elseif($bankroll < $research->price )
        <h5 class="text-center">
            <a  class="btn btn-outline-danger mt-3 purchase-research-button disabled">
                <strong>This costs {{ $research->price }} {{ $research->tree->currency->name }}</strong>
                <br>
                You only have {{ $bankroll}} {{ $research->tree->currency->name }}
            </a>
        </h5>
    @else
        <h5 class="text-center">
            <a href="#" class="btn btn-success mt-3 purchase-research-button">Purchase Research for {{ $research->price }} {{ $research->tree->currency->name }} </a>
        </h5>
    @endif

    @if(count($research->rewards))
        <div class="col-6 mx-auto"><div class="card p-3">
            <h5 class="text-center">Rewards</h5>
            <p class="text-center">
                After you buy this research, you can claim the following rewards:
            </p>
            <div class="row no-gutters">
                @foreach($research->rewards as $reward)
                    <div class="col-4 text-center">
                        {!! $reward->quantity !!} {!! $reward->reward->displayName !!}
                    </div>
                @endforeach
            </div>
            @if(Auth::check() && Auth::user()->hasResearch($research->id))
                @if(App\Models\User\UserResearch::where('user_id',Auth::user()->id)->where('research_id',$research->id)->first()->rewards_claimed)
                    <h5 class="text-center">
                        <a  class="btn btn-outline-secondary mt-3 purchase-research-button disabled">
                            You have already claimed these rewards!
                        </a>
                    </h5>
                @else
                    {!! Form::open(['url' => 'research/claim-rewards/'.$research->id]) !!}
                        <h5 class="text-center">
                            {!! Form::submit('Claim Rewards', ['class' => 'btn btn-success mt-3 ']) !!}
                        </h5>
                    {!! Form::close() !!}

                @endif
            @endif

        </div></div>
    @endif


@endauth
@guest
    <p class="text-center mt-5 text-italic">Log in to purchase for {{ $research->price }} {{ $research->tree->currency->name }}.</p>
@endguest


@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.purchase-research-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('research/purchase/') }}/{{ $research->id }}", 'Purchase Research');
    });
});

</script>
@endsection
