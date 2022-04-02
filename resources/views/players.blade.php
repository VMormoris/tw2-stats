@extends('layouts.basic')

@section('resources')
<!-- Core theme JS-->
<script src="/js/general.js"></script>
<script src="/js/table.js"></script>
<script src="/js/players.js"></script>
@endsection

@section('nav-links')
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world) }}">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container">

    <div class="text-center mt-5">
        <h1>Players' Ranking</h1>
    </div>

    <div id="table">
        
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <div>
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp12" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="ipp12">12</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp25" autocomplete="off">
                        <label class="btn btn-outline-primary" for="ipp25">25</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp50" autocomplete="off">
                        <label class="btn btn-outline-primary" for="ipp50">50</label>
            
                        <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp100" autocomplete="off">
                        <label class="btn btn-outline-primary" for="ipp100">100</label>
                    </div>
                </div>

                <div class="col">
                    <div class="d-flex flex-row-reverse">
                        <button type="submit" class="btn btn-primary" onclick="updateFilter(event)"><i class="fa fa-search" aria-hidden="true"></i></button>
                        <input type="search" placeholder="Search Player" class="searchbar">
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
                        <th scope="col">tribe</th>
                        <th scope="col">villages</th>
                        <th scope="col">points</th>
                        <th scope="col">def bash</th>
                        <th scope="col">off bash</th>
                        <th scope="col">total bash</th>
                        <th scope="vp">victory points</th>
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