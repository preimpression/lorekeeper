@extends($type[0].'.layout')

@section($type[1].'-title') {!! $type[0] != 'research' ? $user->name.'\'s' : 'My' !!} Unlocked Research @endsection

@section($type[1].'-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Research' => $user->url . '/unlocked-research']) !!}


<h1>
    {!! $type[0] != 'research' ? $user->displayName.'\'s' : 'My' !!} Unlocked Research
</h1>

@foreach($trees as $tree)
    <hr>
    <h4>{!! $tree->displayName !!}</h4>
    <div class="row">
        @foreach($tree->researches->where('parent_id',null) as $research)
            <div class="col-12  mx-auto body research-body research-scroll">
                <div class="research-tree ">
                    <ul>
                        @include('research._user_tree_research', ['research' => $research, 'tree' => $tree])
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

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