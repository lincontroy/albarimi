<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...

    protected $middlewareGroups = [
        'web' => [
            // ... other middleware
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\TrackUserActivity::class,
        ],

        'api' => [
            // ...
        ],
    ];

    // ...
}