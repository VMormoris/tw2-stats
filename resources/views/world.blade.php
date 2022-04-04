@extends('layouts.basic')

@section('resources')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
<script src="/js/general.js"></script>
<script src="/js/world.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">{{ $world }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container">
    <div class="text-center mt-5">
        <h1>[{{ $world }}] {{ $name }}</h1>
    </div>

    <div class="d-flex flex-row">
        <div class="container" style="width: 30%;">
            <div class="center-content">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Players:</th>
                            <td id="player_num"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Tribes:</th>
                            <td id="tribe_num"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Villages:</th>
                            <td id="village_num"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Win Condition:</th>
                            <td id="win_cond"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Starting date:</th>
                            <td id="start"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Ending date:</th>
                            <td id="end"></td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="container" style="width: 70%;">
            <div class="text-center mt-2">
                <strong><a>Latest conquers </a></strong><a href="/{{ $world }}/villages">show all</a>
            </div>
            <table class="table table-rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">village</th>
                        <th scope="col">old owner</th>
                        <th scope="col">new owner</th>
                        <th scope="col">old tribe</th>
                        <th scope="col">new tribe</th>
                        <th scope="col">timestamp</th>
                    </tr>
                </thead>
                <tbody id="top5_conquers" class="table-contents">

                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-row mt-5">
        <div class="container" style="width: 50%;">
            <div class="text-center mt-2">
                <strong><a>Top 5 tribes </a></strong><a href="/{{ $world }}/tribes">show all</a>
            </div>
            <table class="table table-rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">tribe</th>
                        <th scope="col">points</th>
                        <th scope="col">villages</th>
                        <th scope="col">domination</th>
                    </tr>
                </thead>
                <tbody id="top5_tribes" class="table-contents">
                </tbody>
            </table>

            <div class="mt-5">
                <canvas id="tribes_graph"></canvas>
            </div>

        </div>

        <div class="container" style="width: 50%;">
            <div class="text-center mt-2">
                <strong><a>Top 5 players </a></strong><a href="/{{ $world }}/players">show all</a>
            </div>
            <table class="table table-rounded">
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
@endsection