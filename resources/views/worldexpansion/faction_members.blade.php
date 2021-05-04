@extends('worldexpansion.layout')

@section('title') {{ $faction->style }}: Members @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Factions' => 'world/factions', $faction->style => 'world/factions/'.$faction->id, 'Members' => 'world/factions/'.$faction->id.'/members']) !!}
<h1>{!! $faction->fullDisplayName !!}: Members</h1>

@if(!count($members))
    <p>No members found.</p>
@else
    {!! $members->render() !!}
      <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
          <div class="col-6 col-md-4 font-weight-bold">Member</div>
          <div class="col-6 col-md-4 font-weight-bold">Rank</div>
          <div class="col-6 col-md font-weight-bold">Standing</div>
        </div>
        @foreach($members as $member)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
          <div class="col-6 col-md-4">{!! $member->displayName !!}</div>
          <div class="col-6 col-md-4">{!! $member->factionRank ? $member->factionRank->displayName : '-' !!}</div>
          <div class="col-6 col-md">{!! $currency->display($member->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first() ? $member->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first()->quantity : 0) !!}</div>
        </div>
        @endforeach
      </div>
    {!! $members->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $members->count() }} result{{ $members->count() == 1 ? '' : 's' }} found.</div>
@endif

@endsection
