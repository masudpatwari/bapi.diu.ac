<?php

namespace App\Http\Controllers\Api;

use App\Models\O_CASHIN;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegistrationSummeryIndexController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $startDate = $request->start_date;//'2016/08/1';
        $endDate = $request->end_date;//'2016/08/30';


        $cashein = O_CASHIN::with('student:ID,NAME,REG_CODE,ROLL_NO,department_id,PHONE_NO,SESSION_NAME,BATCH_ID,emp_id,ADM_DATE','student.department:id,name','student.batch:id,BATCH_NAME,Sess','student.employee:ID,EMP_NAME')
            ->select(['AMOUNT', 'STUDENT_ID'])
            ->whereBetween('PAY_DATE', [$startDate, $endDate])
            ->where('purpose_pay_id', 4) // admission fee = 4;
            ->get();

        return $cashein;

    }
}
