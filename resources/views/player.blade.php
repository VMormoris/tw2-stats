@extends('layouts.world')

@section('resources')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
<!-- Core theme JS-->
<script src="/js/general.js"></script>
<script src="/js/table.js"></script>
<script src="/js/player.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world) }}">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container subpage-container" id="overview">

    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('history')">History</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('conquers')">Conquers</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('villages')">Villages</a></li>
        </ol>
    </nav>

    <div class="text-center mt-2">
        <h1 id="overview-title">Overview</h1>
    </div>

    <div class="d-flex flex-row">
        <div class="container" style="width: 50%;">
            <div class="center-content">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Rank:</th>
                            <td id="rank"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Name:</th>
                            <td id="name"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Tribe:</th>
                            <td id="tname"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Points:</th>
                            <td id="points"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Villages:</th>
                            <td id="vills"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Average Points Per village:</th>
                            <td id="avg-vill-points"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Tribe Changes:</th>
                            <td id="tchanges"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Conquers:</th>
                            <td id="dconquers"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Offensive Bash:</th>
                            <td id="offbash"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Defensive Bash:</th>
                            <td id="defbash"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Total Bash:</th>
                            <td id="totalbash"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Victory Points:</th>
                            <td id="vp"></td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="container" style="width: 50%;">
            <div>
                <canvas id="pointsgraph"></canvas>
            </div>
            <div>
                <canvas id="rankgraph"></canvas>
            </div>
        </div>
    </div>

    <div>
        <canvas id="bashgraph"></canvas>
    </div>

    <div>
        <canvas id="villagesgraph"></canvas>
    </div>

</div>

<div class="container subpage-container" id="history">

    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('')">Overview</a></li>
            <li class="breadcrumb-item active" aria-current="page">History</li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('conquers')">Conquers</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('villages')">Villages</a></li>
        </ol>
    </nav>

    <div class="text-center mt-2">
        <h1 id="history-title">History</h1>
    </div>

    <div id="historyTable">
        
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div>
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="hpp12" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="hpp12">12</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="hpp25" autocomplete="off">
                        <label class="btn btn-outline-primary" for="hpp25">25</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="hpp50" autocomplete="off">
                        <label class="btn btn-outline-primary" for="hpp50">50</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="hpp100" autocomplete="off">
                        <label class="btn btn-outline-primary" for="hpp100">100</label>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default mt-1 rounded">
            <table class="table rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">tribe</th>
                        <th scope="col">villages</th>
                        <th scope="col">points</th>
                        <th scope="col">offbash</th>
                        <th scope="col">defbash</th>
                        <th scope="col">totalbash</th>
                        <th scope="col">rankno</th>
                        <th scope="col">victory points</th>
                        <th scope="col">timestamps</th>
                    </tr>
                </thead>
                
                <tbody class="table-contents">        
                </tbody>

            </table>

            <nav aria-label="...">
                <ul class="pagination justify-content-end pages">
                </ul>
            </nav>

        </div>

    </div>

</div>

<div class="container subpage-container" id="conquers">

    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('')">Overview</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('history')">History</a></li>
            <li class="breadcrumb-item active" aria-current="page">Conquers</li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('villages')">Villages</a></li>
        </ol>
    </nav>

    <div class="text-center mt-2">
        <h1 id="conquers-title">Conquers</h1>
    </div>

    <div id="conquersTable">
        
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div>
                        <input type="radio" class="btn-check ipp" name="conquers-per-page" id="cpp12" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="cpp12">12</label>
            
                        <input type="radio" class="btn-check ipp" name="conquers-per-page" id="cpp25" autocomplete="off">
                        <label class="btn btn-outline-primary" for="cpp25">25</label>
            
                        <input type="radio" class="btn-check ipp" name="conquers-per-page" id="cpp50" autocomplete="off">
                        <label class="btn btn-outline-primary" for="cpp50">50</label>
            
                        <input type="radio" class="btn-check ipp" name="conquers-per-page" id="cpp100" autocomplete="off">
                        <label class="btn btn-outline-primary" for="cpp100">100</label>
                    </div>
                </div>
                <div class="col">
                    <div class="d-flex flex-row-reverse">
                        <button type="submit" class="btn btn-primary" onclick="updateFilter(event)"><i class="fa fa-search"></i></button>
                        <input type="search" placeholder="Search village or owner" class="searchbar">
                        <div class="conquers-radio-container">
                            <input type="radio" class="btn-check conquers-type" name="conquers-type" id="all" autocomplete="off" checked>
                            <label class="btn btn-outline-primary cnqrs-type" for="all">all</label>

                            <input type="radio" class="btn-check conquers-type" name="conquers-type" id="gains" autocomplete="off">
                            <label class="btn btn-outline-success cnqrs-type" for="gains">gains</label>

                            <input type="radio" class="btn-check conquers-type" name="conquers-type" id="losses" autocomplete="off">
                            <label class="btn btn-outline-danger cnqrs-type" for="losses">losses</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default mt-1 rounded">
            <table class="table rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">name</th>
                        <th scope="col">old owner</th>
                        <th scope="col">new owner</th>
                        <th scope="col">points</th>
                        <th scope="col">timestamp</th>
                    </tr>
                </thead>
                
                <tbody class="table-contents">
                </tbody>

            </table>

            <nav aria-label="...">
                <ul class="pagination justify-content-end pages">
                </ul>
            </nav>

        </div>

    </div>

</div>

<div class="container subpage-container" id="villages">

    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('')">Overview</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('history')">History</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('conquers')">Conquers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Villages</li>
        </ol>
    </nav>

    <div class="text-center mt-2">
        <h1 id="villages-title">Villages</h1>
    </div>

    <div id="villTable">
        
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div>
                        <input type="radio" class="btn-check ipp" name="villages-per-page" id="vpp12" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="vpp12">12</label>
            
                        <input type="radio" class="btn-check ipp" name="villages-per-page" id="vpp25" autocomplete="off">
                        <label class="btn btn-outline-primary" for="vpp25">25</label>
            
                        <input type="radio" class="btn-check ipp" name="villages-per-page" id="vpp50" autocomplete="off">
                        <label class="btn btn-outline-primary" for="vpp50">50</label>
            
                        <input type="radio" class="btn-check ipp" name="villages-per-page" id="vpp100" autocomplete="off">
                        <label class="btn btn-outline-primary" for="vpp100">100</label>
                    </div>
                </div>

                <div class="col">
                    <div class="d-flex flex-row-reverse">
                        <button type="submit" class="btn btn-primary" onclick="updateFilter(event)"><i class="fa fa-search"></i></button>
                        <input type="search" placeholder="Search village" class="searchbar">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default mt-1 rounded">
            <table class="table rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">id</th>
                        <th scope="col">name</th>
                        <th scope="col">x</th>
                        <th scope="col">y</th>
                        <th scope="col">points</th>
                        <th scope="col">province</th>
                    </tr>
                </thead>
                
                <tbody class="table-contents">
                </tbody>

            </table>

            <nav aria-label="...">
                <ul class="pagination justify-content-end pages">
                </ul>
            </nav>

        </div>

    </div>

</div>
@endsection