<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class O_IMP_REQUEST_COURSE extends Model
{

    public $timestamps = false;
    protected $table = "IMP_REQUEST_COURSE";
    protected $connection = 'oracle';

    protected $fillable = ['STD_ID', 'COURSE_ID', 'IMP_RQ', 'TYPE'];

    public static function imp_student_marksheet($ies_id, $student_id)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        $marks = DB::connection('oracle')->select('
            SELECT COURSE.ID AS COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.CREDIT, COURSE.COURSE_TYPE, IMP_REQUEST_COURSE.TYPE, MARKS.ID, STATUS_MID, CONTI_TOTAL, STATUS_FINAL, FINAL_TOTAL, COURSE_TOTAL, LETTER_GRADE, GRADE_POINT FROM '.$db_prefix.'.IMP_REQUEST_COURSE
            JOIN '.$db_prefix.'.MARKS ON MARKS.COURSE_ID = IMP_REQUEST_COURSE.COURSE_ID AND MARKS.STD_ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID AND COURSE.ID = MARKS.COURSE_ID
            JOIN '.$db_prefix.'.IMP_REQUEST ON IMP_REQUEST.ID = IMP_REQUEST_COURSE.IMP_RQ
            WHERE IMP_REQUEST_COURSE.STD_ID = '.$student_id.' AND IMP_REQUEST.IES_ID = '.$ies_id.'
        ');

        if (! $marks){
            throw new \Exception('No Marks Found!');
        }
        $marksCol = new Collection($marks);
        $unicmarksCol = $marksCol->unique('course_id');
        
//        $course->course_type

        /**
        "name": "Object Oriented programming Lab",
        "code": "CSE-208",
        "course_type": "0",
        "type": "final",
        "id": "60698",
        "status_mid": "p",
        "conti_total": "0",
        "status_final": "e",
        "final_total": null,
        "course_total": null,
        "letter_grade": null,
        "grade_point": null
         */
        $marks_for_response = [];


        foreach ($unicmarksCol  as $mark){

            //($type == 0) ? 'Non Theory' : 'Theory';

            if ($mark->course_type == 0 ){ // Non Theory: only final exam

                if( $mark->status_final != 'a'){
                    $result = 'incomplete';
                    $marks_for_response[] = [
                        'id' => $mark->id,
                        'name' => $mark->name,
                        'code' => $mark->code,
                        'course_id' => $mark->course_id,
                        'credit' => $mark->credit,
                        'course_type' => course_type($mark->course_type),
                        'incourse_total' => 'NA' ,
                        'final_total' =>  $result ,
                        'course_total' =>  $result ,
                        'letter_grade' =>  $result ,
                        'grade_point' =>  $result ,
                    ];
                }
                else{
                    $marks_for_response[] = [
                        'id' => $mark->id,
                        'name' => $mark->name,
                        'code' => $mark->code,
                        'course_id' => $mark->course_id,
                        'credit' => $mark->credit,
                        'course_type' => course_type($mark->course_type),
                        'incourse_total' => 'NA',
                        'final_total' =>  $mark->final_total,
                        'course_total' =>  $mark->course_total,
                        'letter_grade' =>  $mark->letter_grade,
                        'grade_point' =>  $mark->grade_point,
                    ];
                }

            }elseif ($mark->course_type == 1 ) { // Theory: mid and final exam
                if( $mark->status_mid == 'e' || $mark->status_final == 'e'){
                    $result = 'incomplete';
                    $marks_for_response[] = [
                        'id' => $mark->id,
                        'name' => $mark->name,
                        'code' => $mark->code,
                        'course_id' => $mark->course_id,
                        'credit' => $mark->credit,
                        'course_type' => course_type($mark->course_type),
                        'incourse_total' => $result,
                        'final_total' =>  $mark->status_final == 'e'? $result: $mark->final_total,
                        'course_total' =>  $mark->status_mid == 'e'? $result: $mark->course_total,
                        'letter_grade' =>  $result,
                        'grade_point' =>  $result,
                    ];
                }
                else{

                    $marks_for_response[] = [
                        'id' => $mark->id,
                        'name' => $mark->name,
                        'code' => $mark->code,
                        'course_id' => $mark->course_id,
                        'credit' => $mark->credit,
                        'course_type' => course_type($mark->course_type),
                        'incourse_total' => $mark->course_type == 0 ? 'NA' : $mark->conti_total,
                        'final_total' =>  $mark->final_total,
                        'course_total' =>  $mark->course_total,
                        'letter_grade' =>  $mark->letter_grade,
                        'grade_point' =>  $mark->grade_point,
                    ];
                }
            }

            $result = '';

        }

        return $marks_for_response;
    }

    public static function imp_semester_info($course_id, $department_id, $batch_id)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT COURSE_ID, SEMESTER, YEAR, SEASON FROM '.$db_prefix.'.SEMESTER_INFO_FOR_RESULT 
            JOIN '.$db_prefix.'.COURSE_ALLOCATION_INFO ON COURSE_ALLOCATION_INFO.SIFR_ID = SEMESTER_INFO_FOR_RESULT.ID 
            WHERE COURSE_ALLOCATION_INFO.COURSE_ID = '.$course_id.' AND SEMESTER_INFO_FOR_RESULT.DEPARTMENT_ID = '.$department_id.' AND SEMESTER_INFO_FOR_RESULT.BATCH_ID = '.$batch_id.' AND SEMESTER_INFO_FOR_RESULT.IS_DELETED = 0 AND COURSE_ALLOCATION_INFO.IS_DELETED = 0 AND IS_VERIFIED = 1
        ');
    }

    public static function imp_requested_students($course_id, $campus_id, $ies_id, $type)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT STUDENT.NAME, STUDENT.ID, STUDENT.ROLL_NO, STUDENT.REG_CODE, S_BATCH.BATCH_NAME, IMP_REQUEST_COURSE.COURSE_ID, STUDENT.CAMPUS_ID FROM '.$db_prefix.'.IMP_REQUEST_COURSE 
            JOIN '.$db_prefix.'.IMP_REQUEST ON IMP_REQUEST.ID = IMP_REQUEST_COURSE.IMP_RQ 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.S_BATCH ON S_BATCH.ID = STUDENT.BATCH_ID 
            WHERE IMP_REQUEST_COURSE.COURSE_ID = '.$course_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".' AND IMP_REQUEST.IES_ID = '.$ies_id.'
        ');
    }

    public static function imp_requested_marks($course_id, $ies_id, $type)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT  MARKS.ID, STATUS_MID, MT, CT, AP, AB, CONTI_TOTAL, STATUS_FINAL, M1, M2, M3, M4, M5, M6, M7, M8, FINAL_TOTAL, COURSE_TOTAL, LETTER_GRADE, GRADE_POINT, ROLL, MARKS.STD_ID, DATETIME, ENCRYPTED_TEXT, IS_MODIFIED, GPS_ID FROM '.$db_prefix.'.IMP_REQUEST_COURSE
            JOIN '.$db_prefix.'.MARKS ON MARKS.COURSE_ID = IMP_REQUEST_COURSE.COURSE_ID AND MARKS.STD_ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.IMP_REQUEST ON IMP_REQUEST.ID = IMP_REQUEST_COURSE.IMP_RQ
            WHERE IMP_REQUEST_COURSE.COURSE_ID = '.$course_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".' AND IMP_REQUEST.IES_ID = '.$ies_id.'
        ');
    }

    public static function imp_assigned_batches($ies_id, $assigned_teacher)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT DISTINCT S_BATCH.ID, S_BATCH.BATCH_NAME
            FROM '.$db_prefix.'.IMP_COURSE_TEACHER_ASSING
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.COURSE_ID = IMP_COURSE_TEACHER_ASSING.COURSE_ID
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST_COURSE.STD_ID AND STUDENT.CAMPUS_ID = IMP_COURSE_TEACHER_ASSING.CAMPUS_ID
            JOIN '.$db_prefix.'.S_BATCH ON S_BATCH.ID = STUDENT.BATCH_ID AND S_BATCH.DEPARTMENT_ID = STUDENT.DEPARTMENT_ID
            WHERE IES_ID = '.$ies_id.' AND ASSIGN_TEACHER_ID = '.$assigned_teacher.'
        ');
    }

    public static function imp_assigned_courses_via_batch($ies_id, $batch_id, $assigned_teacher)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT IMP_REQUEST_COURSE.COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.COURSE_TYPE, IMP_REQUEST_COURSE.TYPE, STUDENT.CAMPUS_ID FROM '.$db_prefix.'.IMP_REQUEST 
            JOIN '.$db_prefix.'.IMP_REQUEST_COURSE ON IMP_REQUEST_COURSE.IMP_RQ = IMP_REQUEST.ID 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.COURSE ON COURSE.ID = IMP_REQUEST_COURSE.COURSE_ID 
            JOIN '.$db_prefix.'.IMP_COURSE_TEACHER_ASSING ON IMP_COURSE_TEACHER_ASSING.COURSE_ID = IMP_REQUEST_COURSE.COURSE_ID 
            WHERE IMP_REQUEST.IES_ID = '.$ies_id.' AND STUDENT.BATCH_ID = '.$batch_id.' AND ASSIGN_TEACHER_ID = '.$assigned_teacher.' AND MARKS_FINAL_SUBMITTED = 1
            GROUP BY IMP_REQUEST_COURSE.COURSE_ID, COURSE.NAME, COURSE.CODE, COURSE.COURSE_TYPE, IMP_REQUEST_COURSE.TYPE, STUDENT.CAMPUS_ID
        ');
    }

    public static function imp_requested_students_via_batch($course_id, $campus_id, $type, $ies_id, $batch_id)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT STUDENT.NAME, STUDENT.ID, STUDENT.ROLL_NO, STUDENT.REG_CODE, S_BATCH.BATCH_NAME, IMP_REQUEST_COURSE.COURSE_ID, STUDENT.CAMPUS_ID FROM '.$db_prefix.'.IMP_REQUEST_COURSE 
            JOIN '.$db_prefix.'.IMP_REQUEST ON IMP_REQUEST.ID = IMP_REQUEST_COURSE.IMP_RQ 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.S_BATCH ON S_BATCH.ID = STUDENT.BATCH_ID 
            WHERE IMP_REQUEST_COURSE.COURSE_ID = '.$course_id.' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".' AND IMP_REQUEST.IES_ID = '.$ies_id.' AND S_BATCH.ID = '.$batch_id.'
        ');
    }

    public static function imp_requested_marks_via_batch($course_id, $campus_id, $type, $ies_id, $batch_id)
    {
        $db_prefix = env('O_SCHEMA_PREFIX');
        return DB::connection('oracle')->select('
            SELECT MARKS.ID, STATUS_MID, MT, CT, AP, AB, CONTI_TOTAL, STATUS_FINAL, M1, M2, M3, M4, M5, M6, M7, M8, FINAL_TOTAL, COURSE_TOTAL, LETTER_GRADE, GRADE_POINT, ROLL, MARKS.STD_ID, DATETIME, ENCRYPTED_TEXT, IS_MODIFIED, GPS_ID FROM '.$db_prefix.'.IMP_REQUEST_COURSE
            JOIN '.$db_prefix.'.MARKS ON MARKS.COURSE_ID = IMP_REQUEST_COURSE.COURSE_ID AND MARKS.STD_ID = IMP_REQUEST_COURSE.STD_ID 
            JOIN '.$db_prefix.'.STUDENT ON STUDENT.ID = MARKS.STD_ID 
            JOIN '.$db_prefix.'.IMP_REQUEST ON IMP_REQUEST.ID = IMP_REQUEST_COURSE.IMP_RQ 
            WHERE IMP_REQUEST_COURSE.COURSE_ID = '.$course_id.' AND IMP_REQUEST_COURSE.TYPE = '."'".$type."'".' AND STUDENT.CAMPUS_ID = '.$campus_id.' AND STUDENT.BATCH_ID = '.$batch_id.' AND IMP_REQUEST.IES_ID = '.$ies_id.'
        ');
    }

    public function relStudent()
    {
        return $this->belongsTo(O_STUDENT::class,'std_id', 'id');
    }

    public function relCourse()
    {
        return $this->belongsTo(O_COURSE::class, 'course_id', 'id');
    }

    public function relImpRequest()
    {
        return $this->belongsTo(O_IMP_REQUEST::class, 'imp_rq', 'id');
    }
}
