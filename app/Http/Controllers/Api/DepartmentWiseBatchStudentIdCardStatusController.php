<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentWiseBatchStudentIdCardStatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$department_id)
    {
        $batch = O_BATCH::selectRaw("ID,BATCH_NAME,REG_CARD_PRINTED")
            ->where('department_id', $department_id)
            ->orderBy('BATCH_NAME','desc')
            ->get();

        return $batch;
    }
}
