<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <!-- Meta data for discord links -->
        <meta name="og:type" content="website" key="og_type"/>
        <meta name="og:title" content="{{ $page }} - tw2 stats" key="title"/>
        <meta name="og:site_name" content="tw2-stats.com" key="site_name"/>
        <meta name="og:image" content="/images/logo_big.png" key="image"/>
        <meta name="twitter:card" content="summary_large_image" key="misc-card"/>
        <!-- Other meta tag(s) to be added later
        <meta name="theme-color" content="" key="theme-color"/>
        -->
        @yield('meta')
        <meta name="author" content=""/>
        <!--<meta name="csrf-token" content="{{ csrf_token() }}">-->
        <title>{{ $page }} - tw2 stats</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
        <!-- Core theme CSS -->
        <link href="/css/general.css" rel="stylesheet"/>
        <!-- Fork Awesome 1.2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fork-awesome@1.2.0/css/fork-awesome.min.css" integrity="sha256-XoaMnoYC5TH6/+ihMEnospgm0J1PM/nioxbOUdnM8HY=" crossorigin="anonymous">
        <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">        <!-- Bootstrap core JS-->
        <!-- JavaScript Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        @yield('resources')
    </head>
    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="/"><img src="/images/logo_small.png" alt="logo"></img></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        @yield('nav-links')
                    </ul>
                </div>
                <div class="navbar-end">
                    <a href="https://discord.gg/vxZbCrShaP">
                        <i class="fa fa-discord-alt nav-fa" aria-hidden="true"></i>
                    </a>
                    <a href="https://github.com/VMormoris/tw2-stats">
                        <i class="fa fa-github nav-fa" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </nav>

        @yield('content')
        <footer class="footer mt-auto bg-dark">
            <div class="container py-2">
                <div class="row lh-37">
                    <div class="col">
                        <a class="white no-u" href="https://www.apache.org/licenses/LICENSE-2.0">Copyright © 2022  tw2-stats</a><a class="white no-u">, </a><a class="white" href="/privacy">Privacy Policy</a>
                    </div>
                    <div class="col">
                        <div class="d-flex flex-row-reverse">
                            <em><a class="white ml-2 no-u"> and </a><a class="white" href="#">others</a></em>
                            <!-- Fork Awesome -->
                            <a class="white" href="https://forkaweso.me/Fork-Awesome/">
                                <i class="fa fa-fork-awesome" aria-hidden="true"></i>
                            </a>
                            <!-- Bootstrap Icon -->
                            <a href="https://getbootstrap.com/">
                                <i class="fa fa-bootstrap" aria-hidden="true"></i>
                            </a>
                            <!-- Vue Icon -->
                            <a href="https://vuejs.org/">
                                <svg  class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 196.32 170.02">
                                    <path fill="#42b883" d="M120.83 0L98.16 39.26 75.49 0H0l98.16 170.02L196.32 0h-75.49z"/>
                                    <path fill="#35495e" d="M120.83 0L98.16 39.26 75.49 0H39.26l58.9 102.01L157.06 0h-36.23z"/>
                                </svg>
                            </a>
                            <!-- Laravel Icon -->
                            <a href="https://laravel.com/">
                                <i class="fa fa-laravel" aria-hidden="true"></i>
                            </a>
                            <!-- PostgreSQL Icon -->
                            <a class="ml-2" href="https://www.postgresql.org/">
                                <i class="fa fa-postgresql" aria-hidden="true"></i>
                            </a>
                            <em><a class="white no-u">Build using:</a></em>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </body>
</html>