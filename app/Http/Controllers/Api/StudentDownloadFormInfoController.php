<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_CASHIN;
use App\Models\O_GRADE_POINT_SYSTEM_DETAIL;
use App\Models\O_MARKS;
use App\Models\O_SEMESTERS;
use App\Models\O_SHIFT;
use App\Models\O_STUDENT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StudentDownloadFormInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($studentId, Request $request)
    {

        $student = O_STUDENT::with('department:id,name', 'batch:id,batch_name', 'employee:id,emp_name')
            ->selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO,  ADM_FRM_SL ,  ADM_DATE ,emp_id")
            ->where('VERIFIED', 1)
            ->where(['ID' => $studentId]);


        if ($student->count() > 0) {
            $std = $student->first();

            if (!$std) {
                return response()->json(['message' => 'Student Not Found'], 400);
            }

            $cashin_data = O_CASHIN::get_student_account_info_summary($std->id);

            $std->actual_total_fee = $cashin_data['summary']['actual_total_fee'] ?? 'N/A';
            $std->total_paid = $cashin_data['summary']['total_paid'] ?? 'N/A';
            $std->total_due = $cashin_data['summary']['total_due'] ?? 'N/A';

            return $std;
        }

    }

}
