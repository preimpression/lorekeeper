@extends('admin.layout')

@section('admin-title') {{ $affiliate->id ? 'Edit' : 'Create' }} Affiliate @endsection

@section('admin-content')

@if($affiliate->status == 'Pending')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Affiliates' => 'admin/affiliates',  'Affiliate Requests' => 'admin/affiliates/pending', $affiliate->id ? 'Edit Affiliate' : 'Create Affiliate' => 'admin/affiliates/create']) !!}
@else
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Affiliates' => 'admin/affiliates',  $affiliate->id ? 'Edit Affiliate' : 'Create Affiliate' => 'admin/affiliates/create']) !!}
@endif

<h5 class="float-right"><a href="#" class="btn alert btn-danger affiliate-delete-button" data-id="{{ $affiliate->id }}">Delete</a></h5> 
@if($affiliate->status == 'Pending')
    <h5 class="float-right mr-2"><a href="#" class="btn alert alert-success affiliate-action-button" data-id="{{ $affiliate->id }}" data-action="accept">Accept</a></h5>
    <h5 class="float-right mr-2"><a href="#" class="btn alert alert-danger affiliate-action-button" data-id="{{ $affiliate->id }}" data-action="reject">Reject</a></h5>
@else
    <h5 class="float-right mr-2 alert 
    @if($affiliate->status == 'Accepted') alert-success
    @else alert-danger
    @endif
    ">{{ $affiliate->status }}</h5>
@endif

<h1>{{ $affiliate->id ? 'Edit' : 'Create' }} Affiliate <a href="{{ $affiliate->statusUrl }}"><i class="fas fa-link"></i></a></h1>
@if(!$affiliate->id)
    <p>Bypass the affiliate application step and go straight into creating an affiliate for your site.</p>
@else
    <div class="text-center mb-3" style="clear:both;">{!! $affiliate->icon !!}</div>
@endif


{!! Form::open(['url' => 'admin/affiliates/'.($affiliate->id ? 'edit/'.$affiliate->id : 'create') ]) !!}
@csrf
@honeypot

<div class="row mx-0 px-0 form-group">
    <div class="col-md-1 px-0 pr-md-1 col-form-label">
        {!! Form::label('Site Name') !!}
    </div>
    <div class="col px-0 pr-md-1">
        {!! Form::text('name', $affiliate->name, ['class' => 'form-control', 'placeholder' => 'The name of the affiliating ARPG or site.']) !!}
    </div>
    <div class="col-auto px-0 pl-md-1">
    {!! Form::checkbox('is_featured', 1, $affiliate->id ? $affiliate->is_featured : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Featured Affiliate', 'data-off' => 'Regular Affiliate']) !!}
    </div>
</div>  

<div class="row mx-0 px-0 form-group">
    <div class="col-md-6 px-0 pr-md-1">
        {!! Form::label('Site Url') !!}
        {!! Form::text('url', $affiliate->url, ['class' => 'form-control', 'placeholder' => 'http://']) !!}
    </div>
    <div class="col-md-6 px-0 pl-md-1">
        {!! Form::label('Icon Url (Optional)') !!}
        {!! Form::text('image_url', $affiliate->image_url, ['class' => 'form-control', 'placeholder' => 'Please don\'t use long Wix urls. Max of 100 characters.']) !!}
    </div>
</div>  

<div class="form-group">
    {!! Form::label('Short Description (Optional)') !!}
    {!! Form::textarea('description', $affiliate->description, ['class' => 'form-control', 'style' => 'max-height:4em;', 'placeholder' => 'This will show up on hover over the icon. No more than 100 characters, please.']) !!}
</div>
@if($affiliate->status != 'Pending')
    <div class="form-group">
        {!! Form::label('Staff Comment (Optional)') !!}
        {!! Form::textarea('staff_comment', $affiliate->staff_comment, ['class' => 'form-control', 'style' => 'max-height:4em;', 'placeholder' => 'Staff message about this.']) !!}
    </div>
@endif


<div class="row">
    @if($affiliate->message)
    <div class="col-md mb-3">
        <div class="card card-body alert-secondary">
            <strong>{!! $affiliate->submitter !!} ({!! pretty_date($affiliate->created_at) !!}) said:</strong>
            <p class="mb-0">
                {{ $affiliate->message }}
            </p>
        </div>
    </div>
    @endif

    @if($affiliate->staff_comment)
    <div class="col-md mb-3">
        <div class="card card-body 
            @if($affiliate->status == 'Accepted') alert-success
            @elseif($affiliate->status == 'Pending') alert-warning
            @else alert-danger
            @endif
        ">
            <strong>Staff ({!! $affiliate->staff !!}) ({!! pretty_date($affiliate->updated_at) !!}) said:</strong>
            <p class="mb-0">
                {{ $affiliate->staff_comment }}
            </p>
        </div>
    </div>
    @endif
</div>



<div class="text-right">
    {!! Form::submit( $affiliate->id ? 'Edit' : 'Create' , ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection

@section('scripts')
@parent

<script>

    $('.affiliate-action-button').on('click', function(e) {
        e.preventDefault();
        var $this = $(this)
        loadModal("{{ url('admin/affiliates/edit') }}" + '/' + $this.data('id') + '/' + $this.data('action'), 'Affiliate Request');
    });


    $('.affiliate-delete-button').on('click', function(e) {
        e.preventDefault();
        var $this = $(this)
        loadModal("{{ url('admin/affiliates/delete') }}" + '/' + $this.data('id'), 'Delete Affiliate');
    });

</script>

@endsection