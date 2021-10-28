<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;

class BatchWiseStudentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $batch_id)
    {
        $students = O_STUDENT::selectRaw("ID,NAME,ROLL_NO,REG_CODE,PHONE_NO,ADM_FRM_SL")
            ->where('BATCH_ID', $batch_id)
            ->get();
        return $students;
    }
}
