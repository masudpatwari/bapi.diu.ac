<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'https://bapi.diu.ac/exim/search_student?token=Kyxufto3M1W8ySwrYBFQLAMKBqkbP35uCGBPzKVS2pv1ymiFxGm1xcODuQkC',
        'https://bapi.diu.ac/exim/confirm_payment?token=Kyxufto3M1W8ySwrYBFQLAMKBqkbP35uCGBPzKVS2pv1ymiFxGm1xcODuQkC'
    ];
}
