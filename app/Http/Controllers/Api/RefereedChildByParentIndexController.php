<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\O_REFEREED_BY_CHILD;

class RefereedChildByParentIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $parent_id)
    {
        return O_REFEREED_BY_CHILD::where('refereed_by_paarent_id', $parent_id)->get();
    }
}
