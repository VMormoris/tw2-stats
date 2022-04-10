@extends('layouts.basic')

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
<li class="nav-item"><a class="nav-link" href="/en69">en69</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/tribes">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/players">Players</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/villages">Villages</a></li>
@endsection

@section('content')
<div id="app">
    <test-component></test-component>
</div>
<script src="{{ asset('/js/app.js') }}"></script>
@endsection