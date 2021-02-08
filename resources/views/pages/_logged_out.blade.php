<h1>{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}</h1>
{!! $about->parsed_text !!}

<br><br>
@include('widgets._affiliates', ['affiliates' => $affiliates, 'featured' => $featured_affiliates, 'open' => $open])