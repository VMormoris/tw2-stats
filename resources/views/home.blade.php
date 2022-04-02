@extends('layouts.basic')

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/tribes') }}">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/players') }}">Players</a></li>
<li class="nav-item"><a class="nav-link" href="{{ url('/' . $world . '/villages') }}">Villages</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container">
    <div class="text-center mt-5">
        <h1>A statistics tool for Tribal Wars 2</h1>
        <p class="lead">tw2-stats is an open source tool that provides statistics for the online game Tribal Wars 2.</p>
        <p class="lead">Because the tool is still under development is only running for world: <strong>en69</strong>. I aim to add more worlds in the future. Just be patient because I'm working alone while also being a full time student.</p>
        <p class="lead">I would really appreciate your feedback, ideas and constributions so feel free to visit the repository on GitHub or hang out with us on the Discord Server.</p>
        <!--<p>Bootstrap v5.1.3</p>-->
    </div>
</div>
@endsection