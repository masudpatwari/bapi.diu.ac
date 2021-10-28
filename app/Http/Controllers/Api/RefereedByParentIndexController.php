<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\O_REFEREED_BY_PARENT;
use App\Http\Controllers\Controller;

class RefereedByParentIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return O_REFEREED_BY_PARENT::all();
    }
}
