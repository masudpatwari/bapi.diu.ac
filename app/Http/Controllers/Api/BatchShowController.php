<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BatchShowResource;
use App\Models\O_BATCH;
use App\Models\O_EMP;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchShowController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$id)
    {
        return new BatchShowResource(O_BATCH::find($id)) ?? '';
    }
}
