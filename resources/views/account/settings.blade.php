@extends('account.layout')

@section('account-title') Settings @endsection

@section('account-content')
{!! breadcrumbs(['My Account' => Auth::user()->url, 'Settings' => 'account/settings']) !!}

<h1>Settings</h1>


<div class="card p-3 mb-2">
    <h3>Avatar</h3>
    <div class="text-left"><div class="alert alert-warning">Please note a hard refresh may be required to see your updated avatar. Also please note that uploading a .gif will display a 500 error after; the upload should still work, however.</div></div>
    @if(Auth::user()->isStaff)
        <div class="alert alert-danger">For admins - note that .GIF avatars leave a tmp file in the directory (e.g php2471.tmp). There is an automatic schedule to delete these files.
        </div>
    @endif
    <form enctype="multipart/form-data" action="avatar" method="POST">
        <label>Update Profile Image</label><br>
        <input type="file" name="avatar">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="pull-right btn btn-sm btn-primary">
    </form>
</div>

@if($user_enabled == 1 || (Auth::user()->isStaff && $user_enabled == 2))
<h3>Home Location <span class="text-muted">({{ ucfirst($location_interval) }})</span></h3>
    @if(Auth::user()->isStaff && $user_enabled == 2)
        <div class="alert alert-warning">You can edit this because you are a staff member. Normal users cannot edit their own locations freely.</div>
    @endif
    @if($char_enabled == 1)
        <div class="alert alert-warning">Your characters will have the same home as you.</div>
    @endif
    @if(Auth::user()->canChangeLocation)
        {!! Form::open(['url' => 'account/location']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Location</label>
                <div class="col-md-9">
                {!! Form::select('location', [0=>'Choose a Location'] + $locations, isset(Auth::user()->home_id) ? Auth::user()->home_id : 0, ['class' => 'form-control selectize']) !!}
                </div>
                <div class="col-md text-right">
                    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                </div>
            </div>
        {!! Form::close() !!}
    @else
        <div class="alert alert-warning">
        <strong>You can't change your location right now.</strong>
        You last changed it on {!! format_date(Auth::user()->home_changed, false) !!}.
        Home locations can be changed {{ $location_interval }}.
        </div>
    @endif
@endif

@if($user_faction_enabled == 1 || (Auth::user()->isStaff && $user_faction_enabled == 2))
<h3>Faction <span class="text-muted">({{ ucfirst($location_interval) }})</span></h3>
    @if(Auth::user()->isStaff && $user_enabled == 2)
        <div class="alert alert-warning">You can edit this because you are a staff member. Normal users cannot edit their own faction freely.</div>
    @endif
    @if($char_enabled == 1)
        <div class="alert alert-warning">Your characters will have the same faction as you.</div>
    @endif
    @if(Auth::user()->canChangeFaction)
        <p>Please note that changing your faction will remove you from any special ranks and reset your faction standing!</p>
        {!! Form::open(['url' => 'account/faction']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Faction</label>
                <div class="col-md-9">
                {!! Form::select('faction', [0=>'Choose a Faction'] + $factions, isset(Auth::user()->faction_id) ? Auth::user()->faction_id : 0, ['class' => 'form-control selectize']) !!}
                </div>
                <div class="col-md text-right">
                    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
                </div>
            </div>
        {!! Form::close() !!}
    @else
        <div class="alert alert-warning">
        <strong>You can't change your faction right now.</strong>
        You last changed it on {!! format_date(Auth::user()->faction_changed, false) !!}.
        Faction can be changed {{ $location_interval }}.
        </div>
    @endif
@endif

<h3>Email Address</h3>

<div class="card p-3 mb-2">
    <h3>Profile</h3>
    {!! Form::open(['url' => 'account/profile']) !!}
        <div class="form-group">
            {!! Form::label('text', 'Profile Text') !!}
            {!! Form::textarea('text', Auth::user()->profile->text, ['class' => 'form-control wysiwyg']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Birthday Publicity</h3>
    {!! Form::open(['url' => 'account/dob']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select('birthday_setting', ['0' => '0: No one can see your birthday.', '1' => '1: Members can see your day and month.', '2' => '2: Anyone can see your day and month.', '3' => '3: Full date public.'],Auth::user()->settings->birthday_setting, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Email Address</h3>
    <p>Changing your email address will require you to re-verify your email address.</p>
    {!! Form::open(['url' => 'account/email']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Email Address</label>
            <div class="col-md-10">
                {!! Form::text('email', Auth::user()->email, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Change Password</h3>
    {!! Form::open(['url' => 'account/password']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Old Password</label>
            <div class="col-md-10">
                {!! Form::password('old_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Confirm New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.selectize').selectize();
});

</script>
@endsection
