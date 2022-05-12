@extends('layouts.basic')

@section('meta')
<meta name="og:description" content="Tribes' leaderboard on world: [{{ $world }}]" key="description"/>
@endsection

@section('resources')
<!-- Core theme JS-->
<script src="/js/general.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world) }}">{{ $world }}</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<div id="app">
    <div class="container">
        <breadcrumb></breadcrumb>
        <leaderboard :endpoint="'tribes'" :title="'Tribes\' Ranking'" :placeholder="'Search Tribe'"></leaderboard>
    </div>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection