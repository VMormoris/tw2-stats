@extends('layouts.basic')

@section('resources')
<script src="/js/general.js"></script>
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

        <breadcrumb></breadcrumb>

        <div class="text-center mt-2">
            <h1>[{{ $world }}] {{ $name }}</h1>
        </div>

        <div class="d-flex flex-row mt-5">
            <world-info></world-info>
            <lastfive-conquers><lastfive-conquers>
        </div>

        <div class="d-flex flex-row mt-5">
            <topfive></topfive>
        </div>

    </div>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection