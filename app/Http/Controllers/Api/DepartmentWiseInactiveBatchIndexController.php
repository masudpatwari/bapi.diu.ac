<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentWiseInactiveBatchIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$department_id)
    {

        $students = O_BATCH::selectRaw('id,batch_name')->where([
            'department_id'=>$department_id,
            'active_status'=>0,
        ])->get();

        return $students;
    }
}
