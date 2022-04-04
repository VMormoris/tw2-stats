@extends('layouts.basic')

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
@endsection

@section('content')
<!-- Page content-->
<div class="container full-height">

    <div class="center-content">
        <div class="d-flex flex-row">
            <div class="container" style="width:50%;">
                
                <div class="text-center">
                    <h1>A statistics tool for Tribal Wars 2</h1>
                </div>
                
                <div clas="container mt-5">

                    <p class="lead">tw2-stats is an open source tool that provides statistics for the online browser game "Tribal Wars 2".</p>
                    <p class="lead">Because the tool is still under development is only running for world: <a href="/en69">[EN69] Quadrangular Castle</a>. I aim to add more worlds in the future. Just be patient because I'm working alone while also being a full time student.</p>
                    <p class="lead">
                        I would really appreciate your feedback, ideas and constributions so feel free:
                    </p>
                    
                    <div class="text-center">
                        <a class="btn-rounded btn-custom-link" href="https://discord.gg/vxZbCrShaP" role="button">
                            Join our Chat
                            <i class="fa fa-discord-alt pl-2 fa-btn" aria-hidden="true"></i>
                        </a>
                        <a class="btn-rounded btn-dark ml-5" href="https://github.com/VMormoris/tw2-stats" role="button">
                            Source Code
                            <i class="fa fa-github pl-2 fa-btn" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>
                                
            </div>

            <div class="container" style="width: 50%;">
                <div class="center-content text-center">
                    <img src="images/logo.png" alt="Logo"></img>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection