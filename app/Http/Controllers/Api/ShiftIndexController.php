<?php

namespace App\Http\Controllers\Api;

use App\Models\O_SHIFT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShiftIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return O_SHIFT::all();
    }
}
