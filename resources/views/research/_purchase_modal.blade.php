@if($research)
    {!! Form::open(['url' => 'research/purchase/'.$research->id]) !!}

    <p>
        <strong>{{ $research->name }}</strong> costs {{ $research->price }} {{ isset($research->tree->currency->abbreviation) ? $research->tree->currency->abbreviation : $research->tree->currency->name }}
    </p>

    @auth
        <p>
            You have <strong>{{ $bankroll }} {{ isset($research->tree->currency->abbreviation) ? $research->tree->currency->abbreviation : $research->tree->currency->name }}.
        </p>
        
        @if($bankroll < $research->price )
            <p>You don't have enough {{$research->tree->currency->name}}. Get some more!</p>
        @else
        <p>Are you sure you want to purchase <strong>{{ $research->name }}</strong>?</p>
            <div class="text-right">
                {!! Form::submit('Purchase Research', ['class' => 'btn btn-success w-100']) !!}
            </div>
        @endif
    @endauth

    {!! Form::close() !!}
@else 
    Invalid research selected.
@endif