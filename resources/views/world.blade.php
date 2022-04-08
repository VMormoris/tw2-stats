@extends('layouts.basic')

@section('resources')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
<script src="/js/general.js"></script>
<script src="/js/world.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">{{ $world }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<div id="app">
    <!-- Page content-->
    <div class="container">
        <div class="text-center mt-5">
            <h1>[{{ $world }}] {{ $name }}</h1>
        </div>

        <div class="d-flex flex-row mt-5">
            <world-info></world-info>
            <lastfive-conquers><lastfive-conquers>
        </div>

        <div class="d-flex flex-row mt-5">
            
            <topfive-tribes></topfive-tribes>

            <div class="container" style="width: 50%;">
                <div class="text-center">
                    <strong><a>Top 5 players </a></strong><a href="/{{ $world }}/players">show all</a>
                </div>
                <table class="table table-rounded mt-2">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">player</th>
                            <th scope="col">points</th>
                            <th scope="col">villages</th>
                            <th scope="col">domination</th>
                        </tr>
                    </thead>
                    <tbody id="top5_players" class="table-contents">
                    </tbody>
                </table>

                <div class="mt-5">
                    <canvas id="players_graph"></canvas>
                </div>

            </div>
        </div>

    </div>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection