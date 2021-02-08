@extends('layouts.app')

@section('title') Apply to be an Affiliate @endsection

@section('content')
    
{!! breadcrumbs(['Site Affiliates' => 'affiliates']) !!}
<h1>Apply to be an Affiliate</h1>

@if(!$open) 
    We are currently closed for new affiliates!
@else
    
<div class="card border-danger mb-3">
    <div class="card-body">
        @include('home._affiliate_rules')
    </div>
</div>
    

{!! Form::open(['url' => 'affiliates/apply']) !!}
@csrf
@honeypot
<h3>About Your Site</h3>

<div class="form-group">
    {!! Form::label('Site Name') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'The name of your ARPG or site.']) !!}
</div>

<div class="row mx-0 px-0 form-group">
    <div class="col-md-6 px-0 pr-md-1">
        {!! Form::label('Site Url') !!}
        {!! Form::text('url', null, ['class' => 'form-control', 'placeholder' => 'http://']) !!}
    </div>
    <div class="col-md-6 px-0 pl-md-1">
        {!! Form::label('Icon Url (Optional)') !!}
        {!! Form::text('image_url', null, ['class' => 'form-control', 'placeholder' => 'Please don\'t use long Wix urls. Max of 100 characters.']) !!}
    </div>
</div>  

<div class="form-group">
    {!! Form::label('Short Description (Optional)') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'style' => 'max-height:4em;', 'placeholder' => 'This will show up on hover over your icon. No more than 100 characters, please.']) !!}
</div>

<h3>About You</h3>

@auth
    <p class="mb-2"><strong>You are submitting this as {!! Auth::user()->displayName !!}.</strong> If you wish to submit this as a guest, please log out or use Incognito.</p>
@else
    <div class="form-group">
        {!! Form::label('Your Name') !!}
        {!! Form::text('guest_name', null, ['class' => 'form-control', 'placeholder' => 'So we know who to talk to if there are any issues or questions.']) !!}
    </div>
@endauth

<div class="form-group">
    {!! Form::label('Message (Optional)') !!}
    {!! Form::textarea('message', null, ['class' => 'form-control', 'style' => 'max-height:8em;', 'placeholder' => 'This is a good place to put if you wish to be featured/sister sites or other notes to us.']) !!}
</div>


<div class="text-right">
    {!! Form::submit('Apply', ['class' => 'btn btn-primary btn-block']) !!}
</div>

{!! Form::close() !!}


@endif


@endsection

