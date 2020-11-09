@extends('layouts.app')

@section('title') Affiliates @endsection

@section('content')
{!! breadcrumbs(['Site Affiliates' => 'affiliates']) !!}

@if($open)<h5 class="float-right"><a class="btn btn-primary" href="{{ url('affiliates/apply')}}">Apply to Affiliates</a></h5>@endif
<h1><img src="{{ asset('images/our_affiliate.png') }}" data-toggle="tooltip" title="Our affiliate icon!"/> Affiliates</h1> 


@if($featured->count() > 0)
    <div class="card my-2 text-center">
        <div class="card-header"><h5 class="text-center mb-0">Featured Affiliate{{ $featured->count() == 1 ? '' : 's' }}</h5></div>
        <div class="card-body py-2">
            @foreach($featured as $feat)
                {!! $feat->icon !!} 
            @endforeach
        </div>
    </div>
@endif

<div class="my-4 text-justify">
        @if($affiliates->count() > 0)
            @foreach($affiliates as $affiliate)
                {!! $affiliate->icon !!}
            @endforeach
        @else
            No affiliates found.
        @endif
</div>

@endsection

