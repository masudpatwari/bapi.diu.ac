<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;

class MonthlyAdmissionStudentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $start_date, $end_date)
    {
        $students = O_STUDENT::selectRaw("ID,NAME,DEPARTMENT_ID,ROLL_NO,REG_CODE,PHONE_NO,ADM_FRM_SL,EMP_ID,VERIFIED,ADM_DATE")
            ->with('department')
            ->whereBetween('adm_date', [$start_date,$end_date])
            ->get();
            ->groupBy('department.name')

        return $students;
    }
}
