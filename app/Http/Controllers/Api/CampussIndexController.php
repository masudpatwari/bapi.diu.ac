<?php

namespace App\Http\Controllers\Api;

use App\Models\O_CAMPUS;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CampussIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return O_CAMPUS::all();
    }
}
