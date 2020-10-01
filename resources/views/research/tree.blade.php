@extends('research.layout')

@section('research-title') {{ $tree->name }} @endsection

@section('research-content')
{!! breadcrumbs(['Research Trees' => 'research-trees', $tree->name => $tree->url]) !!}

<h1>
    {{ $tree->name }}
</h1>

<div class="text-center">
    <img src="{{ $tree->treeImageUrl }}"  class="mw-100"/>
    <p class="mb-0">{!! $tree->summary !!}</p>
    <hr>
    {!! $tree->parsed_description !!}
</div>
<div class="row">
    @foreach($researches as $research)
        <div class="col-12  mx-auto body research-body research-scroll">
            <hr>
            <div class="research-tree ">
                <ul>
                    @include('research._tree_research', ['research' => $research, 'tree' => $tree])
                </ul>
            </div>
        </div>
    @endforeach
</div>
    


<script>
    $(function () {
        $('.research-tree ul').hide();
        $('.research-tree>ul').show();
        $('.research-tree ul.active').show();
        $('.research-tree li').on('click', function (e) {
            var children = $(this).find('> ul');
            if (children.is(":visible")) children.hide('fast').removeClass('active');
            else children.show('fast').addClass('active');
            e.stopPropagation();
        });
    });
</script>

@endsection