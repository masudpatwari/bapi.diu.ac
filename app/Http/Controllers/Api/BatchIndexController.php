<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BatchResource;
use App\Models\O_BATCH;
use App\Models\O_EMP;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $batch = O_BATCH::with('relDepartment:id,name','relShift','group','campus')->orderByDesc('id')->paginate(300);

      	return BatchResource::collection($batch);

    }
}
