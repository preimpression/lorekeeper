@extends('layouts.app')

@section('title') Forum :: {{ $forum->name }} @endsection

@section('content')

@if(isset($forum->parent))
{!! breadcrumbs(['Forum' => 'forum', $forum->parent->name => 'forum/'.$forum->parent->id , $forum->name => 'forum/'.$forum->id]) !!}
@else
{!! breadcrumbs(['Forum' => 'forum', $forum->name => 'forum/'.$forum->id]) !!}
@endif

<h1 class="float-left">{!! $forum->displayName !!}</h1>

@if(isset($forum->parent))
    @include('forums._forum_page',['forum' => $forum, 'posts' => $posts])
@else
    <h5 class="text-center" style="clear:both;">Boards in {!! $forum->name !!}</h5>
    @include('forums._category_page',['forum' => $forum, 'forums' => $forum->children])
@endif












@inject('markdown', 'Parsedown')
@php
    $markdown->setSafeMode(true);
@endphp










<br><br><br><br><br><br><br><br><br><br><br><br>









@comments(['model' => $forum,
        'perPage' => 5
    ])



@endsection
