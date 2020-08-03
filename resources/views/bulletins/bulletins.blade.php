@extends('admin.layout')

@section('admin-title') Bulletin :: {{$bulletins->title}} @endsection

@section('admin-content')
    {!! breadcrumbs(['Staff Bulletins' => 'admin/bulletins', $bulletins->title => $bulletins->url]) !!}
    @include('news._news', ['news' => $bulletins])
@endsection
