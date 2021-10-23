<?php

/**
 * Date : 2018-Jun-20;
 * Developer Name : Md. Mesbaul Islam || Arif Bin A. Aziz;
 * Contact : 01738120411;
 * E-mail : rony.max24@gmail.com;
 * Theme Name: Result Management System;
 * Theme URI: N/A;
 * Author: Dhaka International University;
 * Author URI: N/A;
 * Version: 1.1.0
 */

namespace App\Http\Controllers;

use App\Exceptions\StudentNotFound;
use App\Exceptions\StudentNotFoundExceptions;
use App\Http\Resources\ExamRoutineResource;
use App\Models\O_COURSE;
use App\Models\O_GRADE_POINT_SYSTEM_DETAIL;
use App\Models\O_IMP_EXAM_SCHEDULE;
use App\Models\O_IMP_EXIM_ROUTINE;
use App\Models\O_IMP_EXIM_ROUTINE_DETAIL;
use App\Models\O_COURSE_ALLOCATION_INFO;
use App\Models\O_IMP_REQUEST;
use App\Models\O_IMP_REQUEST_COURSE;
use App\Models\O_MARKS;
use App\Models\O_PURPOSE_PAY;
use App\Models\O_SHIFT;
use App\Models\O_STUDENT;
use App\Models\O_BATCH;
use App\Models\O_EMP;
use App\Models\O_DEPARTMENTS;
use App\Models\O_EMP_DEPARTMENTS;
use App\Models\O_DESIGNATION;
use App\Models\O_VIEW_S_BATCH;
use App\Models\O_CASHIN;
use App\Models\M_WP_EMP;
use App\Models\O_VIEW_S_BATCH_REGCARD_PRINT;
use App\Models\O_VIEW_S_BATCH_REGCARD_PRINTED;
use App\Models\O_RELIGION;
use App\Models\O_BANK;
use App\Models\O_SEMESTERS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ErpAdmissionStoreRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    public function cashInReport(Request $request)
    {
        $startDate = $request->start_date;//'2016/08/1';
        $endDate = $request->end_date;//'2016/08/30';
        $purposePayId = $request->purpose_pay_id;

        // PURPOSE_PAY_ID	AMOUNT	NOTE	STUDENT_ID
        // DATE_BANK	PAY_DATE

        //$student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID, ACTUAL_FEE , NO_OF_SEMESTER")->where(['id' => $ora_uid ])->first();
        //  pay_date between '08/16/2016' and '08/21/2016'
        $cashein = O_CASHIN::select(['AMOUNT', 'STUDENT_ID'])
            ->whereBetween('PAY_DATE', [$startDate, $endDate])
            ->where('purpose_pay_id', $purposePayId)
            ->get();

        $totalAmount = $cashein->sum('amount');
        $stdIdArray = array_unique($cashein->pluck("student_id")->toArray());

        $deptWiseData = [];
        $departments = O_DEPARTMENTS::orderBy('name', 'desc')->get();

        $summaryTotalStudent = count($stdIdArray);
        $summaryTotalFemaleStudent = 0;
        $summaryTotalMaleStudent = 0;
        $summaryTotalMaleStudentAmount = 0;
        $summaryTotalFemaleStudentAmount = 0;

        foreach ($departments as $department) {

            $stdOfSelectedDepartment = O_STUDENT::select(['ID', 'GENDER'])->whereIn('id', $stdIdArray)->where('DEPARTMENT_ID', $department->id)->get();
            $stdOfSelectedDepartmentCount = $stdOfSelectedDepartment->count();

            if ($stdOfSelectedDepartmentCount == 0) {
                continue;
            }
            $departmentWiseTotalAmount = $cashein->whereIn('student_id', $stdOfSelectedDepartment->pluck('id')->toArray())->sum('amount');

            $maleStudent = $stdOfSelectedDepartment->where('gender', 'M');
            $femaleStudent = $stdOfSelectedDepartment->where('gender', 'F');
            $maleWiseTotalAmount = $cashein->whereIn('student_id', $maleStudent->pluck('id')->toArray())->sum('amount');
            $femaleWiseTotalAmount = $cashein->whereIn('student_id', $femaleStudent->pluck('id')->toArray())->sum('amount');

            $maleStudentCount = $maleStudent->count();
            $femaleStudentCount = $femaleStudent->count();

            $summaryTotalMaleStudent += $maleStudentCount;
            $summaryTotalFemaleStudent += $femaleStudentCount;
            $summaryTotalMaleStudentAmount += $maleWiseTotalAmount;
            $summaryTotalFemaleStudentAmount += $femaleWiseTotalAmount;

            $deptWiseData[] = [
                'department' => $department->name,
                'totalStudent' => $stdOfSelectedDepartmentCount,
                'maleStudentCount' => $maleStudentCount,
                'femaleStudentCount' => $femaleStudentCount,
                'totalAmount' => $departmentWiseTotalAmount,
                'maleTotalAmount' => $maleWiseTotalAmount,
                'femaleTotalAmount' => $femaleWiseTotalAmount,
            ];

        }
        return [
            'detail' => $deptWiseData,
            'summary' => [
                'totalStudent' => $summaryTotalStudent,
                'totalMaleStudent' => $summaryTotalMaleStudent,
                'totalFemaleStudent' => $summaryTotalFemaleStudent,
                'totalMaleStudentAmount' => $summaryTotalMaleStudentAmount,
                'totalFemaleStudentAmount' => $summaryTotalFemaleStudentAmount,
                'totalAmount' => $totalAmount
            ]
        ];

    }

    public function provisional_transcript_marksheet(int $student_id)
    {

        $result_publish_date_of_last_semester = '';
        $cgpa = '';
        $duration_in_year = '';

        $std = O_STUDENT::with('batch')->where('id', $student_id)->first();
        if (!$std) {
            return response()->json(['message' => 'Student not found'], 400);
        }

        $transcript = $this->make_transcript($student_id);
        if (!empty($transcript)) {
            unset($transcript['student_info']->image);
            unset($transcript['student_info']->password);
            $semesters = $transcript['transcript_data']['results']['semesters'];
            $count_semester = count($semesters);
            $result_publish_date_of_last_semester = $count_semester > 0 ? date("d/m/Y", $semesters[$count_semester - 1]['result_publish_date']) : '';

            $cgpa = $transcript['transcript_data']['results']['cgpa'];
        } else {
            return response()->json(['message' => 'Transcript not complete yet.'], 400);
        }

        $improvement_course_id = O_IMP_REQUEST_COURSE::with('relCourse')->where('std_id', $student_id)->get();

        $improvement_final_course_code = implode(", ", $improvement_course_id->where('type', 'final')->pluck('relCourse.code')->toArray());
        $improvement_incourse_course_code = implode(", ", $improvement_course_id->where('type', 'incourse')->pluck('relCourse.code')->toArray());

        return [
            'student_id' => $std->id,
            'batch_name_as_major' => $std->batch->batch_name,
            'improvement_final_course_code' => $improvement_final_course_code,
            'improvement_incourse_course_code' => $improvement_incourse_course_code,
            'result_publish_date_of_last_semester' => $result_publish_date_of_last_semester,
            'cgpa' => $cgpa,
            'duration_in_month' => $std->batch->no_of_semester * $std->batch->duration_of_sem_m,

        ];


    }

    public function getLatestForeignStudents(Request $request)
    {
        $exceptIds = [];

        if ($request->has('ids')) {
            $exceptIds = explode(',', $request->ids);
        }

        $date = new Carbon();
        $lastDate = $date->subYears(4)->subMonths(6)->format("Y-m-d");

        $students = O_STUDENT::with('department', 'batch', 'relCampus')
            ->whereRaw("lower( trim(nationality) ) not like 'bangla%'")
            ->whereRaw("lower( trim(nationality) ) not like 'bd'")
            ->where('adm_date', '>', $lastDate)
            ->where('verified', 1)
            ->whereNotIn('id', $exceptIds)
            ->get();

        if ($students->count())
            return $students->filter(function ($i) {
                unset($i->image);
                return $i;
            });
    }

    public function getSemesterCourseList(int $student_id, int $semester_number = 0)
    {
        $std = O_STUDENT::with('batch')->where('id', $student_id)->first();
        if (!$std) {
            return response()->json(['message' => 'Student Not Found'], 400);
        }
        $std->image = null;
        $batch_id = $std->batch_id;
        $semseter = O_SEMESTERS::with('allocatedCourses')
            ->where('batch_id', $batch_id)
            ->when($semester_number > 0, function ($q) use ($semester_number) {
                $q->where('semester', $semester_number);
            })
            ->orderBy('semester', 'desc')
            ->first();

        if (!$semseter) {
            return response()->json(['message' => 'Semester Not Found'], 400);
        }

        $courses = O_COURSE::whereIn('id', $semseter->allocatedCourses->pluck('course_id')->toArray())->get();
        unset($semseter->allocatedCourses);
        return ['semester' => $semseter,
            'courses' => $courses
        ];

    }


    public function provisional_result($student_id)
    {
        try {
            $std = O_STUDENT::find($student_id);

            $currentSemester = $std->getMaxAsCurrentSemester();

            if (!empty($std)) {
                $transcript = $this->make_transcript($student_id);
                if (!empty($transcript)) {
                    unset($transcript['student_info']->image);
                    unset($transcript['student_info']->password);
                    $semesters = $transcript['transcript_data']['results']['semesters'];

                    $totalCurrentDue = O_CASHIN::get_student_account_info_summary($student_id);

                    $max_due_amount_to_show_result = env("MAX_DUE_AMOUNT_TO_SHOW_RESULT", 5000);

                    if (isset($totalCurrentDue ['summary']['total_current_due']) && $totalCurrentDue ['summary']['total_current_due'] > $max_due_amount_to_show_result) {
                        if (count($semesters) > 0) {
                            $semesterOnRemoveAllocated_courses = $currentSemester;

                            foreach ($transcript['transcript_data']['semesters'] as &$rowHas2Semester) {

                                if (isset($rowHas2Semester[0])) {
                                    if ($rowHas2Semester[0]['semester'] == $semesterOnRemoveAllocated_courses) {
                                        $rowHas2Semester[0]['allocated_courses'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['total_semester_gpa'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['average_grade'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[0]['semester_result'] = 'Please, clear Due to show result';
                                    }
                                }

                                if (isset($rowHas2Semester[1])) {
                                    if ($rowHas2Semester[1]['semester'] == $semesterOnRemoveAllocated_courses) {
                                        $rowHas2Semester[1]['allocated_courses'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['total_semester_gpa'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['average_grade'] = 'Please, clear Due to show result';
                                        $rowHas2Semester[1]['semester_result'] = 'Please, clear Due to show result';
                                    }
                                }
                            }

                        }
                    }


                    return $transcript;
                } else {
                    return response()->json(['message' => 'Transcript not complete yet.'], 400);
                }

            } else {
                return response()->json(['message' => 'Student Not Found'], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }

    }

    public function get_past_foreign_student($fromPage = 0, $noOfRowsPerpage = 10)
    {
        if ($fromPage < 0 || $noOfRowsPerpage < 1)
            return response()->json(['error' => 'Invalid Page Or Number Of Rows Per Page']);

        return O_STUDENT::selectRaw("student.id,student.Name student_name,student.REG_CODE,student.NATIONALITY, s_department.name, s_batch.batch_name, student.PHONE_NO, s_batch.valid_d_idcard")
            ->join('s_batch', 's_batch.id', '=', 'student.batch_id')
            ->join('s_department', 's_department.id', '=', 'student.DEPARTMENT_ID')
            ->whereRaw("
								s_batch.VALID_D_IDCARD < sysdate and (
								student.NATIONALITY <> null OR (
								LOWER(student.NATIONALITY) not like  'bang%' and
								LOWER(student.NATIONALITY) not like  '%shi' and
								LOWER(student.NATIONALITY) not like  'bd')
								)")
            ->orderBy('s_batch.VALID_D_IDCARD', 'desc')
            ->get()->forPage($fromPage, $noOfRowsPerpage);
    }

    public function get_present_foreign_student($fromPage = 0, $noOfRowsPerpage = 10)
    {
        if ($fromPage < 0 || $noOfRowsPerpage < 1)
            return response()->json(['error' => 'Invalid Page Or Number Of Rows Per Page']);

        return O_STUDENT::selectRaw("student.id,student.name student_name,student.REG_CODE,student.NATIONALITY, s_department.name, s_batch.batch_name, student.PHONE_NO, s_batch.valid_d_idcard")
            ->join('s_batch', 's_batch.id', '=', 'student.batch_id')
            ->join('s_department', 's_department.id', '=', 'student.DEPARTMENT_ID')
            ->whereRaw("
								s_batch.VALID_D_IDCARD >= sysdate and (
								student.NATIONALITY <> null OR (
								LOWER(student.NATIONALITY) not like  'bang%' and
								LOWER(student.NATIONALITY) not like  '%shi' and
								LOWER(student.NATIONALITY) not like  'bd')
								)")
            ->orderBy('s_batch.VALID_D_IDCARD', 'desc')
            ->get()->forPage($fromPage, $noOfRowsPerpage);
    }


    /***
     *   BELLOW CODE IS DUPLICATED SHOULD REMOVE BUT COMMETING IF FACE ANY ISSUE
     */

    // public function get_past_foreign_student($fromPage=0,$noOfRowsPerpage=10)
    // {
    // 	if($fromPage<0 || $noOfRowsPerpage < 1)
    // 			return response()->json(['message' => 'Invalid Page Or Number Of Rows Per Page']);
    //
    // 	return O_STUDENT::selectRaw("student.id,student.Name student_name,student.REG_CODE,student.NATIONALITY, s_department.name, s_batch.batch_name, student.PHONE_NO, s_batch.valid_d_idcard")
    //         ->join('s_batch', 's_batch.id', '=', 'student.batch_id')
    //         ->join('s_department', 's_department.id', '=', 'student.DEPARTMENT_ID')
    // 					->whereRaw("
    // 							s_batch.VALID_D_IDCARD < sysdate and (
    // 							student.NATIONALITY <> null OR (
    // 							LOWER(student.NATIONALITY) not like  'bang%' and
    // 							LOWER(student.NATIONALITY) not like  '%shi' and
    // 							LOWER(student.NATIONALITY) not like  'bd')
    // 							)")
    // 					->orderBy('s_batch.VALID_D_IDCARD','desc')
    //         ->get()->forPage($fromPage,$noOfRowsPerpage);
    // }


    /***
     *   BELLOW CODE IS DUPLICATED SHOULD REMOVE BUT COMMETING IF FACE ANY ISSUE
     */
    // public function get_present_foreign_student($fromPage=0,$noOfRowsPerpage=10)
    // {
    // 	if($fromPage<0 || $noOfRowsPerpage < 1)
    // 			return response()->json(['message' => 'Invalid Page Or Number Of Rows Per Page']);
    //
    // 	return O_STUDENT::selectRaw("student.id,student.name student_name,student.REG_CODE,student.NATIONALITY, s_department.name, s_batch.batch_name, student.PHONE_NO, s_batch.valid_d_idcard")
    // 					->join('s_batch', 's_batch.id', '=', 'student.batch_id')
    // 					->join('s_department', 's_department.id', '=', 'student.DEPARTMENT_ID')
    // 					->whereRaw("
    // 							s_batch.VALID_D_IDCARD >= sysdate and (
    // 							student.NATIONALITY <> null OR (
    // 							LOWER(student.NATIONALITY) not like  'bang%' and
    // 							LOWER(student.NATIONALITY) not like  '%shi' and
    // 							LOWER(student.NATIONALITY) not like  'bd')
    // 							)")
    // 					->orderBy('s_batch.VALID_D_IDCARD','desc')
    // 					->get()->forPage($fromPage,$noOfRowsPerpage);
    // }

    public function registration_cards_print($batch_id, $m_batch_id, $token, $token2, $site_token)
    {
        if (md5($batch_id . $m_batch_id) !== $token || ($token2 != md5(date('dymd'))) || !in_array($site_token, explode(',', env('API_APP_SITE_KEYS'))))
            return "error";

        $cards = O_STUDENT::where(['batch_id' => $batch_id, 'VERIFIED' => 1])->orderBy('ROLL_NO')->with('department')->get();

        if ($cards->count() == 0) {
            return "No Student Found";
        }

        $view = \View::make($this->folder(__METHOD__), compact('cards'));
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('temp'), 'mode' => 'utf-8', 'format' => 'A4-L', 'orientation' => 'P']);
        $mpdf->SetFont('jamesfajardo');
        $mpdf->SetTitle('Registration_card_' . $batch_id . '');
        $mpdf->WriteHTML(file_get_contents(public_path('registration_card_pdf_style.css')), 1);
        $mpdf->WriteHTML($view, 2);
        $mpdf->Output('registration_card_' . $batch_id . '', 'I');
        return 1;
    }

    /*
        This method will show list of batch which are not printed.
     */
    public function show_batch_list_for_reg_card_printing($site_token)
    {
        if (!in_array($site_token, explode(',', env('API_APP_SITE_KEYS'))))
            return "error";

        return O_VIEW_S_BATCH_REGCARD_PRINT::all();
    }


    /*
        This method will show list of batch which are already printed.
     */
    public function show_batch_list_for_reg_card_printed($site_token)
    {
        if (!in_array($site_token, explode(',', env('API_APP_SITE_KEYS'))))
            return "error";

        return O_VIEW_S_BATCH_REGCARD_PRINTED::all();
    }

    public function get_all_batch()
    {
        return O_VIEW_S_BATCH::all();
    }

    public function get_single_batch_detail($batch_id)
    {
        return O_VIEW_S_BATCH::where(['id' => $batch_id])->first();
    }

    public function reg_card_print_done($batch_id, $m_batch_id, $token, $site_token, $token2)
    {

        if (md5($batch_id . $m_batch_id) != $token || ($token2 != md5(date('ydmd'))) || !in_array($site_token, explode(',', env('API_APP_SITE_KEYS'))))
            return "error";

        O_BATCH::where(['id' => $batch_id])->update([
            'reg_card_printed' => 1
        ]);

        return "done";
    }

    public function get_deptartments()
    {
        return O_DEPARTMENTS::orderby('name')
            ->get();

    }

    public function get_batch_id_name($department_id)
    {
        return O_BATCH::select('id', 'batch_name')->where(['department_id' => $department_id])->orderBy('batch_name', 'asc')->get();
    }

    public function check_student($department_id, $batch_id, $reg_code, $roll_no, $phone_no)
    {
        $student = O_STUDENT::selectRaw("'%&^#@1'|| ID || '15411XY' || ID || '452aqz' AS TEMP, NAME, BLOOD_GROUP, EMAIL, PHONE_NO, DOB, BIRTH_PLACE, PARMANENT_ADD, MAILING_ADD, F_NAME, F_CELLNO, F_OCCU, M_NAME, M_CELLNO, M_OCCU, G_NAME, G_CELLNO, G_OCCU, E_NAME, E_CELLNO, E_OCCU, E_ADDRESS, E_RELATION,MARITAL_STATUS")->where(['department_id' => $department_id, 'batch_id' => $batch_id, 'reg_code' => $reg_code, 'roll_no' => $roll_no, 'phone_no' => $phone_no]);
        if ($student->count() > 0) {
            return $student->first();
        }
        return 'nf';
    }

    public function get_student_by_id($std_id)
    {
        $student = O_STUDENT::with('department', 'batch', 'relCampus')->selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO ,  GROUP_ID ,  BLOOD_GROUP ,  EMAIL ,  PHONE_NO ,  ADM_FRM_SL ,  RELIGION_ID ,  GENDER ,  DOB ,  BIRTH_PLACE ,  FG_MONTHLY_INCOME ,  PARMANENT_ADD ,  MAILING_ADD ,  F_NAME ,  F_CELLNO ,  F_OCCU ,  M_NAME ,  M_CELLNO ,  M_OCCU ,  G_NAME ,  G_CELLNO ,  G_OCCU ,  E_NAME ,  E_CELLNO ,  E_OCCU ,  E_ADDRESS ,  E_RELATION ,  EMP_ID ,  NATIONALITY ,  MARITAL_STATUS ,  ADM_DATE ,  CAMPUS_ID ,  STD_BIRTH_OR_NID_NO ,  FATHER_NID_NO ,  MOTHER_NID_NO")
            ->where('VERIFIED', 1)
            ->where(['id' => $std_id]);


        if ($student->count() > 0) {
            $std = $student->first();

            if (!$std) {
                return response()->json(['message' => 'Student Not Found'], 400);
            }
            $std->image = null;
            $batch_id = $std->batch_id;
            $semseter = O_SEMESTERS::where('batch_id', $batch_id)
                ->orderBy('semester', 'desc')
                ->first();

            $std->current_semester = $semseter ? $semseter->semester : 'NA';

            $std->group = '';
            if ($std->batch->group_id) {
                $std->group = DB::connection('oracle')->table('S_GROUP')
//            ->select(DB::raw('count(*) as user_count, status'))
                    ->where('id', $std->batch->group_id)
                    ->first();
            }

            $std->shift = O_SHIFT::where('id', $std->batch->shift_id)->first();

            return $std;
        }
        return 'nf';
    }

    public function get_student_by_batchid(int $batch_id)
    {

        /*//lemon
        try {
            $students = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ")
                ->where('VERIFIED', 1)
                ->where(['batch_id' => $batch_id])
                ->orderBy('roll_no')
                ->get();
            return response()->json(['data' => $students], 200);

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 401);
        }*/

        $students = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ")
            ->where('VERIFIED', 1)
            ->where(['batch_id' => $batch_id])
            ->orderBy('roll_no');


        if ($students->count() > 0) {
            return response()->json(['data' => $students->get()], 200);
        }
        return response()->json(['error' => 'No Student Found!'], 400);
    }

    public function get_student_by_reg_code($reg_code)
    {
        $student = O_STUDENT::selectRaw("ID ,  NAME ,  ROLL_NO ,  REG_CODE ,  PASSWORD ,  DEPARTMENT_ID ,  BATCH_ID ,  SHIFT_ID ,  YEAR ,  REG_SL_NO ,  GROUP_ID ,  BLOOD_GROUP ,  EMAIL ,  PHONE_NO ,  ADM_FRM_SL ,  RELIGION_ID ,  GENDER ,  DOB ,  BIRTH_PLACE ,  FG_MONTHLY_INCOME ,  PARMANENT_ADD ,  MAILING_ADD ,  F_NAME ,  F_CELLNO ,  F_OCCU ,  M_NAME ,  M_CELLNO ,  M_OCCU ,  G_NAME ,  G_CELLNO ,  G_OCCU ,  E_NAME ,  E_CELLNO ,  E_OCCU ,  E_ADDRESS ,  E_RELATION ,  EMP_ID ,  NATIONALITY ,  MARITAL_STATUS ,  ADM_DATE ,  CAMPUS_ID ,  STD_BIRTH_OR_NID_NO ,  FATHER_NID_NO ,  MOTHER_NID_NO")->with('department', 'batch')->where(['REG_CODE' => $reg_code])->first();

        if ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'mobile' => $student->phone_no,
                'roll' => $student->roll_no,
                'reg_code' => $student->reg_code,
                'father_name' => $student->f_name ?? 'No Father Name',
                'mother_name' => $student->m_name ?? 'No Mother Name',
                'department' => $student->department->name,
                'batch' => $student->batch->batch_name,
                'campus_id' => $student->campus_id,
                'session' => $student->batch->sess,
                'shift_id' => $student->shift_id,
            ];
        }
        return response()->json(['error' => 'No Student Found!'], 400);
    }

    public function student_account_info($ora_uid)
    {
        return O_CASHIN::where(['cashin.student_id' => $ora_uid])->with('purposePay')->get()->toArray();
    }


    public function students_account_info_summary(Request $request)
    {

        try {
            if (!$request->has('ora_uids')) {
                return request()->json(['message' => 'ora_ids not found'], 400);
            }

            if (count($request->ora_uids) == 0) {
                return request()->json(['message' => 'ora_ids not found'], 400);
            }

            $data = [];
            foreach ($request->ora_uids as $ora_uid)
                $data[$ora_uid] = O_CASHIN::get_student_account_info_summary($ora_uid);

            return $data;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage() . $exception->getTraceAsString());
            return response()->json(['error' => $exception->getMessage()], 400);
        }

    }

    public function student_account_info_summary($ora_uid)
    {

        try {
            return O_CASHIN::get_student_account_info_summary($ora_uid);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage() . $exception->getTraceAsString());
            return response()->json(['error' => $exception->getMessage()], 400);
        }

    }

    public function batchWiseAccountInfoNonCovid($batchId)
    {
        $stds = O_STUDENT::where('batch_id', (int)$batchId)->orderBy('roll_no')->get();

        if ($stds->count() == 0) {
            return response()->json(['message' => 'No studnet Found on this batch'], 400);
        }

        $studentInfoArray = [];

        foreach ($stds as $std) {
            try {
                $studentInfoArray [] = [
                    'id' => $std->id,
                    'phone_no' => $std->phone_no,
                    'f_cellno' => $std->f_cellno,
                    'm_cellno' => $std->m_cellno,
                    'g_cellno' => $std->g_cellno,
                    'e_cellno' => $std->e_cellno,
                    'name' => $std->name,
                    'reg_code' => $std->reg_code,
                    'roll_no' => $std->roll_no,
                    'summary' => O_CASHIN::get_student_account_info_summary_covid($std->id),
                ];

            } catch (\Exception $exception) {
                Log::error($exception->getMessage() . $exception->getTraceAsString());
                return response()->json(['error' => $exception->getMessage()], 400);
            }
        }
        return $studentInfoArray;


    }

    public function batchWiseAccountInfo($batchId)
    {
        $stds = O_STUDENT::where('batch_id', $batchId)->orderBy('roll_no')->get();
        $studentInfoArray = [];

        foreach ($stds as $std) {
            try {
                $studentInfoArray [] = [
                    'id' => $std->id,
                    'phone_no' => $std->phone_no,
                    'f_cellno' => $std->f_cellno,
                    'm_cellno' => $std->m_cellno,
                    'g_cellno' => $std->g_cellno,
                    'e_cellno' => $std->e_cellno,
                    'name' => $std->name,
                    'reg_code' => $std->reg_code,
                    'roll_no' => $std->roll_no,
                    'summary' => O_CASHIN::get_student_account_info_summary_covid($std->id),
                ];

            } catch (\Exception $exception) {
                Log::error($exception->getMessage() . $exception->getTraceAsString());
                return response()->json(['error' => $exception->getMessage()], 400);
            }
        }
        return $studentInfoArray;


    }

    public function get_accounts_info_for_a_batch_by_std_id($ora_std_id)
    {
        $batch_id = O_STUDENT::select('batch_id')->where(['id' => $ora_std_id])->first()->batch_id;//->column('batch_id');

        return O_BATCH::selectRaw('ID, SAID_FEE, COMMON_SCHOLARSHIP, NO_OF_SEMESTER ')->find($batch_id);
    }

    public function get_batch_info_by_id($batch_id)
    {
        $batch = O_BATCH::with('relDepartment')->find($batch_id);
        return $batch;
    }

    public function get_deptartment_info_by_id($department_id)
    {
        $department = O_DEPARTMENTS::find($department_id);
        return $department;
    }

    public function api_not_found()
    {
        return '404';
    }

    public function get_all_teacher($dept_short_code = '')
    {

        $dept_short_code = strtolower($dept_short_code);

        // return M_WP_EMP::distinct('emp_short_position')->selectRaw('emp_short_position')->get();
        return M_WP_EMP::selectRaw('id, name, position, dept, email1, mno1, concat("https://web.diu.ac/ephoto/", pphoto) profilepic, merit')
            ->where('activestatus', 1)
            ->where(function ($q) use ($dept_short_code) {

                $t_shortcode_array = [
                    'cse' => ['T_CSE%', 'C_CSE%', 'D_CSE', 'DB_G'],
                    'eete' => ['T_eete%', 'C_eete%', 'DB_G', 'D_CSE'],
                    'law' => ['T_LAW%', 'C_LAW%', 'CO_LAW_G'],
                    'eng' => ['T_eng%', 'C_eng%', 'T_ILID'],
                    'pha' => ['T_pha%', 'C_pha%', 'D_CSE', 'DB_G'],
                    'soc' => ['T_soc%', 'C_soc%'],
                    'bba' => ['T_bba%', 'C_bba%', 'CO_BBA_G', 'DB_B', 'REG'],
                    'civil' => ['T_civil%', 'C_civil%', 'CO_CIVIL'],
                ];

                if (strlen($dept_short_code) > 0 && array_key_exists($dept_short_code, $t_shortcode_array)) {
                    foreach ($t_shortcode_array[$dept_short_code] as $code) {
                        $q->orwhere('emp_short_position', 'like', $code);
                    }
                } else {
                    $q->where('emp_short_position', 'like', 'T\_%');
                    $q->orwhere('emp_short_position', 'like', 'C\_%');
                }

            })
            ->orderBy('merit', 'DESC')
            ->get();
    }


    /**
     *   commenting this function after discuss with Masud vai and Mesbaul;
     */

    public function admission_on_going_batch()
    {
        return DB::connection('oracle')->select('
        SELECT
            b . ID,
            b . ID AS "VIEW_ALL",
            d . name AS "DEPARTMENT",
            B . BATCH_NAME,
            d . SHORT_CODE AS "SHORTCODE",
            b . department_id as "dept_id",
            b . shift_id as "shift_id",
            b . group_id as "group_id",
            g . name AS "GROUP",
            s . name AS "SHIFT",
            c . name AS CAMPUS,
            b . NO_OF_SEMESTER AS "NO_OF_SEMESTER",
            b . DURATION_OF_SEM_M AS "DURATION_OF_SEMESTER_IN_MONTH",
            b . NO_SEAT AS "NO_OF_SEAT",
            (B . NO_SEAT - (
              select count(batch_id) as fill FROM ARIF.STUDENT WHERE BATCH_ID = B . ID AND Verified = 1)
            ) AS "Available_Seat",
            b . SESS AS "SESSION",
            b . VALID_D_IDCARD AS "VALID_DATE_OF_ID_CARD",
            b . CLASS_STR_DATE AS "CLASS_START_DATE",
            b . LAST_DATE_OF_ADM AS "LAST_DATE_OF_ADMISSION",
            (
              select count(batch_id) as fill FROM ARIF.STUDENT WHERE BATCH_ID = B . ID AND VERIFIED = 1
            ) AS FILL ,
            b . campus_id AS "CAMPUS_ID"
            from ARIF.S_BATCH b, ARIF.s_department d, ARIF.s_group g, ARIF.shift s,ARIF.campus c
            where b . active_status = 1
            and b . department_id = d . id
            and b . shift_id = s . id
            and g . id = b . group_id
            and b . campus_id = c . id
and nvl(b . LAST_DATE_OF_ADM, sysdate + 1) >= sysdate
            and (B . NO_SEAT -
         (
              select count(batch_id) as fill FROM ARIF.STUDENT WHERE BATCH_ID = B . ID AND Verified = 1
            ))
            > 0'
        );
    }

    public function religion()
    {
        return O_RELIGION::all();
    }

    public function src_by_reg($reg_no)
    {
        return O_STUDENT::selectRaw('
            id,
            name,
            roll_no,
            reg_code,
            department_id,
            batch_id,
            shift_id,
            year,
            reg_sl_no,
            group_id,
            blood_group,
            email,
            phone_no,
            adm_frm_sl,
            religion_id,
            gender,
            dob,
            birth_place,
            fg_monthly_income,
            parmanent_add,
            mailing_add,
            f_name,
            f_cellno,
            f_occu,
            m_name,
            m_cellno,
            m_occu,
            g_name,
            g_cellno,
            g_occu,
            e_name,
            e_cellno,
            e_occu,
            e_address,
            e_relation,
            emp_id,
            nationality,
            marital_status,
            filename,
            mimetype,
            verified,
            id_card_given,
            id_given_date,
            id_receiver,
            adm_date,
            campus_id,
            std_birth_or_nid_no,
            father_nid_no,
            mother_nid_no,
            e_exam_name1,
            e_group1,
            e_roll_no_1,
            e_passing_year1,
            e_ltr_grd_tmark1,
            e_div_cls_cgpa1,
            e_board_university1,
            e_exam_name2,
            e_group2,
            e_roll_no_2,
            e_passing_year2,
            e_ltr_grd_tmark2,
            e_div_cls_cgpa2,
            e_board_university2,
            e_exam_name3,
            e_group3,
            e_roll_no_3,
            e_passing_year3,
            e_ltr_grd_tmark3,
            e_div_cls_cgpa3,
            e_board_university3,
            e_exam_name4,
            e_group4,
            e_roll_no_4,
            e_passing_year4,
            e_ltr_grd_tmark4,
            e_div_cls_cgpa4,
            e_board_university4,
            reg_card_sl,
            session_name,
            cgpa
        ')->with('department', 'batch')->where('REG_CODE', 'like', '%' . $reg_no . '%')->where('VERIFIED', 1)->get();
    }

    public function student_by_id($id)
    {
        $id = (int)$id;

        return O_STUDENT::selectRaw('
            id,
            name,
            roll_no,
            reg_code,
            department_id,
            batch_id,
            shift_id,
            year,
            reg_sl_no,
            group_id,
            blood_group,
            email,
            phone_no,
            adm_frm_sl,
            religion_id,
            gender,
            dob,
            birth_place,
            fg_monthly_income,
            parmanent_add,
            mailing_add,
            f_name,
            f_cellno,
            f_occu,
            m_name,
            m_cellno,
            m_occu,
            g_name,
            g_cellno,
            g_occu,
            e_name,
            e_cellno,
            e_occu,
            e_address,
            e_relation,
            emp_id,
            nationality,
            marital_status,
            filename,
            mimetype,
            verified,
            id_card_given,
            id_given_date,
            id_receiver,
            adm_date,
            campus_id,
            std_birth_or_nid_no,
            father_nid_no,
            mother_nid_no,
            e_exam_name1,
            e_group1,
            e_roll_no_1,
            e_passing_year1,
            e_ltr_grd_tmark1,
            e_div_cls_cgpa1,
            e_board_university1,
            e_exam_name2,
            e_group2,
            e_roll_no_2,
            e_passing_year2,
            e_ltr_grd_tmark2,
            e_div_cls_cgpa2,
            e_board_university2,
            e_exam_name3,
            e_group3,
            e_roll_no_3,
            e_passing_year3,
            e_ltr_grd_tmark3,
            e_div_cls_cgpa3,
            e_board_university3,
            e_exam_name4,
            e_group4,
            e_roll_no_4,
            e_passing_year4,
            e_ltr_grd_tmark4,
            e_div_cls_cgpa4,
            e_board_university4,
            reg_card_sl,
            session_name,
            cgpa
        ')->with('department', 'batch')->where('id', $id)->where('VERIFIED', 1)->first();
    }

    /**
     *       admission function disable after discuss with Masud vai and Mesbaul
     */
    /*
        public function admission(Request $request)
        {
            $admission_on_going = $this->admission_on_going_batch();
            $collection = collect($admission_on_going);
            $batch = $collection->where('id', $request->input('admission_batch_id'))->first();
            $admission_array = [
                'NAME' => $request->input('name'),
                // 'ROLL_NO' => 9999999,
                // 'REG_CODE' => 9999999,
                'PASSWORD' => '123456',
                'DEPARTMENT_ID' => $batch->dept_id,
                'BATCH_ID' => $batch->id,
                'SHIFT_ID' => $batch->shift_id,
                'YEAR' => date('Y'),
                'GROUP_ID' => $batch->group_id,
                'BLOOD_GROUP' => $request->input('blood_group'),
                'EMAIL' => $request->input('email'),
                'PHONE_NO' => $request->input('bd_mobile'),
                'RELIGION_ID' => $request->input('religion'),
                'GENDER' => $request->input('sex'),
                'DOB' => date('Y-m-d', strtotime($request->input('dob'))),
                'BIRTH_PLACE' => $request->input('place_of_birth'),
                'FG_MONTHLY_INCOME' => $request->input('fg_monthly_income'),
                'PARMANENT_ADD' => $request->input('permanent_address'),
                'MAILING_ADD' => $request->input('present_address'),
                'F_NAME' => $request->input('father_name'),
                'F_CELLNO' => $request->input('father_mobile'),
                'M_NAME' => $request->input('mother_name'),
                'M_CELLNO' => $request->input('mother_mobile'),
                'G_NAME' => $request->input('guardian_name'),
                'G_CELLNO' => $request->input('guardian_mobile'),
                'E_NAME' => $request->input('emergency_name'),
                'E_CELLNO' => $request->input('emergency_mobile'),
                'EMP_ID' => 1,
                'NATIONALITY' => $request->input('present_nationality'),
                'MARITAL_STATUS' => $request->input('marital_status'),
                'ADM_DATE' => date('Y-m-d'),
                'CAMPUS_ID' => $batch->campus_id,
                'STD_BIRTH_OR_NID_NO' => $request->input('passport_no'),
                'E_EXAM_NAME1' => $request->input('o_name_of_exam'),
                'E_GROUP1' => $request->input('o_group'),
                'E_ROLL_NO_1' => $request->input('o_roll_no'),
                'E_PASSING_YEAR1' => $request->input('o_year_of_passing'),
                'E_LTR_GRD_TMARK1' => $request->input('o_letter_grade'),
                'E_DIV_CLS_CGPA1' => $request->input('o_cgpa'),
                'E_BOARD_UNIVERSITY1' => $request->input('o_board'),
                'E_EXAM_NAME2' => $request->input('t_name_of_exam'),
                'E_GROUP2' => $request->input('t_group'),
                'E_ROLL_NO_2' => $request->input('t_roll_no'),
                'E_PASSING_YEAR2' => $request->input('t_year_of_passing'),
                'E_LTR_GRD_TMARK2' => $request->input('t_letter_grade'),
                'E_DIV_CLS_CGPA2' => $request->input('t_cgpa'),
                'E_BOARD_UNIVERSITY2' => $request->input('t_board'),
                'E_EXAM_NAME3' => $request->input('th_name_of_exam'),
                'E_GROUP3' => $request->input('th_group'),
                'E_ROLL_NO_3' => $request->input('th_roll_no'),
                'E_PASSING_YEAR3' => $request->input('th_year_of_passing'),
                'E_LTR_GRD_TMARK3' => $request->input('th_letter_grade'),
                'E_DIV_CLS_CGPA3' => $request->input('th_cgpa'),
                'E_BOARD_UNIVERSITY3' => $request->input('th_board'),
                'E_EXAM_NAME4' => $request->input('fo_name_of_exam'),
                'E_GROUP4' => $request->input('fo_group'),
                'E_ROLL_NO_4' => $request->input('fo_roll_no'),
                'E_PASSING_YEAR4' => $request->input('fo_year_of_passing'),
                'E_LTR_GRD_TMARK4' => $request->input('fo_letter_grade'),
                'E_DIV_CLS_CGPA4' => $request->input('fo_cgpa'),
                'E_BOARD_UNIVERSITY4' => $request->input('fo_board'),
                'ADM_FRM_SL' => $request->input('adm_frm_no')
            ];
            $email = $request->input('email');
            $adm_frm_no = $request->input('adm_frm_no');

            if (O_STUDENT::where(['EMAIL' => $email, 'ADM_FRM_SL' => $adm_frm_no])->exists()) {

                $create = O_STUDENT::where(['EMAIL' => $email, 'ADM_FRM_SL' => $adm_frm_no])->update($admission_array);
                $response = O_STUDENT::where(['EMAIL' => $email, 'ADM_FRM_SL' => $adm_frm_no])->first();
            }
            else
            {
                $response = O_STUDENT::create($admission_array);
            }

            if(!empty($response))
            {
                return response()->json($response, 201);
            }
            return response()->json(NULL, 400);
        }
    */


    /**
     *    bellow code commenting after discussion with Mesbaul
     */
    /*
        public function get_student_by_adm_frm_no( $adm_frm_no )
        {
            $response = O_STUDENT::where('adm_frm_no', $adm_frm_no)->first();
            if (!empty($response)) {
                return response()->json($response, 200);
            }
            return response()->json(NULL, 400);
        }
    */
    public function all_employees()
    {
        return M_WP_EMP::selectRaw('id, name, position, dept, email1, mno1, concat("https://web.diu.ac/ephoto/",pphoto) profilepic, merit, oadd  as office_address')->where('activestatus', 1)->orderBy('merit', 'DESC')->get();
    }

    public function get_admission_team()
    {
        return M_WP_EMP::selectRaw('id, name, position, dept, email1, mno1, concat("https://web.diu.ac/ephoto/",pphoto) profilepic, merit, oadd  as office_address')->where('activestatus', 1)->where('emp_short_position', 'like', '%O_INF%')->orwhere('emp_short_position', 'H_INF')->orderBy('merit', 'DESC')->get();
    }

    public static function get_batch_mate($std_id)
    {
        try {
            $std_id = (int)$std_id;

            $student = O_STUDENT::find($std_id);

            if (!$student) throw new StudentNotFoundExceptions('Student Not Found');

            $mates = O_STUDENT::select('ID', 'NAME', 'ROLL_NO', 'REG_CODE')
                ->where('batch_id', $student->batch_id)
                ->where('id', '<>', $student->id)
                ->where('VERIFIED', '1')
                ->orderBy('ROLL_NO')
                ->get();

            if ($mates->count() == 0) {
                return response()->json(['message' => 'No Batch mate Found'], 400);
            }

            return response()->json($mates, 200);
        } catch (StudentNotFoundExceptions $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }

    }

    public function get_banks()
    {
        $banks = O_BANK::orderBy('name', 'asc')->get();
        if ($banks->count() == 0) {
            return response()->json(['message' => 'No banks found'], 400);
        }
        return response()->json($banks, 200);
    }


    public function get_bank($id)
    {
        $banks = O_BANK::where('id', $id)->first();

        if (!$banks) {
            return response()->json(['message' => 'No banks found'], 400);
        }
        return response()->json($banks, 200);
    }

    public function getImprovementExamRoutine(int $std_id, int $examSheduleId = 0)
    {

//        if( ! O_IMP_EXAM_SCHEDULE::find($examSheduleId)){
//            return response()->json(['error'=> 'Exam Schedule Not Exists!'], 400);
//        }

        $examSheduleId = O_IMP_EXAM_SCHEDULE::max('id');

        $impRequests = O_IMP_REQUEST::with('relImpRequestCourse')->where(['std_id' => $std_id, 'ies_id' => $examSheduleId, 'payment_status' => O_IMP_REQUEST::PAID])->get();

        if (!$impRequests) {
            return response()->json(['error' => 'You have not applied!'], 400);
        }

        $stdObj = O_STUDENT::find($std_id);
        if (!$stdObj) {
            return response()->json(['error' => 'No Student Found!'], 400);
        }

        foreach ($impRequests as $key => $request_courses) {
            foreach ($request_courses->relImpRequestCourse as $key => $course) {
                $selfCourseArray[] = [
                    'course_id' => $course->course_id,
                    'course_type' => $course->type
                ];
            }
        }

        $routine = O_IMP_EXIM_ROUTINE::where(['ies_id' => $examSheduleId, 'campus_id' => $stdObj->campus_id, 'department_id' => $stdObj->department_id, 'approve_status' => O_IMP_EXIM_ROUTINE::APPROVED])->first();

        if (!$routine) {
            return response()->json(['error' => 'Routine not published!'], 400);
        }

        if (!isset($selfCourseArray)) {
            return response()->json(['error' => 'Exam Routine not found!'], 400);
        }

        foreach ($selfCourseArray as $key => $course) {
            $routineDetail = O_IMP_EXIM_ROUTINE_DETAIL::with('relShiftDetail', 'relCourse')->where(['imp_xm_routine_id' => $routine->id, 'course_id' => $course['course_id'], 'course_exam_type' => $course['course_type']])->where('exam_date', '>=', time())->first();

            if (!isset($routineDetail->relCourse)) {
                return response()->json(['error' => 'Course not found!'], 400);
            }

            $result['data'][] = [
                'id' => $routineDetail->relCourse->id,
                'name' => $routineDetail->relCourse->name,
                'code' => $routineDetail->relCourse->code,
                'course_exam_type' => $routineDetail->course_exam_type,
                'exam_date' => date('d M, Y', $routineDetail->exam_date),
                'time_duration' => $routineDetail->relShiftDetail->from_time . ' - ' . $routineDetail->relShiftDetail->to_time,
                'place' => $routineDetail->relShiftDetail->place,
            ];
        }
        return $result;

        /*O_IMP_EXIM_ROUTINE_DETAIL*/
//        $routineDetail = [];
//        foreach ($selfCourseArray as $id => $type)
//        $routineDetail = O_IMP_EXIM_ROUTINE_DETAIL::with(['relRoutineParent' =>function($q) use($stdObj){
//            $q->where('department_id' , $stdObj->department_id);
//        }, 'relShiftDetail', 'relCourse'])
//            ->where(['course_id' =>  $id, 'course_exam_type'=> $type])
//            ->where('exam_date', '>=', time())
//            ->orderBy('id')
//            ->get();
//
//        return $routineDetail;

        /*$routineDetail = O_IMP_EXIM_ROUTINE_DETAIL::with(['relRoutineParent' => function($q) use ($stdObj){
                $q->where('DEPARTMENT_ID' , $stdObj->department_id);
             }, 'relShiftDetail', 'relCourse'])
            ->where(function($q) use($selfCourseArray){
               foreach ($selfCourseArray as $id=> $type){
                   $q->orwhere(function($q) use($id, $type){
                       $q->where(['course_id' =>  $id, 'course_exam_type'=> $type]);
                   });
               }

           })
            ->where('exam_date', '>=', time())
            ->orderBy('id')
            ->get();*/
//
//        $routineDetail = O_IMP_EXIM_ROUTINE_DETAIL::with('relRoutineParent', 'relShiftDetail', 'relCourse')
//            ->where(function($q) use($selfCourseArray){
//
//               foreach ($selfCourseArray as $id=> $type){
//                   $q->where(['course_id' =>  $id, 'course_exam_type'=> $type]);
////                   {
////                       $q->where(['course_id' =>  $id, 'course_exam_type'=> $type]);
////                   });
//               }
//
//           })
//            ->where('exam_date', '>=', time())
//            ->get();

        /* if ( ! isset($routineDetail->first()->relRoutineParent) || ! isset($routineDetail->first()->relShiftDetail->relShiftParent)){
             return response()->json(['error'=> 'No Exam Routine Found!'],400);
         }

         if( $routineDetail->first()->relRoutineParent->isNotApproved()  || $routineDetail->first()->relShiftDetail->relShiftParent->isNotApproved()){
             return response()->json(['error'=> 'No Exam Routine Found!'],400);
         }

         return ExamRoutineResource::collection($routineDetail);*/

    }

    public function getImprovementExamSchedule()
    {
        return ['data' => O_IMP_EXAM_SCHEDULE::get()];

    }

    public function attendance_departments(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|exists:mysql.wp_emp,email1',
            ]
        );

//        $email = 'tahzib.cse@diu-bd.net';//$request->email;
//        $email = 'rahman.cse@diu-bd.net';//
        $email = $request->email;
        $employee = M_WP_EMP::where(['email1' => $email])->first();
        if (empty($employee)) {
            return response()->json(['error' => 'Email not found'], 400);
        }

        $departments = O_COURSE_ALLOCATION_INFO::select('S_DEPARTMENT.ID AS DEPARTMENT_ID', 'S_DEPARTMENT.NAME AS DEPARTMENT_NAME', 'S_BATCH.ID AS BATCH_ID', 'S_BATCH.BATCH_NAME', 'SEMESTER_INFO_FOR_RESULT.SEMESTER', 'SIFR_ID', 'COURSE.NAME', 'COURSE.CODE', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'COURSE.DEPARTMENT_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where('TEACHER_ID', $employee->id)->where('MARK_APPROVE_STATUS', '<', 2)->orderBy('DEPARTMENT_NAME', 'ASC')->orderBy('BATCH_NAME', 'ASC')->get();
        if (!empty($departments) && $departments->count() > 0) {
            $array = [];
            foreach ($departments as $key => $department) {
                $array[$department->department_id . $department->batch_id . $department->semester . $department->course_id] = $department;
            }
            return ['data' => $array];
        }
        return NULL;
    }

    public function attendance_students(Request $request)
    {
        $request->validate(
            [
                'department_id' => 'required|integer|exists:oracle.s_department,id',
                'batch_id' => 'required|integer|exists:oracle.s_batch,id',
            ]
        );
        $department_id = $request->department_id;
        $batch_id = $request->batch_id;


        $students = O_STUDENT::select('ID', 'NAME', 'ROLL_NO', 'REG_CODE')->where([
            'DEPARTMENT_ID' => $department_id, 'BATCH_ID' => $batch_id, 'VERIFIED' => 1,
        ])->orderBy('ROLL_NO', 'ASC')->get();

        if (!empty($students)) {
            return ['data' => $students];
        }
        return NULL;
    }

    public function updateStudentsActualFeeAndNumberOfSemester(Request $request)
    {
        $request->validate(
            [
                'students.*' => 'required|integer',
                'actual_fee' => 'required|integer',
                'no_of_semester' => 'required|integer',
            ]
        );

        $update = O_STUDENT::whereIn('id', $request->students)->update([
            'ACTUAL_FEE' => $request->actual_fee,
            'NO_OF_SEMESTER' => $request->no_of_semester,
        ]);
        if (!empty($update)) {
            return response()->json(['success' => 'Update Successful'], 200);
        }
        return response()->json(['error' => 'Update Failed'], 400);
    }

    public function updateCtStudentsActualFeeAndOthers(Request $request)
    {
        $request->validate(
            [
                'students.*' => 'required|integer',
                'actual_fee' => 'required|integer',
                'no_of_semester' => 'required|integer',
                'payment_from_semester' => 'required|integer',
            ]
        );

        $update = O_STUDENT::whereIn('id', $request->students)->update([
            'ACTUAL_FEE' => $request->actual_fee,
            'NO_OF_SEMESTER' => $request->no_of_semester,
            'PAYMENT_FROM_SEMESTER' => $request->payment_from_semester,
        ]);
        if (!empty($update)) {
            return response()->json(['success' => 'Update Successful'], 200);
        }
        return response()->json(['error' => 'Update Failed'], 400);
    }

    public function applyExtraFeeOnStudents(Request $request)
    {

//        $validator = Validator::make($request->all(), [
//            'id' => 'required|integer',
//            'purpose_pay' => 'required|integer',
//            'payment_method' => 'required',
//            'amount' => 'required',
//            'transection_id' => 'required',
//            'bankdate' => 'required',
//            'fromMobileNumber' => 'required',
//        ]);
//
//        if($validator->fails()){
//            return response($validator->messages(), 400);
//        }

        $request->validate(
            [
                'students.*' => 'required|integer',
                'extra_fee' => 'required|integer',
                'purpose_id' => 'required|integer',
                'office_email' => 'required|email',
                'note' => 'required',
            ]
        );

        try {

            $employee = O_EMP::where('official_email', $request->office_email)->first();
            if (!$employee) {
                return response()->json(['message' => 'Employee is not found in ERP'], 400);
            }

            $extra_fee = $request->extra_fee;
            $purpose_pay_id = $request->purpose_id;// Others
            $today = date('Y/m/d'); // 11/13/2016 = m/d/y format
            $note = $request->note . " . Charge From Extra Fee Charge Module";

            DB::beginTransaction();

            foreach ($request->students as $studentId) {

                $receipt_no = $purpose_pay_id . '-' . $studentId;

                throw_if(O_CASHIN::where('receipt_no', $receipt_no)->count() > 0, new \Exception("Duplicate Receipt Found!"));

                $receive_by = $employee->id;

                $cachsin = new O_CASHIN();
                $cachsin->purpose_pay_id = $purpose_pay_id;
                $cachsin->amount = -$extra_fee;
                $cachsin->bank_id = 0;// 0 = NA
                $cachsin->note = $note;
                $cachsin->student_id = $studentId;
                $cachsin->receipt_no = $receipt_no;
                $cachsin->cashorbank = 0; //  1 = cash, 0 = Bank
                $cachsin->receive_by = $receive_by;
                $cachsin->date_bank = $today;
                $cachsin->pay_date = $today;
                $cachsin->varified_by = $receive_by;
                $cachsin->is_varified = 1;
                $cachsin->save();

            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage()], 400);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        DB::commit();

        return response()->json(['message' => 'Extra Fee Applied'], 200);

    }

    public function download_regular_admit_card(Request $request)
    {
        $ora_uid = $request->ora_uid;
        //$ora_uid = 6555;
        $studentObj = O_STUDENT::select('id', 'name', 'roll_no', 'reg_code', 'f_name', 'm_name', 'department_id', 'batch_id', 'year')->with('department', 'batch')->where('id', $ora_uid)->first();

        if (empty($studentObj)) {
            return response()->json(['error' => 'Student not found in ERP'], 400);
        }

        $student_array = [];
        $allocated_courses = [];
        $department_id = $studentObj->department_id;
        $batch_id = $studentObj->batch_id;
        $semester = "No assigned semester";

        $semester = O_SEMESTERS::with(['allocatedCourses' => function ($query) {
            $query->with('course')->get();
        }])->where(['department_id' => $department_id, 'batch_id' => $batch_id])->latest('semester')->first();

        if (!empty($semester) && !empty($semester->allocatedCourses)) {
            $semester_id = $semester->id;
            foreach ($semester->allocatedCourses as $key => $allocated_course) {
                $allocated_courses[] = [
                    'id' => $allocated_course->course->id,
                    'name' => $allocated_course->course->name,
                    'code' => $allocated_course->course->code,
                ];
            }
            $semester = $semester->semester;
        } else {
            return response()->json(['error' => 'No semester found in RMS'], 400);
        }

        $year = substr(date('Y'), -2);
        $student_array = [
            'id' => $studentObj->id,
            'name' => $studentObj->name,
            'roll' => $studentObj->roll_no,
            'reg_code' => $studentObj->reg_code,
            'father_name' => $studentObj->f_name ?? 'No Father Name',
            'mother_name' => $studentObj->m_name ?? 'No Mother Name',
            'department' => $studentObj->department->name,
            'batch' => $studentObj->batch->batch_name,
            'session' => $studentObj->batch->sess,
            'semester' => $semester,
            'allocated_course' => $allocated_courses,
            'payment_status' => 'PAID',
            'admit_sl_no' => $year . $semester_id . $studentObj->id
        ];
        return response()->json($student_array, 200);
    }

    public function getPurposePay()
    {
        return O_PURPOSE_PAY::get();
    }


    public function getPurposePayById($id)
    {
        $purpose_pay = O_PURPOSE_PAY::where('id', $id)->first();

        if (!$purpose_pay) {
            return response()->json(['message' => 'No purpose pay found'], 400);
        }
        return response()->json($purpose_pay, 200);
    }

    public function save_general_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|integer',
            'bank_payment_date' => 'required|date_format:d-m-Y',
            'receipt_no' => 'required',
            'total_payable' => 'required|numeric',
            'student_id' => 'required',
            'employee_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $purpose_id = $request->purpose_id;
        $total_payable = $request->total_payable;
        $bank_id = $request->bank_id;
        $note = $request->note;
        $receipt_no = trim($request->receipt_no);
        $employee_email = $request->employee_email;
        $student_id = $request->student_id;
        $bank_payment_date = date('Y-m-d', strtotime($request->bank_payment_date));
        $today = date('Y-m-d');

        if (strtotime($receipt_no) !== 'bot' && O_CASHIN::where('receipt_no', $receipt_no)->get()->count() > 0) {
            return response()->json(['error' => 'Duplicate Receipt No. Found!'], 400);
        }

        $employee = O_EMP::where('official_email', $employee_email)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee is not found in ERP'], 400);
        }
        $receive_by = $employee->id;

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = $purpose_id;
            $cachsin->amount = $total_payable;
            $cachsin->bank_id = $bank_id;
            $cachsin->note = 'Total Amount: ' . $total_payable . ' TK. VIA CMS. ' . $note;
            $cachsin->student_id = $student_id;
            $cachsin->receipt_no = $receipt_no;
            $cachsin->cashorbank = $bank_id == 0 ? 1 : 0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = '' . $bank_payment_date . ''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = '' . $today . '';
            $cachsin->varified_by = $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json($cachsin, 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }


    public function save_moblie_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'purpose_pay' => 'required|integer',
            'payment_method' => 'required',
            'amount' => 'required',
            'transection_id' => 'required',
            'bankdate' => 'required',
            'fromMobileNumber' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $amount = $request->amount;
        $bankdate = $request->bankdate;// : cooooooooooooomming "2020-05-10"​​
        $fromMobileNumber = $request->fromMobileNumber; //: "99999"        ​​
        $payment_method = $request->payment_method; // "7"        ​​
        $purpose_pay = $request->purpose_pay;//: "5"
        //$transection_id = $request->transection_id;

        $note = $request->note;
        $office_email = $request->office_email;


        $bank_payment_date = date('Y/m/d', strtotime($bankdate));
        $today = date('Y/m/d');


        if (O_CASHIN::where('receipt_no', $request->transection_id)->count() > 0) {
            return response()->json(['error' => 'Transaction ID exists!'], 400);
        }

        $employee = O_EMP::where('official_email', $office_email)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee is not found in ERP'], 400);
        }
        $receive_by = $employee->id;

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = $purpose_pay;
            $cachsin->amount = $amount;
            $cachsin->bank_id = $payment_method;
            $cachsin->note = 'Total Amount: ' . $amount . ' TK. MANUAL INPUT VIA CMS. Note: ' . $note . ', from:' . $fromMobileNumber;
            $cachsin->student_id = $request->id;
            $cachsin->receipt_no = $request->transection_id;
            $cachsin->cashorbank = 0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = '' . $bank_payment_date . ''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = '' . $today . '';
            $cachsin->varified_by = $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json($cachsin, 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }

    public function save_covid_discount(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'std_id' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $std_id = $request->std_id;
        $amount = $request->amount;

        $note = "5th covid discount of " . $std_id;
        $office_email = $request->office_email;


        $bank_payment_date = $today = date('Y/m/d');

        $employee = O_EMP::where('official_email', $office_email)->first();
        if (!$employee) {
            return response()->json('Employee is not found in ERP', 400);
        }
        $receive_by = $employee->id;

        $receipt_no = 'covid19-5-' . trim($request->std_id);

        if (O_CASHIN::where('receipt_no', $receipt_no)->exists()) {
            return response()->json('covid19 SCHOLARSHIP already given', 400);
        }

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = 25; // 25 = waiver
            $cachsin->amount = $amount;
            $cachsin->bank_id = 0;
            $cachsin->note = $note;
            $cachsin->student_id = $request->std_id;
            $cachsin->receipt_no = $receipt_no;
            $cachsin->cashorbank = 0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = '' . $bank_payment_date . ''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = '' . $today . '';
            $cachsin->varified_by = $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json($cachsin, 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }


    public function save_student_scholarship_as_liaison_officer(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'std_id' => 'required|integer',
            'amount' => 'required|numeric',
            'receipt_no' => 'required',
            'office_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $std_id = $request->std_id;
        $amount = $request->amount;
        $office_email = $request->office_email;

        $note = "Existing Student Admission Scholarship From CMS";


        $bank_payment_date = $today = date('Y/m/d');

        $employee = O_EMP::where('official_email', $office_email)->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found in ERP'], 400);
        }

        $receive_by = $employee->id;

        $receipt_no = $request->receipt_no;

        if (O_CASHIN::where('receipt_no', $receipt_no)->exists()) {
            return response()->json(['message' => 'Receipt No. already exists'], 400);
        }

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = 2; // 2 = scholarship
            $cachsin->amount = $amount;
            $cachsin->bank_id = 0;
            $cachsin->note = $note;
            $cachsin->student_id = $request->std_id;
            $cachsin->receipt_no = $receipt_no;
            $cachsin->cashorbank = 0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = '' . $bank_payment_date . ''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = '' . $today . '';
            $cachsin->varified_by = $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json($cachsin, 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Something Went Wrong!'], 400);
        }

    }


    public function importTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'purpose_pay' => 'required|integer',
            'payment_method' => 'required',
            'amount' => 'required',
            'transection_id' => 'required',
            'bankdate' => 'required',
            'fromMobileNumber' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 400);
        }

        $amount = $request->amount;
        $bankdate = $request->bankdate;// : cooooooooooooomming "2020-05-10"​​
        $fromMobileNumber = $request->fromMobileNumber; //: "99999"        ​​
        $payment_method = $request->payment_method; // "7"        ​​
        $purpose_pay = $request->purpose_pay;//: "5"
        //$transection_id = $request->transection_id;

        $note = $request->note;
        $office_email = $request->office_email;


        $bank_payment_date = date('Y/m/d', strtotime($bankdate));
        $today = date('Y/m/d');

        if (O_CASHIN::where('receipt_no', $request->transection_id)->count() > 0) {
            return response()->json(['error' => 'Transaction ID exists!'], 400);
        }

        $employee = O_EMP::where('official_email', $office_email)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee is not found in ERP'], 400);
        }
        $receive_by = $employee->id;

        $payment_method_id = 0;

        if ($payment_method == 'bkash') {
            $payment_method_id = 7;
        } elseif ($payment_method == 'rocket') {
            $payment_method_id = 8;
        } elseif ($payment_method == 'nagad' || $payment_method == 'nogod') {
            $payment_method_id = 9;
        } else {
            return response()->json(['error' => 'Payment Method not valid!'], 400);
        }

        try {
            $cachsin = new O_CASHIN();
            $cachsin->purpose_pay_id = $purpose_pay;
            $cachsin->amount = $amount;
            $cachsin->bank_id = $payment_method_id;
            $cachsin->note = 'Total Amount: ' . $amount . ' TK. VIA CMS. ' . $note . ', from:' . $fromMobileNumber;
            $cachsin->student_id = $request->id;
            $cachsin->receipt_no = $request->transection_id;
            $cachsin->cashorbank = 0; //  1 = cash, 0 = Bank
            $cachsin->receive_by = $receive_by;
            $cachsin->date_bank = '' . $bank_payment_date . ''; // 11/13/2016 = m/d/y format
            $cachsin->pay_date = '' . $today . '';
            $cachsin->varified_by = $receive_by;
            $cachsin->is_varified = 1;
            $cachsin->save();

            return response()->json($cachsin, 200);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => $ex->getMessage()], 400);
        }

    }


    public function getStudentByRegcodePart($txid, $regcodepart = null)
    {

        if (O_CASHIN::where('receipt_no', $txid)->count() > 0) {
            return response()->json(['message' => 'Transection Id already exist.'], 400);
        }

        $students = O_STUDENT::where('reg_code', 'like', "%" . $regcodepart . "%")->get();

        if ($students->count() == 0)
            return response()->json(['message' => 'No Student Found'], 400);

        if ($students->count() > 2)
            return response()->json(['message' => 'found more than one sudent'], 400);
        $std = $students->first();
        unset($std->image);
        return $std;
    }

    public function getStudentByRegcodePartForManualInput($regcodepart)
    {

        $students = O_STUDENT::where('reg_code', 'like', "%" . $regcodepart . "%")->get();

        if ($students->count() == 0)
            return response()->json(['message' => 'No Student Found'], 400);

        if ($students->count() > 2)
            return response()->json(['message' => 'found more than one sudent'], 400);
        $std = $students->first();
        unset($std->image);
        return $std;
    }

    public function getRefStudent(int $type = 0)
    {
//		$type = null;
        if ($type > 3) {

            return response()->json(['error' => 'Ref Type Not Valid'], 400);
        }

        $stds = O_STUDENT::with(['department', 'batch'])
            ->where('refereed_by_parent_id', $type)
            ->where('verified', 1)
            ->where('id_card_given', '!=', 1)
            ->get();

        if ($stds->count() == 0) {
            return response()->json(['error' => 'No Student Found'], 400);
        }
        $stds->transform(function ($i) use ($type) {
            unset($i->image);
            $i->adm_fee = O_CASHIN::where('student_id', $i->id)->first()->amount;

            $i->admissionOfficer = O_EMP::find($i->emp_id);

            if ($type == 3) { // 3 = ref by student. 2=ref by liaison officer

                $i->admittedByStd = O_STUDENT::with('department', 'batch')->where('reg_code', trim($i->ref_val))->first();

                try {
                    unset($i->admittedByStd->image);
                } catch (\Exception $e) {
                    Log::error("Following student do not hav valid ref reg_code");
                    Log::error($i);
                }


            }

            return $i;
        });

        return $stds;
    }


    public function getRefSingleStudent(int $type = 0, int $stdid = 0)
    {

        if ($type > 3) {

            return response()->json(['error' => 'Ref Type Not Valid'], 400);
        }

//		$type = null;

        $std = O_STUDENT::with(['department', 'batch'])
            ->where('id', $stdid)
            ->where('refereed_by_parent_id', $type)
            ->where('verified', 1)
            ->where('id_card_given', 0)
            ->first();

        if (!$std) {
            return response()->json(['error' => 'No Student Found'], 400);
        }
        unset($std->image);
        $std->adm_fee = O_CASHIN::where('student_id', $std->id)->first()->amount;

        if ($type == 3) { // 3 = ref by student. 2=ref by liaison officer
            $std->admittedByStd = O_STUDENT::with('department', 'batch')->where('reg_code', trim($std->ref_val))->first();
            unset($std->admittedByStd->image);
        }

        $std->admissionOfficer = O_EMP::find($std->emp_id);
        $std->admissionOfficerDept = O_EMP_DEPARTMENTS::find($std->admissionOfficer->department_id);
        $std->admissionOfficerPosition = O_DESIGNATION::find($std->admissionOfficer->position_id);

        return $std;
    }

    public function rms_get_batch_info_by_ids(Request $request)
    {
//	var_dump($request->batch_ids);
        return O_BATCH::whereIn('id', (array)$request->batch_ids)->get();
    }
}
