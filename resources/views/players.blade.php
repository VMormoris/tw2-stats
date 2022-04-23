@extends('layouts.basic')

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
    <leaderboard :endpoint="'players'" :title="'Players\' Ranking'" :placeholder="'Search Player'"></leaderboard>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection