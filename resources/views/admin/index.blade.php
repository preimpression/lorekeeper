@extends('admin.layout')

@section('admin-title') Dashboard @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Home' => 'admin']) !!}

<h1>Admin Dashboard</h1>



<div class="row">
  @if($news)
  <div class="col-12">
      <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Latest Bulletin: {!! $news->displayName !!}</h5>
        <small>
            Posted {!! $news->post_at ? format_date($news->post_at) : format_date($news->created_at) !!} by {!! $news->user->displayName !!}
        </small>
        <div class="card-body py-1 pb-0">
            <div class="parsed-text mt-2" id="bulletin">
                {!! $news->parsed_text !!}
            </div>
            <div class="text-right mt-2 mb-0 d-none" id="seemore" >
              <a href="{!! $news->url !!}">Continue reading.</a>
            </div>
            <div class="text-right mt-2 mb-0" >
              <a href="{!! url('admin/bulletins') !!}">More Staff Bulletins</a>
            </div>

            <script>
              if ($('#bulletin').height() > 200) {
                $('#bulletin').attr("style", "max-height:205px; overflow:hidden;");
                $('#seemore').attr("style", "display:block!important;");
              }
            </script>
        </div>
      </div>
    </div>
  </div>
  @else
  <div class="col-12">
    No news.
  </div>
  @endif


    <div class="col-sm-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Prompt Submissions @if($submissionCount)<span class="badge badge-primary">{{ $submissionCount }}</span>@endif</h5>
                <p class="card-text">
                    @if($submissionCount)
                        {{ $submissionCount }} submission{{ $submissionCount == 1 ? '' : 's' }} awaiting processing.
                    @else
                        The submission queue is clear. Hooray!
                    @endif
                </p>
                <div class="text-right">
                    <a href="{{ url('admin/submissions/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Claims @if($claimCount)<span class="badge badge-primary">{{ $claimCount }}</span>@endif</h5>
                <p class="card-text">
                    @if($claimCount)
                        {{ $claimCount }} claim{{ $claimCount == 1 ? '' : 's' }} awaiting processing.
                    @else
                        The claim queue is clear. Hooray!
                    @endif
                </p>
                <div class="text-right">
                    <a href="{{ url('admin/claims/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Design Updates @if($designCount)<span class="badge badge-primary">{{ $designCount }}</span>@endif</h5>
                <p class="card-text">
                    @if($designCount)
                        {{ $designCount }} design update{{ $designCount == 1 ? '' : 's' }} awaiting processing.
                    @else
                        The design update approval queue is clear. Hooray!
                    @endif
                </p>
                <div class="text-right">
                    <a href="{{ url('admin/design-approvals/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">MYO Approvals @if($myoCount)<span class="badge badge-primary">{{ $myoCount }}</span>@endif</h5>
                <p class="card-text">
                    @if($myoCount)
                        {{ $myoCount }} MYO slot{{ $myoCount == 1 ? '' : 's' }} awaiting processing.
                    @else
                        The MYO slot approval queue is clear. Hooray!
                    @endif
                </p>
                <div class="text-right">
                    <a href="{{ url('admin/myo-approvals/pending') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                </div>
            </div>
        </div>
    </div>
    @if($openTransfersQueue)
        <div class="col-sm-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Character Transfers @if($transferCount + $tradeCount)<span class="badge badge-primary">{{ $transferCount + $tradeCount }}</span>@endif</h5>
                    <p class="card-text">
                        @if($transferCount + $tradeCount)
                            {{ $transferCount + $tradeCount }} character transfer{{$transferCount + $tradeCount == 1 ? '' : 's' }} and/or trade{{$transferCount + $tradeCount == 1 ? '' : 's' }} awaiting processing.
                        @else
                            The character transfer/trade queue is clear. Hooray!
                        @endif
                    </p>
                    <div class="text-right">
                        <a href="{{ url('admin/masterlist/transfers/incoming') }}" class="card-link">View Queue <span class="fas fa-caret-right ml-1"></span></a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
