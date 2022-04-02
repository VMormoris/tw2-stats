@extends('layouts.basic')

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Quadrangular Castle (En69)</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container">
    <div class="text-center mt-5">
        <h1>Quadrangular Castle (En69)</h1>
    </div>

    <div class="d-flex flex-row">
        <div class="container" style="width: 30%;">
            <div class="center-content">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Players:</th>
                            <td id="player_num">5,607</td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Tribes:</th>
                            <td id="tribe_num">201</td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Villages:</th>
                            <td id="village_num">14,793</td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Win Condition:</th>
                            <td id="win_cond">75% Domination</td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="container" style="width: 70%;">
            <div class="row text-center mt-2">
                <h3>Latest conquers</h3><a href="/{{ $world }}/villages">show all</a>
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
                <tbody class="table-contents">
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-row">
        <div class="container" style="width: 50%;">
            <div class="text-center mt-2">
                <h3>Top 5 tribes</h3><a href="/{{ $world }}/tribes">show all</a>
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
                <tbody class="table-contents">
                </tbody>
            </table>
        </div>

        <div class="container" style="width: 50%;">
            <div class="text-center mt-2">
                <h3>Top 5 players</h3><a href="/{{ $world }}/players">show all</a>
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
                <tbody class="table-contents">
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection