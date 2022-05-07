@extends('layouts.basic')

@section('meta')
<meta name="og:description" content="Players' leaderboard on world: [{{ $world }}]" key="description"/>
@endsection

@section('resources')
<!-- Core theme JS-->
<script src="/js/general.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world) }}">{{ $world }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<div id="app">
    <div class="container">
        <breadcrumb></breadcrumb>
        <leaderboard :endpoint="'players'" :title="'Players\' Ranking'" :placeholder="'Search Player'"></leaderboard>
    </div>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection