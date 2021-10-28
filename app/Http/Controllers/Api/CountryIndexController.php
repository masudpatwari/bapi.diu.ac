<?php

namespace App\Http\Controllers\Api;

use App\Models\O_COUNTRY;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return O_COUNTRY::all();
    }
}
