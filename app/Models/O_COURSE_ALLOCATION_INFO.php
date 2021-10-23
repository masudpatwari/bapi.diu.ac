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

class O_COURSE_ALLOCATION_INFO extends Eloquent
{
    public $timestamps = false;
    protected $table = "COURSE_ALLOCATION_INFO";
    protected $connection = 'oracle';
    protected $fillable = ['col1_title, col1_mark, col2_title, col2_mark, col3_title, col3_mark, col4_title, col4_mark'];

    /*
     * Called semsesters controller
    */

    public static function allocated_course($semester_id)
    {
        return static::selectRaw("COURSE_ALLOCATION_INFO.ID, COURSE_ALLOCATION_INFO.SIFR_ID, COURSE.ID AS COURSE_ID, COURSE.NAME, CODE, CREDIT, TEACHER_ID, MARK_APPROVE_STATUS, INCOURSE_MARK, FINAL_MARK, TOTAL_MARK, COURSE_TYPE, COL1_TITLE, COL1_MARK, COL2_TITLE, COL2_MARK, COL3_TITLE, COL3_MARK, COL4_TITLE, COL4_MARK")
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->where(['SIFR_ID' => $semester_id, 'IS_DELETED' => 0])
            ->with('teacher')
            ->orderby('COURSE_ALLOCATION_INFO.ID', 'ASC');
    }

    public static function allocated_course_without_teacher($semester_id)
    {
        return static::selectRaw("COURSE_ALLOCATION_INFO.ID, COURSE_ALLOCATION_INFO.SIFR_ID, COURSE.ID AS COURSE_ID, COURSE.NAME, CODE, CREDIT, TEACHER_ID, MARK_APPROVE_STATUS, INCOURSE_MARK, FINAL_MARK, TOTAL_MARK, COURSE_TYPE, COL1_TITLE, COL1_MARK, COL2_TITLE, COL2_MARK, COL3_TITLE, COL3_MARK, COL4_TITLE, COL4_MARK")
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->where(['SIFR_ID' => $semester_id, 'IS_DELETED' => 0])
            ->orderby('COURSE_ALLOCATION_INFO.ID', 'ASC');
    }

    /*
     * Called assigned marksheet controller
     * Called committee marksheet controller
     * Called tabulator marksheet controller
    */
    public static function marksheets_list( $request )
    {
        return static::selectRaw('SEMESTER, BATCH_NAME, YEAR, COURSE.NAME AS COURSE_NAME, CODE, COURSE_ID, CREDIT, SIFR_ID, INCOURSE_MARK, FINAL_MARK, MARK_APPROVE_STATUS, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, ECC, IS_VERIFIED, APPROVED_BY, TEACHER_ID')
        ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
        ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'COURSE.DEPARTMENT_ID')
        ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
        ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
        ->where(['SEMESTER_INFO_FOR_RESULT.ID' => $request->semester_info_table_id, 'COURSE_ALLOCATION_INFO.IS_DELETED' => 0, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'EXEMPTED' => 0])

        ->where(function ($q)use ($request){
            if($request->status == 'pending'){
                $q->where('MARK_APPROVE_STATUS', '<=', \RMS_status::DRAFT_STATUS);
            }else if($request->status == 'complete'){
                $q->where('MARK_APPROVE_STATUS', '>=', \RMS_status::FINAL_STATUS);
            }

            if($request->status == 'pending_for_committee'){
                $q->where('MARK_APPROVE_STATUS', '=', \RMS_status::FINAL_STATUS);
            }else if($request->status == 'complete_for_committee'){
                $q->where('MARK_APPROVE_STATUS', '>', \RMS_status::FINAL_STATUS);
            }
        })
        ->orderby('COURSE_ALLOCATION_INFO.ID', 'ASC');
    }

    /*
     * Called all marksheets controller
    */

    public static function marksheets_all( $request )
    {
        return static::selectRaw('SEMESTER, BATCH_NAME, YEAR, COURSE.NAME AS COURSE_NAME, CODE, COURSE_ID, CREDIT, SIFR_ID, INCOURSE_MARK, FINAL_MARK, MARK_APPROVE_STATUS, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, ECC, IS_VERIFIED, APPROVED_BY, TEACHER_ID')
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'COURSE.DEPARTMENT_ID')
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.BATCH_ID' => $request->batch_id, 'SEMESTER_INFO_FOR_RESULT.SEMESTER' => $request->semester_id, 'COURSE_ALLOCATION_INFO.IS_DELETED' => 0, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'EXEMPTED' => 0])
            ->orderby('COURSE_ALLOCATION_INFO.ID', 'ASC');
    }

    /*
     * Called marksheet parent controller
    */
    public static function marksheet_info($semester_info_table_id, $course_id)
    {
        return static::selectRaw('COURSE_ALLOCATION_INFO.ID AS CAI_ID, COURSE_ALLOCATION_INFO.SIFR_ID, GPS_ID, TEACHER_ID, SEMESTER, YEAR, TOTAL_CREDIT, TOTAL_SUBJECT, RRNO, RESULT_PUBLISH_DATE, S_DEPARTMENT.ID AS DEPARTMENT_ID, S_DEPARTMENT.NAME AS DEPARTMENT_NAME, FACULTY, DEPARTMENT, S_BATCH.ID AS BATCH_ID, BATCH_NAME, SESS, SEASON, SHIFT.NAME AS SHIFT_NAME, COURSE.ID AS COURSE_ID, COURSE.NAME AS COURSE_NAME, CODE, CREDIT, INCOURSE_MARK, FINAL_MARK, INCOURSE_PASS_MARK, FINAL_PASS_MARK, IMPROVABLE_MARK, TOTAL_MARK, ECC, MARK_APPROVE_STATUS, COURSE_TYPE, COL1_TITLE, COL1_MARK, COL2_TITLE, COL2_MARK, COL3_TITLE, COL3_MARK, COL4_TITLE, COL4_MARK')
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
            ->join('S_DEPARTMENT', 'S_DEPARTMENT.ID', '=', 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->join('SHIFT', 'SHIFT.ID', '=', 'S_BATCH.SHIFT_ID')
            ->join('COURSE', 'COURSE.ID', '=', 'COURSE_ALLOCATION_INFO.COURSE_ID')
            ->where(['COURSE_ALLOCATION_INFO.SIFR_ID' => $semester_info_table_id, 'COURSE_ALLOCATION_INFO.COURSE_ID' => $course_id, 'COURSE_ALLOCATION_INFO.IS_DELETED' => 0]);
    }

    /*
     * Called marksheet parent controller
    */
    public static function assigned_semesters_id( $department_id, $employee_id )
    {
        return static::selectRaw('S_BATCH.BATCH_NAME, SEMESTER_INFO_FOR_RESULT.ID, SEMESTER')->distinct()
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where(['TEACHER_ID' => $employee_id, 'SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => $department_id, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'COURSE_ALLOCATION_INFO.IS_DELETED' => 0])
            ->orderby('SEMESTER', 'ASC');
    }

    /*
     * get_all_semesters_of_a_department
     *
     * @param int $department_id
     *
    */
    public static function get_all_semesters_of_a_department( $department_id)
    {
        return static::selectRaw('S_BATCH.BATCH_NAME, SEMESTER_INFO_FOR_RESULT.ID, SEMESTER')->distinct()
            ->join('SEMESTER_INFO_FOR_RESULT', 'SEMESTER_INFO_FOR_RESULT.ID', '=', 'COURSE_ALLOCATION_INFO.SIFR_ID')
            ->join('S_BATCH', 'S_BATCH.ID', '=', 'SEMESTER_INFO_FOR_RESULT.BATCH_ID')
            ->where(['SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID' => $department_id, 'SEMESTER_INFO_FOR_RESULT.IS_DELETED' => 0, 'COURSE_ALLOCATION_INFO.IS_DELETED' => 0])
            ->orderby('SEMESTER', 'ASC');
    }

    public function teacher()
    {
        return $this->belongsTo(M_WP_EMP::class, 'teacher_id', 'id');
    }


    public function course()
    {
        return $this->belongsTo(O_COURSE::class,'course_id', 'id');
    }

    public function marks(){
        return $this->hasMany(O_MARKS::class, 'cai_id', 'id');
    }

    /*
     * */
}
