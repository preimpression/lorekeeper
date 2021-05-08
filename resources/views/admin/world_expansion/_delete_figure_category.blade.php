@if($category)
    {!! Form::open(['url' => 'admin/world/figure-categories/delete/'.$category->id]) !!}

    <p>
        You are about to delete the figure category <strong>{{ $category->name }}</strong>. This is not reversible.
    </p>
    <p>
        If you would like to hide the category from users, you can set it as inactive from the figure category settings page.
    </p>

    @if(count($category->figures))
    <div class="alert alert-danger">
        <h5>If you delete this category, you will also delete: </h5>
        @foreach($category->figures as $key => $figure) <strong>{!! $figure->displayName !!}</strong>@if($key != count($category->figures)-1 && count($category->figures)>2),@endif @if($key == count($category->figures)-2) and @endif @endforeach.
    </div>
    @endif

    <p>Are you sure you want to delete <strong>{{ $category->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Figure Category', ['class' => 'btn btn-danger w-100']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid figure category selected.
@endif
