<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Addresses or ranges of the reverse proxies whose X-Forwarded-* headers
    | are trusted, comma separated. Read by the TrustProxies middleware when
    | its $proxies property isn't set; none are trusted when it's empty.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
