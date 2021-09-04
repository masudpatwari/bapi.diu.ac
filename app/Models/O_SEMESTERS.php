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

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class O_SEMESTERS extends Eloquent
{
    public $timestamps = false;
    protected $table = "SEMESTER_INFO_FOR_RESULT";
    protected $connection = 'oracle';
    protected $fillable = ['department_id', 'batch_id', 'semester', 'year', 'total_credit', 'total_subject', 'gps_id', 'program_officer_id', 'creator_id', 'no_of_sub_must_pass', 'datetime', 'ecc', 'dcid', 'is_deleted', 'rrno', 'exempted', 'season'];

    /*
     * Called semsesters controller
    */
    public static function semesters( $department_id, $batch_id )
    {
        return static::selectRaw('SEMESTER_INFO_FOR_RESULT.ID, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, S_DEPARTMENT.SHORT_CODE, S_BATCH.BATCH_NAME, S_BATCH.ID AS BATCH_ID, SEMESTER, FACULTY, DEPARTMENT, YEAR, SESS, SHIFT.NAME AS SHIFT_NAME, GPS_ID, TOTAL_SUBJECT, TOTAL_CREDIT, PROGRAM_OFFICER_ID, S_DEPARTMENT.ID AS DEPARTMENT_ID, ECC, DCID, RRNO, RESULT_PUBLISH_DATE, NO_OF_SUB_MUST_PASS, RESULT_TABULATION_STATUS, IS_VERIFIED, APPROVED_BY, EXEMPTED, SEASON')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->join('SHIFT', 'SHIFT.ID', '=', 'S_BATCH.SHIFT_ID')
            ->join('GRADE_POINT_SYSTEM', 'GRADE_POINT_SYSTEM.ID', '=', 'SEMESTER_INFO_FOR_RESULT.GPS_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => $department_id, 'SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $batch_id])
            ->orderby('SEMESTER', 'ASC');
    }

    /*
	 * Called resultsheet controller
	*/
    public static function result_semesters($request)
    {
        $rms_status = 3;
        if (in_array(env('DEPT_CHAIRMAN'), get_current_user_roles())){
            $rms_status = \RMS_status::EXAM_COMMITTEE_CHAIRMAN_APPROVED;
        }
        if (in_array(env('OFFICER_OF_CONTROLLER_EXAMINITION'), get_current_user_roles())){
            $rms_status = \RMS_status::DEPT_CHAIRMAN_APPROVED;
        }
        if (in_array(env('CONTROLLER_OF_EXAMINITION'), get_current_user_roles())){
            $rms_status = \RMS_status::OFFICER_OF_CONTROLLER_EXAMINITION_APPROVED;
        }
        if (in_array(env('BOT'), get_current_user_roles())){
            $rms_status = \RMS_status::CONTROLLER_OF_EXAMINITION_APPROVED;
        }
        return static::selectRaw('SEMESTER_INFO_FOR_RESULT.ID, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, S_DEPARTMENT.SHORT_CODE, S_BATCH.BATCH_NAME, S_BATCH.ID AS BATCH_ID, SEMESTER, FACULTY, DEPARTMENT, YEAR, SESS, SHIFT.NAME AS SHIFT_NAME, GPS_ID, TOTAL_SUBJECT, TOTAL_CREDIT, PROGRAM_OFFICER_ID, S_DEPARTMENT.ID AS DEPARTMENT_ID, ECC, DCID, RRNO, RESULT_PUBLISH_DATE, NO_OF_SUB_MUST_PASS, RESULT_TABULATION_STATUS, IS_VERIFIED, APPROVED_BY, EXEMPTED, SEASON')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->join('SHIFT', 'SHIFT.ID', '=', 'S_BATCH.SHIFT_ID')
            ->join('GRADE_POINT_SYSTEM', 'GRADE_POINT_SYSTEM.ID', '=', 'SEMESTER_INFO_FOR_RESULT.GPS_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => session('user.selected_department.id'), 'SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $request->batch_id, 'IS_VERIFIED' => 1])
            ->where(function ($q) use ($request, $rms_status){
                if($request->status == 'pending'){
                    $q->where('RESULT_TABULATION_STATUS', $rms_status);
                }else if($request->status == 'complete'){
                    $q->where('RESULT_TABULATION_STATUS', '>=', $rms_status);
                }
            })
            ->orderby('SEMESTER', 'ASC');
    }

    /*
     * Called semsesters controller
     * Called resultsheet controller
    */
    public static function single_semester($batch_id, $semester_id)
    {
        return static::selectRaw('SEMESTER_INFO_FOR_RESULT.ID, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, S_DEPARTMENT.SHORT_CODE, S_BATCH.BATCH_NAME, S_BATCH.ID AS BATCH_ID, SEMESTER, FACULTY, DEPARTMENT, YEAR, SESS, SHIFT.NAME AS SHIFT_NAME, GPS_ID, TOTAL_SUBJECT, TOTAL_CREDIT, PROGRAM_OFFICER_ID, S_DEPARTMENT.ID AS DEPARTMENT_ID, ECC, DCID, RRNO, RESULT_PUBLISH_DATE, NO_OF_SUB_MUST_PASS, RESULT_TABULATION_STATUS, IS_VERIFIED, APPROVED_BY, EXEMPTED, SEASON')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID')
            ->join('SHIFT', 'SHIFT.ID', '=', 'S_BATCH.SHIFT_ID')
            ->join('GRADE_POINT_SYSTEM', 'GRADE_POINT_SYSTEM.ID', '=', 'SEMESTER_INFO_FOR_RESULT.GPS_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $batch_id, 'SEMESTER_INFO_FOR_RESULT.ID' => $semester_id]);
    }

    /*
     * Called semsesters controller
    */
    public static function semesters_allocated_course( $request )
    {
        return static::selectRaw('ID')
        ->where([
            'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0,
            'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => session('user.selected_department.id'),
            'SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $request->batch_id,
            'SEMESTER_INFO_FOR_RESULT.SEMESTER' => $request->semester_id]);
    }

    public static function parent_semesters_allocated_course( $batch_id, $semester_id )
    {
        return static::selectRaw('ID')
        ->where([
            'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0,
            'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => session('user.selected_department.id'),
            'SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $batch_id,
            'SEMESTER_INFO_FOR_RESULT.SEMESTER' => $semester_id]);
    }



    public static function courses_of_batch( $batch_id )
    {
        $batch = O_BATCH::where('id', $batch_id)->first();
        return static::selectRaw('COURSE.ID, COURSE.NAME, COURSE.CODE, COURSE.CREDIT, COURSE.INCOURSE_MARK, COURSE.FINAL_MARK,COURSE.TOTAL_MARK, SEMESTER_INFO_FOR_RESULT.SEMESTER, SEMESTER_INFO_FOR_RESULT.ID SIFR_ID,SEMESTER_INFO_FOR_RESULT.GPS_ID, COURSE.COURSE_TYPE,COURSE_ALLOCATION_INFO.ID CAI_ID')
            ->join('COURSE_ALLOCATION_INFO', 'COURSE_ALLOCATION_INFO.SIFR_ID', '=', 'SEMESTER_INFO_FOR_RESULT.ID')
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->where([
                'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0,
                'SEMESTER_INFO_FOR_RESULT.EXEMPTED'=>0,
                'SEMESTER_INFO_FOR_RESULT.IS_VERIFIED' => 1,
                'COURSE_ALLOCATION_INFO.IS_DELETED' => 0,
                'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => session('user.selected_department.id'),
            ])->when($batch->parent_batch_id, function ($q) use($batch_id, $batch){
                return $q->where(function ($q) use ($batch, $batch_id){
                    return $q->where('SEMESTER_INFO_FOR_RESULT.BATCH_ID', $batch_id )
                        ->orWhere('SEMESTER_INFO_FOR_RESULT.BATCH_ID' , $batch->parent_batch_id);
                });
            })->when($batch->parent_batch_id==null, function ($q) use($batch_id){
                return $q->where('SEMESTER_INFO_FOR_RESULT.BATCH_ID', $batch_id );
            })
            ->orderBy('SEMESTER')
            ->orderBy('id');
    }

    public static function get_course_id_exists_in_marks_table(int $student_id)
    {
        return  O_MARKS::distinct('course_id')->where('STD_ID', $student_id)->pluck('course_id')->toArray();
    }

    /*
     * Called edit marksheet controller
     */
    public static function semester_info_table_id( $request )
    {
        return static::selectRaw('ID')
            ->where(['DEPARTMENT_ID' => session('user.selected_department.id'), 'BATCH_ID' => $request->batch_id, 'SEMESTER' => $request->semester_id, 'IS_DELETED' => 0]);
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\M_WP_EMP::class, 'emp_id', 'id');
    }

    public function chairman()
    {
        return $this->belongsTo(\App\Models\M_WP_EMP::class, 'dcid', 'id');
    }

    public function programOfficer()
    {
        return $this->belongsTo(\App\Models\M_WP_EMP::class, 'program_officer_id', 'id');
    }

    public function approve_by()
    {
        return $this->belongsTo(\App\Models\M_WP_EMP::class, 'approved_by', 'id');
    }

    public function allocatedCourses()
    {
        return $this->hasMany(O_COURSE_ALLOCATION_INFO::class,'sifr_id', 'id');
    }


    /**
     * get assigned teachers id
     * @param $semester_info_for_result_id integer
     * @return array
     */
    public static function getAllocatedTeachersId($semester_info_for_result_id){
       return O_COURSE_ALLOCATION_INFO::
                    where(['sifr_id'=>$semester_info_for_result_id])
                    ->get()
                    ->pluck('teacher_id');
    }


    /**
     * get exam committee members id
     * @param $semester_info_for_result_id integer
     * @return array
     */
    public static function getAllocatedExamCommitteeMembersId($semester_info_for_result_id){
       return O_EXAM_COMMITTEE::select('EMP_ID')->where(['sifr_id'=>$semester_info_for_result_id])->get()->pluck('emp_id');
    }

    /**
     * get exam tabulators id
     * @param $semester_info_for_result_id integer
     * @return array
     */
    public static function getAllocatedExamTabulatorsId($semester_info_for_result_id){
       return O_EXAM_TABULATOR::select('EMP_ID')->where(['sifr_id'=>$semester_info_for_result_id])->get()->pluck('emp_id');
    }

    /**
     * get Department Chairman id
     * @param $semester_info_for_result_id integer
     * @return integer
     */
    public static function getDeptChairmanId($semester_info_for_result_id){
       return O_SEMESTERS::find($semester_info_for_result_id)->dcid;
    }

    /**
     * get Exam Committee Chairman id
     * @param $semester_info_for_result_id integer
     * @return integer
     */
    public static function getExamCommitteeChairmanId($semester_info_for_result_id){
       return O_SEMESTERS::find($semester_info_for_result_id)->ecc;
    }


    /**
     * get Program officer id
     * @param $semester_info_for_result_id integer
     * @return integer
     */
    public static function getProgramOfficerId($semester_info_for_result_id){
       return O_SEMESTERS::find($semester_info_for_result_id)->program_officer_id;
    }


}

