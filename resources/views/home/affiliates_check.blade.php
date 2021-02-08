@extends('layouts.app')

@section('title') Affiliate Status @endsection

@section('content')
    
{!! breadcrumbs(['Site Affiliates' => 'affiliates', 'Status Affiliate Request: '.$affiliate->name => 'status']) !!}
<h5 class="float-right alert 
@if($affiliate->status == 'Pending') alert-warning
@elseif($affiliate->status == 'Accepted') alert-success
@else alert-danger
@endif
">{{ $affiliate->status }}</h5>
<h1>Status of {{ $affiliate->name }}</h1>

<div class="text-center mb-4" style="clear:both;">
    {!! $affiliate->icon !!}
</div>

<div class="card mb-2">
    <div class="card-body">
        <h5>{{ $affiliate->name }}</h5>
        <p class="mb-1">
            <strong>Site Url:</strong> {{ $affiliate->url }} <br>
            <strong>Requested by:</strong> {!! $affiliate->submitter !!} <br>
            <strong>Short Description:</strong> {{ isset($affiliate->description) ? $affiliate->description : '-' }} <br>
            <strong>Request Message:</strong> {{ isset($affiliate->message) ? $affiliate->message : '-' }} <br>
        </p>
    </div>
</div>

@if($affiliate->status != 'Pending')
    <div class="card mb-2
        @if($affiliate->status == 'Pending') alert-warning
        @elseif($affiliate->status == 'Accepted') alert-success
        @else alert-danger
        @endif
    ">
        <div class="card-body">
            <h5 class="text-center font-weight-light">This has been {{ $affiliate->status }} by {!! $affiliate->staff !!}</h5>
            @if($affiliate->is_featured)<h5 class="text-center">It has been featured!</h5>@endif
            <p class="mb-0 text-center">
                {{ isset($affiliate->staff_comment) ? $affiliate->staff_comment : 'They left no comment.' }}
            </p>
            @if($affiliate->status == 'Rejected')
                <p class="mb-0 mt-2 text-danger text-center">
                    If you want to follow up, feel free to shoot a message to the Deviantart group.
                </p>
            @endif
        </div>
    </div>
@endif


@endsection

