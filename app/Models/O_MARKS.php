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
use Illuminate\Support\Facades\DB;

class O_MARKS extends Eloquent
{
    public $timestamps = false;
    protected $table = "MARKS";
    protected $connection = 'oracle';

    /*
     * Called marksheet parent controller
    */

    public static function statistics($semester_info_table_id, $course_id)
    {
        $result['mp'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_MID' => 'p', 'IS_MODIFIED' => 0])->first();

        $result['ma'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_MID' => 'a', 'IS_MODIFIED' => 0])->first();

        $result['me'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_MID' => 'e', 'IS_MODIFIED' => 0])->first();

        $result['fp'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_FINAL' => 'p', 'IS_MODIFIED' => 0])->first();

        $result['fa'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_FINAL' => 'a', 'IS_MODIFIED' => 0])->first();

        $result['fe'] = static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STATUS_FINAL' => 'e', 'IS_MODIFIED' => 0])->first();

        $result['tm'] = ($result['mp']->count + $result['ma']->count + $result['me']->count);
        $result['tf'] = ($result['fp']->count + $result['fa']->count + $result['fe']->count);
        return $result;
    }

    /*
     * Called marksheet parent controller
    */
    public static function marks_value($semester_info_table_id, $course_id)
    {
        return static::selectRaw('ID, STATUS_MID, MT, CT, AP, AB, CONTI_TOTAL, STATUS_FINAL, M1, M2, M3, M4, M5, M6, M7, M8, FINAL_TOTAL, COURSE_TOTAL, LETTER_GRADE, GRADE_POINT, ROLL, STD_ID, DATETIME, ENCRYPTED_TEXT, IS_MODIFIED')
            ->where(['SIFR_ID' => $semester_info_table_id, 'COURSE_ID' => $course_id]);
    }

    /*
     * Called edit marksheet controller
     */
    public static function marks_value_single($semester_info_table_id, $course_id, $roll_no)
    {
        return static::selectRaw('ID, STATUS_MID, MT, CT, AP, AB, CONTI_TOTAL, STATUS_FINAL, M1, M2, M3, M4, M5, M6, M7, M8, FINAL_TOTAL, COURSE_TOTAL, LETTER_GRADE, GRADE_POINT, ROLL, STD_ID, ENCRYPTED_TEXT, IS_MODIFIED')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'ROLL' => $roll_no]);
    }

    /*
     * Called **** controller
     */
    public static function check_marksheet_exists($semester_info_table_id, $course_id)
    {
        return static::selectRaw('COUNT(*) AS COUNT')
            ->where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id]);
    }

    /**
     * Called **** credit_transfer_marksheet@create
     */
    public static function check_mark_exists_of_a_course_of_a_student(int     $semester_info_table_id, int $course_id, int $student_id)
    {
        return static::where(['COURSE_ID' => $course_id, 'SIFR_ID' => $semester_info_table_id, 'STD_ID'=>$student_id])->get();
    }

    /*
     * This function will calculate total cgpa present and past semester only passed course.
     * Failed course credit will be deducked from total credit.
     */
    public static function tabulation_cgpa( $student_id, $semester_no ){
	   $db_prefix = env('O_SCHEMA_PREFIX');
        $result = DB::connection('oracle')->select('
            SELECT (
                SELECT SUM(GRADE_POINT * '.$db_prefix.'.COURSE.CREDIT) FROM '.$db_prefix.'.MARKS JOIN '.$db_prefix.'.COURSE ON '.$db_prefix.'.COURSE.ID = COURSE_ID WHERE STD_ID = '.$student_id.' AND COURSE_ID IN (
                    SELECT COURSE_ID FROM '.$db_prefix.'.COURSE_ALLOCATION_INFO WHERE SIFR_ID IN (
                        SELECT ID FROM '.$db_prefix.'.SEMESTER_INFO_FOR_RESULT WHERE ID IN (
                            SELECT DISTINCT SIFR_ID FROM '.$db_prefix.'.MARKS WHERE STD_ID = '.$student_id.'
                        ) AND SEMESTER <= '.$semester_no.'
                    ) 
                ) AND to_number(GRADE_POINT) > 0
            ) 
            / 
            (
            SELECT SUM(CREDIT) FROM '.$db_prefix.'.COURSE WHERE ID IN (
                SELECT COURSE_ID FROM '.$db_prefix.'.MARKS WHERE STD_ID = '.$student_id.' AND COURSE_ID IN (
                    SELECT COURSE_ID FROM '.$db_prefix.'.COURSE_ALLOCATION_INFO WHERE SIFR_ID IN (
                        SELECT ID FROM '.$db_prefix.'.SEMESTER_INFO_FOR_RESULT WHERE ID IN (
                            SELECT DISTINCT SIFR_ID  FROM '.$db_prefix.'.MARKS WHERE STD_ID = '.$student_id.'
                        ) AND SEMESTER <= '.$semester_no.'
                    )
                ) AND to_number(grade_point) > 0
            )
            ) AS result FROM dual'
        )[0]->result;
        return round($result, 2);
    }
    public static function hasFailed( $student_id ){
	   $db_prefix = env('O_SCHEMA_PREFIX');
        $result = DB::connection('oracle')->select("SELECT  LETTER_GRADE FROM " . $db_prefix.".MARKS WHERE STD_ID = " .$student_id ." AND (LETTER_GRADE IS NULL OR LETTER_GRADE = 'F')");

        return count($result)>0?true:false;

    }


    /*
     * Called result controller
     */
    public static function semester_marks($semester_info_table_id)
    {
        return static::selectRaw('ID, STATUS_MID, STATUS_FINAL, CONTI_TOTAL, FINAL_TOTAL, LETTER_GRADE, GRADE_POINT, SIFR_ID, ROLL, STD_ID, COURSE_ID, IS_MODIFIED')
            ->where(['SIFR_ID' => $semester_info_table_id]);
    }

    public function student()
    {
        return $this->belongsTo(O_STUDENT::class,'std_id', 'id');
    }

    public function courseAllocation()
    {
        return $this->belongsTo(O_COURSE_ALLOCATION_INFO::class, 'cai_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(O_COURSE::class,'course_id', 'id');
    }


    public function entryBy()
    {
        return $this->belongsTo(M_WP_EMP::class,'creator_id', 'id');
    }

    public function marksHistory()
    {
        return $this->hasMany(O_MARKS_EDIT_HISTORY::class,'marks_id', 'id');
    }

}

