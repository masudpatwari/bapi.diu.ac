<?php

namespace App\Http\Controllers\Api;

use App\Models\O_BATCH;
use App\Models\O_CASHIN;
use App\Models\O_GRADE_POINT_SYSTEM_DETAIL;
use App\Models\O_MARKS;
use App\Models\O_SEMESTERS;
use App\Models\O_SHIFT;
use App\Models\O_STUDENT;
use App\Models\O_StudentReadmission;
use App\Models\O_StudentTransfer;
use App\Traits\bapiTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentDownloadFormInfoController extends Controller
{
    use bapiTrait;

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($studentId, Request $request)
    {

        $student = O_STUDENT::with('department:id,name', 'batch:id,batch_name', 'employee:id,emp_name','shift')
            ->selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO,  ADM_FRM_SL ,  ADM_DATE ,emp_id,SESSION_NAME,EMAIL,PHONE_NO,F_NAME,M_NAME,GENDER")
            ->where('VERIFIED', 1)
            ->where(['ID' => $studentId]);

        if ($student->count() > 0) {
            $std = $student->first();

            $studentProvisionalResult = $this->studentProvisionalResult($std->id);

            $totalSemester = $studentProvisionalResult['transcript_data']['semesters'];


            $completeSemesters = $studentProvisionalResult['transcript_data']['results']['semesters'];



            $totalSemesterSingleArray = array_reduce($totalSemester, 'array_merge', array());


            $studentSemesters = [];

            foreach ($completeSemesters as $completeSemester) {
                $semester_result = $this->semesterResult($totalSemesterSingleArray, $completeSemester['semester']);

                $studentSemesters[] = [
                    'semester' => $completeSemester['semester'],
                    'created_at' => date('d-m-Y', $completeSemester['datetime']),
                    'semester_gpa' => $semester_result['semester_gpa'] ?? 'N/A',
                    'semester_result' => $semester_result['semester_result'] ?? 'N/A',
                    'created_by' => $completeSemester['created_by_user']['NAME'] ?? 'N/A',
                    'incomplete_subject_code' => $semester_result['incomplete_subject_code'] ?? 'N/A',
                    'status' => $completeSemester['result_tabulation_status'],
                ];
            }

            $incomplete_subject_codes = [];
            foreach ($studentSemesters as $incomplete_subject_code) {
                if ($incomplete_subject_code['incomplete_subject_code']) {
                    $incomplete_subject_codes[] = $incomplete_subject_code;
                }
            }

            if (!$std) {
                return response()->json(['message' => 'Student Not Found'], 400);
            }

            $cashin_data = O_CASHIN::get_student_account_info_summary($std->id);

//            return $cashin_data;

            $std->actual_total_fee = $cashin_data['summary']['actual_total_fee'] ?? 'N/A';
            $std->total_paid = $cashin_data['summary']['total_paid'] ?? 'N/A';
            $std->total_due = $cashin_data['summary']['total_due'] ?? 'N/A';
            $std->special_scholarship = $cashin_data['summary']['special_scholarship'] ?? 'N/A';
            $std->studentSemesters = $studentSemesters;
            $std->grade_letter = $studentProvisionalResult['transcript_data']['results']['grade_letter'] ?? 'N/A';
            $std->cgpa = $studentProvisionalResult['transcript_data']['results']['cgpa'] ?? 'N/A';
            $std->incomplete_sub_code = collect($incomplete_subject_codes)->implode('incomplete_subject_code', ',');
            $std->current_semester = $std->getMaxAsCurrentSemester();

            $data = [
                'student' => $std,
                'student_re_admission' => O_StudentReadmission::with('employee:id,emp_name')->where('std_id', $std->id)->select('id', 'date_', 'emp_id')->first(),
                'student_transfer' => O_StudentTransfer::with('employee:id,emp_name')->where('std_id', $std->id)->select('id', 'date_', 'emp_id')->first()
            ];

            return $data;
        }

    }


}
