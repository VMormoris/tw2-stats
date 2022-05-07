@extends('layouts.basic')

@section('meta')
<meta name="og:description" content="Checkout {{ $name }}'s information on world: [{{ $world }}]" key="description"/>
@endsection

@section('resources')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
<!-- Core theme JS-->
<script src="/js/general.js"></script>
<script src="/js/table.js"></script>
<script src="/js/village.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world) }}">{{ $world }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<div class="container subpage-container" id="overview">
    
    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('history')">History</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('conquers')">Conquers</a></li>
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
                            <th>ID:</th>
                            <td id="identifier"></td>
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
                            <th>Coordinates:</th>
                            <td id="coords"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Owner:</th>
                            <td id="player"></td>
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
                            <th>Conquers:</th>
                            <td id="dconquers"></td>
                        </tr>
                    </thead>
                    <thead class="thead-dark">
                        <tr>
                            <th>Province:</th>
                            <td id="provname"></td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div class="container" style="width: 50%;">
            <div>
                <canvas id="pointsgraph"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="container subpage-container" id="history">

    <nav class="mt-5" style="--bs-breadcrumb-divider: '';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('')">Overview</a></li>
            <li class="breadcrumb-item active" aria-current="page">History</li>
            <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="changeView('conquers')">Conquers</a></li>
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
                        <th scope="col">name</th>
                        <th scope="col">owner</th>
                        <th scope="col">points</th>
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
                        <button type="submit" class="btn btn-primary" onclick="updateFilter(event)"><i class="fa fa-search" aria-hidden="true"></i></button>
                        <input type="search" placeholder="Search village or owner" class="searchbar">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default mt-1 rounded">
            <table class="table rounded">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">name</th>
                        <th scope="col">old owner</th>
                        <th scope="col">new owner</th>
                        <th scope="col">old tribe</th>
                        <th scope="col">new tribe</th>
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