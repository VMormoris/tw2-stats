@extends('layouts.basic')

@section('nav-links')
<li class="nav-item"><a class="nav-link active" aria-current="page" href="/">Home</a></li>
<li class="nav-item"><a class="nav-link" href="/en69">en69</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/tribes">Tribes</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/players">Players</a></li>
<li class="nav-item"><a class="nav-link" href="/en69/villages">Villages</a></li>
@endsection

@section('content')

<div class="container">
    <div class="text-center mt-5">
        <h1>Privacy Policy</h1>
    </div>

    <div class="container mt-2">
        <p>
            We at tw2-stats, accessible from tw2-stats.com, don't want your personal 
            data. You should also know that <strong>will never sell any information 
            to third parties.</strong> This privacy policy document contains essential 
            information about the collection of your data and use of cookies by tw2-stats.
        </p>
        <h3>Log Files</h3>
        <p>
            tw2-stats follows a standard procedure of using log files. These files 
            log visitor when they visit our website. The information we collect 
            using this log files are:
            <ul>
                <li>IP Address</li>
                <li>Browser type</li>
                <li>Date and time stamp</li>
            </ul>
            <strong>Why we use logs?</strong><br>
            We use logs in order to be able to indetify malicious users.
        </p>
        <h3>Cookies</h3>
        <p>
            A "cookie" is a small text file that is send from the server of tw2-stats 
            and is stored on your computer or mobile phone when visiting our website.
            <ul>
                <li><strong>XSRF-token:</strong> This cookie validates the user session
                and is necessary to guarantee safety during the website visit to avoid 
                a cross-site request forgery.</li>
                <li><strong>Session cookie</strong> This is a unique cookie to identify a 
                specific website visitor for the duration of his visit (session).</li>
            </ul>
            <strong>Why we use cookies?</strong><br>
            Our website uses cookies to distinguish you from other users. As well as provide 
            us with protection against malicious users.<br>
            <strong>How to control and adjust your cookie settings:</strong><br>
            You can always adjust your browser settings. However, keep in mind that we can't 
            guarantee an optimal service of the website. Bellow you can find instructions on how
            to adjust your settings on a few frequently used browsers:
            <ul>
                <li>Cookie settings within <a href="https://support.google.com/accounts/answer/61416">Google Chrome</a></li>
                <li>Cookie settings within <a href="https://support.apple.com/en-us/HT201265">Apple's Safari</a></li>
                <li>Cookie settings within <a href="https://support.mozilla.org/en-US/kb/enhanced-tracking-protection-firefox-desktop?redirectslug=enable-and-disable-cookies-website-preferences&redirectlocale=en-US">Mozilla Firefox</a></li>
                <li>Cookie settings within <a href="https://help.opera.com/en/latest/web-preferences/#cookies">Opera</a></li>
            </ul>
        </p>
        <h3>Your Rights</h3>
        <p>
            We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:
            <ul>
                <li><strong>The right to access:</strong> You have the right to request copies of your personal data.</li>
                <li><strong>The right to rectification:</strong> You have the right to request that we correct any information you believe is inaccurate. You also have the right to request that we complete the information you believe is incomplete.</li>
                <li><strong>The right to erasure:</strong> You have the right to request that we erase your personal data.</li>
                <li><strong>The right to restrict processing:</strong> You have the right to request that we restrict the processing of your personal data, under certain conditions.</li>
                <li><strong>The right to object to processing:</strong> You have the right to object to our processing of your personal data, under certain conditions.</li>
                <li><strong>The right to data portability:</strong> You have the right to request that we transfer the data that we have collected to another organization, or directly to you, under certain conditions.</strong></li>
            </ul>
            <em>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us.</em>
        </p>
    </div>
</div>

@endsection