<?php

namespace App\Http\Controllers;

use App\Models\O_IMP_REQUEST;
use Illuminate\Http\Request;
use App\Models\O_MARKS;
use App\Models\O_IMP_REQUEST_COURSE;
use App\Models\O_IMP_EXAM_SCHEDULE;
use Illuminate\Support\Facades\DB;

class Imp_Eligible_Courses extends Controller
{
    public $db_prefix = NULL;

    public function __construct()
    {
        $this->db_prefix = env('O_SCHEMA_PREFIX');
    }

    /**
     * @param $id
     * @param $examSchedule
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function eligible_for_incourse($id, $examSchedule)
    {
        /**
         *      ALGO:
         *
         * 1. incourse course list of selected exam
         * 2. if appllied then show how many times applied
         * 3. if paid then show paid status
         * 4. if not paid then withdraw option show.
         */

        $eligible_courses = DB::connection('oracle')->select('
            SELECT MARKS.ID, COURSE.ID AS COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.CREDIT, COURSE.COURSE_TYPE, MARKS.CONTI_TOTAL as incourse_total, MARKS.FINAL_TOTAL as final_total, MARKS.COURSE_TOTAL, MARKS.GRADE_POINT, MARKS.LETTER_GRADE, COURSE.IMPROVABLE_MARK, S_DEPARTMENT.PROGRAM_TYPE FROM '.$this->db_prefix.'.MARKS
            LEFT JOIN '.$this->db_prefix.'.COURSE ON COURSE.ID = MARKS.COURSE_ID
            LEFT JOIN '.$this->db_prefix.'.STUDENT ON STUDENT.ID = MARKS.STD_ID
            LEFT JOIN '.$this->db_prefix.'.S_DEPARTMENT ON S_DEPARTMENT.ID = STUDENT.DEPARTMENT_ID
            LEFT JOIN '.$this->db_prefix.'.SEMESTER_INFO_FOR_RESULT ON SEMESTER_INFO_FOR_RESULT.ID = MARKS.SIFR_ID
            WHERE STD_ID = '.$id.'  AND (MARKS.COURSE_TOTAL <= COURSE.IMPROVABLE_MARK  OR MARKS.COURSE_TOTAL IS NULL ) AND SEMESTER_INFO_FOR_RESULT.RESULT_TABULATION_STATUS > 4
        ');

        return $this->generate_courses( $eligible_courses, $id, $examSchedule,'incourse');
    }

    public function eligible_for_final($id, $examSchedule)
    {

        $eligible_courses = DB::connection('oracle')->select('
            SELECT MARKS.ID, COURSE.ID AS COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.CREDIT, COURSE.COURSE_TYPE, MARKS.CONTI_TOTAL as incourse_total, MARKS.FINAL_TOTAL as final_total, MARKS.COURSE_TOTAL, MARKS.GRADE_POINT, MARKS.LETTER_GRADE, COURSE.IMPROVABLE_MARK, S_DEPARTMENT.PROGRAM_TYPE FROM '.$this->db_prefix.'.MARKS
            LEFT JOIN '.$this->db_prefix.'.COURSE ON COURSE.ID = MARKS.COURSE_ID
            LEFT JOIN '.$this->db_prefix.'.STUDENT ON STUDENT.ID = MARKS.STD_ID
            LEFT JOIN '.$this->db_prefix.'.S_DEPARTMENT ON S_DEPARTMENT.ID = STUDENT.DEPARTMENT_ID
            LEFT JOIN '.$this->db_prefix.'.SEMESTER_INFO_FOR_RESULT ON SEMESTER_INFO_FOR_RESULT.ID = MARKS.SIFR_ID
            WHERE STD_ID = '.$id.' AND ( MARKS.COURSE_TOTAL <= COURSE.IMPROVABLE_MARK  OR MARKS.COURSE_TOTAL IS NULL ) AND SEMESTER_INFO_FOR_RESULT.RESULT_TABULATION_STATUS > 4
        ');

        return $this->generate_courses( $eligible_courses, $id , $examSchedule,'final');
    }

    public function generate_courses( $eligible_courses, $id , $examSchedule, $type)
    {
        $schedule = O_IMP_EXAM_SCHEDULE::find($examSchedule);
        $course_fee = NULL;
        $eligible_courses_array = [];


        if( ! $schedule ){
            return response()->json( ['error'=> 'No Exam Schedule found On this ID.' . $examSchedule ], 400);
        }

        $payment_status = NULL;


        $examRequestObj = O_IMP_REQUEST::where(['std_id'=> $id , 'ies_id'=> $examSchedule, 'type'=>$type])->first();

        if ( $examRequestObj ){
            $payment_status = $examRequestObj->payment_status;
        }


        foreach ($eligible_courses as $key => $course) {

            $eligible_for_incourse  = false;
            $type = '';


            if( debug_backtrace()[1]['function'] == 'eligible_for_incourse')
            {
                $type = 'incourse';
            }
            else {
                $type = 'final';
            }

            if ( debug_backtrace()[1]['function'] == 'eligible_for_incourse' and $course->course_type == 0){
                continue;
            }


            /**
             * previously applied times
             * appplied present exam schedule
             *
             */

            //finding Previously applied time
            $request_course_query = O_IMP_REQUEST_COURSE::where([
                    'std_id' => $id,
                    'course_id' => $course->course_id,
                    'type'=> $type,
                    ]);

            if ($examRequestObj)
                $request_course_query->where('imp_rq','!=',$examRequestObj->id);

            $applied_times = $request_course_query->get()->count();

            if ( $examRequestObj )
            $applied_in_current_exam_shedule = O_IMP_REQUEST_COURSE::where(['std_id' => $id, 'course_id' => $course->course_id, 'type'=> $type, 'imp_rq'=> $examRequestObj->id])->exists();
            else $applied_in_current_exam_shedule =false;

            try{
                $course_fee = O_IMP_EXAM_SCHEDULE::getCourseCost( $schedule, $id, $course->course_id, $type);
            }
            catch (\Exception $exception){
                return response()->json(['error'=> $exception->getMessage()], 400);
            }



            $eligible_courses_array[] = [
                'id' => $course->course_id,
                'name' => $course->name,
                'code' => $course->code,
                'course_id' => $course->course_id,
                'credit' => $course->credit,
                'course_type' => course_type($course->course_type),
                'incourse_total' => $course->course_type == 0 ? 'NA' : $course->incourse_total,
                'final_total' =>  $course->final_total,
                'course_total' =>  $course->course_total,
                'letter_grade' =>  $course->letter_grade,
                'grade_point' =>  $course->grade_point,
                'applied_times' => $applied_times,
                'applied_in_current_exam_shedule' => $applied_in_current_exam_shedule,
                'payment_status' => $payment_status,
                'course_fee' => $course_fee[$course->program_type][$type],
            ];

//            $payment_status = NULL;
        }
        return $eligible_courses_array;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
