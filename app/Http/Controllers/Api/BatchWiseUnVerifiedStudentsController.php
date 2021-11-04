<?php

namespace App\Http\Controllers\Api;

use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BatchWiseUnVerifiedStudentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,$batch_id)
    {
        $students = O_STUDENT::selectRaw("ID,NAME,ROLL_NO,REG_CODE,PHONE_NO,ADM_FRM_SL,EMP_ID,VERIFIED,DEPARTMENT_ID,BATCH_ID")
            ->with('employee:id,emp_name')
            ->where([
                'BATCH_ID' => $batch_id,
                'verified' => 0
            ])->get();

        return $students;
    }
}
