<div class="card mb-3">
    <div class="card-header">
      <h2 class="card-title mb-0">{!! $news->displayName !!}</h2>
        <small>
            Posted {!! $news->post_at ? format_date($news->post_at) : format_date($news->created_at) !!} by {!! $news->user->displayName !!}
        </small>
    </div>
    <div class="card-body">
        <div class="parsed-text" id="newspost_{!! $news->id !!}">
            {!! $news->parsed_text !!}
        </div>

          <div class="text-right mt-2 mb-0 d-none" id="seemore_{!! $news->id !!}" >
            <a href="{!! $news->url !!}">Continue reading.</a>
          </div>
    </div>
</div>

  <script>
    if ($('#newspost_{!! $news->id !!}').height() > 200) {
      $('#newspost_{!! $news->id !!}').attr("style", "max-height:200px; overflow:hidden;");
      $('#seemore_{!! $news->id !!}').attr("style", "display:block!important;");
    }
  </script>
